# Changelog

All notable changes are documented here. Follows [Keep a Changelog](https://keepachangelog.com/) format.

---

## [Unreleased]

### In Progress
- Admin observability dashboard: queue depth, processing latency, AI response time metrics
- Conversation memory with sliding-window summarisation for long sessions

### Planned
- Redis queue driver support (one-line `.env` switch, no code changes)
- OpenAI / Groq provider drivers via existing `ProviderInterface` abstraction
- Per-document context budget management (chunk count vs. `NUM_CTX` guard)
- S3-compatible file storage backend
- Docker Compose for full local stack (app + MySQL + Qdrant)

---

## [1.0.0] — 2025-05-06

### Phase 5 — Shareable Profiles & Widget

- `feat(share)`: generate time-limited signed token links for read-only health summary sharing
- `feat(widget)`: embeddable chat widget endpoint with per-IP rate limiting (60 req/min)
- `fix(share)`: token expiry enforced at query time, not application layer

### Phase 4 — AI Health Chat

- `feat(chat)`: RAG-powered conversational interface with 17 specialist doctor roles
- `feat(chat)`: SSE streaming with mid-stream cancellation support
- `feat(chat)`: per-session model switching (phi / llama3 / gemma4) persisted in browser
- `feat(chat)`: conversation history retained across page reloads via DB-backed sessions
- `refactor(chat)`: extracted role framing to `config/ai.php` — new roles require no code changes

### Phase 3 — Health Recommendations & Profile

- `feat(health)`: `HealthRecommendationService` — cross-document diet, lifestyle, exercise, routine plans
- `feat(profile)`: unified health profile view aggregating all document analyses
- `refactor(analysis)`: recommendations regenerate on new document upload via queue event

### Phase 2 — Structured Medical Analysis

- `feat(analysis)`: `MedicalAnalyzerService` — structured extraction of findings, risk level, concerns, recommendations
- `feat(analysis)`: `PrescriptionAnalyzerService` — per-medicine structured output (name, dosage, frequency, purpose, warnings)
- `feat(jobs)`: `AnalyzeMedicalDocumentJob` dispatched after successful document indexing
- `fix(prompts)`: increased `NUM_CTX` default to 4096 to prevent truncation on multi-page reports

### Phase 1 — Core Infrastructure

- `feat(auth)`: multi-tenant Sanctum auth with `tenant_id` scoping across all models
- `feat(rag)`: `ProcessDocumentJob` — PDF text extraction, sliding-window chunking, embedding, Qdrant upsert
- `feat(embedding)`: `EmbeddingService` using `nomic-embed-text` (768-dim vectors)
- `feat(qdrant)`: `QdrantService` — vector upsert, similarity search with tenant payload filtering
- `feat(gateway)`: `AIManager` orchestration layer with `OllamaProvider` adapter
- `feat(config)`: driver-based provider system in `config/ai.php` — OpenAI stubbed for future use
- `feat(api)`: REST API with rate limiting (120 req/min authenticated), `/api/health-check` endpoint
- `chore(infra)`: database queue driver — Redis-ready via single `.env` change
- `feat(ui)`: health-themed Blade SPA with TailwindCSS

---

## Notes on Versioning

This project uses [Semantic Versioning](https://semver.org/). Pre-1.0 releases may include breaking changes between minor versions. The API is considered stable from 1.0.0.
