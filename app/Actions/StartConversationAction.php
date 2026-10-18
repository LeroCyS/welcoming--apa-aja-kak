<?php

namespace App\Actions;

use App\Models\CaseStatusLog;
use App\Models\Conversation;
use App\Models\Topic;
use App\Models\User;
use App\Services\ConversationMessenger;
use App\Services\CounselorPicker;
use App\Support\AliasGenerator;
use App\Support\IdentityHasher;
use App\Support\Notify;
use App\Support\Realtime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StartConversationAction
{
    public function __construct(protected CounselorPicker $picker, protected ConversationMessenger $messenger) {}

    /**
     * @param  array{counselor_id: ?int, topics: list<int>, urgency: ?string, is_anonymous: bool, message?: ?string}  $data
     */
    public function execute(User $student, array $data): Conversation
    {
        $hash = IdentityHasher::hash($student->id);
        $todayCount = Conversation::where('student_hash', $hash)->where('is_emergency', false)
            ->where('created_at', '>=', now()->startOfDay())->count();
        if (empty($data['is_emergency']) && $todayCount >= config('welcoming.max_new_conversations_per_day')) {
            throw ValidationException::withMessages(['message' => 'Kamu sudah memulai 5 cerita hari ini. Lanjutkan di cerita yang sudah ada dulu, ya.']);
        }

        $counselor = ! empty($data['counselor_id'])
            ? User::counselors()->inSchool($student->school_id)->active()->findOrFail($data['counselor_id'])
            : $this->picker->pick($student->school_id);
        if (! $counselor) {
            throw ValidationException::withMessages(['counselor_id' => 'Belum ada Guru BK yang aktif. Coba lagi nanti atau buka Bantuan Darurat jika mendesak.']);
        }

        $conversation = DB::transaction(function () use ($student, $data, $counselor, $hash) {
            $alias = AliasGenerator::make();
            $c = Conversation::create([
                'uuid' => (string) Str::uuid(),
                'school_id' => $student->school_id,
                'counselor_id' => $counselor->id,
                'student_ref' => (string) $student->id,
                'student_hash' => $hash,
                'student_id' => $data['is_anonymous'] ? null : $student->id,
                'grade_level' => $student->studentProfile?->grade_level ?? 0,
                'is_anonymous' => $data['is_anonymous'],
                'anonymous_alias' => $alias['alias'],
                'anonymous_avatar' => $alias['avatar'],
                'urgency' => $data['urgency'],
                'status' => 'new',
                'is_emergency' => (bool) ($data['is_emergency'] ?? false),
                'from_new_member' => $student->isNewSchoolMember(),
            ]);
            $c->topics()->sync(Topic::whereIn('id', $data['topics'])->pluck('id'));
            CaseStatusLog::create(['conversation_id' => $c->id, 'to_status' => 'new', 'note' => 'Percakapan dimulai', 'created_at' => now()]);

            if (! empty($data['system_first'])) {
                $this->messenger->system($c, $data['system_first']);
            }
            if (filled($data['message'] ?? null)) {
                $m = $this->messenger->add($c, 'student', 'text', $data['message']);
                $severity = $this->messenger->scan($c, $data['message'], $m);
            }

            if ($data['urgency'] === 'urgent' || ($severity ?? 0) >= 3 || $c->is_emergency) {
                $this->messenger->sendEmergencyInfo($c);
            }
            $this->messenger->maybeAutoReply($c);
            $this->messenger->refreshPriority($c);

            return $c;
        });

        $label = $conversation->is_emergency ? 'Kasus kritis: murid menekan tombol Bantuan Darurat' : 'Cerita baru dari '.$conversation->displayNameFor($counselor);
        Notify::user($counselor, $conversation->is_emergency ? 'critical' : 'new_conversation', $label, route('counselor.conversations.show', $conversation), $conversation->is_emergency || $conversation->urgency === 'urgent');
        Realtime::counselor($counselor->id, 'NewConversationAssigned', ['conversation' => $conversation->uuid]);

        return $conversation;
    }
}
