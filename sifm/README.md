# SiFM — the file manager on sifm.hiws.dev

SiFM is a separate project, not hand-written here:

- Source: <https://github.com/HiswillKato/SiFM>
- Licence: MIT (see `LICENSE`)

`sifm.php` is copied from upstream with these local differences:

1. A note saying where it came from.
2. Every `SIFM_*` constant at the top is declared with `if (!defined(...))`,
   so a value that is already set wins and nothing is ever defined twice —
   the defaults can be edited in place, or set before the file is loaded.
3. The shipped pair is `sifm` / `sifm`, and while the password is still that
   one a reminder is repeated on every signed-in page. `SIFM_CREDENTIAL_WARNING`
   hides the reminder without changing anything else.
4. `SIFM_HOST` is pinned to `sifm.hiws.dev` instead of `$_SERVER['HTTP_HOST']`.
5. `SIFM_READONLY` and the short block in the dispatch that refuses every POST
   except the sign-in form and the search box. Multi-select is reassigned only
   from the folder listing (`?folder=` + `multi=1`), so `?signin=` / `?search=`
   can never run a multi action behind the auth or read-only gates; the
   confirmation screen posts to `?multi=` directly.
6. The sign-in throttle — `SIFM_LOGIN_ATTEMPTS` / `SIFM_LOGIN_WINDOW` and the
   `login_tries()` tally the sign-in form consults. The tally lives in a
   private per-UID directory under the system temp folder (`0700`,
   `sifm-<uid>/login.json`, `0600`), trusted only when this worker owns it, so
   a local account cannot pre-create or symlink it. The sign-in compares the
   username and password unconditionally (no timing oracle).
7. Hardening upstream does not have: `abs()` resolves each path through
   `realpath()`, including its nearest existing ancestor, so a symlinked parent
   cannot escape the scope; `import()` resolves once and pins the connection to
   the checked public addresses (Host/SNI name the original host, no redirects,
   no credentials) to close DNS-rebinding TOCTOU; `SIFM_BLOCKED_SERVER_FILES`
   (`.htaccess`, `.user.ini`, `web.config`) are never created, edited, moved,
   renamed, chmodded or deleted; `SIFM_BLOCKED_UPLOAD_EXTENSIONS` blocks
   server-executable names on content entering from outside (upload, import,
   extract, create) while leaving existing files editable/renameable in place;
   `extract()` skips those names, enforces `SIFM_MAX_EXTRACT` /
   `SIFM_MAX_EXTRACT_FILES` and checks free space; writes take `LOCK_EX`.
8. File content is streamed through the manager (`?view=` for inline
   previews/"open", `?download=` for attachment), never by a direct URL, so
   `SIFM_HOME` can live outside the web root and files in scope are not
   directly fetchable. The view handler sets `nosniff`, `private, no-store`
   caching, and sandboxes renderable documents (HTML/SVG/XML). Previews and the
   "Open" action use the handler instead of `file_url()`.
8. `file_url()` builds the preview and "open in browser" addresses from where
   `SIFM_HOME` actually sits, so a scope narrower than the document root still
   resolves instead of asking for the file one level too high.

The scope defaults to the document root, which is what upstream does too —
`SIFM_HOME` falls back to `$_SERVER['DOCUMENT_ROOT']`, then to the folder
`sifm.php` sits in. Because file content is now streamed through the manager's
handlers (not served by direct URL), **set `SIFM_HOME` to a directory outside
the web root** for any writable or non-demo deployment and deny direct web
access to that path. This keeps managed files from being directly fetchable
even if a URL is guessed, while previews/downloads continue to work through the
authenticated handlers. The public read-only demo on this server follows this
posture: its scope is `/var/www/sifm-demo-data` (outside the docroot) and
served only through the manager. If `SIFM_HOME` must live inside the web root,
ensure the web server denies direct access to it (as this vhost does for
`/demo/` and `/home/`). Define `SIFM_HOME` before the file loads to set it.

## The vhost

The vhost is in `/etc/nginx/conf.d/hiws.conf`. It serves this folder with
three scripts and nothing else — `index.php` (the project home),
`demo/index.php` (the manager) and `sifm.php` itself. Every other `.php`
under the folder is refused, so an uploaded PHP file can never run. Managed
paths (`/demo/`, `/home/`) are denied direct access; file content is streamed
only through SiFM's handlers (`?view`, `?download`), so files in scope are not
directly fetchable by URL. `/assets/` is not used here — SiFM loads Bootstrap,
Font Awesome and CodeMirror from their CDNs.

`.htaccess` in this folder is read by nginx's Lua layer, and it refuses
`sifm.php` over HTTP:

```apache
<Files "sifm.php">
    Require all denied
</Files>
```

The script still runs, because the demo loads it with `require()` — this
governs requests, not includes.

## The demo on this server

`demo/index.php` is the whole demo:

```php
define('SIFM_READONLY', true);
define('SIFM_CREDENTIAL_WARNING', false);
define('SIFM_HOME', '/var/www/sifm-demo-data');

require dirname(__DIR__) . '/sifm.php';
```

