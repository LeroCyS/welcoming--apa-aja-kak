<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\ReplyTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Template balasan pribadi Guru BK, dipakai lewat tombol Template atau /shortcut + Enter di ruang chat. */
class ReplyTemplateController extends Controller
{
    protected function rules(Request $request, ?ReplyTemplate $template = null): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'shortcut' => ['nullable', 'string', 'max:30', 'regex:/^[a-z0-9_-]+$/',
                Rule::unique('reply_templates', 'shortcut')->where('counselor_id', $request->user()->id)->ignore($template)],
            'body' => ['required', 'string', 'max:2000'],
            'topic_id' => ['nullable', 'exists:topics,id'],
        ];
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?ReplyTemplate $template = null): array
    {
        // "/Salam Pagi" → "salampagi": garis miring & huruf besar diabaikan.
        $request->merge(['shortcut' => Str::lower(ltrim(str_replace(' ', '', (string) $request->input('shortcut')), '/')) ?: null]);
        $data = $request->validateWithBag('template', $this->rules($request, $template), [
            'shortcut.regex' => 'Shortcut hanya boleh huruf kecil, angka, - dan _ (tanpa spasi).',
            'shortcut.unique' => 'Shortcut ini sudah dipakai template lain.',
        ], ['title' => 'judul', 'body' => 'isi template']);
        $data['shortcut'] ??= $this->generateShortcut($request, $data['title'], $template);

        return $data;
    }

    protected function generateShortcut(Request $request, string $title, ?ReplyTemplate $template): string
    {
        $base = Str::limit(Str::slug($title, ''), 24, '') ?: 'template';
        $taken = ReplyTemplate::where('counselor_id', $request->user()->id)->when($template, fn ($q) => $q->whereKeyNot($template->id))->pluck('shortcut')->all();
        $shortcut = $base;
        for ($i = 2; in_array($shortcut, $taken, true); $i++) {
            $shortcut = $base.$i;
        }

        return $shortcut;
    }

    public function store(Request $request): RedirectResponse
    {
        ReplyTemplate::create($this->validated($request) + ['counselor_id' => $request->user()->id]);

        return redirect()->to(route('counselor.profile', ['tab' => 'template']).'#template')->with('status', 'Template ditambahkan.');
    }

    public function update(Request $request, ReplyTemplate $template): RedirectResponse
    {
        abort_unless($template->counselor_id === $request->user()->id, 403);
        $template->update($this->validated($request, $template));

        return redirect()->to(route('counselor.profile', ['tab' => 'template']).'#template')->with('status', 'Template diperbarui.');
    }

    public function destroy(Request $request, ReplyTemplate $template): RedirectResponse
    {
        abort_unless($template->counselor_id === $request->user()->id, 403);
        $template->delete();

        return redirect()->to(route('counselor.profile', ['tab' => 'template']).'#template')->with('status', 'Template dihapus.');
    }
}
