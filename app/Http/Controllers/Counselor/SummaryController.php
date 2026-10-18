<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateConversationSummary;
use App\Models\CaseNote;
use App\Models\Conversation;
use App\Models\ConversationSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function store(Request $request, Conversation $conversation): JsonResponse|RedirectResponse
    {
        $this->authorize('counsel', $conversation);
        $count = $conversation->messages()->reorder()->where('sender_type', '!=', 'system')->count();
        if ($count < 4) {
            return back()->withErrors(['general' => 'Ringkasan bisa dibuat setelah ada minimal 4 pesan.']);
        }

        $summary = ConversationSummary::create([
            'conversation_id' => $conversation->id,
            'requested_by' => $request->user()->id,
            'status' => 'pending',
            'driver' => config('welcoming.ai.summary_driver'),
        ]);
        GenerateConversationSummary::dispatch($summary->id);

        if ($request->expectsJson()) {
            return response()->json(['id' => $summary->id, 'html' => view('counselor.partials.summary', ['summary' => $summary->fresh(), 'c' => $conversation, 'nonSystemCount' => $count])->render()]);
        }

        return redirect()->to(route('counselor.conversations.show', $conversation).'?tab=ringkasan');
    }

    public function show(Request $request, ConversationSummary $summary): JsonResponse
    {
        $c = $summary->conversation;
        $this->authorize('counsel', $c);

        return response()->json([
            'status' => $summary->status,
            'html' => view('counselor.partials.summary', ['summary' => $summary, 'c' => $c, 'nonSystemCount' => $c->messages()->reorder()->where('sender_type', '!=', 'system')->count()])->render(),
        ]);
    }

    public function copyToNote(Request $request, ConversationSummary $summary): RedirectResponse
    {
        $c = $summary->conversation;
        $this->authorize('counsel', $c);
        abort_unless($summary->status === 'done', 422);
        $s = $summary->content;
        $list = fn ($items) => collect($items)->map(fn ($i) => '- '.$i)->implode("\n");
        $body = "Ringkasan AI ({$summary->created_at->format('d/m H.i')})\n{$s['ringkasan']}\n\nInti masalah:\n".$list($s['inti_masalah'])
            ."\n\nEmosi: ".implode(', ', $s['emosi_terdeteksi'])
            ."\n\nSaran langkah berikutnya:\n".$list($s['saran_langkah_berikutnya'])
            ."\n\nTingkat risiko: {$s['tingkat_risiko']['level']}: {$s['tingkat_risiko']['alasan']}";
        CaseNote::create(['conversation_id' => $c->id, 'counselor_id' => $request->user()->id, 'body' => $body]);

        return redirect()->to(route('counselor.conversations.show', $c).'?tab=catatan')->with('status', 'Ringkasan disalin ke Catatan Privat. Kamu bisa mengeditnya.');
    }
}
