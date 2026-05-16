<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'status',
    ];

    protected $attributes = [
        'status' => 'Healthy',
    ];

    public const STATUS_HEALTHY  = 'Healthy';
    public const STATUS_WARNING  = 'Warning';
    public const STATUS_CRITICAL = 'Critical';

    // ─── Relationships ────────────────────────────────────────────────────────

    public function metrics()
    {
        return $this->hasMany(Metric::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Rolling average of the last $window metric rows for a given column.
     * Used by the anomaly detection engine.
     *
     * @param  string 
     * @param  int    
     * @return float|null    
     */
    public function rollingAverage(string $column, int $window = 20): ?float
    {
        $avg = $this->metrics()
            ->latest()
            ->limit($window)
            ->average($column);

        return $avg !== null ? (float) $avg : null;
    }
}