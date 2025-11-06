<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\IotData;
use Illuminate\Http\Request;

class IoTController extends Controller
{
    /**
     * Store sensor data from IoT device
     * 
     * Endpoint: POST /api/iot/sensor-data
     * Body: {
     *   "farm_id": 1,
     *   "temperature": 30.5,
     *   "humidity": 65.0,
     *   "ammonia": 15.2
     * }
     */
    public function storeSensorData(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,farm_id',
            'temperature' => 'required|numeric|min:-50|max:100',
            'humidity' => 'required|numeric|min:0|max:100',
            'ammonia' => 'required|numeric|min:0|max:1000',
        ]);

        // Store sensor data
        $iotData = IotData::create([
            'farm_id' => $validated['farm_id'],
            'timestamp' => now(),
            'temperature' => $validated['temperature'],
            'humidity' => $validated['humidity'],
            'ammonia' => $validated['ammonia'],
            'data_source' => 'IOT',
        ]);

        // Load farm untuk calculate status
        $farm = Farm::find($validated['farm_id']);
        $status = $farm->calculateStatus();

        // Check if critical
        $isCritical = $iotData->isCritical();

        // Jika critical, bisa trigger notification (implement sesuai kebutuhan)
        if ($isCritical) {
            // TODO: Kirim notifikasi ke owner/admin
            // NotificationService::sendCriticalAlert($farm);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data sensor berhasil disimpan',
            'data' => [
                'id' => $iotData->id,
                'farm_id' => $iotData->farm_id,
                'timestamp' => $iotData->timestamp,
                'temperature' => $iotData->temperature,
                'humidity' => $iotData->humidity,
                'ammonia' => $iotData->ammonia,
                'farm_status' => $status,
                'is_critical' => $isCritical,
                'parameter_status' => $iotData->getParameterStatus(),
            ],
        ], 201);
    }

    /**
     * Get farm status based on latest sensor data
     * 
     * Endpoint: GET /api/farms/{id}/status
     */
    public function getFarmStatus($id)
    {
        $farm = Farm::with(['latestSensorData', 'configs'])->findOrFail($id);
        
        $status = $farm->calculateStatus();
        $latestSensor = $farm->latestSensorData;

        $response = [
            'farm_id' => $farm->farm_id,
            'farm_name' => $farm->farm_name,
            'status' => $status,
            'status_color' => $farm->getStatusColor(),
        ];

        if ($latestSensor) {
            $response['latest_sensor'] = [
                'temperature' => $latestSensor->temperature,
                'humidity' => $latestSensor->humidity,
                'ammonia' => $latestSensor->ammonia,
                'timestamp' => $latestSensor->timestamp,
                'parameter_status' => $latestSensor->getParameterStatus(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    /**
     * Get latest sensor reading for a farm
     * 
     * Endpoint: GET /api/farms/{id}/latest-sensor
     */
    public function getLatestSensor($id)
    {
        $latestData = IotData::getLatestForFarm($id);

        if (!$latestData) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data sensor untuk kandang ini',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'temperature' => $latestData->temperature,
                'humidity' => $latestData->humidity,
                'ammonia' => $latestData->ammonia,
                'timestamp' => $latestData->timestamp,
                'parameter_status' => $latestData->getParameterStatus(),
                'is_critical' => $latestData->isCritical(),
            ],
        ]);
    }
}
