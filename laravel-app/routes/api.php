<?php

use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\HealthProfileController;
use App\Http\Controllers\ShareableReportController;
use App\Http\Controllers\WidgetController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// ── System health check ───────────────────────────────────────────────────────
Route::get('/health-check', function () {
    try {
        $base = preg_replace('#/api/(chat|generate)$#', '', config('ai.providers.ollama.url'));
        $up   = Http::timeout(5)->get($base)->successful();
    } catch (\Exception) {
        $up = false;
    }

    return response()->json([
        'status' => $up ? 'ok' : 'degraded',
        'ollama' => $up ? 'reachable' : 'unreachable',
        'time'   => now()->toISOString(),
    ], $up ? 200 : 503);
});

// ── Public: shareable health report (no auth) ─────────────────────────────────
Route::get('/share/{token}', [ShareableReportController::class, 'show']);

// ── Public widget endpoints (CORS-enabled) ────────────────────────────────────
Route::options('/widget/chat', [WidgetController::class, 'preflight']);
Route::post('/widget/chat',    [WidgetController::class, 'chat'])->middleware('throttle:60,1');

Route::prefix('v1')->group(function () {

    // ── Public: auth ──────────────────────────────────────────────────────────
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);

    // ── Authenticated ─────────────────────────────────────────────────────────
    Route::middleware(['auth:api', 'throttle:120,1'])->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',      [AuthController::class, 'me']);

        // ── Phase 4: AI Health Chat (context-aware with RAG over health documents) ──
        Route::post('/chat',        [AIController::class, 'chat']);
        Route::post('/chat/stream', [AIController::class, 'stream']);
        Route::post('/chat/sse',    [AIController::class, 'sse']);

        // ── Phase 1 & 2: Medical documents (reports + prescriptions) ─────────────
        Route::get('/documents',                     [DocumentController::class, 'index']);
        Route::post('/documents',                    [DocumentController::class, 'store']);
        Route::get('/documents/{id}',                [DocumentController::class, 'show']);
        Route::get('/documents/{id}/analysis',       [DocumentController::class, 'analysis']);
        Route::delete('/documents/{id}',             [DocumentController::class, 'destroy']);
        Route::post('/documents/{id}/reprocess',     [DocumentController::class, 'reprocess']);

        // ── Phase 3: Health recommendations & profile ────────────────────────────
        Route::get('/health-profile',                [HealthProfileController::class, 'index']);
        Route::get('/health-profile/recommendations', [HealthProfileController::class, 'recommendations']);

        // ── Phase 5: Shareable health report ─────────────────────────────────────
        Route::post('/health-profile/share',         [ShareableReportController::class, 'store']);

        // ── Admin only: conversation history & AI logs ───────────────────────────
        Route::middleware('role:admin')->group(function () {
            Route::get('/conversations',              [ConversationController::class, 'index']);
            Route::get('/conversations/{session_id}', [ConversationController::class, 'show']);
            Route::get('/ai/logs',                    [ConversationController::class, 'logs']);
        });
    });
});
