<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\CaseBoardColumn;
use App\Models\Conversation;
use App\Services\CaseStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Kolom kanban buatan Guru BK sendiri & pemindahan kartu di papan kasus. */
class CaseBoardController extends Controller
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:40'],
            'color' => ['required', Rule::in(array_keys(CaseBoardColumn::COLORS))],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validateWithBag('column', $this->rules(), [], ['name' => 'nama kolom']);
        if (CaseBoardColumn::where('counselor_id', $user->id)->count() >= CaseBoardColumn::MAX_PER_COUNSELOR) {
            throw ValidationException::withMessages(['name' => 'Maksimal '.CaseBoardColumn::MAX_PER_COUNSELOR.' kolom buatan sendiri.'])->errorBag('column');
        }
        CaseBoardColumn::create($data + ['counselor_id' => $user->id, 'position' => (int) CaseBoardColumn::where('counselor_id', $user->id)->max('position') + 1]);

        return back()->with('status', 'Kolom “'.$data['name'].'” ditambahkan.');
    }

    public function update(Request $request, CaseBoardColumn $column): RedirectResponse
    {
        abort_unless($column->counselor_id === $request->user()->id, 403);
        $column->update($request->validateWithBag('column', $this->rules(), [], ['name' => 'nama kolom']));

        return back()->with('status', 'Kolom diperbarui.');
    }

    /** Kartu di kolom yang dihapus kembali ke kolom status masing-masing. */
    public function destroy(Request $request, CaseBoardColumn $column): RedirectResponse
    {
        abort_unless($column->counselor_id === $request->user()->id, 403);
        $column->delete();

        return back()->with('status', 'Kolom dihapus. Kartunya kembali ke kolom status.');
    }

    /** Pindahkan kartu: column = kunci status (new, monitoring, …) atau "c{id}" untuk kolom buatan sendiri. */
    public function move(Request $request, Conversation $conversation, CaseStatusService $status): JsonResponse
    {
        $this->authorize('counsel', $conversation);
        $user = $request->user();
        $key = (string) $request->validate(['column' => ['required', 'string']])['column'];

        if (preg_match('/^c(\d+)$/', $key, $m)) {
            $column = CaseBoardColumn::where('counselor_id', $user->id)->findOrFail((int) $m[1]);
            $conversation->forceFill(['board_column_id' => $column->id])->save();

            return response()->json(['ok' => true, 'message' => 'Dipindah ke “'.$column->name.'”.']);
        }

        abort_unless(array_key_exists($key, Conversation::STATUS_LABELS), 422);
        $conversation->forceFill(['board_column_id' => null])->save();
        $status->change($conversation, $key, $user->id);
        $warning = $key === 'referred_parent' && $conversation->isAnonymousToCurrentCounselor()
            ? 'Untuk merujuk ke orang tua, minta murid membuka identitas terlebih dahulu.' : null;

        return response()->json(['ok' => true, 'warning' => $warning, 'message' => 'Status diubah ke “'.$conversation->statusLabel().'”.'
            .($key === 'resolved' ? ' Follow-up otomatis dijadwalkan '.config('welcoming.follow_up_days').' hari lagi.' : '')]);
    }
}
