<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ManualData extends Model
{
    use HasFactory;

    protected $table = 'manual_data';

    protected $fillable = [
        'farm_id',
        'user_id_input',
        'report_date',
        'konsumsi_pakan',
        'konsumsi_air',
        'jumlah_kematian',
    ];

    protected $casts = [
        'report_date' => 'date',
        'konsumsi_pakan' => 'float',
        'konsumsi_air' => 'float',
        'jumlah_kematian' => 'integer',
    ];

    /**
     * Get the farm
     */
    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id', 'farm_id');
    }

    /**
     * Get the user who input this data
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id_input', 'user_id');
    }

    /**
     * Get the peternak who input this data (alias for user)
     */
    public function peternak()
    {
        return $this->belongsTo(User::class, 'user_id_input', 'user_id');
    }

    /**
     * Scope to filter by farm
     */
    public function scopeForFarm($query, $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id_input', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent reports
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('report_date', '>=', now()->subDays($days));
    }

    /**
     * Get daily summary for a farm
     */
    public static function getDailySummary($farmId, $days = 30)
    {
        return self::forFarm($farmId)
            ->where('report_date', '>=', now()->subDays($days))
            ->orderBy('report_date', 'desc')
            ->get();
    }

    /**
     * Get weekly summary
     */
    public static function getWeeklySummary($farmId, $weeks = 4)
    {
        $startDate = now()->subWeeks($weeks);
        
        return self::forFarm($farmId)
            ->where('report_date', '>=', $startDate)
            ->select(
                DB::raw('YEARWEEK(report_date) as week'),
                DB::raw('MIN(report_date) as week_start'),
                DB::raw('MAX(report_date) as week_end'),
                DB::raw('SUM(konsumsi_pakan) as total_pakan'),
                DB::raw('SUM(konsumsi_air) as total_air'),
                DB::raw('SUM(jumlah_kematian) as total_kematian'),
                DB::raw('COUNT(*) as report_count')
            )
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();
    }

    /**
     * Get monthly summary
     */
    public static function getMonthlySummary($farmId, $months = 3)
    {
        $startDate = now()->subMonths($months);
        
        return self::forFarm($farmId)
            ->where('report_date', '>=', $startDate)
            ->select(
                DB::raw('DATE_FORMAT(report_date, "%Y-%m") as month'),
                DB::raw('SUM(konsumsi_pakan) as total_pakan'),
                DB::raw('SUM(konsumsi_air) as total_air'),
                DB::raw('SUM(jumlah_kematian) as total_kematian'),
                DB::raw('AVG(konsumsi_pakan) as avg_pakan_harian'),
                DB::raw('AVG(konsumsi_air) as avg_air_harian'),
                DB::raw('COUNT(*) as report_count')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
    }

    /**
     * Calculate Feed Conversion Ratio (FCR)
     */
    public static function calculateFCR($farmId, $startDate, $endDate)
    {
        $farm = Farm::find($farmId);
        if (!$farm) {
            return null;
        }

        $totalPakan = self::forFarm($farmId)
            ->dateRange($startDate, $endDate)
            ->sum('konsumsi_pakan');

        $initialWeight = $farm->initial_weight ?? 0;
        $initialPopulation = $farm->initial_population ?? 1;
        
        // Get latest manual data to estimate current weight
        $latestData = self::forFarm($farmId)
            ->orderBy('report_date', 'desc')
            ->first();

        if (!$latestData || !$initialWeight) {
            return null;
        }

        // Simple FCR calculation: Total Feed / (Current Pop * Growth)
        // Note: This is simplified. Better to track actual weight sampling
        $totalKematian = self::forFarm($farmId)
            ->dateRange($startDate, $endDate)
            ->sum('jumlah_kematian');

        $currentPopulation = $initialPopulation - $totalKematian;
        $daysElapsed = now()->diffInDays($startDate);
        $estimatedGrowthRate = 0.05; // 50g per day estimate
        $estimatedCurrentWeight = $initialWeight + ($daysElapsed * $estimatedGrowthRate);
        $totalWeightGain = ($estimatedCurrentWeight - $initialWeight) * $currentPopulation;

        if ($totalWeightGain <= 0) {
            return null;
        }

        return round($totalPakan / $totalWeightGain, 2);
    }

    /**
     * Calculate mortality rate
     */
    public static function calculateMortalityRate($farmId, $startDate = null, $endDate = null)
    {
        $farm = Farm::find($farmId);
        if (!$farm || !$farm->initial_population) {
            return null;
        }

        $query = self::forFarm($farmId);
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        $totalKematian = $query->sum('jumlah_kematian');
        $mortalityRate = ($totalKematian / $farm->initial_population) * 100;

        return round($mortalityRate, 2);
    }

    /**
     * Get totals for a farm
     */
    public static function getTotals($farmId, $startDate = null, $endDate = null)
    {
        $query = self::forFarm($farmId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return [
            'total_pakan' => $query->sum('konsumsi_pakan'),
            'total_air' => $query->sum('konsumsi_air'),
            'total_kematian' => $query->sum('jumlah_kematian'),
            'avg_pakan_harian' => $query->avg('konsumsi_pakan'),
            'avg_air_harian' => $query->avg('konsumsi_air'),
            'report_count' => $query->count(),
        ];
    }

    /**
     * Check if pakan is below minimum
     */
    public function isPakanBelowMin()
    {
        $minPakan = $this->farm->getConfig('pakan_min') ?? 50;
        return $this->konsumsi_pakan < $minPakan;
    }

    /**
     * Check if air is below minimum
     */
    public function isAirBelowMin()
    {
        $minAir = $this->farm->getConfig('minum_min') ?? 100;
        return $this->konsumsi_air < $minAir;
    }

    /**
     * Get warnings for this report
     */
    public function getWarnings()
    {
        $warnings = [];

        if ($this->isPakanBelowMin()) {
            $warnings[] = 'Konsumsi pakan di bawah minimum';
        }

        if ($this->isAirBelowMin()) {
            $warnings[] = 'Konsumsi air di bawah minimum';
        }

        if ($this->jumlah_kematian > 0) {
            $mortalityRate = ($this->jumlah_kematian / ($this->farm->initial_population ?? 1)) * 100;
            if ($mortalityRate > 1) {
                $warnings[] = 'Tingkat kematian tinggi: ' . round($mortalityRate, 2) . '%';
            }
        }

        return $warnings;
    }
}
