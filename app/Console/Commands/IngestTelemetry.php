<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MetricController;
use App\Models\Node;
use Illuminate\Http\Request;

class IngestTelemetry extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'telemetry:ingest';

    /**
     * The console command description.
     */
    protected $description = 'Simulates live background telemetry ingestion for all active nodes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nodes = Node::all();

        if ($nodes->isEmpty()) {
            $this->warn('No nodes found. Please run your database seeders first.');
            return Command::FAILURE;
        }

        $controller = app(MetricController::class);

        foreach ($nodes as $node) {
            // Route through MetricController@store so FR-2 threshold checks
            // and FR-3 rolling-average anomaly detection both execute.
            $request = Request::create('/api/metrics', 'POST', [
                'node_id'      => $node->id,
                'cpu_usage'    => rand(20, 40),       // Safe baseline
                'memory_usage' => rand(250, 350),     // Normal allocation in MB
                'request_rate' => rand(90, 110),      // Steady background traffic
                'error_rate'   => rand(0, 2),         // Healthy operational limits
            ]);

            $controller->store($request);

            $this->info("Generated background telemetry for node: {$node->name}");
        }

        return Command::SUCCESS;
    }
}