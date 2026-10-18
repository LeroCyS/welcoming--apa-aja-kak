<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\VoiceNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VoiceNote> */
class VoiceNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'message_id' => Message::factory()->state(['type' => 'voice']),
            'conversation_id' => fn (array $a) => Message::find($a['message_id'])->conversation_id,
            'file_path' => 'voice-notes/demo.wav',
            'mime_type' => 'audio/wav',
            'size_bytes' => 32000,
            'duration_seconds' => 5,
            'disguise_mode' => 'deep',
            'transcript_status' => 'pending',
        ];
    }
}
