# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Lucid Auto Haus** — a car sales consultant's website (brand from the supplied "LAH" logo: black / silver / signal red). Plain PHP 8 + MySQL + Bootstrap 5.3, no framework, no Composer, no build step. Public site plus a password-protected admin panel that manages inventory, leads, testimonials, FAQs and settings.

The folder name `onemillioncar` predates the brand; the site itself is Lucid Auto Haus.

Note: the parent `htdocs/` folder has its own `CLAUDE.md` for a *different* project (Bullger Burger). Nothing here shares code with it.

## Local Development

Runs under XAMPP. Visit `http://localhost/onemillioncar/` (public) and `http://localhost/onemillioncar/admin/` (login `admin` / `admin123` — change under Admin → Account).

```bash
# XAMPP's own binaries (system php/mysql may differ)
PHP=/Applications/XAMPP/xamppfiles/bin/php
MYSQL="/Applications/XAMPP/xamppfiles/bin/mysql --socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock -u root"

$MYSQL < sql/schema.sql          # creates onemillioncar_db and all tables (drops existing!)
$MYSQL < sql/seed.sql            # settings, admin user, 14 demo vehicles, testimonials, FAQs
$PHP tools/make-assets.php       # logo trim, hero JPEGs, favicon, OG card (from images/)
$PHP tools/make-placeholders.php # GD-drawn car photos for every vehicle lacking files (--force to redraw)
chmod -R o+w uploads             # Apache runs as `daemon`; PHP can't write otherwise

$PHP -l some-file.php            # lint a file
```

`includes/config.php` holds DB credentials and is git-ignored; `includes/config.sample.php` is the template. There are no tests.

**Smoke test after changes:** `curl -s http://localhost/onemillioncar/<page> | grep -iE "warning|fatal"`. For screenshots use headless Chrome; for mobile widths, Chrome enforces a ~500px minimum window, so frame the page in a 390px `<iframe>` served from localhost (`X-Frame-Options: SAMEORIGIN` blocks `file://`).

## Architecture

### Request flow

`.htaccess` rewrites clean URLs to flat PHP files: `/about` → `about.php`, `/inventory/{slug}` → `vehicle.php?slug=`, `/sitemap.xml` → `sitemap.php`. Every page starts with `require 'includes/app.php'` (defines `APP_ROOT`, loads config + `db.php` + `functions.php`, starts the `lah_session` cookie session, opens PDO), sets `$pageTitle` / `$metaDescription` / `$hero`, then includes `includes/header.php`, `includes/hero.php`, its own markup, `includes/cta-band.php` and `includes/footer.php`.

Admin pages instead start with `require 'includes/auth.php'; require_login();` and wrap content in `admin/includes/header.php` / `footer.php`. Admin URLs are plain `.php` files (no rewriting).

### URL helpers (always use these)

- `url('inventory')` — site-relative link; works at domain root or in a subfolder because `app_base()` derives the prefix from `SCRIPT_NAME` vs `SCRIPT_FILENAME`. Set `CLEAN_URLS=false` in config if the host ignores `.htaccess` and links fall back to `.php` endpoints.
- `asset('assets/css/styles.css')` — adds `?v=filemtime` cache busting.
- `vehicle_url($v)`, `image_url($path, $fallback)`, `absolute_url($path)` (for OG/JSON-LD/sitemap), `admin_url('leads.php')`.
- `redirect('contact#message')` sends a 303 and exits.

### Data layer

`includes/db.php`: one PDO singleton (`db()`) plus `db_all / db_one / db_value / db_insert / db_update / db_query`. All queries are prepared statements returning plain arrays. No ORM, no models — domain queries live as functions in `includes/functions.php` (`vehicles_search()`, `vehicle_by_slug()`, `inventory_facets()`, `similar_vehicles()`, `testimonials()`, `faqs()`, `lead_create()`).

Tables: `vehicles` (+ `vehicle_images`, cascade delete), `leads` (`type` enum: contact / inquiry / test_drive / trade_in / financing; extra fields as JSON in `details`), `testimonials`, `faqs`, `settings` (key/value), `users`.

### Settings

Every piece of site copy and contact info is a row in `settings`, read with `setting('key', 'default')` (cached per request) and edited in Admin → Settings, whose field list is the `$groups` array in `admin/settings.php` — add a key there and it becomes editable. `site_name()`, `agent_name()`, `agent_first_name()`, `business_hours()`, `social_links()` are thin wrappers.

