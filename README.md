# Lions Academy — REST API (Laravel 12)

API-first backend for the Lions Academy site. Pairs with the TanStack Start
frontend in `../lion-s-roar-academy`.

Stack: **Laravel 12 · MySQL · Sanctum · REST API.**

This repo currently contains **Phase 1 (foundation)**:
project bootstrap, Sanctum auth, `/api/v1` routing skeleton, global JSON
response envelope + error handling, file storage architecture, image upload,
role/policy system, and security hardening (rate limits, honeypot, CORS,
upload protection, password reset).

Content resources (formations, projects, trainers, programme, principles,
inscriptions, contact-messages, settings) are routed-but-stubbed in
`routes/api.php` and will be plugged in next.

---

## Requirements

- PHP **8.3+** with extensions: `mbstring`, `pdo_mysql`, `fileinfo`, `gd`, `bcmath`, `intl`, `openssl`, `tokenizer`, `xml`, `curl`.
- **Composer 2**
- **MySQL 8.0+** (or MariaDB 10.6+)
- Optional: Redis (if you switch `CACHE_STORE`/`SESSION_DRIVER` away from database).

## Initial setup

```bash
# 1. Install dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# Set in .env at minimum:
#   DB_DATABASE / DB_USERNAME / DB_PASSWORD
#   CORS_ALLOWED_ORIGINS     (your frontend origin, e.g. http://localhost:5173)
#   LIONS_FRONTEND_URL       (same)
#   LIONS_ADMIN_URL          (admin SPA URL — used in reset-password emails)
#   LIONS_ADMIN_EMAIL/PASSWORD  (seed credentials — change after first login)
#   SANCTUM_STATEFUL_DOMAINS (only if using cookie auth; bearer tokens don't need it)

# 3. Database
php artisan migrate
php artisan db:seed

# 4. Public storage symlink (for cover images, gallery, trainer photos)
php artisan storage:link

# 5. Serve
php artisan serve   # http://127.0.0.1:8000
```

## Auth model

The admin SPA authenticates via **Sanctum personal access tokens** (`Authorization: Bearer <token>`). The frontend stores the token after `POST /api/v1/auth/login` and sends it on every subsequent request.

Sanctum stateful (cookie) auth is also wired (`statefulApi()` in `bootstrap/app.php`) for same-site setups — set `SANCTUM_STATEFUL_DOMAINS` and use `/sanctum/csrf-cookie` first.

### Auth endpoints

| Method | Path | Auth | Purpose |
|---|---|---|---|
| POST | `/api/v1/auth/login` | – | Returns `{user, token, abilities}`. Throttled. |
| POST | `/api/v1/auth/forgot-password` | – | Sends reset link to `LIONS_ADMIN_URL/reset-password?...`. Always 200. |
| POST | `/api/v1/auth/reset-password` | – | Body `{token, email, password, password_confirmation}`. |
| GET  | `/api/v1/auth/me` | bearer | Current user. |
| POST | `/api/v1/auth/logout` | bearer | Revokes current token. |
| POST | `/api/v1/auth/logout-everywhere` | bearer | Revokes every token of the user. |
| PATCH| `/api/v1/auth/me/password` | bearer | Change own password (requires `current_password`). |

### Media endpoints

| Method | Path | Auth | Purpose |
|---|---|---|---|
| POST   | `/api/v1/admin/media` | bearer (admin/editor) | `multipart/form-data`: `file`, `folder?`, `alt?`. |
| DELETE | `/api/v1/admin/media/{media}` | bearer | Owner or admin. |
| GET    | `/media/{media}` | signed URL | Streams a *private* asset. Public assets use the direct `/storage/...` URL. |

### Health

```
GET /up                  Laravel health endpoint
GET /api/v1/health       { status: "ok", time: ... }
```

## Response envelope

Every JSON response uses the same shape so the frontend's
`src/lib/api.ts` can be a thin client.

**Success**
```json
{ "success": true, "data": <payload>, "meta": <object|null> }
```

**Error**
```json
{
  "success": false,
  "error": {
    "message": "Données invalides.",
    "code": "validation_failed",
    "details": { "email": ["L'adresse email n'est pas valide."] }
  }
}
```

Validation errors → HTTP 422 with `details` shaped exactly like Laravel's
`ValidationException::errors()`, ready to be attached to `react-hook-form`
inputs.

Implemented in [`app/Support/ApiResponse.php`](app/Support/ApiResponse.php) and the global render hook in [`bootstrap/app.php`](bootstrap/app.php).

## Roles

Two backoffice roles, defined in `App\Support\Enums\UserRole`:

| Role | Manages users | Manages settings | Manages content |
|---|---|---|---|
| `admin` | ✅ | ✅ | ✅ |
| `editor` | – | – | ✅ |

