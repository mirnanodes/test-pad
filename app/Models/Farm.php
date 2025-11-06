<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
    use HasFactory;

    protected $table = 'farms';
    protected $primaryKey = 'farm_id';
    public $timestamps = false;

    protected $fillable = [
        'owner_id',
        'peternak_id',
        'farm_name',
        'location',
        'initial_population',
        'initial_weight',
        'farm_area',
    ];

    protected $casts = [
        'initial_population' => 'integer',
        'initial_weight' => 'float',
        'farm_area' => 'integer',
    ];

    /**
     * Get the owner of this farm
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    /**
     * Get the peternak assigned to this farm
     */
    public function peternak()
    {
        return $this->belongsTo(User::class, 'peternak_id', 'user_id');
    }

    /**
     * Get farm configurations
     */
    public function configs()
    {
        return $this->hasMany(FarmConfig::class, 'farm_id', 'farm_id');
    }

    /**
     * Get specific config parameter
     */
    public function getConfig($parameterName)
    {
        return $this->configs()
            ->where('parameter_name', $parameterName)
            ->value('value');
    }

    /**
     * Get all configs as key-value pairs
     */
    public function getConfigArray()
    {
        return $this->configs()
            ->pluck('value', 'parameter_name')
            ->toArray();
    }

    /**
     * Get IoT sensor data
     */
    public function iotData()
    {
        return $this->hasMany(IotData::class, 'farm_id', 'farm_id');
    }

    /**
     * Get latest sensor data
     */
    public function latestSensorData()
    {
        return $this->hasOne(IotData::class, 'farm_id', 'farm_id')
            ->latest('timestamp');
    }

    /**
     * Get manual data entries
     */
    public function manualData()
    {
        return $this->hasMany(ManualData::class, 'farm_id', 'farm_id');
    }

    /**
     * Get notifications related to this farm
     */
    public function notifications()
    {
        return $this->hasMany(NotificationLog::class, 'farm_id', 'farm_id');
    }

    /**
     * Calculate farm status based on latest sensor data and config
     */
    public function calculateStatus()
    {
        $latestData = $this->latestSensorData;
        
        if (!$latestData) {
            return 'normal';
        }

        $config = $this->getConfigArray();
        
        if (empty($config)) {
            return 'normal';
        }

        $criticalCount = 0;
        $warningCount = 0;

        // Check temperature
        $tempNormalMin = $config['suhu_normal_min'] ?? 28;
        $tempNormalMax = $config['suhu_normal_max'] ?? 32;
        $tempCriticalLow = $config['suhu_kritis_rendah'] ?? 26;
        $tempCriticalHigh = $config['suhu_kritis_tinggi'] ?? 35;

        if ($latestData->temperature < $tempCriticalLow || 
            $latestData->temperature > $tempCriticalHigh) {
            $criticalCount++;
        } elseif ($latestData->temperature < $tempNormalMin || 
                  $latestData->temperature > $tempNormalMax) {
            $warningCount++;
        }

        // Check humidity
        $humidityNormalMin = $config['kelembapan_normal_min'] ?? 60;
        $humidityNormalMax = $config['kelembapan_normal_max'] ?? 70;
        $humidityCriticalLow = $config['kelembapan_kritis_rendah'] ?? 50;
        $humidityCriticalHigh = $config['kelembapan_kritis_tinggi'] ?? 80;

        if ($latestData->humidity < $humidityCriticalLow || 
            $latestData->humidity > $humidityCriticalHigh) {
            $criticalCount++;
        } elseif ($latestData->humidity < $humidityNormalMin || 
                  $latestData->humidity > $humidityNormalMax) {
            $warningCount++;
        }

        // Check ammonia
        $ammoniaMax = $config['amonia_max'] ?? 20;
        $ammoniaCritical = $config['amonia_kritis'] ?? 30;

        if ($latestData->ammonia > $ammoniaCritical) {
            $criticalCount++;
        } elseif ($latestData->ammonia > $ammoniaMax) {
            $warningCount++;
        }

        // Determine status
        if ($criticalCount > 0) {
            return 'bahaya';
        } elseif ($warningCount >= 2) {
            return 'waspada';
        }

        return 'normal';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor()
    {
        $status = $this->calculateStatus();
        
        return match($status) {
            'normal' => 'green',
            'waspada' => 'yellow',
            'bahaya' => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope to filter farms by owner
     */
    public function scopeByOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    /**
     * Scope to filter farms by peternak
     */
    public function scopeByPeternak($query, $peternakId)
    {
        return $query->where('peternak_id', $peternakId);
    }
}
