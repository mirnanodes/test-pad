<?php

namespace App\Http\Controllers\Peternak;

use App\Http\Controllers\Controller;
use App\Models\{Farm, ManualData};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $peternak = $request->user();
        $farm = $peternak->assignedFarm;

        if (!$farm) {
            return response()->json(['success' => false, 'message' => 'Anda belum ditugaskan ke kandang'], 404);
        }

        $farm->load('latestSensorData');

        $recentReports = ManualData::forFarm($farm->farm_id)
            ->byUser($peternak->user_id)
            ->recent(7)
            ->latest('report_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'farm' => [
                    'farm_id' => $farm->farm_id,
                    'farm_name' => $farm->farm_name,
                    'location' => $farm->location,
                    'status' => $farm->calculateStatus(),
                ],
                'latest_sensor' => $farm->latestSensorData ? [
                    'temperature' => $farm->latestSensorData->temperature,
                    'humidity' => $farm->latestSensorData->humidity,
                    'ammonia' => $farm->latestSensorData->ammonia,
                    'timestamp' => $farm->latestSensorData->timestamp,
                ] : null,
                'recent_reports' => $recentReports,
            ]
        ]);
    }

    public function getFarm(Request $request)
    {
        $farm = $request->user()->assignedFarm;

        if (!$farm) {
            return response()->json(['success' => false, 'message' => 'Anda belum ditugaskan ke kandang'], 404);
        }

        $farm->load('latestSensorData');

        return response()->json([
            'success' => true,
            'data' => [
                'farm_id' => $farm->farm_id,
                'farm_name' => $farm->farm_name,
                'location' => $farm->location,
                'initial_population' => $farm->initial_population,
                'status' => $farm->calculateStatus(),
                'latest_sensor' => $farm->latestSensorData,
            ]
        ]);
    }
}
