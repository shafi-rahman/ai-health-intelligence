# AI Health Intelligence

A self-hosted, privacy-first AI health assistant built with Laravel 12, Ollama, and Qdrant. Upload your medical reports and prescriptions, get structured AI analysis, chat with specialist doctor roles, and generate shareable health profiles — all running locally on your own machine with no data sent to the cloud.

![PHP](https://img.shields.io/badge/PHP-8.2+-blue) ![Laravel](https://img.shields.io/badge/Laravel-12-red) ![Ollama](https://img.shields.io/badge/Ollama-local%20LLM-green) ![Qdrant](https://img.shields.io/badge/Qdrant-vector%20DB-purple)

---

## Features

- **Medical Report Analyzer** — Upload PDF or image reports; AI extracts key findings, risk level, concerns, and recommendations
- **Prescription Analyzer** — Parses prescriptions into structured medicine cards (dosage, frequency, purpose, warnings)
- **Health Recommendation Engine** — Generates personalized diet, lifestyle, exercise, and daily routine plans based on your documents
- **AI Health Chat** — Chat with 17 specialist doctor roles (Cardiologist, Neurologist, Oncologist, etc.) powered by your own documents via RAG; switch models per session; stop streaming mid-response at any time
- **Shareable Health Profile** — Generate a time-limited token link to share your health summary
- **Multi-tenant** — Each organization or individual user has isolated data
- **100% local** — All AI runs through Ollama; no API keys required, no data leaves your machine

---

## Architecture

```
Browser  →  Laravel 12 (Blade + REST API)
               ↓
         AIManager (RAG pipeline)
           ↙          ↘
    OllamaProvider    EmbeddingService
    (LLM chat)        (nomic-embed-text)
                           ↓
                      Qdrant (vector search)
                           ↑
               ProcessDocumentJob (indexing)
               AnalyzeMedicalDocumentJob (analysis)
```

---

## Requirements

| Requirement   | Version | Notes                                                               |
|---------------|---------|---------------------------------------------------------------------|
| PHP           | 8.2+    | With extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, `zip` |
| Composer      | 2.x     |                                                                     |
| MySQL         | 8.0+    | Or MariaDB 10.6+                                                    |
| Node.js       | 18+     | Only needed if you modify assets (TailwindCSS via CDN, so optional) |
| Ollama        | latest  | [ollama.com](https://ollama.com) — runs LLMs locally                |
| Qdrant        | 1.7+    | Vector database — run via Docker (one command, see below)           |
| Tesseract OCR | 5.x     | **Optional** — only needed to process image-based prescriptions     |

---

## Ollama Models

You need to pull these models before the app works. Run these commands after installing Ollama:

```bash
# Required — embedding model for RAG (document search)
ollama pull nomic-embed-text

# Required — at least one chat model
ollama pull phi          # 3B params · Fastest · Good for quick answers
ollama pull llama3       # 8B params · Balanced · Recommended default
ollama pull gemma4       # 8B params · Best quality · Slower but thorough
```

> **Note:** You need `nomic-embed-text` running for document uploads to work. Chat requires at least one of the three models above.

### Model Comparison

| Model | Size | Speed | Quality | Best For |
|-------|------|-------|---------|----------|
| `phi` | ~2 GB | Fast | Good | Quick questions, low-spec hardware |
| `llama3` | ~5 GB | Medium | Very Good | General use, recommended |
| `gemma4` | ~5 GB | Slower | Best | Detailed analysis, medical reports |
| `nomic-embed-text` | ~300 MB | Fast | — | Embeddings (required, not a chat model) |

### Other Compatible Models (Optional)

You can use any Ollama-compatible model by changing `default_model` in Settings. These work well for health contexts:

```bash
ollama pull mistral       # 7B · Fast and capable
ollama pull meditron      # 7B · Fine-tuned on medical literature (recommended if available)
ollama pull medllama3     # 8B · Medical fine-tune of Llama 3
ollama pull llama3.1      # 8B · Updated Llama 3
ollama pull qwen2.5       # 7B · Strong multilingual support
```

> To use a custom model, select it in **Settings → Default Model** (edit the model list in `config/ai.php` and `resources/views/settings.blade.php`).

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-username/ai-health-intelligence.git
cd ai-health-intelligence/laravel-app
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Set up environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure the database

Open `.env` and update:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_health_intelligence
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database in MySQL:

```sql
CREATE DATABASE ai_health_intelligence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Start Qdrant (vector database)

```bash
docker run -d --name qdrant -p 6333:6333 qdrant/qdrant
```

Verify it's running:

```bash
curl http://localhost:6333/
# Should return: {"title":"qdrant - vector search engine","version":"..."}
```

> The `health_documents` collection is created automatically on your first document upload.

### 7. Install and start Ollama

Download from [ollama.com](https://ollama.com), then:

```bash
# Verify Ollama is running
ollama list

# Pull required models
ollama pull nomic-embed-text
ollama pull phi
ollama pull llama3
ollama pull gemma4
```

Ollama runs on `http://localhost:11434` by default.

### 8. (Optional) Install Tesseract for image OCR

Only needed if you want to upload image-format prescriptions (JPG, PNG, WebP).

**Windows:**
```
Download from: https://github.com/UB-Mannheim/tesseract/wiki
Add to PATH: C:\Program Files\Tesseract-OCR
```

**macOS:**
```bash
brew install tesseract
```

**Linux:**
```bash
sudo apt install tesseract-ocr
```

---

## Running Locally

You need two processes running simultaneously — the web server and the queue worker.

### Option A — Single command (recommended)

```bash
composer run dev
```

This starts both the web server and queue worker using `concurrently`.

### Option B — Two separate terminals

**Terminal 1 — Web server:**
```bash
php artisan serve
```

**Terminal 2 — Queue worker** (required for document processing and AI analysis):
```bash
php artisan queue:listen --tries=1 --timeout=0
```

Then open: **http://127.0.0.1:8000**

---

## First Use

1. Go to `http://127.0.0.1:8000/register` and create an account
2. From the **Dashboard**, upload a medical report — select category `Medical Report`
3. The queue worker processes it: extracts text → indexes in Qdrant → runs AI analysis
4. View the structured analysis in **Health Records**
5. Visit **Health Profile** to see summaries and get personalized recommendations
6. Open **Health AI Chat** and select a specialist doctor role to start chatting
   - Default model is **phi** (fastest); switch to `llama3` or `gemma4` from the dropdown anytime — your choice is remembered per browser
   - Click the **stop button** (turns red during generation) or press **Enter** to cancel a response mid-stream

---

## Environment Variables Reference

```env
# App
APP_NAME="AI Health Intelligence"
APP_URL=http://localhost

# Database
DB_DATABASE=ai_health_intelligence
DB_USERNAME=root
DB_PASSWORD=

# Queue (database driver — no Redis needed)
QUEUE_CONNECTION=database

# Ollama
OLLAMA_URL=http://localhost:11434/api/chat
OLLAMA_EMBEDDING_MODEL=nomic-embed-text
OLLAMA_NUM_CTX=4096          # Context window — increase for longer documents
OLLAMA_NUM_PREDICT=1024      # Max tokens per response

# Qdrant
QDRANT_URL=http://localhost:6333
QDRANT_COLLECTION=health_documents
QDRANT_VECTOR_SIZE=768       # Must match nomic-embed-text output (768-dim)
```

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── Web/                    # Blade page controllers
│   │   ├── DashboardController.php
│   │   ├── HealthProfileWebController.php
│   │   └── DocumentsController.php
│   └── Admin/                  # REST API controllers
│       └── DocumentController.php
├── Services/
│   ├── AI/
│   │   ├── AIManager.php           # RAG pipeline orchestrator
│   │   ├── EmbeddingService.php    # nomic-embed-text embeddings
│   │   └── Providers/
│   │       └── OllamaProvider.php  # Ollama API client
│   ├── Health/
│   │   ├── MedicalAnalyzerService.php      # Extracts findings, risk level
│   │   ├── PrescriptionAnalyzerService.php # Parses medicines
│   │   └── HealthRecommendationService.php # Diet, lifestyle, exercise plans
│   └── Qdrant/
│       └── QdrantService.php       # Vector search client
├── Jobs/
│   ├── ProcessDocumentJob.php          # RAG indexing
│   └── AnalyzeMedicalDocumentJob.php   # Health analysis
└── Models/
    ├── Document.php
    ├── Conversation.php
    ├── ShareableReport.php
    └── Tenant.php

resources/views/
├── layouts/app.blade.php   # Main layout with health nav
├── dashboard.blade.php
├── health-profile.blade.php
├── documents.blade.php
├── chat.blade.php           # 17 specialist doctor roles
└── settings.blade.php
```

---

## API Endpoints

All API routes are prefixed with `/api/v1/` and require token auth (`Authorization: Bearer <token>`).

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/login` | Get API token |
| GET | `/api/v1/documents` | List health documents |
| POST | `/api/v1/documents` | Upload document |
| GET | `/api/v1/documents/{id}/analysis` | Get structured analysis JSON |
| POST | `/api/v1/chat/sse` | Streaming chat (SSE) |
| GET | `/api/v1/health-profile` | Get full health profile |
| GET | `/api/v1/health-profile/recommendations` | Get personalized recommendations |
| POST | `/api/v1/health-profile/share` | Generate shareable token link |
| GET | `/api/share/{token}` | Public — view shared report |

---

## Disclaimer

This application is for **informational and educational purposes only**. AI-generated health information is not a substitute for professional medical advice, diagnosis, or treatment. Always consult a qualified healthcare provider.

---

## 👨‍💻 Author

**Shafi Ur Rahman**  
Senior PHP / Laravel Developer  

[![LinkedIn](https://img.shields.io/badge/LinkedIn-Shafi%20Ur%20Rahman-blue?logo=linkedin&style=flat-square)](https://www.linkedin.com/in/shafirahman-com/)

---

## 📄 License

MIT — free to use, modify, and distribute.
