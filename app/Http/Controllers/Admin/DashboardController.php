<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Farm, RequestLog};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOwners = User::byRoleName('Owner')->count();
        $totalPeternak = User::byRoleName('Peternak')->count();
        $totalFarms = Farm::count();
        $pendingRequests = RequestLog::pending()->count();

        $recentRequests = RequestLog::with('user')
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
                'recent_requests' => $recentRequests,
            ]
        ]);
    }
}
