<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\{Farm, ManualData, IotData};
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function index(Request $request, $id)
    {
        $farm = $request->user()->ownedFarms()->findOrFail($id);

        $days = $request->days ?? 30;
        $startDate = now()->subDays($days);
        $endDate = now();

        $sensorTrends = IotData::getDailyAverages($id, $days);
        $manualSummary = ManualData::getWeeklySummary($id, 4);

        $totals = ManualData::getTotals($id, $startDate, $endDate);
        $fcr = ManualData::calculateFCR($id, $startDate, $endDate);
        $mortalityRate = ManualData::calculateMortalityRate($id, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => [
                'period' => "{$days} hari",
                'sensor_trends' => $sensorTrends,
                'manual_data_summary' => $manualSummary,
                'totals' => $totals,
                'fcr' => $fcr,
                'mortality_rate' => $mortalityRate,
            ]
        ]);
    }

    public function reports(Request $request, $id)
    {
        $farm = $request->user()->ownedFarms()->findOrFail($id);

        $startDate = $request->start_date ?? now()->subDays(30);
        $endDate = $request->end_date ?? now();

        $reports = ManualData::forFarm($id)
            ->dateRange($startDate, $endDate)
            ->with('peternak')
            ->orderBy('report_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reports->map(fn($r) => [
                'report_date' => $r->report_date->format('d M Y'),
                'konsumsi_pakan' => $r->konsumsi_pakan,
                'konsumsi_air' => $r->konsumsi_air,
                'jumlah_kematian' => $r->jumlah_kematian,
                'peternak' => $r->peternak?->name,
                'warnings' => $r->getWarnings(),
            ])
        ]);
    }

    public function statistics(Request $request, $id)
    {
        $farm = $request->user()->ownedFarms()->findOrFail($id);

        $startDate = $request->start_date ?? now()->subDays(30);
        $endDate = $request->end_date ?? now();

        $sensorStats = IotData::getStats($id, $startDate, $endDate);
        $totals = ManualData::getTotals($id, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => [
                'sensor_statistics' => $sensorStats,
                'manual_data_totals' => $totals,
                'fcr' => ManualData::calculateFCR($id, $startDate, $endDate),
                'mortality_rate' => ManualData::calculateMortalityRate($id, $startDate, $endDate),
            ]
        ]);
    }
}
