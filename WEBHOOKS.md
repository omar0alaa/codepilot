# Webhooks — CodePilot AI

## Endpoint

```
POST /github/webhook
```

## Authentication

All webhooks are verified using HMAC SHA256 signatures:

```
X-Hub-Signature-256: sha256=<HMAC-SHA256 of raw body using webhook secret>
```

## Supported Events

| Event | Actions | Processing |
|-------|---------|------------|
| `pull_request` | opened, synchronize, reopened | Triggers AI review |
| `ping` | — | Health check (returns pong) |
| `installation` | created, deleted | Sync/remove repos |
| `installation_repositories` | added, removed | Update repo list |

## Flow

```
1. GitHub sends webhook
2. Signature verified (HMAC SHA256)
3. Rate limit checked (100 req/min per IP)
4. Event stored in webhook_events table
5. ProcessWebhookJob dispatched to queue
6. HTTP 200 returned immediately
7. Worker processes event asynchronously
8. AI review triggered (if PR event)
9. Review posted back to GitHub
```

## Rate Limiting

- 100 requests per minute per IP
- Returns 429 if exceeded

## Retry Logic

- ProcessWebhookJob: 3 tries, backoff [10, 30, 60]s
- PerformAiReviewJob: 2 tries, backoff [10, 30]s
- PostReviewJob: 3 tries, backoff [5, 15, 30]s

## Duplicate Prevention

Each webhook has a unique `X-GitHub-Delivery` ID. Duplicate deliveries are detected and ignored.
