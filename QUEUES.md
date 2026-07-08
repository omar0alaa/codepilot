# Queue Configuration — CodePilot AI

## Overview

CodePilot AI processes GitHub webhooks asynchronously using Redis-backed queues. This ensures webhook endpoints respond immediately with HTTP 200 while AI analysis runs in the background.

## Queue Architecture

```
GitHub Webhook → Webhook Controller → Store Event → Dispatch to Queue → Return 200 OK
                                                      ↓
                                              ProcessWebhookJob (github-webhooks queue)
                                                      ↓
                                              PerformAiReviewJob (ai-reviews queue)
                                                      ↓
                                              PostReviewJob (default queue)
                                                      ↓
                                              GitHub PR Review Posted
```

## Queue Connections

| Queue Name     | Purpose                         | Max Jobs | Timeout |
|----------------|---------------------------------|----------|---------|
| `default`      | General jobs (e.g., PostReview) | 10       | 120s    |
| `high`         | High priority jobs              | 5        | 60s     |
| `github-webhooks`| Webhook processing            | 5        | 300s    |
| `ai-reviews`   | AI review analysis              | 3        | 300s    |

## Jobs

### ProcessWebhookJob
- **Queue:** `github-webhooks`
- **Timeout:** 300s
- **Tries:** 3 (with backoff: 10s, 30s, 60s)
- **Purpose:** Parse webhook event, create/link pull request, trigger review

### PerformAiReviewJob
- **Queue:** `ai-reviews`
- **Timeout:** 300s
- **Tries:** 2
- **Purpose:** Fetch PR diff, call AI provider, store review results

### PostReviewJob
- **Queue:** `default`
- **Timeout:** 120s
- **Tries:** 3 (with backoff: 5s, 15s, 30s)
- **Purpose:** Post review comments and scores back to GitHub PR

## Redis Configuration

```env
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Laravel Horizon

Horizon provides a dashboard for monitoring queue health at `/horizon`.

### Supervisor Configuration

Horizon auto-scales workers based on queue load:
- `minProcesses`: 1
- `maxProcesses`: 10
- `balance`: auto

### Production Deployment

Horizon should be managed by Supervisor:

```ini
[program:horizon]
process_name=%(program_name)s
command=php /var/www/html/artisan horizon
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/horizon.log
```

## Failed Jobs

Failed jobs are stored in the database and can be retried:
- View: `/horizon/failed`
- Retry: `php artisan horizon:retry {id}`
- Retry all: `php artisan horizon:retry all`

## Job Flow

```
1. GitHub sends webhook to /github/webhook
2. Webhook signature verified (HMAC SHA256)
3. WebhookEvent stored in database
4. ProcessWebhookJob dispatched to 'github-webhooks' queue
5. HTTP 200 returned to GitHub immediately
6. Worker picks up ProcessWebhookJob
7. Creates/updates PullRequest record
8. Creates Review record (status: processing)
9. Dispatches PerformAiReviewJob to 'ai-reviews' queue
10. Worker calls AI provider (Groq/OpenAI/etc.)
11. Review results stored in database (status: completed)
12. PostReviewJob dispatched to 'default' queue
13. Worker posts review to GitHub PR via API
14. Review visible on GitHub PR
```
