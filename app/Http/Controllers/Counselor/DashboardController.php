<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\School;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, AnalyticsService $analytics): View
    {
        $period = $request->query('periode', 'bulan');
        $tz = School::current()->timezone;
        $hour = (int) now($tz)->format('G');

        return view('counselor.dashboard', [
            'data' => $analytics->dashboard($period),
            'greeting' => $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam')),
            'today' => now($tz)->translatedFormat('l, j M Y'),
            'myUnassessed' => Conversation::where('counselor_id', $request->user()->id)->where('status', '!=', 'resolved')->whereNull('urgency')->count(),
        ]);
    }
}
