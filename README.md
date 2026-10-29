# HiWS — Hiswill Web Stack

The umbrella site for Hiswill Kato's free web projects (formerly *Hiswill Web
Services*). It is a fresh, from-scratch rebuild: plain PHP 8.3+, MySQLi, Bootstrap
5.3.3 and Font Awesome 6.5.2, no framework and no build step.

## What is here today

| Page | Address | Notes |
| --- | --- | --- |
| Home | `/` | The project cards, read from the database |
| Tools | https://tools.hiws.dev/ | 50+ free online tools, one page each |
| Bible | https://bible.hiws.dev/ | Read, search and compare the Bible |
| File manager | https://sifm.hiws.dev/ | SiFM — browse, upload, edit and download files |
| About | `/about` | Who HiWS is, and who builds it |
| Contact | `/contact` | Self-posting form, CSRF-checked, delivered by mail |
| Disclaimer | `/disclaimer` | |
| Privacy Policy | `/privacy` | |
| Terms & Conditions | `/terms` | |
| 404 | any unknown address | |

Each project answers on its own subdomain, with `/tools`, `/bible` and `/sifm` on
the main site redirecting there. The whole subdomain layout — one server block per
project, including its routing — lives in `/etc/nginx/conf.d/hiws.conf`; the
project folders carry no `.htaccess`. `PROJECTS` in `app/projects.php` lists the
names, and `url('tools/bmi')` builds the right host from anywhere on the site.

The site also answers at `hiws.eu.org` and `hiws.us.ci`. Those names and their
project subdomains redirect to the matching `hiws.dev` address, and one Let's
Encrypt certificate covers all three domains plus a wildcard for each.

## Layout

```
app/            machinery — never served over HTTP
  config.php    the one settings file (identity, contact, database, mail)
  projects.php  PROJECTS — the names that answer on their own subdomain
  init.php      the first line of every page; loads the rest in order
  db.php        the shared MySQLi connection, reached through db()
  functions.php e(), url(), asset(), icon(), slug(), cut(), the form token
  data.php      every query the site makes
  parts/        header.php and footer.php, the shared page frame
assets/         what the browser downloads: Bootstrap, Font Awesome, style.css
tools/          tools.hiws.dev — one PHP file per tool
bible/          bible.hiws.dev — read, search, facts
sifm/           sifm.hiws.dev — SiFM from HiswillKato/SiFM; home/ is its files
deploy/nginx/   the vhost: one server block per subdomain, routing included
database.sql    schema + seed; DROP TABLE first, so it is also the reset
```

## Running it locally

nginx serves the tree directly; there is no dev server.

```bash
mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS hiws CHARACTER SET utf8mb4"
mysql -uroot -proot hiws < database.sql
```

Then open **https://hiws/** — the certificate is signed by the local Hiswill CA, so
there is no warning. `http://hiws/` redirects to it.

## Rebranding

All of the colour, shape and typography lives in the `:root` token block at the top
of `assets/css/style.css`. Changing those `--bs-*` values restyles every Bootstrap
component on the site at once, because nothing here re-implements one.

## Note on the rebuild

The previous site's source is kept only as a reference. The tools, the Bible
reader and the file manager all live on their own subdomains. SiFM is a separate
MIT project, vendored under `sifm/`; see `sifm/README.md` for its update steps.
