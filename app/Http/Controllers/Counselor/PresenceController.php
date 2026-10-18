<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use App\Services\ChatPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Status online guru = tombol Online menyala + heartbeat masih baru. Setiap perubahan langsung
 * dikabarkan ke murid di sekolahnya (lihat AvailabilityService::announce).
 */
class PresenceController extends Controller
{
    public function heartbeat(Request $request, AvailabilityService $availability, ChatPresenter $presenter): JsonResponse
    {
        $user = $request->user();
        $presenter->markDelivered($user);
        $user->counselorProfile->forceFill(['last_seen_at' => now()])->save();

        return response()->json(['online' => $availability->announce($user), 'enabled' => $user->counselorProfile->is_online]);
    }

    public function toggle(Request $request, AvailabilityService $availability): JsonResponse
    {
        $user = $request->user();
        $profile = $user->counselorProfile;
        $online = $request->has('online') ? $request->boolean('online') : ! $profile->is_online;
        $profile->forceFill(['is_online' => $online, 'last_seen_at' => now()])->save();

        return response()->json(['online' => $availability->announce($user), 'enabled' => $online]);
    }
}
