<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmConfig extends Model
{
    use HasFactory;

    protected $table = 'farm_config';
    protected $primaryKey = 'config_id';
    public $timestamps = false;

    protected $fillable = [
        'farm_id',
        'parameter_name',
        'value',
    ];

    protected $casts = [
        'value' => 'float',
    ];

    /**
     * Get the farm that owns this config
     */
    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id', 'farm_id');
    }

    /**
     * Default configuration parameters
     */
    public static function getDefaults()
    {
        return [
            'suhu_normal_min' => 28.0,
            'suhu_normal_max' => 32.0,
            'suhu_kritis_rendah' => 26.0,
            'suhu_kritis_tinggi' => 35.0,
            'kelembapan_normal_min' => 60.0,
            'kelembapan_normal_max' => 70.0,
            'kelembapan_kritis_rendah' => 50.0,
            'kelembapan_kritis_tinggi' => 80.0,
            'amonia_max' => 20.0,
            'amonia_kritis' => 30.0,
            'pakan_min' => 50.0,
            'minum_min' => 100.0,
            'pertumbuhan_mingguan_min' => 0.5,
            'target_bobot' => 2.0,
        ];
    }

    /**
     * Create default configs for a farm
     */
    public static function createDefaultsForFarm($farmId)
    {
        $defaults = self::getDefaults();
        
        foreach ($defaults as $parameter => $value) {
            self::updateOrCreate(
                [
                    'farm_id' => $farmId,
                    'parameter_name' => $parameter,
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }

    /**
     * Update multiple configs for a farm
     */
    public static function updateMultiple($farmId, array $configs)
    {
        foreach ($configs as $parameter => $value) {
            self::updateOrCreate(
                [
                    'farm_id' => $farmId,
                    'parameter_name' => $parameter,
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }

    /**
     * Scope to filter by farm
     */
    public function scopeForFarm($query, $farmId)
    {
        return $query->where('farm_id', $farmId);
    }
}
