<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\{Farm, IotData};
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index(Request $request, $id)
    {
        $farm = $request->user()->ownedFarms()->with(['latestSensorData', 'configs'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'farm' => [
                    'farm_id' => $farm->farm_id,
                    'farm_name' => $farm->farm_name,
                    'location' => $farm->location,
                ],
                'current_status' => $farm->calculateStatus(),
                'latest_sensor' => $farm->latestSensorData ? [
                    'temperature' => $farm->latestSensorData->temperature,
                    'humidity' => $farm->latestSensorData->humidity,
                    'ammonia' => $farm->latestSensorData->ammonia,
                    'timestamp' => $farm->latestSensorData->timestamp,
                    'parameter_status' => $farm->latestSensorData->getParameterStatus(),
                ] : null,
                'config' => $farm->getConfigArray(),
            ]
        ]);
    }

    public function latestSensor(Request $request, $id)
    {
        $farm = $request->user()->ownedFarms()->findOrFail($id);
        $latest = IotData::getLatestForFarm($id);

        if (!$latest) {
            return response()->json(['success' => false, 'message' => 'Belum ada data sensor'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'temperature' => $latest->temperature,
                'humidity' => $latest->humidity,
                'ammonia' => $latest->ammonia,
                'timestamp' => $latest->timestamp,
                'parameter_status' => $latest->getParameterStatus(),
            ]
        ]);
    }

    public function sensorHistory(Request $request, $id)
    {
        $farm = $request->user()->ownedFarms()->findOrFail($id);

        $period = $request->period ?? '24hours';

        $data = match($period) {
            '1hour' => IotData::getHourlyAverages($id, 1),
            '24hours' => IotData::getHourlyAverages($id, 24),
            '7days' => IotData::getDailyAverages($id, 7),
            '30days' => IotData::getDailyAverages($id, 30),
            default => IotData::getHourlyAverages($id, 24),
        };

        return response()->json(['success' => true, 'data' => $data]);
    }
}
