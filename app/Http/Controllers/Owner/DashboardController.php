<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\{Farm, ManualData, RequestLog};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $owner = $request->user();
        $farms = $owner->ownedFarms()->with('latestSensorData')->get();

        $statusCount = [
            'normal' => 0,
            'waspada' => 0,
            'bahaya' => 0,
        ];

        foreach ($farms as $farm) {
            $status = $farm->calculateStatus();
            $statusCount[$status]++;
        }

        $recentReports = ManualData::whereIn('farm_id', $farms->pluck('farm_id'))
            ->with(['peternak', 'farm'])
            ->recent(7)
            ->latest('report_date')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_farms' => $farms->count(),
                'farms_status' => $statusCount,
                'recent_reports' => $recentReports,
                'farms' => $farms->map(fn($f) => [
                    'farm_id' => $f->farm_id,
                    'farm_name' => $f->farm_name,
                    'location' => $f->location,
                    'status' => $f->calculateStatus(),
                    'latest_sensor' => $f->latestSensorData,
                ])
            ]
        ]);
    }

    public function getFarms(Request $request)
    {
        $farms = $request->user()->ownedFarms()->with(['peternak', 'latestSensorData'])->get();

        return response()->json([
            'success' => true,
            'data' => $farms->map(fn($f) => [
                'farm_id' => $f->farm_id,
                'farm_name' => $f->farm_name,
                'location' => $f->location,
                'peternak' => $f->peternak?->name,
                'status' => $f->calculateStatus(),
                'latest_sensor' => $f->latestSensorData,
            ])
        ]);
    }

    public function showFarm(Request $request, $id)
    {
        $farm = $request->user()->ownedFarms()->with(['peternak', 'latestSensorData'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'farm_id' => $farm->farm_id,
                'farm_name' => $farm->farm_name,
                'location' => $farm->location,
                'initial_population' => $farm->initial_population,
                'peternak' => $farm->peternak?->name,
                'status' => $farm->calculateStatus(),
                'latest_sensor' => $farm->latestSensorData,
            ]
        ]);
    }

    public function requestAddFarm(Request $request)
    {
        $validated = $request->validate([
            'farm_name' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer',
        ]);

        RequestLog::create([
            'user_id' => $request->user()->user_id,
            'sender_name' => $request->user()->name,
            'request_type' => RequestLog::TYPE_ADD_FARM,
            'request_content' => json_encode($validated),
            'status' => RequestLog::STATUS_PENDING,
            'sent_time' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Permintaan tambah kandang berhasil dikirim']);
    }
}
