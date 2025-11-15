<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use Illuminate\Http\Request;

class RequestLogController extends Controller
{
    public function index(Request $request)
{
    $query = RequestLog::with('user.role');

    if ($request->status) $query->where('status', $request->status);
    if ($request->type) $query->where('request_type', $request->type);

    // Sort
    if ($request->sort === 'oldest') {
        $query->orderBy('sent_time', 'asc');
    } else {
        $query->orderBy('sent_time', 'desc');
    }

    $requests = $query->get();

    return response()->json([
        'success' => true,
        'data' => $requests->map(function($r) {
            // Decode request_content JSON
            $content = json_decode($r->request_content, true);

            // Fix phone number mapping - prioritas: content['phone'] > user.phone_number > 'N/A'
            $phone = 'N/A';
            if ($content && isset($content['phone'])) {
                $phone = $content['phone'];
            } elseif ($r->user && $r->user->phone_number) {
                $phone = $r->user->phone_number;
            }

            return [
                'id' => $r->request_id,
                'name' => $r->sender_name,
                'phone' => $phone,
                'request_type' => $r->request_type,
                'status' => $r->status,
                'created_at' => $r->sent_time,
                'details' => $content, // ⬅️ TAMBAH ini untuk modal detail
                'user' => $r->user ? [
                    'name' => $r->user->name,
                    'role' => $r->user->role ? [
                        'name' => $r->user->role->name
                    ] : null
                ] : null
            ];
        })
    ]);
}
    public function show($id)
    {
        try {
            $request = RequestLog::with('user')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $request->getFormattedDisplay()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error showing request log: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:menunggu,diproses,selesai,ditolak'
            ]);

            $requestLog = RequestLog::findOrFail($id);
            $requestLog->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating request status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
