# Changelog

All notable changes to CodePilot AI will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v0.1.0] - 2026-07-07

### Added
- **Phase 1**: Project planning, architecture design, GitHub repository initialization
  - ROADMAP.md, ARCHITECTURE.md, DECISIONS.md, TODO.md
  - GitHub Project board (v2) with milestones M1-M9
  - Branch protection on main
  
- **Phase 2**: Docker development environment
  - Docker Compose with PHP 8.4, Nginx, PostgreSQL, Redis, Mailpit, MinIO, Horizon
  - Production Dockerfile (Alpine-based)
  - .env.example with all configuration variables
  - Makefile with 30+ convenience commands
  
- **Phase 3**: Foundation & Authentication
  - Laravel 12 application structure
  - User model with GitHub integration (OAuth)
  - Registration, login, email verification
  - GitHub OAuth controller and callback
  - AI Provider abstraction layer (Strategy Pattern)
  - Groq AI provider implementation
  - AI Provider Factory for easy switching
  - Core database models: User, Repository, PullRequest, Review, WebhookEvent
  - Database migrations for all entities
  - Dashboard controller with stats
  - Repository, PullRequest, Review, Profile, Settings controllers
  - Repository Policy for authorization
  - Blade views: welcome, dashboard, auth (login, register)
  - GitHub Actions CI and CD workflows
  - AdminMiddleware for role-based access
  
- **Phase 4**: GitHub Integration
  - GitHub App installation flow (controller + routes)
  - Repository synchronization service
  - GitHub API service layer (PR diffs, reviews, check runs, repos)
  - Token encryption service (AES via Laravel Crypt)
  - Repository enable/disable with webhook creation
  - Repository views: index, show, settings
  - PR view with review details
  
- **Phase 5**: Webhook Engine
  - HMAC SHA256 signature verification service
  - Webhook processing service (event parsing, PR linking)
  - Rate limiting (100 req/min per IP)
  - Ping event handler
  - Duplicate delivery prevention
  - Idempotent job processing
  - Retry logic with backoff
  
- **Phase 6**: Queue System
  - ProcessWebhookJob (github-webhooks queue)
  - PerformAiReviewJob (ai-reviews queue)
  - PostReviewJob (posts review to GitHub PR)
  - Laravel Horizon configuration
  - Supervisor configuration for production
  - QUEUES.md documentation
  
- **Phase 7**: AI Review Engine
  - PromptTemplateBuilder (structured JSON output prompts)
  - ReviewAnalyzerService (orchestrates AI call + response parsing)
  - Context window management (12k char truncation)
  - Confidence scoring (0.0-1.0 per issue)
  - 5 review categories: security, performance, maintainability, readability, architecture
  - AI_PROVIDER_GUIDE.md documentation
  - chat() and getModel() added to AiProviderInterface and GroqProvider
  
- **Phase 8**: PR Processing & Feedback
  - GitHubCheckService (Checks API with annotations)
  - DiffChunkingService (file-boundary chunking for large diffs)
  - Review status tracking (pending → processing → completed/failed)
  - Auto-approve (score≥80), request changes (score<50), or comment
  
- **Phase 9**: Dashboard & UI/UX
  - Chart.js integration (score trend, issue distribution)
  - Pull request index with search and filtering
  - Reviews index with status filter
  - Detailed review show view with issues, scores, suggestions
  - Profile show/edit views
  - Settings view (AI provider, notifications, GitHub connection)
  - Responsive Tailwind CSS design
  
- **Phase 10**: Analytics & Notifications
  - AnalyticsService: quality trends, security trends, technical debt
  - Repository health with letter grades (A-F)
  - Developer metrics and review frequency
  - Common problems analysis
  - Analytics dashboard with charts
  - Email notifications (ReviewCompleted, ReviewFailed, CriticalIssue)
  - Database notifications (in-app)
  
- **Phase 11**: Admin Panel
  - Admin dashboard with system stats
  - User management (list, update role, delete)
  - AI provider configuration UI
  - Prompt template viewer
  - Queue monitoring (failed jobs viewer)
  - Webhook event log viewer
  - System settings management
  
- **Phase 12**: Documentation & Release
  - DEPLOYMENT.md
  - WEBHOOKS.md
  - DATABASE.md
  - API.md
  - GitHub Actions CI (PHP 8.4, PostgreSQL, Redis, Pint, PHPStan, tests)
  - GitHub Actions CD (Docker build, migrations, cache optimization, health check)

### Architecture
- MVC with Repository Pattern and Service Layer
- Strategy Pattern for AI providers
- DTO-style structured prompts
- Queue-driven webhook processing
- Encrypted token storage
- SOLID principles throughout

### Technology Stack
- **Backend**: Laravel 12, PHP 8.4, PostgreSQL, Redis
- **Frontend**: Blade, Tailwind CSS, Alpine.js, Chart.js
- **AI**: Groq (default), OpenAI/Claude/Gemini (planned)
- **Infrastructure**: Docker, Docker Compose, Nginx, Supervisor
- **CI/CD**: GitHub Actions
