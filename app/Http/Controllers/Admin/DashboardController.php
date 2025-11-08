<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Farm, RequestLog};
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $recentRequests = RequestLog::with('user')
            ->newest()
            ->limit(5)
            ->get()
            ->map(function($r) {
                // Cek jika request punya relasi user (Owner/Peternak)
                if ($r->user) {
                    // Gunakan fungsi lama, karena ini sudah benar
                    return $r->getFormattedDisplay();
                }

                // PERBAIKAN: Jika ini GUEST (user == null)
                // Buat data array secara manual, karena getFormattedDisplay() gagal
                return [
                    'id' => $r->request_id,
                    'name' => $r->sender_name ?? 'Tamu',
                    'role' => 'Guest',
                    'type' => $r->request_type,
                    'status' => $r->status,
                    'details' => $r->request_content,
                    'created_at' => $r->sent_time ? Carbon::parse($r->sent_time)->diffForHumans() : 'Waktu tidak tersedia',
                    'updated_at' => $r->sent_time ? Carbon::parse($r->sent_time)->diffForHumans() : 'Waktu tidak tersedia',
                ];
            });

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
