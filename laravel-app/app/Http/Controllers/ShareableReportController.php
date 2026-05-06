<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\ShareableReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareableReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $medicalReports = Document::where('tenant_id', $tenantId)
            ->where('category', 'medical_report')
            ->where('status', 'ready')
            ->whereNotNull('analysis_result')
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'analysis_result', 'created_at']);

        $prescriptions = Document::where('tenant_id', $tenantId)
            ->where('category', 'prescription')
            ->where('status', 'ready')
            ->whereNotNull('analysis_result')
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'analysis_result', 'created_at']);

        if ($medicalReports->isEmpty() && $prescriptions->isEmpty()) {
            return response()->json([
                'message' => 'No analyzed health documents found to share.',
            ], 422);
        }

        $report = ShareableReport::create([
            'user_id'     => $request->user()->id,
            'tenant_id'   => $tenantId,
            'token'       => bin2hex(random_bytes(20)),
            'report_data' => [
                'user_name'       => $request->user()->name,
                'generated_at'    => now()->toISOString(),
                'medical_reports' => $medicalReports->toArray(),
                'prescriptions'   => $prescriptions->toArray(),
                'disclaimer'      => config('ai.medical_disclaimer'),
            ],
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'share_url'  => url("/api/share/{$report->token}"),
            'token'      => $report->token,
            'expires_at' => $report->expires_at->toISOString(),
        ], 201);
    }

    public function show(string $token): JsonResponse
    {
        $report = ShareableReport::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return response()->json($report->report_data);
    }
}
