# CLAUDE.md — Namma E Store

Guidance for Claude (and any dev) working in this repo. Read this before
writing or editing views, controllers, or assets. See [README.md](README.md)
for install/setup and [SETUP_GUIDE.md](SETUP_GUIDE.md) for DB/XAMPP steps.

---

## What this is

Custom PHP MVC multi-seller marketplace, no framework. Four panels,
each a top-level sibling folder (not nested under `app/`):

| Panel | URL prefix | Folder |
|---|---|---|
| Customer storefront | `/` | `frontend/` |
| Admin | `/mc-admin/` | `admin/` |
| Seller dashboard | `/my-seller/` | `seller-panel/` |
| REST API (for the future React frontend) | `/api/` | `api/` |

Each panel has its own `Controllers/`, `Services/`, `Views/`, `Routes/routes.php`,
and `assets/` — a panel's own folder is the only thing that changes when you
update it; `admin/`, `frontend/`, `seller-panel/`, and `api/` don't share code
with each other directly.

`app/` now holds only the shared library used by all four panels: `Core/`
(Router, Controller, Model, Auth, DB, payment providers, invoice templates),
`Config/` (app/database/providers config, merged in from the old root
`config/`), `Helpers/` (`functions.php`, `csrf.php`), `Models/`, and
`Repositories/`.

**Document root is `public/`**, not the project root. `public/index.php` is
the real entry point; `app/`, `admin/`, `frontend/`, `seller-panel/`, `api/`,
and `app/Config/` all sit outside `public/` and are unreachable by any URL —
no `.htaccess`/`nginx.conf` blocklist needed for them. (A root `index.php` +
`.htaccess` still exist as a documented-deprecated fallback for hosts that
can't point their doc root anywhere but the project folder; static assets
won't load under that path, so don't develop against it — browse
`http://localhost/<project>/public/` on XAMPP instead.)

Each panel's `assets/` folder (CSS/JS) is served by `app/bootstrap.php`
directly (a small static-file route matching `/assets/{panel}/...`), since
those folders live outside `public/` for the same reason the code does, but
static files still need a stable URL. Don't move panel assets into
`public/assets/` — that would break the per-panel colocation this route
depends on.

---

## Hard rules for markup and assets

**No inline CSS.** Never use a `style="..."` attribute. Add a class and
define it in the panel's stylesheet under `{admin,frontend,seller-panel}/assets/css/`.
The codebase currently has legacy `style="..."` usage from before this rule —
don't copy it, and clean it up if you're already editing that block.

**No inline JS.** Never use `onclick="..."`, `onchange="..."`, or a
`<script>` block embedded in a view. Put JS in a file under
`{admin,frontend,seller-panel}/assets/js/` and wire it up with an event
listener keyed off a class or `data-*` attribute, not an inline handler.

**Exception — form field validation.** Real-time client-side validation
triggers (`oninput="validateField(this)"`, `onblur="validateField(this)"`,
`onsubmit="return validateForm(this)"`) may be written inline directly on
the form/field in the view. This is the one allowed use of inline JS
attributes. The actual validation logic must NOT live inline — it stays in
the shared `{admin,frontend}/assets/js/form-validation.js` file as reusable
functions (`validateField`, `validateForm`, etc.) that the inline attributes
call into. Any other inline handler (image preview, toggling a field based
on a select, etc.) is not validation and must follow the normal no-inline-JS
rule — external file, `data-*`/class-keyed listener.

**Never clear field values on a failed validation.** On both client-side
validation failure and server-side redirect-back-with-errors, every field
must keep exactly what the user typed. Never call `form.reset()` or blank a
field as part of error handling — only an explicit user click on a reset/clear
control may empty the form.

**Every `<img>` needs a real `alt`.** Not empty, not the filename — describe
what the image is (product name, banner purpose, brand name). Decorative-only
images (rare) get `alt=""` explicitly, never an omitted attribute.

**Uploaded image filenames must include the site name.** `uploadFile()` in
[functions.php](app/Helpers/functions.php) currently generates a fully random
name (`bin2hex(random_bytes(16))`) for security — the original filename is
never trusted. When adding/touching upload logic, prefix that random name with
the site slug so stored files read as `{site-slug}-{random}.{ext}`, e.g.
`namma-e-store-9f3a1b2c.jpg`. Keep the random suffix — it's what prevents
path-guessing and filename collisions; the site slug is only there for
identification/branding, not uniqueness. Pull the slug from `mc_settings.site_name`
(via `slugify()`) rather than hardcoding it, since the site name is
configurable (Admin → Settings) and not hardcoded anywhere else in the app.

**Asset versioning:** always link CSS/JS through `asset('frontend/css/main.css')`
(or `admin_asset()` / `seller_asset()`), never a raw `<link href="...">` /
`<script src="...">` path — `asset()` appends a cache-busting `?v=mtime`.

---

## Existing conventions to follow (already enforced in code)

- **Escape all output.** Use `e($value)` for anything user-supplied or
  DB-sourced going into HTML — never echo raw. See `app/Helpers/functions.php:25`.
- **URLs:** `url()`, `admin_url()`, `seller_url()` — never hardcode `/mc-admin/`
  or `/my-seller/` paths in views.
- **Sort/pagination:** any user-controlled `ORDER BY` column must go through
  `safeSortField()` (whitelists against allowed columns) before it touches SQL —
  column names can't be parameterized like values.
- **File uploads:** always go through `uploadFile()` — it whitelists extension,
  verifies real MIME via `finfo` (never trusts client `Content-Type` or the
  original filename), and caps size at 5MB. Don't hand-roll `move_uploaded_file()`.
- **DB table prefix:** all tables are `mc_*`.
- **Site name:** read from `mc_settings.site_name`, never hardcoded as
  "Namma E Store" in code — that's just the seeded default.

---

## Structure quick reference

```
public/                    ← Document root — set your webserver here
├── index.php               ← Real entry point
├── .htaccess
└── uploads/                 ← User-uploaded files (product images etc.)

app/                        ← Shared library — used by all four panels below
├── bootstrap.php            ← Panel routing (admin/seller/frontend/api) + static-asset serving
├── Core/                    ← Router, Controller, Model, Auth, DB, payment providers
├── Config/                  ← app.php, database.php, providers.php
├── Helpers/functions.php    ← e(), url(), asset(), uploadFile(), pagination, etc.
├── Models/                  ← Shared models (User, Product, Order...)
└── Repositories/

admin/                      ← /mc-admin/
├── Controllers/  Services/  Views/  Routes/routes.php  assets/{css,js}/

frontend/                   ← / (storefront)
├── Controllers/  Services/  Views/  Routes/routes.php  assets/{css,js}/

seller-panel/               ← /my-seller/
├── Controllers/  Services/  Views/  Routes/routes.php  assets/{css,js}/

api/                        ← /api/ (REST endpoints for the future React frontend)
├── Controllers/  Routes/routes.php

index.php, .htaccess         ← deprecated fallback entry point, kept only for
                                hosts that can't point their doc root at public/
```

Each panel keeps its own CSS/JS under `<panel>/assets/` — don't cross-import
one panel's stylesheet into another, and don't move any panel's `assets/`
into `public/assets/` (see "What this is" above for why they're served via
`bootstrap.php` instead of sitting directly in the web root).
