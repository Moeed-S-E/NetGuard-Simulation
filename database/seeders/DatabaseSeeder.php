<?php

namespace Database\Seeders;

use App\Models\Node;
use App\Models\Metric;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Core Microservices
        $services = [
            ['name' => 'Auth Service', 'ip_address' => '10.0.1.4'],
            ['name' => 'API Gateway', 'ip_address' => '10.0.1.2'],
            ['name' => 'DB Service', 'ip_address' => '10.0.2.10'],
        ];

        foreach ($services as $service) {
            $node = Node::create($service);

            // 2. Backfill 25 historical "normal" records per node so rolling average works
            for ($i = 25; $i >= 1; $i--) {
                Metric::create([
                    'node_id'      => $node->id,
                    'cpu_usage'    => rand(15, 35),       // Normal baseline stable CPU
                    'memory_usage' => rand(200, 300),     // Normal baseline memory in MB
                    'request_rate' => rand(80, 120),      // Normal request volumes
                    'error_rate'   => rand(0, 3),         // Low baseline error rate
                    'created_at'   => Carbon::now()->subMinutes($i * 2), // Spaced out in time
                ]);
            }
        }
    }
}