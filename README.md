# the-one-who-dutifully-keeps (CMS Service)

Private **Botble CMS** (Laravel 13 / PHP 8.4) service responsible for content management, media administration, and back-office operations.

> **Private Commercial Codebase:** This repository contains commercial Botble CMS code and must remain private at all times.

## Building the Image

This repository owns the **build** definition (`docker/php/Dockerfile`) with `base`, `dev`, and `prod` targets:

```bash
# Development image (PHP 8.4 FPM + Xdebug)
docker build --target dev -f docker/php/Dockerfile .

# Production image (PHP 8.4 FPM + OPcache + no-dev Composer dependencies)
docker build --target prod -f docker/php/Dockerfile .
```

## Running the Stack

This service does **not** run standalone via a local Compose file. The full runtime stack (Nginx, MySQL, Redis, environment variables, and network orchestration) is managed and launched from the **master repository** (`the-one-who-rules-them-all`):

```bash
# From the master repository root:
cp .env.example .env
make up
```

See [`AGENTS.md`](./AGENTS.md) for codebase layout and contribution rules.
