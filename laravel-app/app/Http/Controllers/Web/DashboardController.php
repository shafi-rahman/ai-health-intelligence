<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AILog;
use App\Models\Conversation;
use App\Models\Document;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'medical_reports'    => Document::where('tenant_id', $tenantId)->where('category', 'medical_report')->count(),
            'reports_ready'      => Document::where('tenant_id', $tenantId)->where('category', 'medical_report')->whereNotNull('analysis_result')->count(),
            'prescriptions'      => Document::where('tenant_id', $tenantId)->where('category', 'prescription')->count(),
            'prescriptions_ready'=> Document::where('tenant_id', $tenantId)->where('category', 'prescription')->whereNotNull('analysis_result')->count(),
            'analyses_done'      => Document::where('tenant_id', $tenantId)->whereNotNull('analysis_result')->count(),
            'conversations'      => Conversation::where('tenant_id', $tenantId)->count(),
        ];

        $recentDocs = Document::where('tenant_id', $tenantId)
            ->latest()
            ->limit(6)
            ->get();

        $recentLogs = AILog::where('tenant_id', $tenantId)
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact('stats', 'recentDocs', 'recentLogs'));
    }
}
