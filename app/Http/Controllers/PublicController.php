<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureNotQuickLocked;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function landing(): View
    {
        return view('public.landing');
    }

    public function privacy(): View
    {
        return view('public.privacy');
    }

    public function terms(): View
    {
        return view('public.terms');
    }

    /**
     * Bantuan Darurat bisa dibuka siapa saja. Murid yang sedang masuk (dan tidak terkunci) melihatnya
     * di dalam tampilan murid lengkap dengan menu dan tombol kembali; selain itu tampilan publik.
     */
    public function emergency(Request $request): View
    {
        $school = School::current();
        $user = $request->user();
        $data = [
            'school' => $school,
            'contacts' => $school->emergencyContacts()->where('is_active', true)->get(),
        ];

        if ($user?->isStudent() && ! $user->needsOnboarding() && ! $request->session()->get(EnsureNotQuickLocked::SESSION_KEY)) {
            $previous = url()->previous();

            return view('student.emergency', $data + [
                'back' => str_starts_with($previous, url('/')) && $previous !== $request->fullUrl() ? $previous : route('student.home'),
            ]);
        }

        return view('public.emergency', $data);
    }
}
