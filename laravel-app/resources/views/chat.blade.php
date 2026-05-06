<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health AI Chat — {{ auth()->check() ? auth()->user()->tenant->name : 'AI Health Intelligence' }}</title>
    @auth
    <meta name="session-token"  content="{{ session('api_token', '') }}">
    <meta name="default-model"  content="{{ auth()->user()->tenant->default_model ?? 'phi' }}">
    <meta name="default-system" content="{{ auth()->user()->tenant->system_prompt ?? '' }}">
    @endauth
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #f0f4f8;
            --surface:   #ffffff;
            --border:    #e2e8f0;
            --text:      #0f172a;
            --muted:     #64748b;
            --primary:   #10b981;
            --primary2:  #2563eb;
            --radius:    12px;
            --font: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
        }

        html, body { height: 100%; font-family: var(--font); background: var(--bg); color: var(--text); font-size: 14px; }

        /* ── Layout ── */
        .app { display: flex; flex-direction: column; height: 100vh; max-width: 960px; margin: 0 auto; }

        /* ── Header ── */
        .header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 20px; height: 56px; background: var(--surface);
            border-bottom: 1px solid var(--border); flex-shrink: 0; gap: 12px;
        }
        .header-left  { display: flex; align-items: center; gap: 10px; }
        .header-brand { display: flex; align-items: center; gap: 8px; }
        .brand-logo   {
            width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #10b981, #2563eb); flex-shrink: 0;
        }
        .brand-logo svg { width: 16px; height: 16px; color: #fff; }
        .brand-name   { font-size: 14px; font-weight: 700; color: var(--text); }
        .back-link    { font-size: 12px; color: var(--muted); text-decoration: none; padding: 4px 8px; border-radius: 6px; transition: background .15s; }
        .back-link:hover { background: var(--bg); color: var(--text); }
        .header-sep   { width: 1px; height: 20px; background: var(--border); }

        .specialist-pill {
            display: flex; align-items: center; gap: 6px; padding: 4px 10px;
            background: var(--bg); border: 1px solid var(--border); border-radius: 20px;
            font-size: 12px; font-weight: 500; color: var(--text); cursor: pointer;
            transition: border-color .15s;
        }
        .specialist-pill:hover { border-color: var(--primary); }
        .specialist-pill .s-dot { width: 8px; height: 8px; border-radius: 50%; }

        .header-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .session-id   { font-size: 11px; color: var(--muted); font-family: monospace; display: none; }

        .tabs { display: flex; gap: 2px; background: var(--bg); padding: 3px; border-radius: 8px; }
        .tab-btn { border: none; background: transparent; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-family: var(--font); cursor: pointer; color: var(--muted); font-weight: 500; transition: all .15s; }
        .tab-btn:hover { color: var(--text); }
        .tab-btn.active { background: var(--surface); color: var(--text); box-shadow: 0 1px 3px rgba(0,0,0,.08); }

        .btn-ghost { border: 1px solid var(--border); background: transparent; border-radius: 8px; padding: 6px 12px; font-size: 12px; font-family: var(--font); cursor: pointer; color: var(--muted); font-weight: 500; transition: all .15s; }
        .btn-ghost:hover { background: var(--bg); color: var(--text); }

        /* ── Specialist Panel ── */
        .specialist-panel-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.35); z-index: 100;
            align-items: flex-start; justify-content: center; padding-top: 70px;
        }
        .specialist-panel-overlay.open { display: flex; }
        .specialist-panel {
            background: var(--surface); border-radius: 16px; border: 1px solid var(--border);
            padding: 20px; width: 700px; max-width: calc(100vw - 32px); max-height: calc(100vh - 100px);
            overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.15);
        }
        .specialist-panel h3 { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
        .specialist-panel p  { font-size: 12px; color: var(--muted); margin-bottom: 16px; }
        .specialist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px; }
        .s-card {
            border: 2px solid var(--border); border-radius: 12px; padding: 12px 14px;
            cursor: pointer; transition: all .15s; background: var(--bg);
        }
        .s-card:hover { border-color: #94a3b8; background: var(--surface); }
        .s-card.selected { background: var(--surface); }
        .s-card-emoji   { font-size: 22px; margin-bottom: 6px; }
        .s-card-name    { font-size: 12px; font-weight: 600; color: var(--text); line-height: 1.3; }
        .s-card-desc    { font-size: 11px; color: var(--muted); margin-top: 2px; }

        /* ── Settings bar ── */
        .settings {
            display: flex; gap: 8px; padding: 8px 20px; background: #f8fafc;
            border-bottom: 1px solid var(--border); flex-shrink: 0; flex-wrap: wrap; align-items: center;
        }
        .settings-group { display: flex; align-items: center; gap: 6px; }
        .settings label { font-size: 11px; color: var(--muted); font-weight: 600; white-space: nowrap; text-transform: uppercase; letter-spacing: .04em; }
        .settings select, .settings input {
            border: 1px solid var(--border); border-radius: 8px; padding: 5px 10px;
            font-size: 12px; font-family: var(--font); outline: none; background: var(--surface); color: var(--text); height: 32px;
        }
        .settings select:focus, .settings input:focus { border-color: var(--primary); }
        #apiKey { width: 180px; font-family: monospace; }
        .divider { width: 1px; height: 18px; background: var(--border); }

        /* ── Tab panes ── */
        .tab-pane { display: none; flex: 1; overflow: hidden; flex-direction: column; }
        .tab-pane.active { display: flex; }

        /* ── Chat ── */
        .chat { flex: 1; overflow-y: auto; padding: 24px 20px; display: flex; flex-direction: column; gap: 20px; }

        /* Empty state */
        .empty-state { margin: auto; text-align: center; max-width: 400px; }
        .empty-doctor-avatar {
            width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 32px; margin: 0 auto 14px; border: 3px solid var(--border);
        }
        .empty-state h3 { font-size: 17px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
        .empty-state .subtitle { font-size: 13px; color: var(--muted); margin-bottom: 20px; }
        .example-questions { text-align: left; }
        .example-questions p { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 8px; }
        .example-q {
            display: block; width: 100%; text-align: left; border: 1px solid var(--border);
            background: var(--surface); border-radius: 10px; padding: 9px 14px;
            font-size: 13px; color: var(--text); cursor: pointer; margin-bottom: 6px;
            font-family: var(--font); transition: all .15s;
        }
        .example-q:hover { border-color: var(--primary); background: #f0fdf4; color: var(--primary); }

        /* Messages */
        .message { display: flex; gap: 10px; max-width: 86%; }
        .message.user      { align-self: flex-end; flex-direction: row-reverse; }
        .message.assistant { align-self: flex-start; }

        .msg-avatar {
            width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 15px;
            border: 2px solid var(--border); background: var(--surface); margin-top: 2px;
        }
        .msg-avatar.user-av { background: linear-gradient(135deg, #10b981, #2563eb); color: #fff; font-size: 13px; font-weight: 700; border: none; }

        .msg-body { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .msg-meta { font-size: 11px; color: var(--muted); padding: 0 4px; }
        .message.user .msg-meta { text-align: right; }

        .bubble { padding: 12px 16px; border-radius: 16px; line-height: 1.65; word-break: break-word; }
        .message.user      .bubble { background: linear-gradient(135deg, #10b981, #2563eb); color: #fff; border-bottom-right-radius: 4px; }
        .message.assistant .bubble { background: var(--surface); border: 1px solid var(--border); border-bottom-left-radius: 4px; color: var(--text); }

        .thinking-text { color: var(--muted); font-size: 13px; font-style: italic; }
        .thinking-note { font-size: 11px; color: #d97706; display: block; margin-top: 4px; }

        .cursor { display: inline-block; width: 2px; height: 14px; background: var(--muted); border-radius: 1px; margin-left: 2px; vertical-align: middle; animation: blink .8s step-end infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }

        .bubble pre { background: #1e293b; color: #e2e8f0; border-radius: 8px; padding: 14px; overflow-x: auto; font-size: 12.5px; margin: 8px 0; }
        .bubble code { font-family: 'Fira Code', Consolas, monospace; font-size: 12.5px; }
        .bubble p code { background: #f1f5f9; color: #0f172a; padding: 1px 5px; border-radius: 4px; }
        .bubble strong { font-weight: 600; }
        .bubble p { margin-bottom: 6px; }
        .bubble p:last-child { margin-bottom: 0; }
        .bubble ul, .bubble ol { margin: 6px 0 6px 18px; }
        .bubble li { margin-bottom: 3px; }

        .disclaimer-strip {
            font-size: 10.5px; color: var(--muted); background: #f8fafc; border-top: 1px solid var(--border);
            padding: 4px 16px 5px; border-radius: 0 0 12px 12px; display: flex; align-items: center; gap: 4px;
        }

        .error-msg { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 10px 14px; border-radius: var(--radius); font-size: 13px; align-self: center; max-width: 80%; }

        /* ── Input area ── */
        .input-area {
            padding: 12px 20px 16px; background: var(--surface);
            border-top: 1px solid var(--border); flex-shrink: 0;
        }
        .input-wrap {
            display: flex; gap: 8px; align-items: flex-end;
            background: var(--bg); border: 1.5px solid var(--border); border-radius: 14px;
            padding: 8px 8px 8px 14px; transition: border-color .15s;
        }
        .input-wrap:focus-within { border-color: var(--primary); background: var(--surface); }
        #input {
            flex: 1; border: none; background: transparent; resize: none; outline: none;
            font-size: 14px; font-family: var(--font); min-height: 24px; max-height: 140px;
            overflow-y: auto; line-height: 1.5; color: var(--text);
        }
        .send-btn {
            width: 36px; height: 36px; border-radius: 10px; border: none; cursor: pointer; flex-shrink: 0;
            background: linear-gradient(135deg, #10b981, #2563eb); color: #fff; display: flex; align-items: center; justify-content: center;
            transition: opacity .15s;
        }
        .send-btn:disabled { opacity: .4; cursor: not-allowed; }
        .send-btn svg { width: 16px; height: 16px; }
        .input-hint { font-size: 11px; color: var(--muted); text-align: center; margin-top: 6px; }

        /* ── History / Logs ── */
        .panel-wrap    { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 12px; }
        .panel-header  { display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; padding-bottom: 10px; border-bottom: 1px solid var(--border); }
        .session-card  { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 12px 14px; cursor: pointer; transition: border-color .15s; }
        .session-card:hover { border-color: var(--primary); }
        .session-hdr   { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
        .session-id-lbl { font-family: monospace; font-size: 12px; color: var(--primary); font-weight: 600; }
        .session-meta  { font-size: 11px; color: var(--muted); }
        .session-prev  { font-size: 13px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .messages-view { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; }
        .history-msg   { padding: 8px 12px; border-radius: 8px; margin-bottom: 8px; font-size: 13px; line-height: 1.5; }
        .history-msg.user      { background: #f0fdf4; border-left: 3px solid var(--primary); }
        .history-msg.assistant { background: #f8fafc; border-left: 3px solid var(--border); }
        .history-msg-role { font-size: 10px; font-weight: 700; color: var(--muted); margin-bottom: 3px; text-transform: uppercase; letter-spacing: .05em; }
        .empty-panel   { text-align: center; color: var(--muted); padding: 40px; font-size: 13px; }

        /* ── Logs table ── */
        .logs-wrap { flex: 1; overflow: auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: var(--surface); border-radius: var(--radius); overflow: hidden; font-size: 13px; }
        th { background: #f8fafc; color: var(--muted); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border); }
        td { padding: 9px 12px; border-bottom: 1px solid var(--border); vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        .badge-ok  { background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-err { background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .mono      { font-family: monospace; font-size: 12px; color: var(--muted); }
        .log-prompt { max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--muted); }
        .loading-row td { text-align: center; color: var(--muted); padding: 32px; }
    </style>
</head>
<body>
<div class="app">

<!-- ── Header ──────────────────────────────────────────────────────────── -->
<header class="header">
    <div class="header-left">
        @auth
        <a href="{{ route('dashboard') }}" class="back-link">← Dashboard</a>
        <div class="header-sep"></div>
        @endauth
        <div class="header-brand">
            <div class="brand-logo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <span class="brand-name">Health AI Chat</span>
        </div>
        <div class="header-sep"></div>
        <!-- Specialist pill -->
        <button class="specialist-pill" onclick="toggleSpecialistPanel()" id="specialistPill">
            <span id="pillEmoji">🩺</span>
            <span id="pillName">General Practitioner</span>
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" style="color:var(--muted)">
                <path d="M2 3.5L5 6.5L8 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    <div class="header-right">
        <div class="tabs">
            <button class="tab-btn active" data-tab="chat"    onclick="switchTab('chat')">Chat</button>
            <button class="tab-btn"        data-tab="history" onclick="switchTab('history')">History</button>
            <button class="tab-btn"        data-tab="logs"    onclick="switchTab('logs')">Logs</button>
        </div>
        <button class="btn-ghost" id="newChatBtn">New Chat</button>
    </div>
</header>

<!-- ── Specialist Selection Panel ──────────────────────────────────────── -->
<div class="specialist-panel-overlay" id="specialistOverlay" onclick="handleOverlayClick(event)">
    <div class="specialist-panel">
        <h3>Choose Your Specialist</h3>
        <p>Select the type of doctor to consult. The AI will take on that specialist's role.</p>
        <div class="specialist-grid" id="specialistGrid">
            <!-- filled by JS -->
        </div>
    </div>
</div>

<!-- ── Settings bar ─────────────────────────────────────────────────────── -->
<div class="settings" id="settingsBar">
    @guest
    <div class="settings-group">
        <label for="apiKey">API Key</label>
        <input type="password" id="apiKey" placeholder="your-api-key" autocomplete="off">
    </div>
    <div class="divider"></div>
    @endguest
    @auth <input type="hidden" id="apiKey"> @endauth

    <div class="settings-group">
        <label for="model">Model</label>
        <select id="model">
            <option value="phi">phi — fast (3B)</option>
            <option value="llama3">llama3 — quality (8B)</option>
            <option value="gemma4">gemma4 — best (8B)</option>
        </select>
    </div>

    <div class="divider"></div>

    <div class="settings-group" style="flex:1; min-width:0;">
        <label>Active Specialist</label>
        <span id="activeSpecialistLabel" style="font-size:13px;color:var(--text);font-weight:500;">🩺 General Practitioner · General Medicine</span>
    </div>
</div>

<!-- ═══════════════════ CHAT PANE ═══════════════════════════════════════ -->
<div class="tab-pane active" id="pane-chat">
    <div class="chat" id="chat">
        <!-- Empty state filled by JS -->
        <div id="emptyState"></div>
    </div>

    <div class="input-area">
        <div class="input-wrap">
            <textarea id="input" placeholder="Ask your doctor anything…" rows="1"></textarea>
            <button class="send-btn" id="sendBtn" title="Send (Enter)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
        <p class="input-hint">Enter to send · Shift+Enter for new line · <span id="sessionHint"></span></p>
    </div>
</div>

<!-- ═══════════════════ HISTORY PANE ════════════════════════════════════ -->
<div class="tab-pane" id="pane-history">
    <div class="panel-wrap">
        <div class="panel-header">
            <span>Conversation History</span>
            <button class="btn-ghost" onclick="loadHistory()">↻ Refresh</button>
        </div>
        <div id="historyContent"><div class="empty-panel">Switch to this tab to load history</div></div>
    </div>
</div>

<!-- ═══════════════════ LOGS PANE ════════════════════════════════════════ -->
<div class="tab-pane" id="pane-logs">
    <div class="logs-wrap">
        <div class="panel-header" style="margin-bottom:14px">
            <span>Request Logs</span>
            <button class="btn-ghost" onclick="loadLogs()">↻ Refresh</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Time</th><th>Model</th><th>Session</th><th>Prompt</th><th>Duration</th><th>Status</th>
                </tr>
            </thead>
            <tbody id="logsBody">
                <tr class="loading-row"><td colspan="6">Switch to this tab to load logs</td></tr>
            </tbody>
        </table>
    </div>
</div>

</div><!-- .app -->

<script>
// ─────────────────────────────────────────────────────────────────────────────
// SPECIALIST DATA
// ─────────────────────────────────────────────────────────────────────────────
const SPECIALISTS = {
  general_practitioner: {
    label: 'General Practitioner', short: 'General Medicine', emoji: '🩺', color: '#10b981',
    examples: ['What do my blood test results mean?', 'I have had a persistent cough for 2 weeks', 'Should I be concerned about this symptom?'],
    prompt: 'You are Dr. AI, a knowledgeable General Practitioner (GP).\n\nYour role:\n- Help patients understand symptoms, test results, and medical reports\n- Provide general health education and preventive care advice\n- Guide on when to seek specialist or emergency care\n- Explain medical terminology in clear, simple terms\n\n⚠️ DISCLAIMER: You provide informational insights only. You cannot diagnose or prescribe. Always recommend consulting a qualified healthcare provider for medical decisions.'
  },
  cardiologist: {
    label: 'Cardiologist', short: 'Heart & Cardiovascular', emoji: '❤️', color: '#ef4444',
    examples: ['Explain my ECG / EKG results', 'What is my cholesterol level telling me?', 'How serious is high blood pressure?'],
    prompt: 'You are Dr. AI, a knowledgeable Cardiologist specialising in heart and cardiovascular health.\n\nYour role:\n- Explain cardiac test results: ECG, echocardiogram, stress tests, angiograms\n- Discuss conditions: hypertension, arrhythmia, coronary artery disease, heart failure\n- Explain cholesterol panels, blood pressure readings, and cardiac risk factors\n- Discuss cardiovascular medications and lifestyle interventions\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult a cardiologist for heart health decisions.'
  },
  neurologist: {
    label: 'Neurologist', short: 'Brain & Nervous System', emoji: '🧠', color: '#8b5cf6',
    examples: ['Explain my MRI brain scan report', 'What causes chronic migraines?', 'Understanding my nerve conduction results'],
    prompt: 'You are Dr. AI, a knowledgeable Neurologist specialising in brain and nervous system health.\n\nYour role:\n- Explain brain and spine imaging reports (MRI, CT scans)\n- Discuss conditions: migraines, epilepsy, stroke, MS, Parkinson\'s, neuropathy\n- Interpret nerve conduction studies and EEG results\n- Explain neurological medications and treatments\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult a neurologist for neurological health decisions.'
  },
  gastroenterologist: {
    label: 'Gastroenterologist', short: 'Digestive System', emoji: '🫃', color: '#f59e0b',
    examples: ['Explain my endoscopy / colonoscopy report', 'What is IBS and how is it managed?', 'Understanding my liver function tests'],
    prompt: 'You are Dr. AI, a knowledgeable Gastroenterologist specialising in digestive health.\n\nYour role:\n- Explain endoscopy, colonoscopy, and abdominal imaging reports\n- Discuss conditions: IBS, Crohn\'s disease, GERD, liver disease, pancreatitis\n- Interpret liver function tests, H. pylori results, and stool analyses\n- Explain GI medications and dietary interventions\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult a gastroenterologist for digestive health decisions.'
  },
  pulmonologist: {
    label: 'Pulmonologist', short: 'Lungs & Respiratory', emoji: '🫁', color: '#06b6d4',
    examples: ['Explain my pulmonary function / spirometry test', 'Understanding my chest X-ray report', 'What is COPD and how is it managed?'],
    prompt: 'You are Dr. AI, a knowledgeable Pulmonologist specialising in lung and respiratory health.\n\nYour role:\n- Explain pulmonary function tests, chest X-rays, and CT scan reports\n- Discuss conditions: asthma, COPD, pneumonia, sleep apnea, pulmonary fibrosis\n- Explain oxygen saturation, respiratory medications, and breathing exercises\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult a pulmonologist for respiratory health decisions.'
  },
  endocrinologist: {
    label: 'Endocrinologist', short: 'Hormones & Diabetes', emoji: '⚗️', color: '#3b82f6',
    examples: ['Explain my HbA1c and blood sugar levels', 'Understanding my thyroid test (TSH, T3, T4)', 'What is insulin resistance?'],
    prompt: 'You are Dr. AI, a knowledgeable Endocrinologist specialising in hormonal and metabolic health.\n\nYour role:\n- Explain hormone tests: thyroid (TSH, T3, T4), insulin, cortisol, reproductive hormones\n- Discuss conditions: diabetes, hypothyroidism, hyperthyroidism, PCOS, adrenal disorders\n- Interpret HbA1c, blood glucose, and metabolic panels\n- Explain medications like insulin, metformin, and thyroid replacements\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult an endocrinologist for hormonal health decisions.'
  },
  orthopedic: {
    label: 'Orthopedic Specialist', short: 'Bones, Joints & Muscles', emoji: '🦴', color: '#64748b',
    examples: ['Explain my X-ray / MRI joint report', 'Understanding my bone density (DEXA) scan', 'What does this joint or back pain indicate?'],
    prompt: 'You are Dr. AI, a knowledgeable Orthopedic Specialist focusing on musculoskeletal health.\n\nYour role:\n- Explain X-rays, MRI, and bone density scan reports\n- Discuss conditions: fractures, arthritis, osteoporosis, ligament tears, scoliosis, herniated discs\n- Explain orthopedic procedures, rehabilitation, and pain management\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult an orthopedic specialist for musculoskeletal health decisions.'
  },
  dermatologist: {
    label: 'Dermatologist', short: 'Skin, Hair & Nails', emoji: '🧴', color: '#ec4899',
    examples: ['What could this skin condition be?', 'Understanding my skin biopsy report', 'How to manage chronic eczema or psoriasis?'],
    prompt: 'You are Dr. AI, a knowledgeable Dermatologist specialising in skin, hair, and nail health.\n\nYour role:\n- Discuss skin conditions: eczema, psoriasis, acne, rosacea, fungal infections\n- Explain skin biopsy reports and dermatological test results\n- Provide skincare guidance and discuss topical and systemic treatments\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult a dermatologist for skin health decisions.'
  },
  psychiatrist: {
    label: 'Psychiatrist', short: 'Mental Health', emoji: '🧘', color: '#a78bfa',
    examples: ['What is the difference between anxiety and panic disorder?', 'Understanding psychiatric medication side effects', 'Coping strategies for depression and stress'],
    prompt: 'You are Dr. AI, a knowledgeable Psychiatrist providing mental health information and support.\n\nYour role:\n- Explain mental health conditions: depression, anxiety, bipolar disorder, PTSD, ADHD\n- Provide information on psychiatric medications and general effects\n- Share evidence-based coping strategies and psychotherapy approaches\n- Help understand mental health assessments and reports\n\n⚠️ DISCLAIMER: You provide informational support only. Always consult a licensed mental health professional. For emergencies, contact crisis services immediately.'
  },
  pediatrician: {
    label: 'Pediatrician', short: "Children's Health", emoji: '👶', color: '#22d3ee',
    examples: ["Is my child's growth and development on track?", 'Understanding the childhood vaccination schedule', 'What do these pediatric lab results mean?'],
    prompt: "You are Dr. AI, a knowledgeable Pediatrician specialising in children's health from birth through adolescence.\n\nYour role:\n- Explain child development milestones and growth charts\n- Discuss common childhood illnesses, vaccines, and preventive care\n- Interpret pediatric lab results and developmental assessments\n- Provide guidance on nutrition, sleep, and child wellness\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult a pediatrician for your child's health decisions."
  },
  gynecologist: {
    label: "Gynecologist / OB", short: "Women's Health", emoji: '🌸', color: '#f472b6',
    examples: ["Explain my Pap smear / cervical screening results", 'Understanding PCOS symptoms and treatment', 'What do my hormone levels mean?'],
    prompt: "You are Dr. AI, a knowledgeable Gynecologist and Obstetrician specialising in women's reproductive and general health.\n\nYour role:\n- Explain cervical screening, pelvic ultrasound, and gynecological test results\n- Discuss conditions: PCOS, endometriosis, fibroids, menstrual disorders\n- Provide information on reproductive health, pregnancy, and menopause\n- Explain hormonal contraceptives and fertility treatments\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult a gynecologist for women's health decisions."
  },
  oncologist: {
    label: 'Oncologist', short: 'Cancer Care', emoji: '🎗️', color: '#fb923c',
    examples: ['Help me understand my pathology / biopsy report', 'Explain what this cancer staging means', 'What are common side effects of chemotherapy?'],
    prompt: 'You are Dr. AI, a knowledgeable Oncologist providing information about cancer and cancer care.\n\nYour role:\n- Explain pathology reports, tumor markers, and cancer staging systems\n- Discuss treatment types: chemotherapy, radiation, immunotherapy, targeted therapy\n- Help understand cancer test results and medical terminology\n- Provide information on managing treatment side effects and supportive care\n\n⚠️ DISCLAIMER: Cancer care requires specialised medical attention. You provide informational insights only. Always work with your oncology team for all treatment decisions.'
  },
  nephrologist: {
    label: 'Nephrologist', short: 'Kidneys', emoji: '🫘', color: '#84cc16',
    examples: ['Explain my kidney function tests (eGFR, creatinine)', 'Understanding chronic kidney disease stages', 'What do my urine analysis results mean?'],
    prompt: 'You are Dr. AI, a knowledgeable Nephrologist specialising in kidney health.\n\nYour role:\n- Explain kidney function tests: eGFR, creatinine, BUN, urinalysis\n- Discuss conditions: CKD, kidney stones, nephrotic syndrome, dialysis\n- Interpret urine and blood tests related to kidney function\n- Discuss kidney-friendly diets and medications\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult a nephrologist for kidney health decisions.'
  },
  ophthalmologist: {
    label: 'Ophthalmologist', short: 'Eyes & Vision', emoji: '👁️', color: '#0ea5e9',
    examples: ['Explain my eye examination report', 'Understanding my glaucoma test results', 'What does my vision prescription (SPH, CYL, AXIS) mean?'],
    prompt: 'You are Dr. AI, a knowledgeable Ophthalmologist specialising in eye health and vision care.\n\nYour role:\n- Explain eye examination reports, visual field tests, and OCT scan results\n- Discuss eye conditions: glaucoma, cataracts, macular degeneration, diabetic retinopathy\n- Interpret vision prescriptions and contact lens parameters\n- Discuss eye treatments, surgeries, and medications\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult an ophthalmologist for eye health decisions.'
  },
  ent_specialist: {
    label: 'ENT Specialist', short: 'Ear, Nose & Throat', emoji: '👂', color: '#f97316',
    examples: ['Explain my audiogram / hearing test results', 'Understanding chronic sinusitis treatment', 'What does this throat or larynx examination show?'],
    prompt: 'You are Dr. AI, a knowledgeable ENT (Ear, Nose and Throat) Specialist.\n\nYour role:\n- Explain audiograms, hearing tests, and ear-related test results\n- Discuss conditions: sinusitis, tonsillitis, hearing loss, vertigo, sleep apnea, thyroid nodules\n- Interpret nasal endoscopy and laryngoscopy reports\n- Discuss ENT treatments, medications, and surgical options\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult an ENT specialist for ear, nose, and throat health decisions.'
  },
  rheumatologist: {
    label: 'Rheumatologist', short: 'Joints & Autoimmune', emoji: '🦿', color: '#14b8a6',
    examples: ['Explain my rheumatoid factor and ANA blood tests', 'Understanding lupus symptoms and management', 'What does chronic joint inflammation indicate?'],
    prompt: 'You are Dr. AI, a knowledgeable Rheumatologist specialising in joints, muscles, and autoimmune conditions.\n\nYour role:\n- Explain autoimmune blood tests: ANA, RF, anti-CCP, ESR, CRP\n- Discuss conditions: rheumatoid arthritis, lupus, gout, fibromyalgia, ankylosing spondylitis\n- Interpret imaging results for joints and soft tissue\n- Discuss immunosuppressive medications and biologic therapies\n\n⚠️ DISCLAIMER: You provide informational insights only. Always consult a rheumatologist for autoimmune and joint health decisions.'
  },
  nutritionist: {
    label: 'Nutritionist / Dietitian', short: 'Nutrition & Diet', emoji: '🥗', color: '#65a30d',
    examples: ['What should I eat with my diabetes / heart condition?', 'Understanding my vitamin and mineral deficiency results', 'Create a general diet plan for better health'],
    prompt: 'You are Dr. AI, a knowledgeable Nutritionist and Registered Dietitian providing evidence-based nutrition guidance.\n\nYour role:\n- Provide dietary advice based on health conditions\n- Explain nutritional test results: vitamin levels, mineral deficiencies, metabolic markers\n- Discuss therapeutic diets for diabetes, heart disease, kidney disease, celiac disease\n- Share meal planning strategies and evidence-based nutritional interventions\n\n⚠️ DISCLAIMER: Nutritional advice should be personalised to your specific health condition. Always consult a registered dietitian or healthcare provider for medical nutrition therapy.'
  },
};

// ─────────────────────────────────────────────────────────────────────────────
// STATE & HELPERS
// ─────────────────────────────────────────────────────────────────────────────
const LS = { get: (k, d='') => localStorage.getItem(k) ?? d, set: (k,v) => localStorage.setItem(k, v) };

let currentSpecialist = LS.get('specialist_key', 'general_practitioner');

function newSessionId() { const id = 'hc-' + Math.random().toString(36).slice(2,10); LS.set('session_id', id); return id; }
function getSessionId() { return LS.get('session_id') || newSessionId(); }
function getApiKey()    { return document.getElementById('apiKey').value.trim(); }

// ─────────────────────────────────────────────────────────────────────────────
// SPECIALIST PANEL
// ─────────────────────────────────────────────────────────────────────────────
function buildSpecialistGrid() {
    const grid = document.getElementById('specialistGrid');
    grid.innerHTML = '';
    Object.entries(SPECIALISTS).forEach(([key, s]) => {
        const card = document.createElement('div');
        card.className = 's-card' + (key === currentSpecialist ? ' selected' : '');
        card.style.borderColor = key === currentSpecialist ? s.color : '';
        card.innerHTML = `
            <div class="s-card-emoji">${s.emoji}</div>
            <div class="s-card-name">${s.label}</div>
            <div class="s-card-desc">${s.short}</div>`;
        card.onclick = () => selectSpecialist(key);
        grid.appendChild(card);
    });
}

function toggleSpecialistPanel() {
    const ov = document.getElementById('specialistOverlay');
    if (ov.classList.contains('open')) { ov.classList.remove('open'); }
    else { buildSpecialistGrid(); ov.classList.add('open'); }
}

function handleOverlayClick(e) {
    if (e.target === document.getElementById('specialistOverlay')) toggleSpecialistPanel();
}

function selectSpecialist(key) {
    currentSpecialist = key;
    LS.set('specialist_key', key);
    const s = SPECIALISTS[key];
    document.getElementById('pillEmoji').textContent = s.emoji;
    document.getElementById('pillName').textContent  = s.label;
    document.getElementById('activeSpecialistLabel').textContent = s.emoji + ' ' + s.label + ' · ' + s.short;
    document.getElementById('input').placeholder = 'Ask your ' + s.label + ' anything…';
    toggleSpecialistPanel();
    resetEmptyState();
}

function getSpecialistPrompt() {
    const base = SPECIALISTS[currentSpecialist]?.prompt ?? SPECIALISTS.general_practitioner.prompt;
    const custom = document.querySelector('meta[name="default-system"]')?.content;
    return custom && custom.trim() ? custom.trim() : base;
}

// ─────────────────────────────────────────────────────────────────────────────
// EMPTY STATE
// ─────────────────────────────────────────────────────────────────────────────
function resetEmptyState() {
    const s   = SPECIALISTS[currentSpecialist] || SPECIALISTS.general_practitioner;
    const es  = document.getElementById('emptyState');
    es.style.display = '';
    es.className = 'empty-state';
    es.innerHTML = `
        <div class="empty-doctor-avatar" style="border-color:${s.color};background:${s.color}18;">${s.emoji}</div>
        <h3>Dr. AI — ${s.label}</h3>
        <p class="subtitle">${s.short} · Powered by Ollama</p>
        <div class="example-questions">
            <p>Try asking:</p>
            ${s.examples.map(ex => `<button class="example-q" onclick="sendExample(this.textContent)">${ex}</button>`).join('')}
        </div>`;
}

function sendExample(text) {
    document.getElementById('input').value = text;
    send();
}

// ─────────────────────────────────────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────────────────────────────────────
const sessionToken  = document.querySelector('meta[name="session-token"]')?.content;
const defaultModel  = document.querySelector('meta[name="default-model"]')?.content;

const apiKeyEl  = document.getElementById('apiKey');
const modelEl   = document.getElementById('model');
const inputEl   = document.getElementById('input');
const sendBtn   = document.getElementById('sendBtn');
const chatEl    = document.getElementById('chat');
const emptyEl   = document.getElementById('emptyState');

if (sessionToken) { apiKeyEl.value = sessionToken; LS.set('api_key', sessionToken); }
else              { apiKeyEl.value = LS.get('api_key'); }

modelEl.value = LS.get('model', 'phi');
apiKeyEl.addEventListener('change', () => LS.set('api_key', apiKeyEl.value));
modelEl.addEventListener('change',  () => LS.set('model', modelEl.value));

// Apply saved specialist
const saved = LS.get('specialist_key', 'general_practitioner');
if (SPECIALISTS[saved]) {
    currentSpecialist = saved;
    const s = SPECIALISTS[saved];
    document.getElementById('pillEmoji').textContent = s.emoji;
    document.getElementById('pillName').textContent  = s.label;
    document.getElementById('activeSpecialistLabel').textContent = s.emoji + ' ' + s.label + ' · ' + s.short;
    inputEl.placeholder = 'Ask your ' + s.label + ' anything…';
}

document.getElementById('sessionHint').textContent = 'Session: ' + getSessionId();
resetEmptyState();

// ─────────────────────────────────────────────────────────────────────────────
// TABS
// ─────────────────────────────────────────────────────────────────────────────
function switchTab(tab) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('pane-' + tab).classList.add('active');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    document.getElementById('settingsBar').style.display  = tab === 'chat' ? '' : 'none';
    document.getElementById('newChatBtn').style.display   = tab === 'chat' ? '' : 'none';
    if (tab === 'history') loadHistory();
    if (tab === 'logs')    loadLogs();
}

document.getElementById('newChatBtn').addEventListener('click', () => {
    const id = newSessionId();
    document.getElementById('sessionHint').textContent = 'Session: ' + id;
    chatEl.innerHTML = '';
    chatEl.appendChild(emptyEl);
    resetEmptyState();
});

// ─────────────────────────────────────────────────────────────────────────────
// MARKDOWN
// ─────────────────────────────────────────────────────────────────────────────
function renderMarkdown(raw) {
    let s = raw.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    s = s.replace(/```[\w]*\n?([\s\S]*?)```/g, (_, c) => `<pre><code>${c.trimEnd()}</code></pre>`);
    s = s.replace(/`([^`\n]+)`/g, '<code>$1</code>');
    s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    s = s.replace(/^[-*]\s+(.+)/gm, '<li>$1</li>');
    s = s.replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>');
    return s.split(/\n{2,}/).map(p => p.startsWith('<pre>') || p.startsWith('<ul>') ? p : '<p>' + p.replace(/\n/g,'<br>') + '</p>').join('');
}

// ─────────────────────────────────────────────────────────────────────────────
// MESSAGES
// ─────────────────────────────────────────────────────────────────────────────
function appendMessage(role, text = '') {
    emptyEl.style.display = 'none';
    const sp = SPECIALISTS[currentSpecialist] || SPECIALISTS.general_practitioner;

    const wrap   = document.createElement('div');
    wrap.className = 'message ' + role;

    const avatar = document.createElement('div');
    avatar.className = 'msg-avatar' + (role === 'user' ? ' user-av' : '');
    avatar.textContent = role === 'user'
        ? ('{{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : "U" }}')
        : sp.emoji;
    if (role === 'assistant') avatar.style.borderColor = sp.color;

    const body   = document.createElement('div');
    body.className = 'msg-body';

    const meta   = document.createElement('div');
    meta.className = 'message-meta msg-meta';
    meta.textContent = role === 'user' ? 'You' : ('Dr. AI · ' + sp.label);

    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    if (text) bubble.innerHTML = renderMarkdown(text);

    body.appendChild(meta);
    body.appendChild(bubble);

    if (role === 'assistant') {
        wrap.appendChild(avatar);
        wrap.appendChild(body);
    } else {
        wrap.appendChild(body);
        wrap.appendChild(avatar);
    }

    chatEl.appendChild(wrap);
    scrollBottom();
    return bubble;
}

function appendError(msg) {
    emptyEl.style.display = 'none';
    const d = document.createElement('div');
    d.className = 'error-msg';
    d.textContent = '⚠ ' + msg;
    chatEl.appendChild(d);
    scrollBottom();
}

function scrollBottom() { chatEl.scrollTop = chatEl.scrollHeight; }

// ─────────────────────────────────────────────────────────────────────────────
// THINKING INDICATOR
// ─────────────────────────────────────────────────────────────────────────────
function startThinking(bubble) {
    let secs = 0;
    const label = document.createElement('span');
    label.className = 'thinking-text';
    label.textContent = 'Thinking…';
    const note = document.createElement('span');
    note.className = 'thinking-note';
    bubble.appendChild(label);
    bubble.appendChild(note);
    const timer = setInterval(() => {
        secs++;
        label.textContent = 'Thinking… ' + secs + 's';
        if (secs === 10) note.textContent = 'Model may be loading — first request can take 30–60s';
        if (secs === 45) note.textContent = 'Still loading large model into memory…';
    }, 1000);
    return { timer, label, note };
}

function stopThinking(t) { clearInterval(t.timer); t.label?.remove(); t.note?.remove(); }

// ─────────────────────────────────────────────────────────────────────────────
// STOP / STREAMING STATE
// ─────────────────────────────────────────────────────────────────────────────
let currentAbortController = null;
let isStreaming = false;

const SEND_ICON = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>';
const STOP_ICON = '<svg fill="currentColor" viewBox="0 0 24 24"><rect x="5" y="5" width="14" height="14" rx="2.5"/></svg>';

function setStreamingState(on) {
    isStreaming = on;
    const btn = document.getElementById('sendBtn');
    btn.innerHTML = on ? STOP_ICON : SEND_ICON;
    btn.title     = on ? 'Stop generation' : 'Send (Enter)';
    btn.style.background = on
        ? 'linear-gradient(135deg, #ef4444, #b91c1c)'
        : '';
}

function stopStreaming() {
    if (currentAbortController) {
        currentAbortController.abort();
        currentAbortController = null;
    }
}

function handleSendBtn() {
    if (isStreaming) stopStreaming();
    else             send();
}

// ─────────────────────────────────────────────────────────────────────────────
// SEND
// ─────────────────────────────────────────────────────────────────────────────
async function send() {
    const prompt = inputEl.value.trim();
    if (!prompt || isStreaming) return;

    const apiKey = getApiKey();
    if (!apiKey) { appendError('API key not set.'); return; }

    appendMessage('user', prompt);
    inputEl.value = '';
    autoResize();

    const bubble = appendMessage('assistant');
    const cursor = document.createElement('span');
    cursor.className = 'cursor';
    bubble.appendChild(cursor);

    const thinking = startThinking(bubble);
    let rawText = '', firstToken = false;

    currentAbortController = new AbortController();
    setStreamingState(true);

    try {
        const res = await fetch('/api/v1/chat/sse', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + apiKey, 'Accept': 'text/event-stream' },
            body: JSON.stringify({
                prompt,
                session_id: getSessionId(),
                model:      modelEl.value,
                system:     getSpecialistPrompt(),
            }),
            signal: currentAbortController.signal,
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => ({ message: 'HTTP ' + res.status }));
            bubble.parentElement?.remove();
            appendError(errData.message ?? 'HTTP ' + res.status);
            return;
        }

        const reader  = res.body.getReader();
        const decoder = new TextDecoder();
        let   buf     = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            buf += decoder.decode(value, { stream: true });
            const lines = buf.split('\n');
            buf = lines.pop();

            for (const line of lines) {
                if (!line.startsWith('data: ')) continue;
                const data = line.slice(6);
                if (data === 'true') continue;

                if (!firstToken) {
                    firstToken = true;
                    stopThinking(thinking);
                    cursor.remove();
                }

                rawText += data.replace(/\\n/g, '\n');
                bubble.textContent = rawText;
                bubble.appendChild(cursor);
                scrollBottom();
            }
        }

        // Stream finished naturally
        cursor.remove();
        finalizeResponse(bubble, rawText, false);

    } catch (err) {
        stopThinking(thinking);
        cursor.remove();

        if (err.name === 'AbortError') {
            // User stopped — keep whatever arrived, finalize it
            finalizeResponse(bubble, rawText, true);
        } else {
            bubble.parentElement?.remove();
            appendError(err.message);
        }
    } finally {
        currentAbortController = null;
        setStreamingState(false);
        inputEl.focus();
        scrollBottom();
    }
}

function finalizeResponse(bubble, rawText, wasStopped) {
    if (!rawText) {
        if (wasStopped) bubble.parentElement?.remove();
        else bubble.textContent = '(no response)';
        return;
    }
    bubble.innerHTML = renderMarkdown(rawText);
    const strip = document.createElement('div');
    strip.className = 'disclaimer-strip';
    const stopNote = wasStopped ? ' <em style="color:#d97706">(stopped)</em>' : '';
    strip.innerHTML = '<svg width="10" height="10" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg> Informational only — not a substitute for professional medical advice.' + stopNote;
    bubble.appendChild(strip);
}

// ─────────────────────────────────────────────────────────────────────────────
// HISTORY
// ─────────────────────────────────────────────────────────────────────────────
async function loadHistory() {
    const el = document.getElementById('historyContent');
    el.innerHTML = '<div class="empty-panel">Loading…</div>';
    const apiKey = getApiKey();
    if (!apiKey) { el.innerHTML = '<div class="empty-panel">Set your API key in the Chat tab first.</div>'; return; }

    try {
        const res  = await fetch('/api/v1/conversations', { headers: { 'Authorization': 'Bearer ' + apiKey } });
        const data = await res.json();
        if (!Array.isArray(data) || !data.length) { el.innerHTML = '<div class="empty-panel">No conversations yet.</div>'; return; }
        el.innerHTML = '';
        data.forEach(conv => {
            const card = document.createElement('div');
            card.className = 'session-card';
            card.innerHTML = `
                <div class="session-hdr">
                    <span class="session-id-lbl">${esc(conv.session_id)}</span>
                    <span class="session-meta">${conv.message_count} msgs · ${timeAgo(conv.last_active)}</span>
                </div>
                <div class="session-prev">${esc(conv.last_message || '—')}</div>`;
            card.onclick = () => loadSession(conv.session_id, el);
            el.appendChild(card);
        });
    } catch (e) { el.innerHTML = `<div class="empty-panel" style="color:#dc2626">Error: ${esc(e.message)}</div>`; }
}

async function loadSession(sessionId, container) {
    const apiKey = getApiKey();
    try {
        const res  = await fetch('/api/v1/conversations/' + encodeURIComponent(sessionId), { headers: { 'Authorization': 'Bearer ' + apiKey } });
        const data = await res.json();
        document.getElementById('messages-view-panel')?.remove();
        const panel = document.createElement('div');
        panel.id = 'messages-view-panel';
        panel.className = 'messages-view';
        panel.innerHTML = `
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <button class="btn-ghost" onclick="document.getElementById('messages-view-panel').remove()">✕</button>
                <span class="mono">${esc(sessionId)}</span>
            </div>
            ${(data.messages || []).map(m => `
                <div class="history-msg ${m.role}">
                    <div class="history-msg-role">${m.role}</div>
                    <div>${esc(m.content)}</div>
                </div>`).join('')}`;
        const first = container.querySelector('.session-card');
        if (first) container.insertBefore(panel, first); else container.appendChild(panel);
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (e) { appendError('Could not load session: ' + e.message); }
}

// ─────────────────────────────────────────────────────────────────────────────
// LOGS
// ─────────────────────────────────────────────────────────────────────────────
async function loadLogs() {
    const tbody = document.getElementById('logsBody');
    tbody.innerHTML = '<tr class="loading-row"><td colspan="6">Loading…</td></tr>';
    const apiKey = getApiKey();
    if (!apiKey) { tbody.innerHTML = '<tr class="loading-row"><td colspan="6">Set API key in Chat tab first.</td></tr>'; return; }

    try {
        const res  = await fetch('/api/v1/ai/logs', { headers: { 'Authorization': 'Bearer ' + apiKey } });
        const data = await res.json();
        if (!Array.isArray(data) || !data.length) { tbody.innerHTML = '<tr class="loading-row"><td colspan="6">No logs yet.</td></tr>'; return; }
        tbody.innerHTML = data.map(log => `
            <tr>
                <td class="mono">${shortTime(log.created_at)}</td>
                <td class="mono">${esc(log.model)}</td>
                <td class="mono" style="font-size:11px">${esc(log.session_id)}</td>
                <td class="log-prompt">${esc(log.prompt_preview)}</td>
                <td class="mono">${log.duration_ms != null ? (log.duration_ms/1000).toFixed(1)+'s' : '—'}</td>
                <td>${log.status === 'success' ? '<span class="badge-ok">ok</span>' : `<span class="badge-err" title="${esc(log.error||'')}">err</span>`}</td>
            </tr>`).join('');
    } catch (e) { tbody.innerHTML = `<tr class="loading-row"><td colspan="6" style="color:#dc2626">Error: ${esc(e.message)}</td></tr>`; }
}

// ─────────────────────────────────────────────────────────────────────────────
// UTILITIES
// ─────────────────────────────────────────────────────────────────────────────
function esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function timeAgo(iso) {
    if (!iso) return '';
    const d = Math.floor((Date.now() - new Date(iso))/1000);
    if (d < 60) return d+'s ago'; if (d < 3600) return Math.floor(d/60)+'m ago';
    if (d < 86400) return Math.floor(d/3600)+'h ago'; return Math.floor(d/86400)+'d ago';
}
function shortTime(iso) { return iso ? new Date(iso).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit',second:'2-digit'}) : ''; }

function autoResize() { inputEl.style.height='auto'; inputEl.style.height=Math.min(inputEl.scrollHeight,140)+'px'; }
inputEl.addEventListener('input', autoResize);
inputEl.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (isStreaming) stopStreaming();
        else             send();
    }
});
sendBtn.addEventListener('click', handleSendBtn);
inputEl.focus();
</script>
</body>
</html>
