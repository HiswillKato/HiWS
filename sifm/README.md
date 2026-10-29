# SiFM — the file manager on sifm.hiws.dev

SiFM is a separate project, not hand-written here:

- Source: <https://github.com/HiswillKato/SiFM>
- Licence: MIT (see `LICENSE`)

`sifm.php` is copied from upstream unchanged except for two lines near the top:

1. A note saying where it came from.
2. `SIFM_HOME` points at `home/` instead of the document root, so the manager
   never lists or edits the application file itself. `home/` is ignored by git,
   which means uploaded files survive a deploy.

The vhost is in `/etc/nginx/conf.d/hiws.conf`. It serves this folder with
`sifm.php` as the only executable script: every other `.php` under the folder is
refused, so an uploaded PHP file can never run. `/assets/` is not used here —
SiFM loads Bootstrap, Font Awesome and CodeMirror from their CDNs.

## Updating from upstream

```bash
curl -fsSL https://raw.githubusercontent.com/HiswillKato/SiFM/main/sifm.php -o /tmp/sifm.php
# re-apply the SIFM_HOME change, then:
cp /tmp/sifm.php sifm/sifm.php
```

The admin account is configured in `sifm.php` (`SIFM_ADMIN`, `SIFM_PASSWORD`,
a `password_hash()` value). Change the password there, not in the database.

> **Security warning:** The default SiFM login is `sifm` / `sifm`. Change both the username and password in `sifm.php` immediately after installation, especially before exposing the manager to the internet.