### Vehicles

`vehicles.status`: `available` / `pending` / `sold` / `hidden`. Public queries use `status IN ("available","pending")`; `inventory?status=sold` shows sold. `vehicle_price()` returns `sale_price` when set and lower than `price`; `vehicle_on_sale()` drives the "Price Drop" badge. `cover_image` is a path; if blank the first `vehicle_images` row is used. Slugs are auto-generated from year/make/model/trim and de-duplicated in `admin/vehicle-form.php`.

Photos: `admin/includes/upload.php::save_vehicle_image()` validates via finfo + `getimagesize`, decodes with GD (strips payloads, applies EXIF orientation), resizes to 1600px and always writes JPEG into `uploads/vehicles/`. XAMPP's GD has **no WebP support**, so WebP uploads fail with a clear message. `uploads/.htaccess` disables script execution.

### Forms and leads

All five public forms (contact, vehicle inquiry / test drive, trade-in, financing) POST to themselves, use `form_guard_fields()` (CSRF token `_token`, timestamp `_ts` for a 3-second minimum, honeypot `website`), validate with `post_str()` / `valid_email()` / `valid_phone()`, keep input with `old()`, then call `lead_create()` and `redirect()` with a `flash_set()` message (rendered by `flash_render()`). `lead_notify()` sends a best-effort `mail()` to `notify_email` and `lead_sms_notify()` (`includes/sms.php`) texts the same lead through Twilio — leads are always stored regardless, and neither notifier can block or fail the submission.

Admin POSTs call `require_csrf()` (403 on failure — Apache remaps unknown codes like 419 to 500).

### SMS alerts

`includes/sms.php` posts to Twilio's REST API with cURL (no SDK). Credentials are
settings rows, edited under Admin → Settings → SMS alerts, never in code or git:
`sms_enabled`, `sms_notify_number`, `twilio_account_sid`, `twilio_auth_token`,
`twilio_from_number`. `sms_enabled()` requires the toggle *and* every credential,
so a half-filled form simply sends nothing.

Numbers are normalised to E.164 by `sms_e164()` (bare 10 digits are assumed +1).
`lead_sms_body()` keeps each alert inside `SMS_MAX_SEGMENTS` (2) by deriving the
character budget from the text's own encoding — one character outside GSM-7
(`·`, a curly quote, an emoji, most accents) drops a segment from 153 characters
to 67, so the separators this file writes are plain ASCII and the visitor's
message is trimmed, or dropped, to fit. The admin link is never trimmed.

Failures are logged with `error_log('[sms] …')` and swallowed. The settings page
has a "Save & send test SMS" button that reports Twilio's own error text.

### Front-end

`assets/css/styles.css` defines the palette as `--lah-*` custom properties; dark sections (`.section-dark`, `.section-ink`) alternate with light `.section-paper`. Fonts Montserrat (display) + Inter (body). `assets/js/scripts.js` is dependency-free: sticky header, `.reveal` intersection observer, gallery + lightbox (`[data-gallery]`), payment calculators (`[data-calculator]` — any container with `calc_*` inputs), inventory select auto-submit, counters (`[data-count]`).

Hero images are the supplied showroom photos; the salesperson is always on the right, so `includes/hero.php` puts copy on the left and takes a per-page `focus` (object-position) so the face stays in frame on mobile.

### Placeholder imagery

`images/` holds the client's originals (logo, headshot, six hero shots) and is never served directly. `tools/make-assets.php` derives everything in `assets/img/`. Vehicle photos in the seed are drawn by `tools/make-placeholders.php` (stylised side profiles in each car's `color_hex`) and are meant to be replaced by real uploads through the admin; the tool skips files that already exist.

## Things worth knowing

- The seeded consultant name **"Alex Morgan"**, phone, email and address are placeholders — the real ones go in Admin → Settings.
- Twilio credentials are seeded blank on purpose. Never commit a real Auth Token; enter it in the admin panel, where the field is write-only (blank keeps the stored value).
- `sql/seed.sql` was produced with a shell heredoc, so the admin password hash inside it is a literal bcrypt string; regenerate with `php -r "echo password_hash('x', PASSWORD_DEFAULT);"` if you change it.
- Prices are CAD; the calculators assume 13% HST (Ontario).
