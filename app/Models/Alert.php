<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_id',
        'type',
        'description',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'created_at'  => 'datetime',
    ];

    protected $attributes = [
        'is_resolved' => false,
    ];

    // ─── Alert type constants ─────────────────────────────────────────────────

    public const TYPE_DDOS        = 'DDoS Attack';
    public const TYPE_MEMORY_LEAK = 'Memory Leak';
    public const TYPE_ERROR_SPIKE = 'Error Spike';
    public const TYPE_ANOMALY     = 'Statistical Anomaly';
    public const TYPE_CPU_SPIKE   = 'CPU Spike';

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * Each alert is linked to the node that triggered it.
     */
    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    // ─── Query Scopes ─────────────────────────────────────────────────────────

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function resolve(): bool
    {
        $this->is_resolved = true;
        return $this->save();
    }
}