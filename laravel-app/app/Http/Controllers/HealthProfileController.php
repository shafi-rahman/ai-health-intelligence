<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\Health\HealthRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $medicalReports = Document::where('tenant_id', $tenantId)
            ->where('category', 'medical_report')
            ->whereIn('status', ['ready', 'processing'])
            ->latest()
            ->get(['id', 'title', 'status', 'analysis_result', 'created_at']);

        $prescriptions = Document::where('tenant_id', $tenantId)
            ->where('category', 'prescription')
            ->whereIn('status', ['ready', 'processing'])
            ->latest()
            ->get(['id', 'title', 'status', 'analysis_result', 'created_at']);

        $latestReport = $medicalReports->where('status', 'ready')
            ->whereNotNull('analysis_result')
            ->first();

        return response()->json([
            'medical_reports' => $medicalReports,
            'prescriptions'   => $prescriptions,
            'latest_risk_level' => $latestReport?->analysis_result['risk_level'] ?? null,
            'disclaimer'        => config('ai.medical_disclaimer'),
        ]);
    }

    public function recommendations(Request $request, HealthRecommendationService $service): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $latestReport = Document::where('tenant_id', $tenantId)
            ->where('category', 'medical_report')
            ->where('status', 'ready')
            ->whereNotNull('analysis_result')
            ->latest()
            ->first();

        if (!$latestReport) {
            return response()->json([
                'message' => 'No analyzed medical reports found. Upload and process a medical report first.',
            ], 422);
        }

        $latestPrescription = Document::where('tenant_id', $tenantId)
            ->where('category', 'prescription')
            ->where('status', 'ready')
            ->whereNotNull('analysis_result')
            ->latest()
            ->first();

        $model = $request->input('model', 'phi');

        $recommendations = $service->generate(
            $latestReport->analysis_result,
            $latestPrescription?->analysis_result ?? [],
            $model
        );

        return response()->json($recommendations);
    }
}
