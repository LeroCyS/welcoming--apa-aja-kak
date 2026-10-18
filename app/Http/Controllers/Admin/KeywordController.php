<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConversationFlag;
use App\Models\SensitiveKeyword;
use App\Services\TextNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function __construct(protected TextNormalizer $normalizer) {}

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'phrase' => ['required', 'string', 'max:120'],
            'severity' => ['required', 'integer', 'between:1,3'],
            'category' => ['required', 'string', 'max:40'],
        ], [], ['phrase' => 'frasa', 'severity' => 'tingkat', 'category' => 'kategori']);
        $data['phrase'] = $this->normalizer->normalize($data['phrase']);
        $data['is_active'] = $request->boolean('is_active', true);
        if ($data['phrase'] === '') {
            back()->withErrors(['phrase' => 'Frasa tidak valid setelah dinormalisasi.'])->throwResponse();
        }

        return $data;
    }

    public function store(Request $request): RedirectResponse
    {
        SensitiveKeyword::create($this->validated($request) + ['school_id' => $request->user()->school_id]);

        return redirect()->route('admin.settings', ['tab' => 'kata-kunci'])->with('status', 'Kata kunci ditambahkan (sudah dinormalisasi).');
    }

    public function update(Request $request, SensitiveKeyword $keyword): RedirectResponse
    {
        $this->ensureSameSchool($keyword);
        $keyword->update($this->validated($request));

        return redirect()->route('admin.settings', ['tab' => 'kata-kunci'])->with('status', 'Kata kunci diperbarui.');
    }

    public function destroy(SensitiveKeyword $keyword): RedirectResponse
    {
        $this->ensureSameSchool($keyword);
        // Frasa yang sudah pernah memicu flag hanya dinonaktifkan agar riwayat flag tetap utuh.
        if (ConversationFlag::where('sensitive_keyword_id', $keyword->id)->exists()) {
            $keyword->update(['is_active' => false]);
            $msg = 'Kata kunci dinonaktifkan (masih tercatat di riwayat flag).';
        } else {
            $keyword->delete();
            $msg = 'Kata kunci dihapus.';
        }

        return redirect()->route('admin.settings', ['tab' => 'kata-kunci'])->with('status', $msg);
    }
}
