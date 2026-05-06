@extends('layouts.app')
@section('title', 'Health Records')

@section('content')

{{-- Header --}}
<div class="px-8 py-6 bg-white border-b border-gray-200 flex-shrink-0 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Health Records</h1>
        <p class="text-gray-500 text-sm mt-0.5">{{ $documents->count() }} document{{ $documents->count() !== 1 ? 's' : '' }} uploaded</p>
    </div>
    <a href="{{ route('dashboard') }}"
       class="px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors"
       style="background: linear-gradient(135deg, #10b981, #2563eb);">
        + Upload Document
    </a>
</div>

<div class="flex-1 px-8 py-6">

    @if($documents->isEmpty())

        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-gray-900 font-semibold text-lg">No health records yet</p>
            <p class="text-gray-500 text-sm mt-1 mb-5">Upload medical reports, prescriptions, or lab results to get AI-powered health insights.</p>
            <a href="{{ route('dashboard') }}"
               class="inline-block px-5 py-2.5 text-white text-sm font-medium rounded-lg transition-colors"
               style="background: linear-gradient(135deg, #10b981, #2563eb);">
                Upload your first record
            </a>
        </div>

    @else

        {{-- Filter tabs --}}
        <div class="flex gap-1 mb-5 bg-gray-100 p-1 rounded-xl w-fit flex-wrap">
            <button onclick="filterDocs('all')" id="tab-all"
                    class="tab-btn px-4 py-1.5 text-sm font-medium rounded-lg transition-colors bg-white text-gray-900 shadow-sm">
                All <span class="ml-1 text-xs text-gray-400">{{ $documents->count() }}</span>
            </button>
            <button onclick="filterDocs('medical_report')" id="tab-medical_report"
                    class="tab-btn px-4 py-1.5 text-sm font-medium rounded-lg transition-colors text-gray-500">
                Medical Reports <span class="ml-1 text-xs text-gray-400">{{ $documents->where('category', 'medical_report')->count() }}</span>
            </button>
            <button onclick="filterDocs('prescription')" id="tab-prescription"
                    class="tab-btn px-4 py-1.5 text-sm font-medium rounded-lg transition-colors text-gray-500">
                Prescriptions <span class="ml-1 text-xs text-gray-400">{{ $documents->where('category', 'prescription')->count() }}</span>
            </button>
            <button onclick="filterDocs('general')" id="tab-general"
                    class="tab-btn px-4 py-1.5 text-sm font-medium rounded-lg transition-colors text-gray-500">
                General <span class="ml-1 text-xs text-gray-400">{{ $documents->where('category', 'general')->count() }}</span>
            </button>
            <button onclick="filterDocs('failed')" id="tab-failed"
                    class="tab-btn px-4 py-1.5 text-sm font-medium rounded-lg transition-colors text-gray-500">
                Failed <span class="ml-1 text-xs text-gray-400">{{ $documents->where('status', 'failed')->count() }}</span>
            </button>
        </div>

        {{-- Analysis modal --}}
        <div id="analysisModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.5);">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[80vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900" id="modalTitle">Analysis Result</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div id="modalBody" class="space-y-4 text-sm text-gray-700"></div>
            </div>
        </div>

        {{-- Documents table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Document</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Analysis</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Uploaded</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody id="docTableBody" class="divide-y divide-gray-50">
                    @foreach($documents as $doc)
                    <tr class="doc-row hover:bg-gray-50 transition-colors" data-category="{{ $doc->category }}" data-status="{{ $doc->status }}" data-id="{{ $doc->id }}">

                        {{-- Title --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-xs font-bold
                                    {{ $doc->category === 'medical_report' ? 'bg-emerald-50 text-emerald-700' : ($doc->category === 'prescription' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                                    {{ $doc->category === 'medical_report' ? 'MR' : ($doc->category === 'prescription' ? 'Rx' : 'GN') }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate max-w-xs">{{ $doc->title }}</p>
                                    <p class="text-xs text-gray-400 uppercase">{{ $doc->type }}</p>
                                    @if($doc->status === 'failed' && $doc->error)
                                        <p class="text-xs text-red-500 truncate max-w-xs mt-0.5">{{ $doc->error }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Category --}}
                        <td class="px-5 py-4">
                            @if($doc->category === 'medical_report')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">Medical Report</span>
                            @elseif($doc->category === 'prescription')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">Prescription</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">General</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                @if($doc->status === 'ready') bg-green-100 text-green-700
                                @elseif($doc->status === 'failed') bg-red-100 text-red-600
                                @elseif($doc->status === 'processing') bg-blue-100 text-blue-700
                                @else bg-amber-100 text-amber-700 @endif">
                                @if($doc->status === 'processing')
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                                @endif
                                {{ $doc->status }}
                            </span>
                        </td>

                        {{-- Analysis --}}
                        <td class="px-5 py-4">
                            @if($doc->analysis_result)
                                <button onclick="viewAnalysis({{ $doc->id }}, '{{ addslashes($doc->title) }}', {{ json_encode($doc->analysis_result) }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    </svg>
                                    View
                                </button>
                            @elseif($doc->status === 'ready' && in_array($doc->category, ['medical_report', 'prescription']))
                                <span class="text-xs text-amber-500">Analysing…</span>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Uploaded --}}
                        <td class="px-5 py-4 text-gray-400 whitespace-nowrap text-xs">
                            {{ $doc->created_at->format('M j, Y') }}<br>
                            <span class="text-gray-300">{{ $doc->created_at->diffForHumans() }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="reprocess({{ $doc->id }}, this)"
                                        class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors whitespace-nowrap">
                                    Re-process
                                </button>
                                <div class="relative" id="del-wrap-{{ $doc->id }}">
                                    <button onclick="askDelete({{ $doc->id }})"
                                            id="del-btn-{{ $doc->id }}"
                                            class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                        Delete
                                    </button>
                                    <div id="del-confirm-{{ $doc->id }}"
                                         class="hidden absolute right-0 top-0 flex items-center gap-1 bg-white border border-gray-200 rounded-lg shadow-lg p-1 z-10 whitespace-nowrap">
                                        <span class="text-xs text-gray-600 px-2">Sure?</span>
                                        <button onclick="confirmDelete({{ $doc->id }})"
                                                class="px-2.5 py-1 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">
                                            Yes
                                        </button>
                                        <button onclick="cancelDelete({{ $doc->id }})"
                                                class="px-2.5 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100 rounded-md transition-colors">
                                            No
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif

</div>

@endsection

@push('scripts')
<script>
const TOKEN = '{{ session("api_token", "") }}';

// ── Category/status filter ────────────────────────────────────────────────────
function filterDocs(filter) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
        btn.classList.add('text-gray-500');
    });
    const active = document.getElementById('tab-' + filter);
    active.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
    active.classList.remove('text-gray-500');

    document.querySelectorAll('.doc-row').forEach(row => {
        let show = false;
        if (filter === 'all')    show = true;
        else if (filter === 'failed') show = row.dataset.status === 'failed';
        else show = row.dataset.category === filter;
        row.style.display = show ? '' : 'none';
    });
}

// ── Analysis modal ────────────────────────────────────────────────────────────
function viewAnalysis(id, title, data) {
    document.getElementById('modalTitle').textContent = title;
    const body = document.getElementById('modalBody');
    body.innerHTML = '';

    if (data.summary) {
        body.innerHTML += `<div><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Summary</p><p>${data.summary}</p></div>`;
    }

    if (data.risk_level) {
        const colors = { low: 'green', medium: 'amber', high: 'red', unknown: 'gray' };
        const c = colors[data.risk_level] || 'gray';
        body.innerHTML += `<div><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Risk Level</p>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-${c}-100 text-${c}-700">${data.risk_level.toUpperCase()}</span></div>`;
    }

    if (data.key_findings?.length) {
        body.innerHTML += `<div><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Key Findings</p><ul class="list-disc pl-4 space-y-1">${data.key_findings.map(f => `<li>${f}</li>`).join('')}</ul></div>`;
    }

    if (data.possible_concerns?.length) {
        body.innerHTML += `<div><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Possible Concerns</p><ul class="list-disc pl-4 space-y-1 text-amber-700">${data.possible_concerns.map(c => `<li>${c}</li>`).join('')}</ul></div>`;
    }

    if (data.medicines?.length) {
        const meds = data.medicines.map(m => `<li><strong>${m.name}</strong> — ${m.dosage}, ${m.frequency}<br><span class="text-gray-500 text-xs">${m.purpose || ''}</span></li>`).join('');
        body.innerHTML += `<div><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Medicines</p><ul class="list-disc pl-4 space-y-2">${meds}</ul></div>`;
    }

    if (data.recommendations?.length) {
        body.innerHTML += `<div><p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Recommendations</p><ul class="list-disc pl-4 space-y-1 text-emerald-700">${data.recommendations.map(r => `<li>${r}</li>`).join('')}</ul></div>`;
    }

    if (data.disclaimer) {
        body.innerHTML += `<div class="p-3 bg-blue-50 rounded-lg text-xs text-blue-700 border border-blue-100">${data.disclaimer}</div>`;
    }

    document.getElementById('analysisModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('analysisModal').classList.add('hidden');
}

document.getElementById('analysisModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ── Re-process ────────────────────────────────────────────────────────────────
async function reprocess(id, btn) {
    const original = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Queuing…';
    try {
        const res  = await fetch(`/api/v1/documents/${id}/reprocess`, {
            method: 'POST', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (res.ok) {
            btn.textContent = 'Queued!';
            setTimeout(() => { btn.textContent = original; btn.disabled = false; }, 2000);
        } else {
            alert(data.message || 'Failed.'); btn.textContent = original; btn.disabled = false;
        }
    } catch (e) { alert(e.message); btn.textContent = original; btn.disabled = false; }
}

// ── Delete ────────────────────────────────────────────────────────────────────
function askDelete(id)    { document.getElementById('del-btn-' + id).classList.add('hidden'); document.getElementById('del-confirm-' + id).classList.remove('hidden'); }
function cancelDelete(id) { document.getElementById('del-btn-' + id).classList.remove('hidden'); document.getElementById('del-confirm-' + id).classList.add('hidden'); }

async function confirmDelete(id) {
    const wrap = document.getElementById('del-wrap-' + id);
    wrap.innerHTML = '<span class="text-xs text-gray-400">Deleting…</span>';
    try {
        const res = await fetch(`/api/v1/documents/${id}`, {
            method: 'DELETE', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Accept': 'application/json' },
        });
        if (res.ok) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            row.style.transition = 'opacity .3s'; row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
        } else {
            const data = await res.json();
            wrap.innerHTML = `<span class="text-xs text-red-500">${data.message || 'Error'}</span>`;
        }
    } catch (e) { wrap.innerHTML = `<span class="text-xs text-red-500">${e.message}</span>`; }
}

document.addEventListener('click', e => {
    if (!e.target.closest('[id^="del-wrap-"]')) {
        document.querySelectorAll('[id^="del-confirm-"]').forEach(el => {
            if (!el.classList.contains('hidden')) cancelDelete(el.id.replace('del-confirm-', ''));
        });
    }
});
</script>
@endpush
