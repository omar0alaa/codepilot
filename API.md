# API Documentation — CodePilot AI

## Overview

CodePilot AI is primarily a web application, but provides API endpoints for programmatic access via Laravel Sanctum tokens.

## Authentication

### API Tokens

Users can generate API tokens from their profile settings. Include the token in requests:

```
Authorization: Bearer <token>
```

## Endpoints

### Webhooks (Public)

#### POST /github/webhook
Receives GitHub webhook events. Verified via HMAC SHA256 signature.

### Authenticated Routes (Require Bearer Token)

#### Dashboard
- `GET /api/dashboard` — Get dashboard stats

#### Repositories
- `GET /api/repositories` — List repositories
- `POST /api/repositories/sync` — Sync from GitHub
- `GET /api/repositories/{id}` — Repository details
- `PATCH /api/repositories/{id}` — Update settings
- `DELETE /api/repositories/{id}` — Remove repository

#### Pull Requests
- `GET /api/pull-requests` — List PRs (with search/filter)
- `GET /api/pull-requests/{id}` — PR details with reviews
- `POST /api/pull-requests/{id}/re-review` — Trigger re-review

#### Reviews
- `GET /api/reviews` — List reviews (with status filter)
- `GET /api/reviews/{id}` — Review details with issues

#### Analytics
- `GET /api/analytics` — Analytics dashboard data
- `GET /api/analytics/repositories/{id}/health` — Repo health score

### Admin Routes (Require Admin Role)
- `GET /api/admin/dashboard` — System stats
- `GET /api/admin/users` — List all users
- `PATCH /api/admin/users/{id}` — Update user role
- `GET /api/admin/webhooks` — Webhook event log

## Rate Limiting

- API endpoints: 60 requests/minute per token
- Webhook endpoint: 100 requests/minute per IP

## Response Format

```json
{
  "data": { ... },
  "message": "Success",
  "status": 200
}
```

Error:
```json
{
  "message": "Error description",
  "errors": { ... }
}
```
