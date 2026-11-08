# AGENTS.md — HiWS (Hiswill Web Stack)

Hiswill Web Stack, **HiWS** for short, is the umbrella site for Hiswill Kato's free
web projects. It is a fresh rebuild of the old "Hiswill Web Services" site, which
lived at `hiws.eu.org`; the old source is kept only as reference and none of it is
carried over verbatim.

Plain procedural PHP 8.3+, MySQLi, Bootstrap 5.3.3 and Font Awesome 6.5.2 (both
committed under `assets/`), no framework, no Composer, no npm, no build step.
nginx + php-fpm serves it; data lives in MariaDB.

## Where it is served

- **https://hiws.dev/** is the canonical address. `https://www.hiws.dev/`,
  **https://hiws.eu.org/** and **https://hiws.us.ci/** are aliases: nginx
  redirects their apex, www and project subdomains to the matching `hiws.dev`
  address, so one page never has two spellings.
- vhost `/etc/nginx/conf.d/hiws.conf`, docroot `/var/www/hiws`. **`root` and the
  included `snippets/php-site.conf` are both load-bearing:** `.htaccess` holds the
  router and the `app/` deny-list, so dropping the snippet's Lua `.htaccess` layer
  would 404 every short address while `/about.php` still worked — which reads as a
  rewrite bug rather than a config one. The same snippet also carries the shared
  `/pma/` route (phpMyAdmin from `/usr/share/phpmyadmin` via the `/usr/share/pma`
  symlink, its own FPM pool).
- Each project is its own home on a subdomain: **https://tools.hiws.dev/**,
  **https://bible.hiws.dev/** and **https://sifm.hiws.dev/**. The vhost has one
  server block per project, each with its folder as `root`, and it owns that
  project's routing too — `/bmi` → `bmi.php`, `/john/3` → `read.php`. The project
  folders carry no `.htaccess`; the whole subdomain layout is in
  `/etc/nginx/conf.d/hiws.conf`. `app/projects.php` lists the names in `PROJECTS`,
  so `url('tools/bmi')` builds an address on that project's host from anywhere.
  The old `/tools`, `/bible` and `/sifm` addresses 301 to the subdomains. Locally,
  add `127.0.0.1 hiws.dev www.hiws.dev tools.hiws.dev bible.hiws.dev sifm.hiws.dev`
  to `/etc/hosts` to use them.
- That vhost — every server block and its routing — is versioned at
  `deploy/nginx/hiws.conf`, which is denied over HTTP. Edit it there, then
  `sudo cp deploy/nginx/hiws.conf /etc/nginx/conf.d/hiws.conf && sudo nginx -t &&
  sudo systemctl reload nginx`.
- TLS is one Let's Encrypt certificate at `/etc/letsencrypt/live/hiws.dev/`,
  covering `hiws.dev`, `hiws.eu.org`, `hiws.us.ci` and one wildcard for each.
  Renewal uses DNS-01 through the Cloudflare credentials in
  `/root/.secrets/cloudflare.ini`; check it with
  `sudo certbot renew --cert-name hiws.dev --dry-run`. A deploy hook reloads
  nginx after a successful renewal.
- Database **`hiws`** on `127.0.0.1`, account `root` / `root` (see `app/config.php`).

## How a request flows

1. A page at the root (`about.php`, …) is reached by its short address (`/about`),
   which the root `.htaccess` rewrites.
2. On a project subdomain the nginx server block sets the project folder as the
   document root and turns the readable addresses into scripts: `/bmi` is
   `tools/bmi.php`, `/john/3` is `bible/read.php?book=john&chapter=3`. The old
   in-tree addresses on `hiws.dev` redirect to the subdomain. Adding a project is
   a server block here plus its name in `PROJECTS`; its folder needs no router.
3. The page's first line is `require __DIR__ . '/app/init.php'`, which loads
   `config` → `projects` → `db` → `functions` → `data` and installs the error
   handler.
4. The page sets `$title`, requires `app/parts/header.php`, prints its body, then
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
curl -sk -o /dev/null -w '%{http_code}\n' https://tools.hiws.dev/bmi    # project home
curl -sk -o /dev/null -w '%{http_code}\n' https://bible.hiws.dev/john/3 # 404 until the text is loaded
curl -sk -o /dev/null -w '%{http_code}\n' https://sifm.hiws.dev/        # 302 to ?signin
curl -sk -o /dev/null -w '%{http_code}\n' https://hiws/tools            # 301 to the subdomain
curl -sk -o /dev/null -w '%{http_code}\n' https://hiws/robots.txt       # text/plain
curl -sk -o /dev/null -w '%{http_code}\n' https://hiws/sitemap.xml      # application/xml, built by sitemap.php
sudo nginx -t && sudo systemctl reload nginx                            # after a vhost change
```

## Database

`database.sql` is the schema **and** the reset. Import with:

```bash
mysql -uroot -proot hiws < database.sql
php database/bible.php     # the KJV text — the bible table is created empty
```

**It starts with `DROP TABLE`** — importing it destroys every existing row. There
is no migration runner, so a schema change is applied by hand and then reflected in
`database.sql`. `projects.status = 0` hides a row from the home page without
deleting it. The site publishes one translation, the King James Version: it is
public domain, and `BIBLE_VERSIONS` in `app/config.php` is the list a `?v=` may
name.

## The projects

All three cards on the home page are live. A project folder is a document root of
its own, so it holds only its own pages:

- **tools/** — one file per tool; `/bmi` is `bmi.php`.
- **bible/** — `read.php`, `search.php`, `facts.php`, `jics.php`; nginx routes the
  book/chapter addresses.
- **sifm/** — SiFM, vendored from <https://github.com/HiswillKato/SiFM> (MIT).
  `sifm.php` is upstream plus the local differences `sifm/README.md` lists:
  every `SIFM_*` define is guarded so a value set beforehand wins, the shipped
  pair is `sifm`/`sifm` with a reminder that `SIFM_CREDENTIAL_WARNING` can
  hide, and there is a read-only mode and a sign-in throttle. `SIFM_HOME` is
  the document root, so the manager lists its own files as well as anything
  uploaded. The manager is reached through `sifm/demo/index.php`, a wrapper
  that defines `SIFM_READONLY`, `SIFM_CREDENTIAL_WARNING` and `SIFM_HOME` —
  scoped to the demo folder — before it loads `sifm.php`, and `.htaccess`
  refuses `/sifm.php` over HTTP so the wrapper cannot be bypassed. Only
  `index.php`, `demo/index.php` and `sifm.php` may execute — every other
  `.php` under the folder is refused, so an uploaded PHP file can never run.
