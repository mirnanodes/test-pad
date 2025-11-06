<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class IotData extends Model
{
    use HasFactory;

    protected $table = 'iot_data';
    public $timestamps = false;

    protected $fillable = [
        'farm_id',
        'timestamp',
        'temperature',
        'humidity',
        'ammonia',
        'data_source',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'temperature' => 'float',
        'humidity' => 'float',
        'ammonia' => 'float',
    ];

    /**
     * Get the farm that owns this data
     */
    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id', 'farm_id');
    }

    /**
     * Scope to filter by farm
     */
    public function scopeForFarm($query, $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope to get data from last N hours
     */
    public function scopeLastHours($query, $hours = 24)
    {
        return $query->where('timestamp', '>=', now()->subHours($hours));
    }

    /**
     * Scope to get data from last N days
     */
    public function scopeLastDays($query, $days = 7)
    {
        return $query->where('timestamp', '>=', now()->subDays($days));
    }

    /**
     * Get hourly averages
     */
    public static function getHourlyAverages($farmId, $hours = 24)
    {
        return self::forFarm($farmId)
            ->lastHours($hours)
            ->select(
                DB::raw('DATE_FORMAT(timestamp, "%Y-%m-%d %H:00:00") as hour'),
                DB::raw('AVG(temperature) as avg_temperature'),
                DB::raw('AVG(humidity) as avg_humidity'),
                DB::raw('AVG(ammonia) as avg_ammonia'),
                DB::raw('MIN(temperature) as min_temperature'),
                DB::raw('MAX(temperature) as max_temperature')
            )
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();
    }

    /**
     * Get daily averages
     */
    public static function getDailyAverages($farmId, $days = 30)
    {
        return self::forFarm($farmId)
            ->lastDays($days)
            ->select(
                DB::raw('DATE(timestamp) as date'),
                DB::raw('AVG(temperature) as avg_temperature'),
                DB::raw('AVG(humidity) as avg_humidity'),
                DB::raw('AVG(ammonia) as avg_ammonia'),
                DB::raw('MIN(temperature) as min_temperature'),
                DB::raw('MAX(temperature) as max_temperature'),
                DB::raw('MIN(humidity) as min_humidity'),
                DB::raw('MAX(humidity) as max_humidity')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get latest reading for a farm
     */
    public static function getLatestForFarm($farmId)
    {
        return self::forFarm($farmId)
            ->orderBy('timestamp', 'desc')
            ->first();
    }

    /**
     * Get statistics for a farm in date range
     */
    public static function getStats($farmId, $startDate = null, $endDate = null)
    {
        $query = self::forFarm($farmId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->select(
            DB::raw('AVG(temperature) as avg_temperature'),
            DB::raw('AVG(humidity) as avg_humidity'),
            DB::raw('AVG(ammonia) as avg_ammonia'),
            DB::raw('MIN(temperature) as min_temperature'),
            DB::raw('MAX(temperature) as max_temperature'),
            DB::raw('MIN(humidity) as min_humidity'),
            DB::raw('MAX(humidity) as max_humidity'),
            DB::raw('MIN(ammonia) as min_ammonia'),
            DB::raw('MAX(ammonia) as max_ammonia'),
            DB::raw('COUNT(*) as total_readings')
        )->first();
    }

    /**
     * Check if sensor data indicates critical conditions
     */
    public function isCritical()
    {
        $farm = $this->farm;
        if (!$farm) {
            return false;
        }

        $config = $farm->getConfigArray();
        if (empty($config)) {
            return false;
        }

        // Check temperature
        if ($this->temperature < ($config['suhu_kritis_rendah'] ?? 26) || 
            $this->temperature > ($config['suhu_kritis_tinggi'] ?? 35)) {
            return true;
        }

        // Check humidity
        if ($this->humidity < ($config['kelembapan_kritis_rendah'] ?? 50) || 
            $this->humidity > ($config['kelembapan_kritis_tinggi'] ?? 80)) {
            return true;
        }

        // Check ammonia
        if ($this->ammonia > ($config['amonia_kritis'] ?? 30)) {
            return true;
        }

        return false;
    }

    /**
     * Get parameter status
     */
    public function getParameterStatus()
    {
        $farm = $this->farm;
        if (!$farm) {
            return [
                'temperature' => 'unknown',
                'humidity' => 'unknown',
                'ammonia' => 'unknown',
            ];
        }

        $config = $farm->getConfigArray();
        
        return [
            'temperature' => $this->getTemperatureStatus($config),
            'humidity' => $this->getHumidityStatus($config),
            'ammonia' => $this->getAmmoniaStatus($config),
        ];
    }

    private function getTemperatureStatus($config)
    {
        $normalMin = $config['suhu_normal_min'] ?? 28;
        $normalMax = $config['suhu_normal_max'] ?? 32;
        $criticalLow = $config['suhu_kritis_rendah'] ?? 26;
        $criticalHigh = $config['suhu_kritis_tinggi'] ?? 35;

        if ($this->temperature < $criticalLow || $this->temperature > $criticalHigh) {
            return 'bahaya';
        }
        if ($this->temperature >= $normalMin && $this->temperature <= $normalMax) {
            return 'normal';
        }
        return 'waspada';
    }

    private function getHumidityStatus($config)
    {
        $normalMin = $config['kelembapan_normal_min'] ?? 60;
        $normalMax = $config['kelembapan_normal_max'] ?? 70;
        $criticalLow = $config['kelembapan_kritis_rendah'] ?? 50;
        $criticalHigh = $config['kelembapan_kritis_tinggi'] ?? 80;

        if ($this->humidity < $criticalLow || $this->humidity > $criticalHigh) {
            return 'bahaya';
        }
        if ($this->humidity >= $normalMin && $this->humidity <= $normalMax) {
            return 'normal';
        }
        return 'waspada';
    }

    private function getAmmoniaStatus($config)
    {
        $max = $config['amonia_max'] ?? 20;
        $critical = $config['amonia_kritis'] ?? 30;

        if ($this->ammonia > $critical) {
            return 'bahaya';
        }
        if ($this->ammonia <= $max) {
            return 'normal';
        }
        return 'waspada';
    }
}
