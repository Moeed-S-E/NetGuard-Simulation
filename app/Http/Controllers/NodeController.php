<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\JsonResponse;

class NodeController extends Controller
{
    /**
     * GET /api/nodes
     *
     * Returns every registered microservice node together with:
     *  - its current status (Healthy / Warning / Critical)
     *  - the most recent metric snapshot
     *  - count of unresolved alerts
     *
     * The frontend polls this every 3 000 ms to refresh the status cards (FR-1).
     */
    public function index(): JsonResponse
    {
        $nodes = Node::with([
            // Only pull the single latest metric row per node
            'metrics' => fn ($q) => $q->latest()->limit(1),
            // Only unresolved alerts count
            'alerts'  => fn ($q) => $q->where('is_resolved', false),
        ])->get();

        $payload = $nodes->map(function (Node $node) {
            return [
                'id'               => $node->id,
                'name'             => $node->name,
                'ip_address'       => $node->ip_address,
                'status'           => $node->status,
                'latest_metric'    => $node->metrics->first(),
                'active_alerts'    => $node->alerts->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $payload,
        ]);
    }

    /**
     * GET /api/nodes/{id}
     *
     * Returns a single node with its last 20 metrics (for chart history)
     * and all unresolved alerts.
     */
    public function show(int $id): JsonResponse
    {
        $node = Node::with([
            'metrics' => fn ($q) => $q->latest()->limit(20),
            'alerts'  => fn ($q) => $q->where('is_resolved', false)->latest(),
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $node,
        ]);
    }
}