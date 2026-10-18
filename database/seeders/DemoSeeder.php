<?php

namespace Database\Seeders;

use App\Contracts\Summarizer;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\CaseNote;
use App\Models\CaseStatusLog;
use App\Models\Conversation;
use App\Models\ConversationFlag;
use App\Models\ConversationSummary;
use App\Models\CounselingSession;
use App\Models\CounselingSlot;
use App\Models\FollowUp;
use App\Models\IdentityRequest;
use App\Models\Message;
use App\Models\MoodEntry;
use App\Models\School;
use App\Models\SessionInvitation;
use App\Models\StoryPost;
use App\Models\StoryReaction;
use App\Models\Topic;
use App\Models\User;
use App\Models\VoiceNote;
use App\Services\AiPayloadSanitizer;
use App\Services\KeywordScanner;
use App\Services\PriorityCalculator;
use App\Support\AliasGenerator;
use App\Support\IdentityHasher;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    protected Collection $topics;

    protected Collection $counselors;

    protected string $tz;

    protected CarbonImmutable $now;

    /** Bank dialog per topik: [pembuka murid, balasan guru, lanjutan murid, balasan guru] */
    protected const DIALOGS = [
        'akademik' => [
            ['Aku lagi ngerasa capek banget karena tugas numpuk dan ujian tinggal dua minggu.', 'Makasih udah cerita. Wajar banget kalau kamu capek. Mata pelajaran mana yang paling bikin kepikiran?', 'Matematika sama Kimia. Aku udah belajar tapi nilaiku tetap jelek, takut ngecewain orang tua.', 'Kita coba pecah jadi langkah kecil ya. Gimana kalau kita susun jadwal belajar bareng?'],
            ['Aku gak tahu ini masalah atau bukan, tapi aku stres banget sama nilai rapor kemarin.', 'Nggak ada cerita yang terlalu kecil. Bagian mana dari nilai rapor yang paling bikin kamu sedih?', 'Ranking aku turun jauh. Orang tuaku diam aja tapi aku tahu mereka kecewa.', 'Perasaanmu valid. Turunnya ranking bukan berarti kamu gagal. Yuk kita lihat apa yang bisa diperbaiki pelan-pelan.'],
            ['Aku butuh saran soal cara belajar. Udah begadang tapi tetap gak masuk.', 'Begadang justru bikin ingatan kurang optimal. Biasanya kamu belajar jam berapa dan berapa lama?', 'Biasanya jam 11 malam sampai jam 2. Paginya ngantuk di kelas.', 'Coba kita geser ke sore dengan teknik 25 menit belajar, 5 menit istirahat. Mau dicoba seminggu?'],
        ],
        'keluarga' => [
            ['Aku lagi ngerasa sedih karena orang tuaku sering bertengkar akhir-akhir ini.', 'Makasih sudah percaya cerita ke aku. Itu pasti berat. Kamu sendiri gimana perasaannya saat mereka bertengkar?', 'Aku takut dan cuma bisa ngurung diri di kamar. Kadang gak bisa tidur.', 'Kamu nggak salah apa-apa. Mengurung diri untuk merasa aman itu wajar. Apa ada orang dewasa lain di rumah yang bisa kamu ajak bicara?'],
            ['Aku butuh saran soal orang tuaku yang maksa aku masuk jurusan yang gak aku suka.', 'Pasti bingung ya, di antara keinginanmu dan harapan orang tua. Jurusan apa yang sebenarnya kamu minati?', 'Aku pengen desain grafis, tapi ayah maunya kedokteran.', 'Gimana kalau kita siapkan data soal desain grafis, lalu latihan cara menyampaikannya ke ayah?'],
        ],
        'pertemanan' => [
            ['Aku lagi ngerasa sendirian karena teman-teman satu circle tiba-tiba jauhin aku.', 'Ditinggal circle itu sakit banget. Kamu tahu nggak kira-kira apa yang terjadi?', 'Gak tahu, mereka bikin grup baru tanpa aku. Aku dikucilkan di kelas.', 'Terima kasih sudah cerita. Kalau kamu sengaja dikucilkan, itu bukan hal yang boleh dibiarkan. Kita bisa pikirkan langkah bareng.'],
            ['Aku gak tahu ini masalah atau bukan, tapi aku susah bilang nggak kalau teman minta contekan.', 'Itu masalah yang penting kok. Apa yang biasanya kamu rasakan setelah memberi contekan?', 'Kesel sama diri sendiri, tapi takut dijauhi kalau nolak.', 'Kita latihan cara menolak yang tetap hangat, yuk. Teman yang baik akan menghargai batasanmu.'],
        ],
        'asmara' => [
            ['Aku butuh saran soal pacarku yang suka ngecek HP aku terus.', 'Makasih sudah cerita. Gimana perasaanmu ketika dia melakukan itu?', 'Gak nyaman, tapi dia bilang itu tanda sayang.', 'Dalam hubungan yang sehat, kamu berhak punya privasi dan merasa aman. Yuk kita bahas batasan yang kamu inginkan.'],
        ],
        'bullying' => [
            ['Aku dibully sama kakak kelas, sering diejek terus di kantin.', 'Terima kasih sudah berani cerita. Apa yang kamu alami bukan salahmu. Sudah berapa lama ini terjadi?', 'Udah sebulan. Kemarin aku dipalak juga.', 'Keselamatanmu nomor satu. Aku ingin membantu menghentikan ini. Mau kita bicarakan langkah yang aman untukmu?'],
            ['Ada teman sekelas yang nyebarin foto aku buat diolok olok di grup.', 'Itu tidak boleh terjadi padamu. Apakah kamu masih menyimpan bukti tangkapan layarnya?', 'Masih. Aku malu banget masuk kelas.', 'Simpan baik-baik ya. Kamu tidak perlu menghadapi ini sendirian. Kita atur langkah perlindungan bareng.'],
        ],
        'diri-sendiri' => [
            ['Aku lagi ngerasa gak berguna karena selalu dibandingin sama sepupuku.', 'Dibanding-bandingkan itu menyakitkan. Kamu sendiri suka melakukan apa?', 'Aku suka gambar, tapi katanya itu gak penting.', 'Gambarmu penting karena itu bagian dari dirimu. Mau cerita lebih banyak soal karya-karyamu?'],
            ['Aku gak tahu ini masalah atau bukan, tapi akhir-akhir ini aku nangis terus tanpa alasan.', 'Terima kasih sudah cerita. Menangis tanpa tahu sebabnya bisa jadi tanda kamu lelah. Sejak kapan kamu merasakannya?', 'Sejak awal semester. Aku juga gak bisa tidur.', 'Aku senang kamu cerita sekarang. Kita pelan-pelan cari tahu bareng ya, kamu nggak sendirian.'],
        ],
        'masa-depan' => [
            ['Aku butuh saran soal kuliah. Bingung mau ambil jurusan apa.', 'Bingung itu wajar banget. Pelajaran apa yang paling kamu nikmati?', 'Biologi sama Bahasa Inggris. Tapi gak tahu kerjanya nanti apa.', 'Banyak pilihan yang menggabungkan keduanya. Mau kita jadwalkan sesi eksplorasi minat?'],
        ],
        'belum-tahu' => [
            ['Aku gak tahu ini masalah atau bukan, tapi semuanya terasa campur aduk.', 'Nggak apa-apa kalau belum tahu harus mulai dari mana. Yang paling terasa berat sekarang apa?', 'Sekolah, rumah, teman. Semuanya. Aku capek hidup kayak gini.', 'Terima kasih sudah jujur. Kita urai satu per satu ya. Aku di sini.'],
        ],
    ];

    public function run(KeywordScanner $scanner, PriorityCalculator $priority): void
    {
        $school = School::current();
        $this->tz = $school->timezone;
        $this->now = CarbonImmutable::now();
        $this->topics = Topic::all()->keyBy('slug')->toBase();
        $this->counselors = User::counselors()->inSchool($school)->with('counselorProfile')->orderBy('id')->get();
        fake()->seed(42);
        mt_srand(42);

        $this->seedMoods();
        $this->seedSlots();

        $students = User::students()->with('studentProfile')->where('email', '!=', 'murid1@contoh.id')->get()->values();
        $alya = User::students()->with('studentProfile')->where('email', 'murid1@contoh.id')->first();

        // Distribusi bulan (0 = bulan ini) → daftar topik. Akademik melonjak bulan ini agar memicu insight.
        $plan = [
            0 => ['akademik', 'akademik', 'akademik', 'akademik', 'akademik', 'akademik', 'akademik', 'akademik', 'keluarga', 'keluarga', 'pertemanan', 'bullying', 'diri-sendiri', 'belum-tahu'],
            1 => ['akademik', 'akademik', 'akademik', 'akademik', 'keluarga', 'keluarga', 'keluarga', 'pertemanan', 'pertemanan', 'pertemanan', 'asmara', 'masa-depan', 'masa-depan'],
            2 => ['akademik', 'akademik', 'keluarga', 'pertemanan', 'pertemanan', 'bullying', 'diri-sendiri', 'masa-depan', 'asmara', 'keluarga'],
            3 => ['akademik', 'keluarga', 'pertemanan', 'diri-sendiri', 'masa-depan', 'bullying', 'akademik', 'asmara'],
            4 => ['akademik', 'keluarga', 'pertemanan', 'masa-depan', 'diri-sendiri', 'akademik', 'belum-tahu'],
            5 => ['akademik', 'pertemanan', 'keluarga', 'masa-depan', 'diri-sendiri', 'akademik', 'bullying', 'asmara'],
        ];

        $i = 0;
        foreach ($plan as $monthsAgo => $topicList) {
            foreach ($topicList as $j => $slug) {
                $student = $students[$i % $students->count()];
                $i++;
                $created = $this->dateInMonth($monthsAgo, $j, count($topicList));
                $isActive = $monthsAgo === 0 || ($monthsAgo === 1 && $j % 3 === 0);
                $status = $isActive ? (['new', 'monitoring', 'monitoring', 'referred_parent'][$j % 4]) : 'resolved';
                if ($monthsAgo === 0 && $j < 3) {
                    $status = 'new';
                }
                $this->makeConversation($student, $this->counselors[$i % 3], [$slug, ...($j % 5 === 0 && $slug !== 'belum-tahu' ? ['diri-sendiri'] : [])], $created, [
                    'urgency' => ['normal', 'important', 'normal', 'urgent', 'normal', 'important'][$j % 6],
                    'anonymous' => $j % 3 !== 1,
                    'status' => $status,
                    'dialog' => self::DIALOGS[$slug][$j % count(self::DIALOGS[$slug])],
                    'unanswered' => $monthsAgo === 0 && $j % 4 === 0,
                ], $scanner, $priority);
            }
        }

        // Percakapan khusus: kasus kritis (flag severity 3) yang belum dibalas, tampil paling atas di inbox Bu Rina.
        $this->makeConversation($students[5], $this->counselors[0], ['belum-tahu', 'diri-sendiri'], $this->now->subMinutes(45), [
            'urgency' => 'urgent', 'anonymous' => true, 'status' => 'new', 'unanswered' => true,
            'dialog' => ['Aku udah gak kuat lagi. Kadang aku pengen mati aja biar semuanya selesai.'],
        ], $scanner, $priority);

        // Percakapan milik murid demo (Alya): satu aktif anonim dengan Bu Rina, satu selesai dengan sesi tatap muka.
        $active = $this->makeConversation($alya, $this->counselors[0], ['pertemanan'], $this->now->subDays(5)->setTime(10, 0), [
            'urgency' => 'important', 'anonymous' => true, 'status' => 'monitoring',
            'dialog' => ['Aku lagi ngerasa sendirian karena teman-teman satu circle tiba-tiba jauhin aku.', 'Makasih udah mau cerita, aku dengerin ya. Pelan-pelan aja. Sejak kapan kamu merasa mereka menjauh?', 'Sejak pembagian kelompok tugas minggu lalu. Mereka bikin grup baru tanpa aku.', 'Pasti rasanya bingung dan sedih ya. Perasaanmu valid. Mau cerita apa yang terjadi saat pembagian kelompok itu?'],
            'voice' => true,
        ], $scanner, $priority);
        $this->addArticleMessage($active, 'ditinggal-circle-saat-pertemanan-berubah', $this->now->subDays(4)->setTime(9, 0));

        $past = $this->makeConversation($alya, $this->counselors[0], ['akademik'], $this->now->subMonths(2)->setTime(9, 30), [
            'urgency' => 'normal', 'anonymous' => false, 'status' => 'resolved',
            'dialog' => self::DIALOGS['akademik'][2],
        ], $scanner, $priority);
        $this->makeSession($past, $alya, 'completed', $this->now->subMonths(2)->addDays(3)->setTime(10, 0));

        // Sesi & undangan untuk percakapan teridentifikasi yang masih aktif.
        $revealedActive = Conversation::where('status', '!=', 'resolved')->whereNotNull('student_id')->with('counselor')->get();
        foreach ($revealedActive->take(4) as $k => $c) {
            $student = User::find($c->student_id);
            if ($k < 2) {
                $this->makeSession($c, $student, 'scheduled', null);
            } else {
                $this->makeInvitation($c);
            }
        }

        // Permintaan buka identitas yang masih pending di salah satu percakapan anonim.
        $anonActive = Conversation::where('status', 'monitoring')->where('is_anonymous', true)->whereNull('student_id')->where('id', '!=', $active->id)->first();
        if ($anonActive) {
            $req = IdentityRequest::create(['conversation_id' => $anonActive->id, 'counselor_id' => $anonActive->counselor_id, 'message' => 'Boleh aku mengenalmu lebih dekat? Supaya aku bisa membantu lebih baik.', 'status' => 'pending']);
            $this->msg($anonActive, 'counselor', 'identity_request', $req->message, ['identity_request_id' => $req->id], $this->now->subHours(3), $anonActive->counselor_id);
        }

        // Ringkasan AI untuk beberapa percakapan.
        $summarizer = app(Summarizer::class);
        $sanitizer = app(AiPayloadSanitizer::class);
        foreach (Conversation::where('status', '!=', 'resolved')->has('messages', '>=', 4)->with('topics')->take(4)->get() as $c) {
            $msgs = $c->messages()->with('voiceNote')->get();
            $payload = $sanitizer->build($c, $msgs);
            ConversationSummary::create([
                'conversation_id' => $c->id, 'requested_by' => $c->counselor_id, 'status' => 'done',
                'content' => $summarizer->summarize($payload['messages'], $payload['context']),
                'last_message_id' => $msgs->last()->id, 'message_count' => $msgs->count(), 'driver' => $summarizer->name(),
            ]);
        }

        // Catatan privat contoh.
        foreach (Conversation::where('status', 'monitoring')->take(5)->get() as $c) {
            CaseNote::create(['conversation_id' => $c->id, 'counselor_id' => $c->counselor_id, 'body' => 'Murid terlihat lebih terbuka di pesan kedua. Pantau perkembangan minggu depan, tawarkan sesi tatap muka bila perlu.']);
        }

        foreach (Conversation::all() as $c) {
            $priority->refresh($c);
        }

        $this->seedStoryPosts();
        $this->seedLoginAudit();
    }

    protected function dateInMonth(int $monthsAgo, int $index, int $count): CarbonImmutable
    {
        $local = $this->now->setTimezone($this->tz);
        if ($monthsAgo === 0) {
            $startDay = 1;
            $endDay = max(1, $local->day);
        } else {
            $m = $local->startOfMonth()->subMonths($monthsAgo);
            $startDay = 1;
            $endDay = $m->daysInMonth;
        }
        $base = $local->startOfMonth()->subMonths($monthsAgo);
        $day = $startDay + (int) floor(($endDay - $startDay) * ($index + 0.5) / $count);
        $dt = $base->setDay(max(1, $day))->setTime(8 + ($index * 3) % 9, ($index * 17) % 60);
        if ($dt->greaterThan($local)) {
            $dt = $local->subHours(2 + $index);
        }

        return $dt->setTimezone('UTC');
    }

    protected function makeConversation(User $student, User $counselor, array $topicSlugs, CarbonImmutable $created, array $o, KeywordScanner $scanner, PriorityCalculator $priority): Conversation
    {
        $alias = AliasGenerator::make();
        $anonymous = $o['anonymous'];
        $c = Conversation::create([
            'uuid' => (string) Str::uuid(),
            'school_id' => $student->school_id,
            'counselor_id' => $counselor->id,
            'student_ref' => (string) $student->id,
            'student_hash' => IdentityHasher::hash($student->id),
            'student_id' => $anonymous ? null : $student->id,
            'grade_level' => $student->studentProfile->grade_level,
            'is_anonymous' => $anonymous,
            'anonymous_alias' => $alias['alias'],
            'anonymous_avatar' => $alias['avatar'],
            'urgency' => $o['urgency'],
            'status' => 'new',
            'created_at' => $created,
            'updated_at' => $created,
        ]);
        $c->topics()->sync($this->topics->only($topicSlugs)->pluck('id'));
        CaseStatusLog::create(['conversation_id' => $c->id, 'changed_by' => null, 'from_status' => null, 'to_status' => 'new', 'note' => 'Percakapan dimulai', 'created_at' => $created]);

        $dialog = $o['dialog'];
        if (! empty($o['unanswered'])) {
            $dialog = [$dialog[0]];
        }
        $t = $created;
        $firstResponse = null;
        foreach ($dialog as $n => $text) {
            $fromStudent = $n % 2 === 0;
            $m = $this->msg($c, $fromStudent ? 'student' : 'counselor', 'text', $text, null, $t, $fromStudent ? null : $counselor->id);
            if ($fromStudent) {
                foreach ($scanner->scan($text, $c->school_id) as $kw) {
                    ConversationFlag::create([
                        'conversation_id' => $c->id, 'message_id' => $m->id, 'sensitive_keyword_id' => $kw->id, 'severity' => $kw->severity,
                        'reviewed_at' => $o['status'] === 'resolved' ? $t->addHour() : null, 'reviewed_by' => $o['status'] === 'resolved' ? $counselor->id : null,
                        'created_at' => $t, 'updated_at' => $t,
                    ]);
                    if ($kw->severity === 3) {
                        $this->msg($c, 'system', 'emergency_info', 'Kamu tidak sendirian. Kalau kamu sedang dalam bahaya, segera hubungi kontak darurat di bawah ini.', null, $t->addSecond());
                    }
                }
            } elseif (! $firstResponse) {
                $firstResponse = $t;
            }
            $t = $t->addMinutes(8 + ($n * 13) % 50);
        }

        if (! empty($o['voice'])) {
            $this->addVoiceNote($c, $t);
            $t = $t->addMinutes(5);
        }

        $status = $o['status'];
        $resolvedAt = null;
        if ($status !== 'new' || $firstResponse) {
            if ($firstResponse) {
                CaseStatusLog::create(['conversation_id' => $c->id, 'changed_by' => $counselor->id, 'from_status' => 'new', 'to_status' => 'monitoring', 'note' => 'Otomatis saat guru membalas', 'created_at' => $firstResponse]);
            }
            if (in_array($status, ['referred_parent', 'resolved'], true)) {
                CaseStatusLog::create(['conversation_id' => $c->id, 'changed_by' => $counselor->id, 'from_status' => 'monitoring', 'to_status' => $status, 'created_at' => $t]);
            }
            if ($status === 'new' && $firstResponse) {
                $status = 'monitoring';
            }
        }
        if ($status === 'resolved') {
            $resolvedAt = $t;
            $this->msg($c, 'system', 'system', 'Ceritamu ditandai selesai. Kalau butuh lagi, kamu bisa kirim pesan kapan saja ya.', null, $t);
            $sched = $t->addDays(config('welcoming.follow_up_days'));
            $sent = $sched->lessThan($this->now);
            $fu = FollowUp::create([
                'conversation_id' => $c->id, 'scheduled_at' => $sched, 'sent_at' => $sent ? $sched : null,
                'response' => $sent ? ['better', 'better', 'same'][$c->id % 3] : null, 'responded_at' => $sent ? $sched->addHours(5) : null,
            ]);
            if ($sent) {
                $this->msg($c, 'system', 'follow_up', 'Hai, gimana kabarmu sekarang?', ['follow_up_id' => $fu->id], $sched);
            }
        }

        $last = $c->messages()->reorder()->latest('id')->first();
        $c->forceFill([
            'status' => $status,
            'resolved_at' => $resolvedAt,
            'first_response_at' => $firstResponse,
            'last_message_at' => $last?->created_at,
            'last_student_message_at' => $c->messages()->reorder()->where('sender_type', 'student')->latest('id')->value('created_at'),
            'last_counselor_message_at' => $c->messages()->reorder()->where('sender_type', 'counselor')->latest('id')->value('created_at'),
        ])->saveQuietly();

        // pesan lama sudah dibaca
        $c->messages()->where('created_at', '<', $this->now->subHours(6))->update(['read_at' => $this->now->subHours(5)]);

        return $c;
    }

    protected function msg(Conversation $c, string $sender, string $type, ?string $body, ?array $meta, CarbonImmutable $at, ?int $senderId = null): Message
    {
        $m = new Message(['conversation_id' => $c->id, 'sender_type' => $sender, 'sender_id' => $senderId, 'type' => $type, 'body' => $body, 'meta' => $meta]);
        $m->created_at = $at;
        $m->updated_at = $at;
        $m->save();

        return $m;
    }

    protected function addArticleMessage(Conversation $c, string $slug, CarbonImmutable $at): void
    {
        $article = Article::where('slug', $slug)->first();
        if ($article) {
            $this->msg($c, 'counselor', 'article', 'Aku nemu bacaan yang mungkin pas buat kamu.', ['article_id' => $article->id], $at, $c->counselor_id);
        }
    }

    protected function addVoiceNote(Conversation $c, CarbonImmutable $at): void
    {
        $m = $this->msg($c, 'student', 'voice', null, null, $at);
        $path = 'voice-notes/'.$c->uuid.'/'.Str::uuid().'.wav';
        $wav = self::sineWav(4);
        Storage::disk('local')->put($path, $wav);
        VoiceNote::create([
            'message_id' => $m->id, 'conversation_id' => $c->id, 'file_path' => $path, 'mime_type' => 'audio/wav',
            'size_bytes' => strlen($wav), 'duration_seconds' => 4, 'disguise_mode' => 'deep',
            'transcript' => '(Transkrip tidak tersedia dalam mode demo)', 'transcript_status' => 'skipped',
        ]);
    }

    /** WAV mono 16 kHz berisi nada lembut (dummy). */
    public static function sineWav(int $seconds): string
    {
        $rate = 16000;
        $samples = '';
        $n = $rate * $seconds;
        for ($i = 0; $i < $n; $i++) {
            $env = min(1, $i / 1600, ($n - $i) / 1600);
            $v = (int) (sin(2 * M_PI * 220 * $i / $rate) * 6000 * $env * (0.6 + 0.4 * sin($i / 3000)));
            $samples .= pack('v', $v & 0xFFFF);
        }
        $len = strlen($samples);

        return 'RIFF'.pack('V', 36 + $len).'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, $rate, $rate * 2, 2, 16).'data'.pack('V', $len).$samples;
    }

    protected function makeSession(Conversation $c, User $student, string $status, ?CarbonImmutable $start): void
    {
        if ($start) {
            $slot = CounselingSlot::create(['counselor_id' => $c->counselor_id, 'start_at' => $start, 'end_at' => $start->addMinutes(45), 'location' => 'Ruang BK', 'status' => 'booked']);
        } else {
            $slot = CounselingSlot::where('counselor_id', $c->counselor_id)->where('status', 'available')->where('start_at', '>', $this->now->addDay())->orderBy('start_at')->first();
            if (! $slot) {
                return;
            }
            $slot->update(['status' => 'booked']);
        }
        $c->forceFill(['student_id' => $student->id, 'revealed_to_counselor_id' => $c->is_anonymous ? $c->counselor_id : null, 'identity_revealed_at' => $c->is_anonymous ? $this->now->subDay() : null])->saveQuietly();
        $inv = SessionInvitation::create(['conversation_id' => $c->id, 'counselor_id' => $c->counselor_id, 'message' => 'Gimana kalau kita ngobrol langsung? Pilih waktu yang nyaman ya.', 'status' => 'accepted', 'expires_at' => $slot->start_at->copy()->addDays(7), 'responded_at' => $slot->start_at->copy()->subDays(2)]);
        $inv->slots()->attach($slot->id);
        $this->msg($c, 'counselor', 'session_invite', $inv->message, ['invitation_id' => $inv->id], CarbonImmutable::parse($slot->start_at)->subDays(3)->min($this->now->subHour()), $c->counselor_id);
        CounselingSession::create([
            'uuid' => (string) Str::uuid(), 'conversation_id' => $c->id, 'session_invitation_id' => $inv->id, 'counseling_slot_id' => $slot->id,
            'counselor_id' => $c->counselor_id, 'student_id' => $student->id, 'status' => $status,
        ]);
    }

    protected function makeInvitation(Conversation $c): void
    {
        $slots = CounselingSlot::where('counselor_id', $c->counselor_id)->where('status', 'available')->where('start_at', '>', $this->now->addDays(2))->orderBy('start_at')->take(3)->get();
        if ($slots->isEmpty()) {
            return;
        }
        $inv = SessionInvitation::create(['conversation_id' => $c->id, 'counselor_id' => $c->counselor_id, 'message' => 'Mau ngobrol langsung di sekolah? Ini beberapa pilihan waktunya.', 'status' => 'pending', 'expires_at' => $this->now->addDays(6)]);
        $inv->slots()->attach($slots->pluck('id'));
        $this->msg($c, 'counselor', 'session_invite', $inv->message, ['invitation_id' => $inv->id], $this->now->subHours(20), $c->counselor_id);
    }

    protected function seedSlots(): void
    {
        $local = $this->now->setTimezone($this->tz)->startOfDay();
        foreach ($this->counselors as $k => $counselor) {
            for ($d = 1; $d <= 14; $d++) {
                $day = $local->addDays($d);
                if ($day->isWeekend()) {
                    continue;
                }
                foreach ([[9, 0], [10, 30], [13, 0]] as $s => [$h, $m]) {
                    if (($d + $s + $k) % 5 === 0) {
                        continue;
                    }
                    $start = $day->setTime($h, $m);
                    CounselingSlot::create([
                        'counselor_id' => $counselor->id, 'start_at' => $start->setTimezone('UTC'), 'end_at' => $start->addMinutes(45)->setTimezone('UTC'),
                        'location' => 'Ruang BK', 'status' => ($d + $s) % 11 === 0 ? 'blocked' : 'available',
                    ]);
                }
            }
        }
    }

    protected function seedMoods(): void
    {
        $today = $this->now->setTimezone($this->tz)->startOfDay();
        $students = User::students()->orderBy('id')->get();
        $rows = [];
        foreach ($students as $idx => $s) {
            if ($s->email === 'murid1@contoh.id') {
                for ($d = 60; $d >= 1; $d--) {
                    $mood = match (true) {
                        $d <= 3 => ['anxious', 'sad', 'anxious'][$d - 1],
                        default => ['happy', 'neutral', 'happy', 'neutral', 'sad', 'happy', 'anxious'][$d % 7],
                    };
                    $note = match ($d) {
                        1 => 'Masih kepikiran soal teman-teman. Susah fokus di kelas.',
                        2 => 'Makan siang sendirian lagi hari ini.',
                        3 => 'Deg-degan mau presentasi, takut salah.',
                        default => $d % 9 === 0 ? 'Hari yang lumayan, sempat ngobrol sama adik.' : null,
                    };
                    $rows[] = $this->moodRow($s->id, $today->subDays($d), $mood, $note);
                }

                continue;
            }
            if ($idx % 4 === 3) {
                continue; // sebagian murid tidak rutin check-in
            }
            for ($d = 60; $d >= 0; $d--) {
                if (($d + $idx) % 3 === 0) {
                    continue;
                }
                $r = mt_rand(1, 100);
                $mood = $r <= 32 ? 'happy' : ($r <= 64 ? 'neutral' : ($r <= 78 ? 'anxious' : ($r <= 92 ? 'sad' : 'angry')));
                $rows[] = $this->moodRow($s->id, $today->subDays($d), $mood, null);
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            MoodEntry::insert($chunk);
        }
    }

    protected function moodRow(int $studentId, CarbonImmutable $date, string $mood, ?string $note): array
    {
        $at = $date->setTime(19, 0)->setTimezone('UTC');

        return [
            'student_id' => $studentId, 'entry_date' => $date->toDateString(), 'mood' => $mood,
            'note' => $note ? encrypt($note, false) : null, 'created_at' => $at, 'updated_at' => $at,
        ];
    }

    /** Cerita Teman contoh: sebagian sudah tampil (dengan reaksi), sebagian menunggu tinjauan Guru BK. */
    protected function seedStoryPosts(): void
    {
        $approved = [
            ['Dulu aku takut banget ketahuan cerita ke BK. Ternyata bisa mulai tanpa nama, dan dibalasnya pelan-pelan tanpa dihakimi. Kalau kamu lagi ragu, coba aja dulu.', 'Murid kelas 11'],
            ['Aku pernah dikucilkan di kelas sampai malas sekolah. Yang membantuku: cerita ke satu orang yang kupercaya dan ikut ekskul baru. Pelan-pelan aku punya teman lagi.', 'Seorang teman'],
            ['Waktu ujian aku sering nggak bisa tidur. Teknik napas 4-7-8 dari Self-Help lumayan bantu. Kamu nggak sendirian kok.', 'Murid kelas 12'],
            ['Orang tuaku sering bertengkar dan aku capek di rumah. Ngobrol sama Guru BK bikin aku tahu itu bukan salahku.', 'Seorang teman'],
        ];
        $schoolId = $this->counselors->first()->school_id;
        $moderator = $this->counselors->first()->id;
        foreach ($approved as $k => [$body, $label]) {
            $post = StoryPost::create(['school_id' => $schoolId, 'author_hash' => hash('sha256', 'demo-story-'.$k), 'display_label' => $label, 'body' => $body, 'status' => 'approved', 'moderated_by' => $moderator, 'moderated_at' => $this->now->subDays(12 - $k * 2)]);
            foreach (array_keys(StoryPost::REACTIONS) as $t => $type) {
                foreach (range(1, (($k + 2) * ($t + 1)) % 7 + 1) as $r) {
                    StoryReaction::create(['story_post_id' => $post->id, 'reactor_hash' => hash('sha256', "demo-reactor-{$k}-{$type}-{$r}"), 'type' => $type]);
                }
            }
        }
        StoryPost::create(['school_id' => $schoolId, 'author_hash' => hash('sha256', 'demo-story-pending-1'), 'display_label' => 'Murid kelas 10', 'body' => 'Aku baru pindah ke sekolah ini dan masih susah dapat teman. Ada yang pernah ngerasain hal yang sama?', 'status' => 'pending']);
        StoryPost::create(['school_id' => $schoolId, 'author_hash' => hash('sha256', 'demo-story-pending-2'), 'display_label' => 'Seorang teman', 'body' => 'Makasih buat siapa pun yang nulis soal overthinking minggu lalu. Aku jadi berani cerita ke Guru BK.', 'status' => 'pending']);
    }

    protected function seedLoginAudit(): void
    {
        $users = User::pluck('id')->all();
        $rows = [];
        for ($m = 5; $m >= 0; $m--) {
            $count = 80 + (5 - $m) * 25 + ($m % 2) * 15;
            for ($k = 0; $k < $count; $k++) {
                $at = $this->now->subMonths($m)->startOfMonth()->addMinutes(mt_rand(0, ($m === 0 ? max(1, $this->now->day - 1) : 27) * 1440));
                $rows[] = ['user_id' => $users[array_rand($users)], 'action' => 'login', 'ip' => '127.0.0.1', 'created_at' => $at->min($this->now)];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            AuditLog::insert($chunk);
        }
    }
}
