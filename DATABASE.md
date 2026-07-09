# Database Schema — CodePilot AI

## Tables

### users
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | User name |
| email | string | Unique email |
| password | string | Hashed password |
| github_id | string | GitHub user ID (nullable) |
| github_username | string | GitHub username (nullable) |
| github_token | text | Encrypted OAuth token |
| github_installation_id | string | GitHub App installation ID |
| avatar_url | string | GitHub avatar URL |
| role | string | user or admin |

### repositories
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | FK → users | Owner |
| github_id | bigint | GitHub repo ID |
| name | string | Repository name |
| full_name | string | owner/repo |
| is_private | boolean | Private flag |
| is_enabled | boolean | Reviews enabled |
| default_branch | string | Main branch |

### pull_requests
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| repository_id | FK → repositories | Parent repo |
| github_pr_id | bigint | GitHub PR ID |
| number | int | PR number |
| title | string | PR title |
| state | string | open/closed |
| head_branch | string | Source branch |
| base_branch | string | Target branch |

### reviews
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| pull_request_id | FK → pull_requests | Parent PR |
| status | string | pending/processing/completed/failed |
| overall_score | int | 0-100 total score |
| category_scores | json | Per-category scores |
| issues | json | Array of issues |
| suggestions | json | General suggestions |
| summary | text | AI summary |
| ai_provider | string | Provider used |
| ai_model | string | Model used |

### webhook_events
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| event_id | string | GitHub delivery ID |
| event_type | string | pull_request, installation, etc. |
| action | string | opened, synchronize, etc. |
| repository_id | FK → repositories | Related repo |
| payload | json | Full webhook payload |
| status | string | received/processing/processed/failed |

### notifications
| Column | Type | Description |
|--------|------|-------------|
| id | uuid | Primary key |
| type | string | Notification class |
| notifiable_type | string | User model |
| notifiable_id | bigint | User ID |
| data | text | JSON notification data |
| read_at | timestamp | When read |

## ER Diagram

```
users → repositories → pull_requests → reviews
                ↓                         ↓
         webhook_events          notifications
```
