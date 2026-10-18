<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\School;
use App\Models\SensitiveKeyword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $school = School::current();

        return view('admin.settings', [
            'tab' => in_array($request->query('tab'), ['sekolah', 'kontak', 'kata-kunci', 'laporan-iseng'], true) ? $request->query('tab') : 'sekolah',
            'school' => $school,
            'contacts' => $school->emergencyContacts()->get(),
            'keywords' => SensitiveKeyword::where('school_id', $school->id)->orderByDesc('severity')->orderBy('phrase')->get(),
            'levels' => School::LEVELS,
            // Hanya metadata (alias, waktu, pelapor), admin tidak pernah melihat isi cerita.
            'pranks' => Conversation::inSchool($school)->whereNotNull('prank_reported_at')->with('prankReporter.counselorProfile')
                ->addSelect(['same_student_reports' => Conversation::query()->from('conversations as c2')->selectRaw('COUNT(*)')
                    ->whereColumn('c2.student_hash', 'conversations.student_hash')->whereColumn('c2.school_id', 'conversations.school_id')->whereNotNull('c2.prank_reported_at')])
                ->latest('prank_reported_at')->get(),
            'timezones' => ['Asia/Jakarta' => 'WIB (Asia/Jakarta)', 'Asia/Makassar' => 'WITA (Asia/Makassar)', 'Asia/Jayapura' => 'WIT (Asia/Jayapura)'],
        ]);
    }

    public function updateSchool(Request $request): RedirectResponse
    {
        $school = School::current();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'level' => ['required', Rule::in(array_keys(School::LEVELS))],
            'address' => ['nullable', 'string', 'max:500'],
            'timezone' => ['required', 'timezone'],
            'default_service_start' => ['required', 'date_format:H:i'],
            'default_service_end' => ['required', 'date_format:H:i', 'after:default_service_start'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], ['default_service_end.after' => 'Jam selesai harus setelah jam mulai.', 'logo.uploaded' => 'Foto gagal diunggah, kemungkinan ukurannya terlalu besar untuk server. Coba foto lain atau perkecil dulu.'], ['name' => 'nama sekolah']);

        if ($request->hasFile('logo')) {
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }
        unset($data['logo']);
        $school->update($data);

        return redirect()->route('admin.settings', ['tab' => 'sekolah'])->with('status', 'Data sekolah tersimpan.');
    }
}
