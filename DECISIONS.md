# CodePilot AI - Architecture Decisions

## ADR-001: Technology Stack Selection
**Status:** Accepted  
**Date:** 2026-07-07

### Context
We need to select a technology stack for building a production-grade SaaS application that demonstrates senior-level engineering skills.

### Decision
We will use:
- **Backend:** Laravel 12 with PHP 8.4+
- **Database:** PostgreSQL
- **Cache/Queue:** Redis
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Infrastructure:** Docker + Nginx + Supervisor

### Consequences
- **Positive:** Modern stack, excellent documentation, strong ecosystem
- **Negative:** PHP may be less trendy than Node.js/Python for AI projects
- **Mitigation:** Demonstrate senior skills through architecture, not just language choice

---

## ADR-002: AI Provider Abstraction
**Status:** Accepted  
**Date:** 2026-07-07

### Context
We need to support multiple AI providers (Ollama, OpenAI, Claude, Gemini, OpenRouter) and allow easy switching.

### Decision
Implement the **Strategy Pattern** with a `AiProviderInterface` and individual provider classes.

### Consequences
- **Positive:** Easy to add new providers, testable, follows Open/Closed principle
- **Negative:** Slight overhead for simple use cases
- **Mitigation:** The pattern pays off as we add more providers

---

## ADR-003: Webhook Processing Strategy
**Status:** Accepted  
**Date:** 2026-07-07

### Context
GitHub webhooks must be processed quickly to avoid timeouts. AI reviews take time.

### Decision
Implement a **Queue-based processing** system:
1. Webhook endpoint validates and returns 200 immediately
2. Store event in database
3. Dispatch to Redis queue
4. Worker processes review asynchronously

### Consequences
- **Positive:** Fast webhook response, scalable, reliable
- **Negative:** Added complexity with queues
- **Mitigation:** Laravel Horizon provides excellent queue monitoring

---

## ADR-004: Database Choice
**Status:** Accepted  
**Date:** 2026-07-07

### Context
Need a relational database that supports JSON columns, complex queries, and is production-ready.

### Decision
Use **PostgreSQL** instead of MySQL.

### Rationale
- Better JSON support (jsonb columns)
- More advanced indexing options
- Stronger consistency guarantees
- Better support for complex queries in analytics

---

## ADR-005: Authentication Strategy
**Status:** Accepted  
**Date:** 2026-07-07

### Context
Users need to connect their GitHub accounts and manage repositories.

### Decision
Use **Laravel Sanctum** for API authentication + **GitHub OAuth** for GitHub integration.

### Consequences
- **Positive:** Lightweight, supports SPA and API tokens
- **Negative:** Need to implement OAuth flow manually
- **Mitigation:** Laravel Socialite can simplify OAuth

---

## ADR-006: Repository Pattern Implementation
**Status:** Accepted  
**Date:** 2026-07-07

### Context
Need to decouple business logic from data access for testability and maintainability.

### Decision
Implement **Repository Pattern** with interfaces in `app/Contracts/Repositories/` and implementations in `app/Repositories/`.

### Consequences
- **Positive:** Testable, swappable data sources, clean architecture
- **Negative:** More files to maintain
- **Mitigation:** Use Laravel's service container for automatic injection

---

## ADR-007: Frontend Architecture
**Status:** Accepted  
**Date:** 2026-07-07

### Context
Need a modern, responsive UI without the complexity of a separate SPA.

### Decision
Use **Blade templates** with **Tailwind CSS** for styling and **Alpine.js** for interactivity.

### Rationale
- Server-side rendering (SEO friendly, fast initial load)
- Tailwind provides utility-first styling
- Alpine.js adds reactivity without Vue/React complexity
- Chart.js for analytics visualizations

---

## ADR-008: Queue System
**Status:** Accepted  
**Date:** 2026-07-07

### Context
AI reviews are resource-intensive and time-consuming. Need a robust queue system.

### Decision
Use **Redis** as the queue driver with **Laravel Horizon** for monitoring.

### Consequences
- **Positive:** Fast, reliable, excellent dashboard
- **Negative:** Requires Redis setup
- **Mitigation:** Docker Compose includes Redis

---

## ADR-009: Testing Strategy
**Status:** Accepted  
**Date:** 2026-07-07

### Context
Need comprehensive testing to ensure production readiness.

### Decision
Implement:
- **Unit tests** for services and repositories
- **Feature tests** for API endpoints and webhooks
- **Integration tests** for GitHub API and AI providers (with mocks)
- **Mocking** for external services (GitHub, AI providers)

---

## ADR-010: CI/CD Approach
**Status:** Accepted  
**Date:** 2026-07-07

### Context
Need automated testing and deployment to ensure code quality.

### Decision
Use **GitHub Actions** with:
- Multi-stage pipeline (lint, test, analyze, build)
- Branch protection on `main`
- Automated deployments to staging/production

---

**Last Updated:** 2026-07-07
