<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\ChatPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounselorController extends Controller
{
    public function index(Request $request, AvailabilityService $availability): View
    {
        return view('student.counselors', [
            'counselors' => User::counselors()->inSchool($request->user()->school_id)->active()->with(['counselorProfile', 'serviceHours'])->orderBy('id')->get(),
            'availability' => $availability,
        ]);
    }

    /** Status online guru untuk polling (60 dtk). */
    public function statuses(Request $request, AvailabilityService $availability, ChatPresenter $presenter): JsonResponse
    {
        $presenter->markDelivered($request->user());

        return response()->json(User::counselors()->inSchool($request->user()->school_id)->active()->with(['counselorProfile', 'serviceHours'])->get()
            ->mapWithKeys(fn (User $u) => [$u->id => $availability->statusFor($u)]));
    }
}
