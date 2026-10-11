# AGENTS.md — `the-one-who-dutifully-keeps` (CMS Service)

## Purpose
This repository (`the-one-who-dutifully-keeps`) is the **CMS & Admin Backend** service within the `the-one-who-rules-them-all` polyglot multi-repo architecture. It is built on **Botble CMS** (Laravel 13, PHP 8.4) and serves as the source of truth for editorial content, media management, menus, pages, and administrative workflows.

## License & Privacy Warning
> **STRICTLY PRIVATE — NEVER PUBLISH**
> This repository contains a **purchased commercial Botble CMS license**. Do **NOT** make this repository public, mirror it to public registries, or redistribute its source code.

## Repository Layout
```text
.
├── app/                  # Standard Laravel application layer
├── bootstrap/            # Framework bootstrap & cache directory
├── config/               # Laravel configuration files
├── database/             # Migrations, factories, and seeders
├── docker/
│   └── php/              # Service-owned container build configuration
│       ├── Dockerfile    # Multi-stage PHP-FPM build (base, dev, prod)
│       ├── entrypoint.sh # Runtime permission fixer & command entrypoint
│       ├── php.ini       # Development PHP & Xdebug configuration
│       └── php.prod.ini  # Production PHP & OPcache configuration
├── platform/
│   ├── core/             # Botble core modules (READ-ONLY)
│   ├── packages/         # Botble core packages (READ-ONLY)
│   ├── plugins/          # First-party & custom CMS plugins (EDITABLE)
│   └── themes/           # Frontend & custom CMS themes (EDITABLE)
├── public/               # Web server document root
├── routes/               # Application route definitions
└── storage/              # App storage, framework cache, sessions, logs
```

## Building the Container Image
Per project design principles, **this repository owns BUILD** (`Dockerfile` and PHP runtime configuration), while the **master repository (`the-one-who-rules-them-all`) owns RUN** (Docker Compose, Nginx, MySQL, Redis, `.env`).

From the root of `services/cms`:

```bash
# Build the development target (includes Xdebug, disabled by default)
docker build --target dev -f docker/php/Dockerfile -t cms:dev .

# Build the production target (optimized Composer autoloader + OPcache enabled)
docker build --target prod -f docker/php/Dockerfile -t cms:prod .
```

## Mandatory Rules for Agents & Contributors
1. **Never modify Botble core**: Do **NOT** edit files under `platform/core/` or `platform/packages/` unless explicitly instructed by the user. Upgrading Botble overwrites changes in those directories.
2. **Custom code placement**: All custom features, hooks, models, and UI customizations **must** live inside `platform/plugins/<your-plugin>/` or `platform/themes/<your-theme>/`.
3. **Never commit secrets or `.env`**: Environment variables are injected at runtime by the master repository's `.env` and `compose.yaml`. Never commit `.env` files, API keys, database dumps containing real user data, or credentials.
4. **Keep Docker changes in `docker/`**: Do not add `compose.yaml` or `docker-compose.yml` to this repository. All container build assets belong exclusively inside `docker/`.
5. **Git Submodule Hygiene**: This directory is a git submodule (`the-one-who-dutifully-keeps`). Always commit and push changes inside this repo first, then update the submodule pointer in the master repo.
