<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Farm, RequestLog};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Count all users by role (global counts, not filtered by auth user)
        $totalOwners = User::byRoleName('Owner')->count();
        $totalPeternak = User::byRoleName('Peternak')->count();

        // Count all farms (global count)
        $totalFarms = Farm::count();

        // Count pending requests (all users)
        $pendingRequests = RequestLog::pending()->count();

        // Count guest requests (requests from non-authenticated users)
        $guestRequests = RequestLog::where('user_id', 0)
            ->orWhereNull('user_id')
            ->count();

        // Get recent requests with user info (latest 5, all users)
        $recentRequests = RequestLog::with(['user.role'])
            ->newest()
            ->limit(5)
            ->get()
            ->map(fn($r) => $r->getFormattedDisplay());

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalOwners + $totalPeternak,
                'total_owners' => $totalOwners,
                'total_peternak' => $totalPeternak,
                'total_farms' => $totalFarms,
                'pending_requests' => $pendingRequests,
                'guest_requests' => $guestRequests,
                'recent_requests' => $recentRequests,
            ]
        ]);
    }
}
