# Deployment Guide — CodePilot AI

## Prerequisites
- Docker & Docker Compose
- GitHub App with OAuth credentials
- AI Provider API key (Groq by default)

## Quick Start

```bash
# Clone
git clone https://github.com/omar0alaa/codepilot.git
cd codepilot

# Configure
cp .env.example .env
# Edit .env with your credentials

# Start all services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Start queue workers
docker-compose exec app php artisan horizon
```

## Services

| Service | Port | Purpose |
|---------|------|---------|
| Nginx | 8080 | Web server |
| PHP-FPM | 9000 | Application |
| PostgreSQL | 5432 | Database |
| Redis | 6379 | Cache + Queue |
| Mailpit | 8025 | Email testing |
| MinIO | 9001 | Object storage |

## Production Deployment

### Docker

```bash
docker-compose -f docker-compose.prod.yml up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan horizon
```

### Railway / DigitalOcean / AWS

1. Set environment variables in your platform
2. Build Docker image: `docker build -t codepilot-ai .`
3. Run migrations: `php artisan migrate --force`
4. Start horizon: `php artisan horizon`

## Environment Variables

See `.env.example` for all required variables.

## Health Checks

- Application: `GET /health`
- Queue: `GET /horizon/api/stats`
