# Contributing

Contributions are welcome. This document covers the conventions this project follows.

---

## Scope

Before opening a PR, check the roadmap in the README. Items marked `Planned` are good candidates for contribution. For anything outside the roadmap, open an issue first — architectural changes need discussion before implementation.

---

## Commit Messages

This project follows [Conventional Commits](https://www.conventionalcommits.org/) with a focus on engineering intent over feature marketing.

### Format

```
<type>(<scope>): <subject>
```

### Types

| Type | When |
|---|---|
| `feat` | New capability |
| `fix` | Bug correction |
| `refactor` | Code change with no behaviour change |
| `perf` | Performance improvement |
| `docs` | Documentation only |
| `test` | Tests only |
| `chore` | Build, config, dependency changes |
| `ci` | CI/CD pipeline changes |

### Scopes

Use the service or layer being changed: `gateway`, `rag`, `queue`, `embedding`, `qdrant`, `auth`, `chat`, `analysis`, `config`, `migrations`, `api`.

### Examples

```
feat(gateway): add provider failover routing with configurable backoff
fix(rag): correct chunk overlap causing duplicate context in retrieval
refactor(queue): isolate document ingestion state transitions to dedicated service
perf(embedding): batch embed requests to reduce round-trip latency
docs(architecture): document request lifecycle and multi-tenant isolation model
chore(deps): update qdrant-client to 1.9.0
```

Avoid vague subjects like `update code`, `fix stuff`, `improvements`. The commit message is the audit trail for architectural decisions.

---

## Branch Naming

```
feat/gateway-provider-failover
fix/rag-duplicate-chunks
refactor/queue-state-isolation
docs/architecture-request-lifecycle
```

---

## Code Standards

- PSR-12 formatting — enforced via `./vendor/bin/pint`
- No `var_dump` or `dd()` in committed code
- Service classes stay single-responsibility — if a service needs to call another service directly, route through `AIManager` or a dedicated orchestrator
- New providers must implement the provider interface — do not call Ollama or any LLM directly outside of a provider class
- Tenant scope must be applied at the model/query level — never filter by `tenant_id` ad hoc in a controller

---

## Pull Requests

Use the PR template. PRs without a description of the architectural rationale for significant changes will be returned for revision.

For non-trivial contributions, include:
- What the change does
- Why this approach over alternatives
- Any operational or scaling considerations
- How to test it

---

## Testing

```bash
php artisan test
```

Integration tests that require Ollama or Qdrant are skipped by default unless those services are available. Use `--group=integration` to include them explicitly.

---

## Local Setup

See [README — Installation](README.md#installation).
