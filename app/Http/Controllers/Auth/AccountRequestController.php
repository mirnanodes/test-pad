<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use Illuminate\Http\Request;

class AccountRequestController extends Controller
{
    /**
     * Submit account request (untuk guest users)
     * 
     * Endpoint: POST /api/request-account
     * Body: {
     *   "name": "John Doe",
     *   "phone": "08123456789",
     *   "description": "optional message"
     * }
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'description' => 'nullable|string|max:1000',
        ]);

        $requestLog = RequestLog::create([
            'user_id' => 0, // Guest request, no user_id
            'sender_name' => $validated['name'],
            'request_type' => RequestLog::TYPE_NEW_ACCOUNT,
            'request_content' => json_encode([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'description' => $validated['description'] ?? 'Permintaan akun baru',
            ]),
            'status' => RequestLog::STATUS_PENDING,
            'sent_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan akun berhasil dikirim. Admin akan menghubungi Anda melalui WhatsApp.',
            'data' => [
                'request_id' => $requestLog->request_id,
                'status' => $requestLog->status,
            ],
        ], 201);
    }

    /**
     * Check account request status (by name or phone)
     * 
     * Endpoint: GET /api/request-account/status?phone=08123456789
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $requestLog = RequestLog::where('user_id', 0) // Guest requests only
            ->where('request_content', 'like', '%' . $request->phone . '%')
            ->newest()
            ->first();

        if (!$requestLog) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada permintaan ditemukan untuk nomor ini.',
            ], 404);
        }

        $content = json_decode($requestLog->request_content, true);

        return response()->json([
            'success' => true,
            'data' => [
                'request_id' => $requestLog->request_id,
                'name' => $content['name'] ?? $requestLog->sender_name,
                'phone' => $content['phone'] ?? '',
                'status' => $requestLog->status,
                'status_color' => $requestLog->getStatusColor(),
                'request_type' => $requestLog->getRequestTypeLabel(),
                'sent_time' => $requestLog->sent_time->format('d M Y H:i'),
            ],
        ]);
    }
}