It sets three switches and then loads the manager, so the vendored file stays
exactly as upstream has it and deleting the folder puts everything back to
normal:

- **`SIFM_READONLY`** — the demo browses, searches and downloads, and refuses
  every change with *"SiFM is currently in read-only mode. You cannot make
  changes."*
- **`SIFM_CREDENTIAL_WARNING`** — no reminder about the default sign-in
  details.
- **`SIFM_HOME`** — the scope is a data directory outside the web root
  (`/var/www/sifm-demo-data`), so the file list shows only the sample content
  there (a `notes/` folder, a markdown file, a chart, a PDF and a ZIP). It can
  never see `sifm.php` or anything else beside it, and the sample files are not
  directly fetchable by URL — content is streamed through SiFM's `?view` /
  `?download` handlers.

Because `.htaccess` refuses `/sifm.php`, this wrapper is the only way in:
nobody can reach the unrestricted copy by asking for it directly.

The folder is not in git — `.gitignore` has `/sifm/demo/` — so a deploy does
not carry it. It belongs to this server: the wrapper above and the sample files
placed in `/var/www/sifm-demo-data` (readable by the web server) are all it
takes to recreate it. The read-only demo's sample content is intentionally
public to signed-in demo visitors, but served only through the manager.

## Change the default sign-in details

> **The shipped login is `sifm` / `sifm`. Change both before this is reachable
> from the internet.** Whoever signs in has full read and write access to every
> file the manager can see.

The account lives in `sifm.php`, at the top — there is no database:

1. Make a hash of your own password:

   ```bash
   php -r "echo password_hash('yourpass', PASSWORD_DEFAULT), PHP_EOL;"
   ```

2. Set `SIFM_ADMIN` to your username and `SIFM_PASSWORD` to that hash.

While `SIFM_PASSWORD` is still the value the file ships with, every signed-in
page repeats this warning — it disappears on its own once the constant changes.
The sign-in page stays clean; the reminder is for whoever is already inside.
To silence it while the password is still the shipped one, set
`SIFM_CREDENTIAL_WARNING` to `false`: the banner is then hidden everywhere,
which changes nothing about who can sign in.

## Sign-in throttle

After `SIFM_LOGIN_ATTEMPTS` (5) failed sign-ins from one client address, that
address is refused for `SIFM_LOGIN_WINDOW` (5 minutes) — correct password or
not. The tally lives in a private per-UID directory under the system temp
folder (`0700`, `sifm-<uid>/login.json`, `0600`), trusted only when this PHP
worker owns the directory and file, so starting a new session or clearing the
cookie does not reset it, a successful sign-in clears it, and another local
account cannot pre-create or symlink it. A temp folder that cannot be used is
counted as no failures (fail-open), never as a lockout. Both constants sit at
the top of `sifm.php`; set the attempts to `0` to switch the throttle off.

The address is `REMOTE_ADDR`, which nginx has already replaced with the real
visitor — `set_real_ip_from` Cloudflare's ranges plus `real_ip_header
CF-Connecting-IP`, both in `deploy/nginx/hiws.conf`. Without that every
request would be counted against a Cloudflare edge address, and unrelated
visitors would be locked out together. The app's `sleep(1)` per attempt still
sits behind the tally as a second line.

## Read-only mode

Set `SIFM_READONLY` to `true` at the top of `sifm.php` — or define it before
the file is loaded — and the manager stops accepting changes. Every POST is
refused except the sign-in form and the search box, so browsing, downloading
and the read views keep working while nothing can be created, uploaded, edited,
moved or deleted. A refused request shows a warning on the page, and answers
with JSON for the buttons that post through `fetch()`.

This server uses it the second way: `demo/index.php` defines the constant
before loading `sifm.php`, so the copy everyone is shown is read-only while
the vendored file keeps its normal setting.

## Updating from upstream

```bash
curl -fsSL https://raw.githubusercontent.com/HiswillKato/SiFM/main/sifm.php -o /tmp/sifm.php
# re-apply, in this order: the vendored note; wrap every define in
# if (!defined(...)); the sifm/sifm pair, the reminder and
# SIFM_CREDENTIAL_WARNING; SIFM_HOST pinned to sifm.hiws.dev;
# SIFM_READONLY, its refusal message and the dispatch block (multi
# reassigned only from ?folder=; ?multi= stays direct); the login
# throttle (private per-UID temp dir, unconditional credential compare);
# the ?view handler (inline streaming, nosniff/sandbox headers) and
# preview/"open" URLs routed through it instead of direct file_url();
# file_url(); then the hardening in abs(), import() (resolve-once,
# connect-pinned), SIFM_BLOCKED_SERVER_FILES / the extended
# SIFM_BLOCKED_UPLOAD_EXTENSIONS and their guards on every write path,
# and extract()'s entry whitelist + zip-bomb caps, then:
cp /tmp/sifm.php sifm/sifm.php
```
