<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class CounselorPicker
{
    public function __construct(protected AvailabilityService $availability) {}

    /** (a) online & dalam jam layanan, (b) beban kasus non-resolved paling sedikit, (c) acak. */
    public function pick(int $schoolId): ?User
    {
        /** @var Collection<int, User> $counselors */
        $counselors = User::counselors()->inSchool($schoolId)->active()->with(['counselorProfile', 'serviceHours'])
            ->withCount(['assignedConversations as open_count' => fn ($q) => $q->where('status', '!=', 'resolved')])
            ->get();
        if ($counselors->isEmpty()) {
            return null;
        }

        $available = $counselors->filter(fn (User $u) => $this->availability->isAvailable($u));
        $pool = $available->isNotEmpty() ? $available : $counselors;
        $min = $pool->min('open_count');

        return $pool->where('open_count', $min)->shuffle()->first();
    }
}
