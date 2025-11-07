<?php

namespace App\Http\Controllers\Peternak;

use App\Http\Controllers\Controller;
use App\Models\ManualData;
use Illuminate\Http\Request;

class ManualInputController extends Controller
{
    public function index(Request $request)
    {
        $peternak = $request->user();
        $farm = $peternak->assignedFarm;

        if (!$farm) {
            return response()->json(['success' => false, 'message' => 'Anda belum ditugaskan ke kandang'], 404);
        }

        $startDate = $request->start_date ?? now()->subDays(30);

        $reports = ManualData::forFarm($farm->farm_id)
            ->byUser($peternak->user_id)
            ->where('report_date', '>=', $startDate)
            ->orderBy('report_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reports->map(fn($r) => [
                'id' => $r->id,
                'report_date' => $r->report_date->format('d M Y'),
                'konsumsi_pakan' => $r->konsumsi_pakan,
                'konsumsi_air' => $r->konsumsi_air,
                'jumlah_kematian' => $r->jumlah_kematian,
                'warnings' => $r->getWarnings(),
            ])
        ]);
    }

    public function store(Request $request)
    {
        $peternak = $request->user();
        $farm = $peternak->assignedFarm;

        if (!$farm) {
            return response()->json(['success' => false, 'message' => 'Anda belum ditugaskan ke kandang'], 404);
        }

        $validated = $request->validate([
            'report_date' => 'required|date',
            'konsumsi_pakan' => 'required|numeric|min:0',
            'konsumsi_air' => 'required|numeric|min:0',
            'jumlah_kematian' => 'required|integer|min:0',
        ]);

        // Check if report already exists for this date
        $existing = ManualData::forFarm($farm->farm_id)
            ->where('report_date', $validated['report_date'])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Laporan untuk tanggal ini sudah ada'], 422);
        }

        $report = ManualData::create([
            'farm_id' => $farm->farm_id,
            'user_id_input' => $peternak->user_id,
            'report_date' => $validated['report_date'],
            'konsumsi_pakan' => $validated['konsumsi_pakan'],
            'konsumsi_air' => $validated['konsumsi_air'],
            'jumlah_kematian' => $validated['jumlah_kematian'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil disimpan',
            'data' => [
                'id' => $report->id,
                'report_date' => $report->report_date->format('d M Y'),
                'konsumsi_pakan' => $report->konsumsi_pakan,
                'konsumsi_air' => $report->konsumsi_air,
                'jumlah_kematian' => $report->jumlah_kematian,
                'warnings' => $report->getWarnings(),
            ]
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $report = ManualData::where('user_id_input', $request->user()->user_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $report->id,
                'report_date' => $report->report_date->format('d M Y'),
                'konsumsi_pakan' => $report->konsumsi_pakan,
                'konsumsi_air' => $report->konsumsi_air,
                'jumlah_kematian' => $report->jumlah_kematian,
                'warnings' => $report->getWarnings(),
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $report = ManualData::where('user_id_input', $request->user()->user_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'konsumsi_pakan' => 'sometimes|numeric|min:0',
            'konsumsi_air' => 'sometimes|numeric|min:0',
            'jumlah_kematian' => 'sometimes|integer|min:0',
        ]);

        $report->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diupdate',
            'data' => [
                'id' => $report->id,
                'report_date' => $report->report_date->format('d M Y'),
                'konsumsi_pakan' => $report->konsumsi_pakan,
                'konsumsi_air' => $report->konsumsi_air,
                'jumlah_kematian' => $report->jumlah_kematian,
                'warnings' => $report->getWarnings(),
            ]
        ]);
    }
}
