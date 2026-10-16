# HiWS — Hiswill Web Stack

The umbrella site for Hiswill Kato's free web projects (formerly *Hiswill Web
Services*). It is a fresh, from-scratch rebuild: plain PHP 8.3+, MySQLi, Bootstrap
5.3.3 and Font Awesome 6.5.2, no framework and no build step.

## What is here today

| Page | Address | Notes |
| --- | --- | --- |
| Home | `/` | The project cards, read from the database |
| About | `/about` | Who HiWS is, and who builds it |
| Contact | `/contact` | Self-posting form, CSRF-checked, delivered by mail |
| Disclaimer | `/disclaimer` | |
| Privacy Policy | `/privacy` | |
| Terms & Conditions | `/terms` | |
| 404 | any unknown address | |

## Layout

```
app/            machinery — never served over HTTP
  config.php    the one settings file (identity, contact, database, mail)
  init.php      the first line of every page; loads the rest in order
  db.php        the shared MySQLi connection, reached through db()
  functions.php e(), url(), asset(), icon(), slug(), cut(), the form token
  data.php      every query the site makes
  parts/        header.php and footer.php, the shared page frame
assets/         what the browser downloads: Bootstrap, Font Awesome, style.css
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

The previous site's source is kept only as a reference. The tools, the Bible reader
and the file manager will be ported one at a time; `AGENTS.md` tracks what is still
to come.
