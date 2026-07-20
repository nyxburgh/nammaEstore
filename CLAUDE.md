# CLAUDE.md — Namma E Store

Guidance for Claude (and any dev) working in this repo. Read this before
writing or editing views, controllers, or assets. See [README.md](README.md)
for install/setup and [SETUP_GUIDE.md](SETUP_GUIDE.md) for DB/XAMPP steps.

---

## What this is

Custom PHP MVC multi-vendor marketplace, no framework. Three panels
sharing one codebase:

| Panel | URL prefix | Folder |
|---|---|---|
| Customer storefront | `/` | `app/frontend/` |
| Admin | `/mc-admin/` | `app/admin/` |
| Vendor dashboard | `/my-vendor/` | `app/vendor-panel/` |

Shared code lives in `app/core/` (Router, Controller, Model, Auth, DB,
payment providers, invoice templates) and `app/models/`. Shared view
helpers are in `app/helpers/functions.php`.

---

## Hard rules for markup and assets

**No inline CSS.** Never use a `style="..."` attribute. Add a class and
define it in the panel's stylesheet under `assets/{admin,frontend,vendor-panel}/css/`.
The codebase currently has legacy `style="..."` usage from before this rule —
don't copy it, and clean it up if you're already editing that block.

**No inline JS.** Never use `onclick="..."`, `onchange="..."`, or a
`<script>` block embedded in a view. Put JS in a file under
`assets/{admin,frontend,vendor-panel}/js/` and wire it up with an event
listener keyed off a class or `data-*` attribute, not an inline handler.

**Every `<img>` needs a real `alt`.** Not empty, not the filename — describe
what the image is (product name, banner purpose, brand name). Decorative-only
images (rare) get `alt=""` explicitly, never an omitted attribute.

**Uploaded image filenames must include the site name.** `uploadFile()` in
[functions.php](app/helpers/functions.php) currently generates a fully random
name (`bin2hex(random_bytes(16))`) for security — the original filename is
never trusted. When adding/touching upload logic, prefix that random name with
the site slug so stored files read as `{site-slug}-{random}.{ext}`, e.g.
`namma-e-store-9f3a1b2c.jpg`. Keep the random suffix — it's what prevents
path-guessing and filename collisions; the site slug is only there for
identification/branding, not uniqueness. Pull the slug from `mc_settings.site_name`
(via `slugify()`) rather than hardcoding it, since the site name is
configurable (Admin → Settings) and not hardcoded anywhere else in the app.

**Asset versioning:** always link CSS/JS through `asset('frontend/css/main.css')`
(or `admin_asset()` / `vendor_asset()`), never a raw `<link href="...">` /
`<script src="...">` path — `asset()` appends a cache-busting `?v=mtime`.

---

## Existing conventions to follow (already enforced in code)

- **Escape all output.** Use `e($value)` for anything user-supplied or
  DB-sourced going into HTML — never echo raw. See `app/helpers/functions.php:25`.
- **URLs:** `url()`, `admin_url()`, `vendor_url()` — never hardcode `/mc-admin/`
  or `/my-vendor/` paths in views.
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
app/
├── bootstrap.php          ← Panel routing (admin/vendor/frontend)
├── core/                  ← Router, Controller, Model, Auth, DB, payment providers
├── helpers/functions.php  ← e(), url(), asset(), uploadFile(), pagination, etc.
├── models/                ← Shared models (User, Product, Order...)
├── admin/                 ← Controllers/Services/Views for /mc-admin/
├── frontend/              ← Controllers/Services/Views for storefront
└── vendor-panel/          ← Controllers/Services/Views for /my-vendor/
assets/{admin,frontend,vendor-panel}/{css,js}/
```

Each panel keeps its own CSS/JS under `assets/<panel>/` — don't cross-import
one panel's stylesheet into another.
