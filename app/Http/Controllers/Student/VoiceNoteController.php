<?php

namespace App\Http\Controllers\Student;

use App\Actions\SendStudentMessageAction;
use App\Http\Controllers\Controller;
use App\Jobs\TranscribeVoiceNote;
use App\Models\Conversation;
use App\Models\VoiceNote;
use App\Services\ConversationMessenger;
use App\Support\WavInspector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VoiceNoteController extends Controller
{
    public function store(Request $request, Conversation $conversation, ConversationMessenger $messenger, SendStudentMessageAction $student, MessageController $messages): JsonResponse
    {
        $this->authorize('reply', $conversation);
        $request->validate([
            'audio' => ['required', 'file', 'mimetypes:audio/wav,audio/x-wav,audio/wave,audio/vnd.wave', 'extensions:wav', 'max:'.config('welcoming.voice_max_kb')],
            'duration' => ['required', 'integer', 'min:1', 'max:'.config('welcoming.voice_max_seconds')],
            'disguise_mode' => ['required', Rule::in(['none', 'deep', 'high', 'robot'])],
        ], [
            'audio.max' => 'Ukuran pesan suara maksimal 5 MB.',
            'audio.mimetypes' => 'Format pesan suara harus WAV.',
            'audio.extensions' => 'Format pesan suara harus WAV.',
            'duration.max' => 'Pesan suara maksimal 2 menit.',
        ]);

        $file = $request->file('audio');
        $seconds = WavInspector::duration($file->getRealPath());
        if ($seconds === null) {
            throw ValidationException::withMessages(['audio' => 'File audio tidak valid.']);
        }
        if ($seconds > config('welcoming.voice_max_seconds') + 1) {
            throw ValidationException::withMessages(['audio' => 'Pesan suara maksimal 2 menit.']);
        }

        $voice = DB::transaction(function () use ($conversation, $messenger, $file, $seconds, $request) {
            $m = $messenger->add($conversation, 'student', 'voice');
            $path = $file->storeAs('voice-notes/'.$conversation->uuid, Str::uuid().'.wav', 'local');

            return VoiceNote::create([
                'message_id' => $m->id,
                'conversation_id' => $conversation->id,
                'file_path' => $path,
                'mime_type' => 'audio/wav',
                'size_bytes' => $file->getSize(),
                'duration_seconds' => max(1, (int) round($seconds)),
                'disguise_mode' => $request->input('disguise_mode'),
                'transcript_status' => 'pending',
            ]);
        });

        TranscribeVoiceNote::dispatch($voice->id);
        $student->after($conversation, $voice->message);

        return response()->json($messages->payload($request, $conversation->fresh()));
    }
}
