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
            
            return [
                'id' => $r->request_id,
                'name' => $r->sender_name,
                'phone' => $content['phone'] ?? $r->phone_number ?? 'N/A', // ⬅️ dari JSON content
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
        $request = RequestLog::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $request->getFormattedDisplay()
        ]);
    }

    public function updateStatus(Request $request, $id)
{
    $validated = $request->validate([
        'status' => 'required|in:menunggu,diproses,selesai,ditolak'
    ]);

    $requestLog = RequestLog::findOrFail($id);
    $requestLog->update(['status' => $validated['status']]);

    return response()->json([
        'success' => true,
        'message' => 'Status berhasil diupdate'
    ]);
}

}
