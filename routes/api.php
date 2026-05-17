<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\MetricController;
use App\Http\Controllers\NodeController;
use Illuminate\Support\Facades\Route;

// ── Node routes ───────────────────────────────────────────────────────────────
Route::get('/nodes',      [NodeController::class, 'index']);
Route::get('/nodes/{id}', [NodeController::class, 'show']);

// ── Metric routes ─────────────────────────────────────────────────────────────
Route::get('/metrics',                  [MetricController::class, 'index']);
Route::get('/metrics/{nodeId}/history', [MetricController::class, 'history']);
Route::post('/metrics',                 [MetricController::class, 'store']);

// ── Alert routes ──────────────────────────────────────────────────────────────
Route::get('/alerts',                [AlertController::class, 'index']);
Route::get('/alerts/all',            [AlertController::class, 'all']);
Route::patch('/alerts/{id}/resolve', [AlertController::class, 'resolve']);

// ── Simulation routes (FR-4 — Admin attack injection panel) ───────────────────
Route::post('/simulate/attack',     [AlertController::class, 'simulateAttack']);
Route::post('/simulate/memoryleak', [AlertController::class, 'simulateMemoryLeak']);
Route::post('/simulate/cpuspike',   [AlertController::class, 'simulateCpuSpike']);