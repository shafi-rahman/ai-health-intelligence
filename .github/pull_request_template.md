## What this changes

<!-- One paragraph. What does this PR do and why? -->

## Approach

<!-- Why this implementation over alternatives? Any tradeoffs made? -->

## Scope

- [ ] Touches the RAG pipeline (`AIManager`, `EmbeddingService`, `QdrantService`)
- [ ] Touches document processing (`ProcessDocumentJob`, `AnalyzeMedicalDocumentJob`)
- [ ] Touches chat / streaming (`AIController`, `OllamaProvider`)
- [ ] Touches the API surface (new/changed endpoints)
- [ ] Touches tenant isolation (auth, scoping, Qdrant filters)
- [ ] Config change (`.env` variables, `config/ai.php`, `config/qdrant.php`)
- [ ] Database migration

## Testing

<!-- How was this tested? Include queue worker behaviour, edge cases, and any integration testing against Ollama/Qdrant. -->

## Checklist

- [ ] `./vendor/bin/pint` passes (PSR-12)
- [ ] `php artisan test` passes
- [ ] No `dd()` / `var_dump()` left in
- [ ] Tenant scoping preserved (if touching data layer)
- [ ] Provider abstraction respected (no direct Ollama calls outside provider class)
- [ ] CHANGELOG.md updated
