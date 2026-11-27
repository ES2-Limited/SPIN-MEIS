# SPIN MEIS 2.0 — Build Log

## Phase 0: Foundation
**Status:** Completed  
**Date:** 2026-11-27

### What was built
- **Laravel 11 + Livewire 4.4** application skeleton with Pest testing and Laravel Pint linting.
- **Docker Compose** (`docker-compose.yml`, `docker/php/Dockerfile`, `docker/nginx/default.conf`) with PHP 8.3-FPM, Nginx, PostgreSQL 16, and Mailpit.
- **CI & Environments:** GitHub Actions workflow (`.github/workflows/ci.yml`), `.env.staging.example`, `.env.production.example`, and single-server deploy script (`deploy/deploy.sh`).
- **UI & PWA:** Responsive Livewire layout (`resources/views/components/layouts/app.blade.php`) with desktop sidebar, mobile bottom navigation (360px+), teal/amber palette, `public/manifest.json`, `public/sw.js`, and placeholder pages (`App\Livewire\SectionPage`) for all PRD section 5 menu entries.
- **API & Observability:** `/api/v1/health`, OpenAPI 3.1 spec (`public/openapi.json` + `/api/v1/openapi.json`), `CorrelationIdMiddleware` (`X-Correlation-ID` + log context), and standard JSON error format (`code`, `message`, `field_errors`, `correlation_id`) in `bootstrap/app.php`.

### Deferred
- Hosting provider selection and OpenID Connect (OIDC) identity provider configuration (Decision D-2).
- Playwright browser smoke suite (will be added once authentication and interactive forms are built in Phase 1/3).

---

## Phase 1: Identity, Ownership, Access Control & Audit
**Status:** Completed  
**Date:** 2026-11-27

### What was built
- **Auth & Roles:** Installed `laravel/fortify` (TOTP 2FA support), `spatie/laravel-permission`, and `bacon/bacon-qr-code`. Seeded all 11 permissions and 9 PRD roles in `Database\Seeders\RolesAndPermissionsSeeder`.
- **User Scope & Lifecycle:** Added `owner_type` (`national`, `rbda`, `state`), `owner_id`, `status` (`invited`, `active`, `disabled`), and lifecycle timestamps to `users`.
- **Master Data:** Created `states`, `rbdas`, `river_basins`, and `schemes` tables/models; `Scheme` auto-computes `owner_type`/`owner_id` from `delivery_model` (`model_1` → RBDA, `model_2` → State).
- **Central Scope & Policy Layer:** `OwnerScope` + `BelongsToOwner` trait filter all queries by owner; `OwnerScopedPolicy` enforces read-only World Bank access and separation of duties (`submitted_by !== verifier`).
- **Audit Log:** `audit_logs` table, `AuditLogger`, `Auditable` trait, and auth/role event listeners in `AppServiceProvider` logging `auth.login`, `role.assigned`, `role.removed`, and model CRUD.
- **Admin Screens:** Livewire `UsersManager`, `MasterDataManager`, and `AuditLogViewer` at `/admin/users`, `/admin/master-data`, `/admin/audit-log`.

### Deferred
- Confirming which specific states are Model 1 vs Model 2 (Decision D-3).
- Quarterly access review PDF/CSV export (will be wired with Phase 7 reporting).
