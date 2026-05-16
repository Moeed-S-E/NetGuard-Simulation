<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Metric extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_id',
        'cpu_usage',
        'memory_usage',
        'request_rate',
        'error_rate',
    ];

    protected $casts = [
        'cpu_usage'    => 'integer',
        'memory_usage' => 'integer',
        'request_rate' => 'integer',
        'error_rate'   => 'integer',
        'created_at'   => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    // ─── Anomaly Detection ────────────────────────────────────────────────────

    public const MONITORED_COLUMNS = [
        'cpu_usage',
        'memory_usage',
        'request_rate',
        'error_rate',
    ];

    public const ERROR_RATE_THRESHOLD = 50;

    public const ANOMALY_MULTIPLIER = 4;
 
    public const ROLLING_WINDOW = 20;
}