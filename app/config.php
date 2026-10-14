<?php
declare(strict_types=1);

/* =========================================================================
   HiWS — Hiswill Web Stack. THE ONE CONFIG FILE.

   Every page starts at app/init.php, which requires this file first, so a
   setting added here is available everywhere and nowhere needs a second copy.

   This file is committed on purpose: it holds only the local-machine
   settings (root/root on this box) and no secrets beyond them. The live
   server has its own copy of this file with its own database account, and
   app/ is refused over HTTP by .htaccess, so nothing in here is readable
   from the web.
   ========================================================================= */

/* -------------------------------------------------------------------------
   1. Identity
   ------------------------------------------------------------------------- */

define('SITE_NAME', 'HiWS');
define('SITE_FULL_NAME', 'Hiswill Web Stack');
define('SITE_TAGLINE', 'Free web projects and tools for everyone');
define('SITE_YEAR', 2022);            /* the year the first HiWS project went up */

/* The real public address. Used for canonical links and the sitemap only;
   the site itself answers to whatever name the visitor typed. */
define('SITE_DOMAIN', 'hiws.eu.org');

/* -------------------------------------------------------------------------
   2. Contact
   ------------------------------------------------------------------------- */

define('SITE_EMAIL', 'contact@hiws.eu.org');
define('SITE_PHONE', '+256761236205');
define('SITE_WHATSAPP', '256761236205');
define('SITE_LOCATION', '7V2V+36W Bbanda, Mityana');

/* -------------------------------------------------------------------------
   3. Database
   ------------------------------------------------------------------------- */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'hiws');

/* -------------------------------------------------------------------------
   4. Address

   Left empty, so url() builds addresses from the request and the site works
   as http://hiws/ and https://hiws/ without being edited. HOSTS pins which
   names may drive that, so a stray Host header cannot make HiWS hand out
   links to a foreign domain. On a live server, set BASE_URL to the real
   address instead and it wins.
   ------------------------------------------------------------------------- */

define('BASE_URL', '');
define('HOSTS', ['hiws', 'www.hiws', 'hiws.eu.org', 'www.hiws.eu.org', 'localhost', '127.0.0.1']);

/* -------------------------------------------------------------------------
   5. Mail

   The contact form hands messages to the mail server on this machine, which
   is Postfix on loopback. MAIL_USER stays empty for an unauthenticated
   local relay; a provider that needs a login sets it (and MAIL_PASS) here.
   ------------------------------------------------------------------------- */

define('MAIL_FROM', 'noreply@mail.local');
define('MAIL_ADMIN', 'admin@mail.local');
define('MAIL_SUBJECT_PREFIX', '[' . SITE_NAME . '] ');

/* -------------------------------------------------------------------------
   6. Housekeeping
   ------------------------------------------------------------------------- */

/* true while working on the site, so PHP shows every error on screen. */
define('SHOW_ERRORS', false);

/* Items shown per page on any listing (tools, projects). */
define('PER_PAGE', 12);
