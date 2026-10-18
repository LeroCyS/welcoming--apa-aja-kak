<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\StoryPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Guru BK meninjau Cerita Teman di sekolahnya: tampilkan, tolak (dengan alasan untuk murid), atau turunkan cerita yang sudah tampil. */
class StoryModerationController extends Controller
{
    public function moderate(Request $request, StoryPost $story): RedirectResponse
    {
        $this->ensureSameSchool($story);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $story->update([
            'status' => $data['decision'],
            'reject_reason' => $data['decision'] === 'rejected' ? ($data['reason'] ?? null) : null,
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);

        return redirect()->route('counselor.selfhelp.index', ['tab' => 'cerita-teman'])
            ->with('status', $data['decision'] === 'approved' ? 'Cerita ditampilkan untuk murid di sekolahmu.' : 'Cerita tidak ditampilkan.');
    }
}
