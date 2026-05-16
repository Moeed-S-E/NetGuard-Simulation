<?php


use App\Http\Controllers\AlertController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\NodeController;
use Illuminate\Support\Facades\Route;

// ── Node routes ───────────────────────────────────────────────────────────────
Route::get('/nodes',       [NodeController::class, 'index']);  
Route::get('/nodes/{id}',  [NodeController::class, 'show']);   

// ── Metric routes ─────────────────────────────────────────────────────────────
Route::get('/metrics',                      [MetricsController::class, 'index']);   // latest per node
Route::get('/metrics/{nodeId}/history',     [MetricsController::class, 'history']); // last 20 for chart
Route::post('/metrics',                     [MetricsController::class, 'store']);   // ingest + anomaly check

// ── Alert routes ──────────────────────────────────────────────────────────────
Route::get('/alerts',            [AlertController::class, 'index']);          // unresolved only
Route::get('/alerts/all',        [AlertController::class, 'all']);            // full history
Route::patch('/alerts/{id}/resolve', [AlertController::class, 'resolve']);   // mark resolved


// ─────────────────────────────────────────────────────────────────────────────
// routes/web.php  (add these lines — simulation panel, FR-4)
// ─────────────────────────────────────────────────────────────────────────────
//
// Route::post('/simulate/attack',      [AlertController::class, 'simulateAttack']);
// Route::post('/simulate/memoryleak',  [AlertController::class, 'simulateMemoryLeak']);
// Route::post('/simulate/cpuspike',    [AlertController::class, 'simulateCpuSpike']);
//
// Or keep them in api.php if you prefer everything JSON:
Route::post('/simulate/attack',     [AlertController::class, 'simulateAttack']);
Route::post('/simulate/memoryleak', [AlertController::class, 'simulateMemoryLeak']);
Route::post('/simulate/cpuspike',   [AlertController::class, 'simulateCpuSpike']);