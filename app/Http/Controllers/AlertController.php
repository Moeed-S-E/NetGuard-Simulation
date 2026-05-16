<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Metric;
use App\Models\Node;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * GET /api/alerts
     *
     * Returns all unresolved alerts across every node, newest first.
     * The frontend alert feed polls this every 3 000 ms (FR-1).
     */
    public function index(): JsonResponse
    {
        $alerts = Alert::with('node')
            ->unresolved()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $alerts,
        ]);
    }

    /**
     * GET /api/alerts/all
     *
     * Returns full alert history (resolved + unresolved) for the admin panel log.
     */
    public function all(): JsonResponse
    {
        $alerts = Alert::with('node')->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $alerts,
        ]);
    }

    /**
     * PATCH /api/alerts/{id}/resolve
     *
     * Marks a single alert as resolved.
     * Also checks whether the parent node can return to Healthy status
     * (only if no other unresolved alerts remain).
     */
    public function resolve(int $id): JsonResponse
    {
        $alert = Alert::findOrFail($id);
        $alert->resolve();

        // Recover node status if no more active alerts exist
        $node = $alert->node;
        $stillActive = Alert::where('node_id', $node->id)
            ->where('is_resolved', false)
            ->exists();

        if (! $stillActive) {
            $node->status = Node::STATUS_HEALTHY;
            $node->save();
        }

        return response()->json([
            'success' => true,
            'message' => "Alert #{$id} resolved.",
            'node_status' => $node->status,
        ]);
    }

    // ─── Simulation / Attack Injection Routes (FR-4) ─────────────────────────
    // These endpoints are intentionally unsecured for demo / academic purposes.
    // They inject crafted metric payloads that are guaranteed to breach thresholds
    // and trigger the anomaly engine in MetricsController@store.

    /**
     * POST /simulate/attack
     *
     * Injects a DDoS-profile metric burst into the first available node:
     * request_rate skyrockets while error_rate also spikes.
     */
    public function simulateAttack(Request $request): JsonResponse
    {
        return $this->injectSimulation(
            $request,
            Alert::TYPE_DDOS,
            [
                'cpu_usage'    => 95,
                'memory_usage' => 1800,
                'request_rate' => 9999,  // way above any rolling avg × 4
                'error_rate'   => 85,
            ]
        );
    }

    /**
     * POST /simulate/memoryleak
     *
     * Injects a memory-leak profile: memory grows out of proportion
     * while CPU and request_rate stay normal, making the anomaly obvious.
     */
    public function simulateMemoryLeak(Request $request): JsonResponse
    {
        return $this->injectSimulation(
            $request,
            Alert::TYPE_MEMORY_LEAK,
            [
                'cpu_usage'    => 42,
                'memory_usage' => 7800,   // extreme vs typical ~200-400 MB baseline
                'request_rate' => 120,
                'error_rate'   => 12,
            ]
        );
    }

    /**
     * POST /simulate/cpuspike
     *
     * Injects a CPU spike with normal memory/request levels.
     */
    public function simulateCpuSpike(Request $request): JsonResponse
    {
        return $this->injectSimulation(
            $request,
            Alert::TYPE_CPU_SPIKE,
            [
                'cpu_usage'    => 99,
                'memory_usage' => 310,
                'request_rate' => 130,
                'error_rate'   => 8,
            ]
        );
    }

    // ─── Private helper ───────────────────────────────────────────────────────

    /**
     * Insert an extreme metric row for a target node, then run it through the
     * standard ingestion pipeline (MetricsController@store logic is duplicated
     * here to keep the simulation self-contained and testable independently).
     *
     * @param  Request  $request   Optionally pass ?node_id=X to target a specific node
     * @param  string   $alertType The human-readable alert type label
     * @param  array    $payload   The crafted metric values
     */
    private function injectSimulation(
        Request $request,
        string  $alertType,
        array   $payload
    ): JsonResponse {

        // Target a specific node if provided, otherwise pick the first node
        $nodeId = $request->query('node_id')
            ?? Node::value('id');

        if (! $nodeId) {
            return response()->json([
                'success' => false,
                'message' => 'No nodes found in database. Run seeders first.',
            ], 422);
        }

        $node = Node::findOrFail($nodeId);

        // Persist the crafted metric row
        $metric = Metric::create(array_merge(['node_id' => $node->id], $payload));

        // Create a named alert for the simulation type immediately
        // (the anomaly engine in MetricsController would catch it too,
        //  but here we name it explicitly for the demo UI)
        $description = match ($alertType) {
            Alert::TYPE_DDOS        => "Simulated DDoS attack: request_rate={$payload['request_rate']}, error_rate={$payload['error_rate']}%",
            Alert::TYPE_MEMORY_LEAK => "Simulated memory leak: memory_usage={$payload['memory_usage']} MB",
            Alert::TYPE_CPU_SPIKE   => "Simulated CPU spike: cpu_usage={$payload['cpu_usage']}%",
            default                 => "Simulated anomaly injected for demo purposes.",
        };

        $alert = Alert::create([
            'node_id'     => $node->id,
            'type'        => $alertType,
            'description' => $description,
            'is_resolved' => false,
        ]);

        // Push node to Critical immediately
        $node->status = Node::STATUS_CRITICAL;
        $node->save();

        return response()->json([
            'success'     => true,
            'message'     => "{$alertType} simulation injected into node '{$node->name}'.",
            'metric'      => $metric,
            'alert'       => $alert,
            'node_status' => $node->status,
        ], 201);
    }
}