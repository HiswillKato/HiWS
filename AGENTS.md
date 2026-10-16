# AGENTS.md — HiWS (Hiswill Web Stack)

Hiswill Web Stack, **HiWS** for short, is the umbrella site for Hiswill Kato's free
web projects. It is a fresh rebuild of the old "Hiswill Web Services" site, which
lived at `hiws.eu.org`; the old source is kept only as reference and none of it is
carried over verbatim.

Plain procedural PHP 8.3+, MySQLi, Bootstrap 5.3.3 and Font Awesome 6.5.2 (both
committed under `assets/`), no framework, no Composer, no npm, no build step.
nginx + php-fpm serves it; data lives in MariaDB.

## Where it is served

- **https://hiws/** (alias `http://www.hiws/`, `https://www.hiws/`). Both names are
  in `/etc/hosts` as `127.0.0.1`, and `/etc/wsl.conf` sets `generateHosts = false`
  so WSL does not wipe them on restart.
- vhost `/etc/nginx/conf.d/hiws.conf`, docroot `/var/www/hiws`. **`root` and the
  included `snippets/php-site.conf` are both load-bearing:** `.htaccess` holds the
  router and the `app/` deny-list, so dropping the snippet's Lua `.htaccess` layer
  would 404 every short address while `/about.php` still worked — which reads as a
  rewrite bug rather than a config one. The same snippet also carries the shared
  `/pma/` route (phpMyAdmin from `/usr/share/phpmyadmin` via the `/usr/share/pma`
  symlink, its own FPM pool).
- TLS certificate `/etc/ssl/hiswill-ca/certs/hiws.crt`, key in `private/`, signed by
  **Hiswill CA** (`/etc/ssl/hiswill-ca/ca.crt`), which the local browsers trust.
  To mint another name, copy the shape of the existing ones: EC P-256, SHA-256,
  730 days, SAN + `extendedKeyUsage = serverAuth`.
- Database **`hiws`** on `127.0.0.1`, account `root` / `root` (see `app/config.php`).

## How a request flows

1. A page at the root (`about.php`, …) is reached by its short address (`/about`),
   which the root `.htaccess` rewrites.
2. The page's first line is `require __DIR__ . '/app/init.php'`, which loads
   `config` → `db` → `functions` → `data` and installs the error handler.
3. The page sets `$title`, requires `app/parts/header.php`, prints its body, then
   requires `app/parts/footer.php`.

`app/` is refused over HTTP by both the root `.htaccess` and `app/.htaccess`,
because `app/config.php` holds the database account.

## House rules that matter here

- **Bootstrap 5 built-ins only.** No invented components. Branding is done by
  publishing `--bs-*` tokens in `assets/css/style.css`, so `btn`, `card`, `badge`,
  `list-group`, `form-control` pick it up untouched. The only bespoke classes are
  `.brand-mark` and the quiet sticky navbar, neither of which Bootstrap provides.
- Custom classes elsewhere are limited to colour, text and button helpers
  (`.text-*`, `.btn-*`, `.bg-*`, `.border-*`).
- Every value printed into HTML goes through `e()`.
- Shared logic lives in `app/functions.php` (or `app/data.php` for a query); a page
  must not grow its own copy. Anything used once belongs on that page.
- The icon column in both tables holds a Font Awesome 6 name **without** the style
  prefix — `fa-screwdriver-wrench` renders as
  `<i class="fa-solid fa-screwdriver-wrench">` through `icon()`.

## Run and verify

There is no test suite and no dev-server command; nginx serves the tree directly.

```bash
php -l <file>                              # syntax check a file
for f in $(find . -name '*.php'); do php -l "$f"; done   # all of them
curl -sk -o /dev/null -w '%{http_code}\n' https://hiws/
curl -sk -o /dev/null -w '%{http_code}\n' https://hiws/app/config.php   # must be 403
sudo nginx -t && sudo systemctl reload nginx                            # after a vhost change
```

## Database

`database.sql` is the schema **and** the reset. Import with:

```bash
mysql -uroot -proot hiws < database.sql
```

**It starts with `DROP TABLE`** — importing it destroys every existing row. There
is no migration runner, so a schema change is applied by hand and then reflected in
`database.sql`. `projects.status = 0` hides a row from the home page without
deleting it.

## Still to build

The three projects the home page links to are not ported yet. Until they are, those
cards answer with the 404 page:

- `/tools` — the online tools (17 rows already sit in the `tools` table, with their
  explainer text, ready to render).
- `/bible` — the online Bible reader.
- `/sifm` — the simple file manager.
