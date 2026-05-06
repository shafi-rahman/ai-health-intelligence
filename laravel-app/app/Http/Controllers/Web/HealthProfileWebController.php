<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Document;

class HealthProfileWebController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $medicalReports = Document::where('tenant_id', $tenantId)
            ->where('category', 'medical_report')
            ->latest()
            ->get();

        $prescriptions = Document::where('tenant_id', $tenantId)
            ->where('category', 'prescription')
            ->latest()
            ->get();

        return view('health-profile', compact('medicalReports', 'prescriptions'));
    }
}
