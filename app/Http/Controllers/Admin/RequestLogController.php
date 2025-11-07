<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use Illuminate\Http\Request;

class RequestLogController extends Controller
{
    public function index(Request $request)
    {
        $query = RequestLog::with('user');

        if ($request->status) $query->byStatus($request->status);
        if ($request->type) $query->byType($request->type);

        $sort = $request->sort === 'oldest' ? 'oldest' : 'newest';
        $requests = $query->$sort()->get();

        return response()->json([
            'success' => true,
            'data' => $requests->map(fn($r) => $r->getFormattedDisplay())
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

        return response()->json(['success' => true, 'message' => 'Status berhasil diupdate']);
    }
}
