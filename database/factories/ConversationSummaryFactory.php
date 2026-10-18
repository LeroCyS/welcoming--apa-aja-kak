<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConversationSummary> */
class ConversationSummaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'requested_by' => fn (array $a) => Conversation::find($a['conversation_id'])->counselor_id,
            'status' => 'done',
            'content' => ['ringkasan' => fake()->sentence(), 'inti_masalah' => [], 'emosi_terdeteksi' => [], 'tindakan_sudah_dilakukan' => [], 'saran_langkah_berikutnya' => [], 'tingkat_risiko' => ['level' => 'rendah', 'alasan' => '-']],
            'message_count' => 4,
            'driver' => 'fake',
        ];
    }
}
