# Lucid Auto Haus

Website and admin panel for a car sales consultant. Plain PHP 8, MySQL, Bootstrap 5 — no framework, no build step.

## Setup (XAMPP)

1. Copy the folder into `htdocs/onemillioncar/`.
2. Create `includes/config.php` from `includes/config.sample.php` (defaults work for XAMPP: root, no password).
3. Import the database:
   ```bash
   /Applications/XAMPP/xamppfiles/bin/mysql -u root < sql/schema.sql
   /Applications/XAMPP/xamppfiles/bin/mysql -u root < sql/seed.sql
   ```
4. Generate the web assets and placeholder photos:
   ```bash
   /Applications/XAMPP/xamppfiles/bin/php tools/make-assets.php
   /Applications/XAMPP/xamppfiles/bin/php tools/make-placeholders.php
   chmod -R o+w uploads
   ```
5. Open `http://localhost/onemillioncar/`. Admin: `http://localhost/onemillioncar/admin/` — `admin` / `admin123`.

## First things to change in the admin

- **Account** → change the password.
- **Settings → Business** → your name, title and bio.
- **Settings → Contact** → phone, WhatsApp, email, notification email, address, hours, map.
- **Vehicles** → replace the generated placeholder photos with real ones.

## Pages

Home · Inventory (filters, sorting, pagination) · Vehicle detail (gallery, specs, payment calculator, inquiry / test-drive form) · About · Services · Financing (calculator + pre-approval) · Trade-In (appraisal form) · Testimonials · FAQ · Contact · Privacy · sitemap.xml / robots.txt

See `CLAUDE.md` for the code layout.

## Automatic cPanel deployment

This repository includes cPanel push deployment in `.cpanel.yml`. Every push to
GitHub's `main` branch runs `.github/workflows/cpanel-deploy.yml`, which pushes
the same commit to a cPanel-managed Git repository. cPanel's post-receive hook
then runs `tools/cpanel-deploy.sh` and publishes the application to
`$HOME/public_html`.

The deployment deliberately preserves these production-only files:

- `includes/config.php` (database credentials and production settings)
- `uploads/vehicles/` (photos uploaded from the admin panel)

The root `.htaccess` **must stay committed**. Every clean URL (`/services`,
`/about`, `/inventory/<slug>`) is produced by its rewrite rules; a server left
with an older or hand-written copy answers those URLs with 404 — or 500 if its
rules loop — while the `.php` endpoints keep working. `tools/cpanel-deploy.sh`
now refuses to deploy if the file is missing from the repository.

### One-time setup

1. In **cPanel → Files → Git Version Control**, create a new, empty repository
   outside `public_html`, for example `/home/CPANEL_USER/repositories/onemillioncar`.
   Do not clone GitHub into it; GitHub Actions will push to this repository.
2. If the website's document root is not `/home/CPANEL_USER/public_html`, change
   the path in `.cpanel.yml` before the first deployment.
3. Create a dedicated, passphrase-free SSH deployment key. Import and authorize
   its public key in **cPanel → Security → SSH Access**.
4. In the GitHub repository, open **Settings → Secrets and variables → Actions**
   and add these repository secrets:
   - `CPANEL_REPOSITORY_URL`: the cPanel repository's SSH clone URL shown by
     Git Version Control.
   - `CPANEL_SSH_PRIVATE_KEY`: the complete private deployment key, including
     its BEGIN/END lines.
   - `CPANEL_SSH_KNOWN_HOSTS`: the verified SSH host-key line for the cPanel
     server. Obtain it from the hosting provider or verify the output of
     `ssh-keyscan -p 22 CPANEL_HOST` before saving it.
5. Before the first deployment, create `public_html/includes/config.php` from
   `includes/config.sample.php`, enter the production database credentials, and
   import `sql/schema.sql` and `sql/seed.sql` through phpMyAdmin or the terminal.
6. Push to `main`, or run **Deploy to cPanel** manually from GitHub's Actions
   tab. cPanel deployment logs are stored under `~/.cpanel/logs/`.

No public webhook PHP endpoint is required. GitHub's push event starts the
workflow, and cPanel's managed Git hook performs the deployment.
