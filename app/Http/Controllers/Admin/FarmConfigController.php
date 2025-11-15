<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Farm, FarmConfig};
use Illuminate\Http\Request;

class FarmConfigController extends Controller
{
    public function index()
    {
        $farms = Farm::with(['owner', 'peternak'])->get();

        return response()->json([
            'success' => true,
            'data' => $farms->map(fn($f) => [
                'farm_id' => $f->farm_id,
                'farm_name' => $f->farm_name,
                'location' => $f->location,
                'owner' => $f->owner->name,
                'peternak' => $f->peternak?->name,
                'status' => $f->calculateStatus(),
            ])
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'owner_id' => 'required|exists:users,user_id',
                'farm_name' => 'required|string|max:100',
                'location' => 'nullable|string|max:255',
                'initial_population' => 'nullable|integer',
                'initial_weight' => 'nullable|numeric',
                'farm_area' => 'nullable|integer',
            ]);

            $farm = Farm::create($validated);

            // Create default config
            FarmConfig::createDefaultsForFarm($farm->farm_id);

            return response()->json(['success' => true, 'message' => 'Farm berhasil dibuat', 'data' => $farm], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating farm: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $farm = Farm::with(['owner', 'peternak', 'latestSensorData'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'farm_id' => $farm->farm_id,
                'farm_name' => $farm->farm_name,
                'location' => $farm->location,
                'owner' => $farm->owner->name,
                'peternak' => $farm->peternak?->name,
                'initial_population' => $farm->initial_population,
                'initial_weight' => $farm->initial_weight,
                'farm_area' => $farm->farm_area,
                'status' => $farm->calculateStatus(),
                'latest_sensor' => $farm->latestSensorData,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $farm = Farm::findOrFail($id);

            $validated = $request->validate([
                'farm_name' => 'sometimes|string|max:100',
                'location' => 'nullable|string|max:255',
                'initial_population' => 'nullable|integer',
                'initial_weight' => 'nullable|numeric',
                'farm_area' => 'nullable|integer',
                'peternak_id' => 'nullable|exists:users,user_id',
            ]);

            $farm->update($validated);

            return response()->json(['success' => true, 'message' => 'Farm berhasil diupdate', 'data' => $farm]);
        } catch (\Exception $e) {
            \Log::error('Error updating farm: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Farm::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Farm berhasil dihapus']);
        } catch (\Exception $e) {
            \Log::error('Error deleting farm: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getFarmConfig(Request $request)
    {
        try {
            $farmId = $request->farm_id ?? 1;
            $farm = Farm::findOrFail($farmId);
            $config = $farm->getConfigArray();

            return response()->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting farm config: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateFarmConfig(Request $request)
    {
        try {
            $farmId = $request->farm_id ?? 1;

            $validated = $request->validate([
                'suhu_normal_min' => 'nullable|numeric',
                'suhu_normal_max' => 'nullable|numeric',
                'suhu_kritis_rendah' => 'nullable|numeric',
                'suhu_kritis_tinggi' => 'nullable|numeric',
                'kelembapan_normal_min' => 'nullable|numeric',
                'kelembapan_normal_max' => 'nullable|numeric',
                'kelembapan_kritis_rendah' => 'nullable|numeric',
                'kelembapan_kritis_tinggi' => 'nullable|numeric',
                'amonia_max' => 'nullable|numeric',
                'amonia_kritis' => 'nullable|numeric',
                'pakan_min' => 'nullable|numeric',
                'minum_min' => 'nullable|numeric',
                'pertumbuhan_mingguan_min' => 'nullable|numeric',
                'target_bobot' => 'nullable|numeric',
            ]);

            FarmConfig::updateMultiple($farmId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating farm config: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resetConfig(Request $request)
    {
        try {
            $farmId = $request->farm_id ?? 1;
            FarmConfig::createDefaultsForFarm($farmId);

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi berhasil direset ke default'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error resetting farm config: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}