@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Header --}}
<div class="px-8 py-6 bg-white border-b border-gray-200 flex-shrink-0">
    <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-500 text-sm mt-0.5">Welcome back, {{ auth()->user()->name }}</p>
</div>

<div class="flex-1 px-8 py-6 space-y-6">

    {{-- Disclaimer banner --}}
    <div class="flex items-start gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-700 text-xs">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <span>This platform provides <strong>informational insights only</strong> and is not a substitute for professional medical advice. Always consult a qualified healthcare provider.</span>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Medical Reports</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['medical_reports'] }}</p>
            <p class="text-xs text-emerald-600 mt-1">{{ $stats['reports_ready'] }} analysed</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Prescriptions</p>
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['prescriptions'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['prescriptions_ready'] }} analysed</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">AI Analyses</p>
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['analyses_done'] }}</p>
            <p class="text-xs text-gray-400 mt-1">insights generated</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Chat Sessions</p>
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['conversations'] }}</p>
            <p class="text-xs text-gray-400 mt-1">all time</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Upload health document --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-1">Upload Health Document</h2>
            <p class="text-xs text-gray-400 mb-4">AI will extract insights and index it for your health chat.</p>
            <form id="uploadForm" class="space-y-3">
                @csrf
                <input type="text" id="docTitle" placeholder="Document title" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                        <select id="docCategory"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            <option value="medical_report">Medical Report</option>
                            <option value="prescription">Prescription</option>
                            <option value="general">General Health Doc</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">File type</label>
                        <select id="docType"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            <option value="pdf">PDF (.pdf)</option>
                            <option value="txt">Text (.txt)</option>
                            <option value="docx">Word (.docx)</option>
                            <option value="image">Image (JPG/PNG)</option>
                            <option value="text">Paste text</option>
                            <option value="url">URL</option>
                        </select>
                    </div>
                </div>

                <div id="fileInput">
                    <input type="file" id="docFile" accept=".pdf"
                           class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p id="fileHint" class="text-xs text-gray-400 mt-1">PDF up to 20 MB</p>
                </div>
                <div id="textInput" class="hidden">
                    <textarea id="docContent" rows="4" placeholder="Paste your medical report or health notes here..."
                              class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                </div>
                <div id="urlInput" class="hidden">
                    <input type="url" id="docUrl" placeholder="https://example.com/report"
                           class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div id="uploadStatus" class="hidden text-sm"></div>
                <button type="submit"
                        class="w-full py-2.5 text-white text-sm font-medium rounded-lg transition-colors"
                        style="background: linear-gradient(135deg, #10b981, #2563eb);">
                    Upload &amp; Analyse
                </button>
            </form>
        </div>

        {{-- Recent health records --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Recent Health Records</h2>
                <a href="{{ route('documents') }}" class="text-xs text-emerald-600 hover:underline">View all</a>
            </div>
            @if($recentDocs->isEmpty())
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-400">No health records yet. Upload your first document.</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($recentDocs as $doc)
                        <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-xs font-bold
                                {{ $doc->category === 'medical_report' ? 'bg-emerald-100 text-emerald-700' : ($doc->category === 'prescription' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $doc->category === 'medical_report' ? 'MR' : ($doc->category === 'prescription' ? 'Rx' : 'GN') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $doc->title }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $doc->analysis_result ? '✓ Analysed · ' : '' }}{{ $doc->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0
                                {{ $doc->status === 'ready' ? 'bg-green-100 text-green-700' : ($doc->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ $doc->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <a href="{{ route('health-profile') }}"
           class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-emerald-300 hover:shadow-sm transition-all flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 group-hover:bg-emerald-100 flex items-center justify-center flex-shrink-0 transition-colors">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">Health Profile</p>
                <p class="text-xs text-gray-400 mt-0.5">View your analyses & insights</p>
            </div>
        </a>

        <a href="{{ route('chat') }}"
           class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center flex-shrink-0 transition-colors">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">Health AI Chat</p>
                <p class="text-xs text-gray-400 mt-0.5">Ask questions about your reports</p>
            </div>
        </a>

        <a href="{{ route('documents') }}"
           class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-purple-300 hover:shadow-sm transition-all flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-purple-50 group-hover:bg-purple-100 flex items-center justify-center flex-shrink-0 transition-colors">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">Health Records</p>
                <p class="text-xs text-gray-400 mt-0.5">Manage all uploaded documents</p>
            </div>
        </a>

    </div>

    {{-- Recent AI activity --}}
    @if($recentLogs->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Recent AI Activity</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b border-gray-100">
                        <th class="pb-3 font-medium">Time</th>
                        <th class="pb-3 font-medium">Model</th>
                        <th class="pb-3 font-medium">Query Preview</th>
                        <th class="pb-3 font-medium">Duration</th>
                        <th class="pb-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentLogs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 text-gray-400 text-xs whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</td>
                        <td class="py-3 font-mono text-xs text-gray-600">{{ $log->model }}</td>
                        <td class="py-3 text-gray-700 max-w-xs truncate">{{ $log->prompt_preview }}</td>
                        <td class="py-3 text-gray-400 text-xs">{{ $log->duration_ms ? $log->duration_ms . 'ms' : '—' }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $log->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
const TOKEN = '{{ session("api_token", "") }}';

const typeEl     = document.getElementById('docType');
const fileDiv    = document.getElementById('fileInput');
const fileEl     = document.getElementById('docFile');
const fileHint   = document.getElementById('fileHint');
const textDiv    = document.getElementById('textInput');
const urlDiv     = document.getElementById('urlInput');
const statusEl   = document.getElementById('uploadStatus');

const FILE_TYPES = { pdf: '.pdf', docx: '.docx', txt: '.txt', image: '.jpg,.jpeg,.png,.webp' };
const FILE_HINTS = {
    pdf:   'PDF up to 20 MB',
    docx:  'Word document (.docx) up to 20 MB',
    txt:   'Plain text file (.txt) up to 20 MB',
    image: 'JPG or PNG image up to 20 MB (OCR will extract text)',
};

typeEl.addEventListener('change', () => {
    const t = typeEl.value;
    const isFile = t in FILE_TYPES;
    fileDiv.classList.toggle('hidden', !isFile);
    textDiv.classList.toggle('hidden', t !== 'text');
    urlDiv.classList.toggle('hidden',  t !== 'url');
    if (isFile) {
        fileEl.accept = FILE_TYPES[t];
        fileHint.textContent = FILE_HINTS[t];
    }
});

document.getElementById('uploadForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const title    = document.getElementById('docTitle').value.trim();
    const type     = typeEl.value;
    const category = document.getElementById('docCategory').value;
    if (!title) return;

    statusEl.className   = 'text-sm text-blue-600';
    statusEl.textContent = 'Uploading…';
    statusEl.classList.remove('hidden');

    try {
        let body, headers;

        if (type in FILE_TYPES) {
            const file = fileEl.files[0];
            if (!file) { statusEl.className = 'text-sm text-red-600'; statusEl.textContent = 'Select a file.'; return; }
            const fd = new FormData();
            fd.append('title',    title);
            fd.append('type',     type);
            fd.append('category', category);
            fd.append('file',     file);
            body    = fd;
            headers = { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Authorization': 'Bearer ' + TOKEN };
        } else {
            const content = type === 'url'
                ? document.getElementById('docUrl').value.trim()
                : document.getElementById('docContent').value.trim();
            if (!content) { statusEl.className = 'text-sm text-red-600'; statusEl.textContent = 'Content required.'; return; }
            body    = JSON.stringify({ title, type, category, [type === 'url' ? 'url' : 'content']: content });
            headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Authorization': 'Bearer ' + TOKEN };
        }

        const res  = await fetch('/api/v1/documents', { method: 'POST', headers, body });
        const data = await res.json();

        if (res.ok) {
            statusEl.className   = 'text-sm text-emerald-600';
            statusEl.textContent = `✓ "${data.title}" uploaded — AI analysis running in background`;
            e.target.reset();
            typeEl.dispatchEvent(new Event('change'));
            setTimeout(() => location.reload(), 3000);
        } else {
            statusEl.className   = 'text-sm text-red-600';
            statusEl.textContent = data.message ?? 'Upload failed.';
        }
    } catch (err) {
        statusEl.className   = 'text-sm text-red-600';
        statusEl.textContent = err.message;
    }
});
</script>
@endpush
