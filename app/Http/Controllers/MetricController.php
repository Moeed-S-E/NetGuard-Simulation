<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Metric;
use App\Models\Node;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    /**
     * GET /api/metrics
     *
     * Returns the most recent metric row for every node.
     * Polled by the frontend every 3 000 ms to update Chart.js datasets (FR-1).
     */
    public function index(): JsonResponse
    {
        // One latest metric per node — sub-select trick for SQLite compatibility
        $metrics = Metric::with('node')
            ->latest()
            ->get()
            ->unique('node_id')   // keep only the freshest per node
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $metrics,
        ]);
    }

    /**
     * GET /api/metrics/{nodeId}/history
     *
     * Returns the last 20 metric rows for a node so Chart.js can draw a line graph.
     */
    public function history(int $nodeId): JsonResponse
    {
        $metrics = Metric::where('node_id', $nodeId)
            ->latest()
            ->limit(20)
            ->get()
            ->reverse()   // chronological order for the chart
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $metrics,
        ]);
    }

    /**
     * POST /api/metrics
     *
     * Ingests a new telemetry packet for a node.
     * After insertion it runs two checks required by the PRD:
     *
     *   FR-2 — Static threshold: if error_rate > 50, create an Error Spike alert
     *           and set the node status to Critical.
     *
     *   FR-3 — Statistical anomaly: compute rolling average of last 20 rows per
     *           monitored column. If the incoming value exceeds avg × 4 (i.e. 300%
     *           above the average), register a Statistical Anomaly alert and push
     *           the node to Critical.
     *
     * Request body (JSON):
     *   node_id       integer  required
     *   cpu_usage     integer  0-100
     *   memory_usage  integer  MB
     *   request_rate  integer  req/s
     *   error_rate    integer  0-100
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'node_id'      => 'required|integer|exists:nodes,id',
            'cpu_usage'    => 'required|integer|min:0|max:100',
            'memory_usage' => 'required|integer|min:0',
            'request_rate' => 'required|integer|min:0',
            'error_rate'   => 'required|integer|min:0|max:100',
        ]);

        // Persist the raw metric entry
        $metric = Metric::create($validated);
        $node   = Node::find($validated['node_id']);

        $alerts  = [];
        $isCritical = false;

        // ── FR-2: Static threshold check ─────────────────────────────────────
        if ($metric->error_rate > Metric::ERROR_RATE_THRESHOLD) {
            $alert = Alert::create([
                'node_id'     => $node->id,
                'type'        => Alert::TYPE_ERROR_SPIKE,
                'description' => "Error rate reached {$metric->error_rate}% — exceeds static threshold of "
                                 . Metric::ERROR_RATE_THRESHOLD . '%.',
                'is_resolved' => false,
            ]);
            $alerts[]   = $alert;
            $isCritical = true;
        }

        // ── FR-3: Statistical anomaly detection ───────────────────────────────
        foreach (Metric::MONITORED_COLUMNS as $column) {
            $incomingValue = $metric->{$column};

            // Compute baseline: average of last 20 rows EXCLUDING the row we
            // just inserted (use id < current) so the new value doesn't skew avg.
            $rollingAvg = Metric::where('node_id', $node->id)
                ->where('id', '<', $metric->id)
                ->latest()
                ->limit(Metric::ROLLING_WINDOW)
                ->average($column);

            // Skip if there is not enough history yet
            if ($rollingAvg === null || $rollingAvg == 0) {
                continue;
            }

            // Flag when value exceeds avg by more than 300% (i.e. > avg × 4)
            if ($incomingValue > $rollingAvg * Metric::ANOMALY_MULTIPLIER) {
                $pct   = round(($incomingValue / $rollingAvg - 1) * 100);
                $alert = Alert::create([
                    'node_id'     => $node->id,
                    'type'        => Alert::TYPE_ANOMALY,
                    'description' => "Column '{$column}' spiked to {$incomingValue} — {$pct}% above "
                                     . "rolling average of " . round($rollingAvg, 1)
                                     . " (threshold: 300%).",
                    'is_resolved' => false,
                ]);
                $alerts[]   = $alert;
                $isCritical = true;
            }
        }

        // ── Update node status ────────────────────────────────────────────────
        if ($isCritical) {
            $node->status = Node::STATUS_CRITICAL;
        } elseif ($metric->cpu_usage >= 70 || $metric->memory_usage >= 800) {
            // Elevated but not anomalous → Warning
            if ($node->status !== Node::STATUS_CRITICAL) {
                $node->status = Node::STATUS_WARNING;
            }
        } else {
            // All clear — only recover to Healthy if no other unresolved alerts exist
            $hasOtherAlerts = Alert::where('node_id', $node->id)
                ->where('is_resolved', false)
                ->exists();

            if (! $hasOtherAlerts) {
                $node->status = Node::STATUS_HEALTHY;
            }
        }
        $node->save();

        return response()->json([
            'success'        => true,
            'data'           => $metric->load('node'),
            'alerts_created' => $alerts,
            'node_status'    => $node->status,
        ], 201);
    }
}