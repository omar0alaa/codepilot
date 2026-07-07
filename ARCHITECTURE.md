# CodePilot AI - Architecture Documentation

## 1. Introduction
This document outlines the architectural decisions, patterns, and structure for the CodePilot AI application. It adheres to SOLID principles, Clean Architecture, and Laravel best practices.

## 2. Technology Stack
*   **Backend:** Laravel 12 (PHP 8.4+)
*   **Database:** PostgreSQL 16
*   **Cache/Queue:** Redis 7
*   **Frontend:** Blade, Tailwind CSS, Alpine.js, Chart.js
*   **Infrastructure:** Docker, Nginx, Supervisor
*   **AI:** Provider Abstraction Layer (Ollama, OpenAI, Claude, Gemini, OpenRouter)

## 3. Architectural Patterns

### 3.1 Repository Pattern
*   **Purpose:** Decouple the application logic from the data access layer.
*   **Implementation:** 
    *   Interfaces defined in `app/Contracts/Repositories/`
    *   Implementations in `app/Repositories/`
    *   Example: `UserRepositoryInterface`, `PullRequestRepositoryInterface`

### 3.2 Service Layer
*   **Purpose:** Contain complex business logic. Controllers must remain "thin."
*   **Implementation:**
    *   Services in `app/Services/`
    *   Example: `GitHubAppService`, `ReviewOrchestrationService`, `AiReviewService`

### 3.3 DTO (Data Transfer Objects)
*   **Purpose:** Ensure type-safe data passing between layers.
*   **Implementation:** Using `Spatie\LaravelData` or custom DTOs in `app/DTOs/`.

### 3.4 Strategy Pattern (AI Providers)
*   **Purpose:** Allow switching between AI providers (Ollama, OpenAI, etc.) without changing the core review logic.
*   **Implementation:**
    *   `App\Contracts\AiProviderInterface`
    *   `App\Services\Ai\OllamaProvider`, `App\Services\Ai\OpenAiProvider`

## 4. Database Schema (Key Entities)

### 4.1 Users & Auth
*   `users`: Local auth, profile.
*   `github_accounts`: Stores GitHub OAuth tokens and metadata.
*   `personal_access_tokens`: API tokens (Sanctum).

### 4.2 GitHub Integration
*   `repositories`: Repo metadata, settings (is_enabled, prompt_template_id).
*   `installations`: GitHub App installation IDs.
*   `pull_requests`: PR metadata (number, title, head/tail sha).

### 4.3 Review Engine
*   `reviews`: The main review record (overall score, status).
*   `review_comments`: Individual AI findings (file, line, severity, suggestion).
*   `prompt_templates`: Admin-managed prompts (versioned).
*   `ai_providers`: Configuration for connected AI services.

### 4.4 System
*   `webhook_events`: Log of incoming GitHub payloads.
*   `audit_logs`: Track admin/system actions.
*   `failed_jobs`: Queue failure monitoring.

## 5. Webhook Processing Flow
1.  **Receive:** `POST /github/webhook`
2.  **Validate:** `GitHubSignatureMiddleware` (HMAC SHA256).
3.  **Acknowledge:** Return `200 OK` immediately.
4.  **Dispatch:** `ProcessWebhookJob` to Redis Queue.
5.  **Process:** 
    *   Verify event type (PR Opened/Sync).
    *   Download Diff (GitHub API).
    *   Dispatch `PerformAiReviewJob`.
6.  **Review:** AI analyzes code, generates comments.
7.  **Post:** Post results back to GitHub via PR Review API.

## 6. Queue System
*   **Connection:** Redis.
*   **Jobs:**
    *   `ProcessWebhookJob`: Initial handler.
    *   `PerformAiReviewJob`: Heavy lifting (AI calls).
    *   `PostReviewJob`: Posting comments back to GitHub.
    *   `SyncRepositoryJob`: Background repo syncing.
*   **Monitoring:** Laravel Horizon.

## 7. Security Considerations
*   **Webhooks:** Signature verification is mandatory.
*   **Tokens:** GitHub tokens encrypted at rest (Laravel Encryption).
*   **Policies:** `ReviewPolicy`, `RepositoryPolicy` to ensure users only access their own data.
*   **Rate Limiting:** Apply to all API endpoints and Webhook endpoints.

## 8. Directory Structure (App)
```text
app/
├── Contracts/      # Interfaces (Repositories, Services)
├── DTOs/           # Data Transfer Objects
├── Enums/          # ReviewStatus, Severity, EventType
├── Http/
│   ├── Controllers/ # Thin controllers
│   ├── Middleware/ # GitHubSignature, RateLimiter
│   ├── Requests/    # Form Requests (Validation)
├── Jobs/           # Queueable jobs
├── Listeners/      # Event listeners
├── Models/         # Eloquent models
├── Notifications/  # Email/Slack notifications
├── Policies/       # Authorization logic
├── Repositories/   # Data access layer
├── Rules/          # Custom validation rules
├── Services/       # Business logic
│   ├── Ai/         # AI Provider strategies
│   ├── GitHub/     # GitHub API interaction
└── Support/        # Helper classes
```

## 9. CI/CD Pipeline
*   **Static Analysis:** PHPStan (Level 8).
*   **Code Style:** Laravel Pint.
*   **Testing:** PestPHP or PHPUnit.
*   **Security:** `composer audit`, `npm audit`.
*   **Deployment:** Docker build & push to registry.