Route guard examples (in `routes/api.php`):
- `->middleware(['auth:sanctum', 'admin'])` — any active staff
- `->middleware(['auth:sanctum', 'admin:admin'])` — admin role only

Policies are registered in `App\Providers\AuthServiceProvider`
(`UserPolicy`, `MediaAssetPolicy`).

## Security

- **Sanctum tokens** with per-device naming (one token per device, refreshed on login).
- **Password reset** with one-time-use tokens (60-min expiry).
- **Rate limits** (see `App\Providers\RateLimitServiceProvider`):
  - `auth` — 5/min per IP + per email
  - `public-write` — 10/hour per IP (for inscription/contact)
  - `public-read` — 60/min per IP
  - `admin` — 120/min per user
  - `media-download` — 120/min per IP
  All configurable via `.env`.
- **CORS** — origins driven by `CORS_ALLOWED_ORIGINS` (comma list).
- **Honeypot** middleware on public POSTs: silent reject if bait field
  is filled or form is submitted faster than humanly possible.
- **Turnstile** verifier ready to wire (`App\Services\Security\TurnstileVerifier`) — set `TURNSTILE_SECRET_KEY` to enable.
- **Upload protection** — server-side MIME re-check via `finfo`, size limits, extension whitelist, SHA-256 checksum, intrinsic dimension capture for images.
- **Strict Eloquent** in non-prod (`Model::shouldBeStrict`) catches lazy-loads and missing attributes early.
- **Forced HTTPS** in production (`URL::forceScheme('https')`).

## File storage

Three disks (in `config/filesystems.php`):

- `public` — cover images, gallery, trainer photos. Served via `/storage/...` after `php artisan storage:link`.
- `private` — sensitive uploads (inscription docs, CIN/photo). Served only through a signed-URL route.
- `local` — internal app files.

S3 ready: switch `FILESYSTEM_DISK=s3` and set AWS keys.

## Project layout (this repo)

```
app/
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── Auth/  (Login, Logout, Me, Forgot, Reset)
│   │   └── Media/MediaController.php
│   ├── Middleware/  (ForceJsonResponse, EnsureUserIsAdmin, Honeypot)
│   ├── Requests/    (Auth/*, Media/UploadMediaRequest)
│   └── Resources/   (UserResource, MediaAssetResource)
├── Models/          (User, MediaAsset)
├── Notifications/   (ResetPasswordNotification)
├── Policies/        (UserPolicy, MediaAssetPolicy)
├── Providers/       (App, Auth, RateLimit)
├── Services/
│   ├── Auth/AuthService.php
│   ├── Media/MediaService.php
│   └── Security/TurnstileVerifier.php
└── Support/
    ├── ApiResponse.php
    └── Enums/  (UserRole, InscriptionStatus, ContactMessageStatus)
bootstrap/app.php        ← middleware/exception/routing config
config/                  ← cors, sanctum, auth, filesystems, lions
database/
├── migrations/  (users, password_reset_tokens, sessions, cache, jobs, sanctum tokens, media_assets)
├── factories/   (UserFactory)
└── seeders/     (DatabaseSeeder, AdminUserSeeder)
routes/api.php   ← /api/v1/* with stubs for next-phase resources
```

## Next phase

1. Migrations + Eloquent models for content (formations, formation_categories,
   program_months, projects, project_gallery, trainers, trainer_modules,
   principles, inscriptions, inscription_documents, contact_messages, settings).
2. Public read controllers matching the frontend's `fetchFormations`,
   `fetchFormationBySlug`, `fetchProgram`, `fetchProjects`, `fetchTrainers`,
   `fetchPrinciples` contracts in `src/lib/api.ts`.
3. Inscription + Contact write controllers with multipart file upload
   for CIN/photo documents.
4. Admin CRUD controllers behind `middleware(['auth:sanctum','admin'])`.
5. Inscription CSV export.
6. Mail notification on new inscription/contact submission to `LIONS_NOTIFY_EMAIL`.
7. Settings KV table backed by `SiteSettings` model.

## Frontend integration

When this is deployed, the only frontend change needed is replacing the
mock bodies in [`src/lib/api.ts`](../lion-s-roar-academy/src/lib/api.ts) with `fetch()` calls.
The TanStack Router loaders, error components, and types all stay
identical — the comment at the top of that file anticipates exactly this.

Example (next phase):
```ts
export async function fetchFormations(): Promise<Formation[]> {
  const res = await fetch(`${import.meta.env.VITE_API_URL}/api/v1/formations`);
  const body = await res.json();
  if (!body.success) throw new Error(body.error.message);
  return body.data;
}
```
