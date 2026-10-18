<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\CounselorProfile;
use App\Models\CounselorServiceHour;
use App\Models\ReplyTemplate;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['counselorProfile', 'serviceHours']);

        return view('counselor.profile', [
            'user' => $user,
            'profile' => $user->counselorProfile,
            'hours' => collect([1, 2, 3, 4, 5, 6, 0])->mapWithKeys(fn ($d) => [$d => $user->serviceHours->firstWhere('day_of_week', $d)]),
            'templates' => ReplyTemplate::with('topic')->where('counselor_id', $user->id)->orderBy('title')->get(),
            'topics' => Topic::active()->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $request->user()->counselorProfile;
        $data = $request->validate([
            'nickname' => ['required', 'string', 'max:60'],
            'gender' => ['nullable', Rule::in(['female', 'male'])],
            'hobbies' => ['nullable', 'string', 'max:120'],
            'favorite_food' => ['nullable', 'string', 'max:120'],
            'intro_message' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], ['photo.max' => 'Foto maksimal 2 MB.', 'photo.uploaded' => 'Foto gagal diunggah, kemungkinan ukurannya terlalu besar untuk server. Coba foto lain atau perkecil dulu.'], ['nickname' => 'nama panggilan', 'intro_message' => 'kalimat pengantar']);

        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->storeAs('photos', Str::random(32).'.'.$request->file('photo')->extension(), 'public');
        }
        unset($data['photo']);
        $profile->update($data);

        return back()->with('status', 'Profil tersimpan. Murid akan melihat versi terbaru.');
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'days' => ['array'],
            'days.*.active' => ['nullable', 'boolean'],
            'days.*.start' => ['nullable', 'date_format:H:i'],
            'days.*.end' => ['nullable', 'date_format:H:i'],
        ]);
        $user = $request->user();
        foreach ($data['days'] ?? [] as $d => $row) {
            if (! empty($row['active']) && (empty($row['start']) || empty($row['end']) || $row['end'] <= $row['start'])) {
                throw ValidationException::withMessages(['days' => 'Jam selesai harus setelah jam mulai ('.CounselorServiceHour::DAY_NAMES[(int) $d].').']);
            }
        }
        DB::transaction(function () use ($data, $user) {
            $user->serviceHours()->delete();
            foreach ($data['days'] ?? [] as $d => $row) {
                if (! empty($row['active'])) {
                    CounselorServiceHour::create(['counselor_id' => $user->id, 'day_of_week' => (int) $d, 'start_time' => $row['start'].':00', 'end_time' => $row['end'].':00']);
                }
            }
        });

        return back()->with('status', 'Jam layanan tersimpan.');
    }

    public function updateAutoReply(Request $request): RedirectResponse
    {
        $data = $request->validate(['offline_auto_reply' => ['nullable', 'string', 'max:1000']], [], ['offline_auto_reply' => 'pesan auto-reply']);
        // Kosong atau sama dengan teks bawaan = pakai teks bawaan (ikut berubah bila teks bawaan diperbarui).
        $text = trim((string) ($data['offline_auto_reply'] ?? ''));
        $request->user()->counselorProfile->update(['offline_auto_reply' => $text === '' || $text === CounselorProfile::DEFAULT_AUTO_REPLY ? null : $text]);

        return back()->with('status', 'Pesan auto-reply tersimpan.');
    }
}
