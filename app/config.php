<?php
declare(strict_types=1);

/* =========================================================================
   HiWS — Hiswill Web Stack. THE ONE CONFIG FILE.

   Every page starts at app/init.php, which requires this file first, so a
   setting added here is available everywhere and nowhere needs a second copy.

   Secrets and machine-local credentials live in app/config.local.php (not
   committed). This tracked file keeps safe defaults only. The live server has
   its own copy/config.local.php, and app/ is refused over HTTP by .htaccess.
   ========================================================================= */

/* Local overrides (secrets, machine-specific settings). Not committed. */
$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    require $local;
}

/* -------------------------------------------------------------------------
   1. Identity
   ------------------------------------------------------------------------- */

define('SITE_NAME', 'HiWS');
define('SITE_FULL_NAME', 'Hiswill Web Stack');
define('SITE_TAGLINE', 'Free web projects and tools for everyone');
define('SITE_YEAR', 2022);            /* the year the first HiWS project went up */

/* The words every page carries unless it names its own. A tool page passes
   its row's keywords instead, which is what that column is for. */
define('SITE_KEYWORDS', 'hiws, hiswill web stack, hiswill kato, free web tools, online bible, free web projects, developer tools, uganda, web utilities, calculator, seo, keywords, converter, lookup, generator, bible study, scripture, password, ssl, ip address, dns, hash, encoding, compression, math, finance, uganda resources');

/* The real public address. Used for canonical links and the sitemap only;
   the site itself answers to whatever name the visitor typed. */
define('SITE_DOMAIN', 'hiws.dev');

/* -------------------------------------------------------------------------
   2. Contact
   ------------------------------------------------------------------------- */

define('SITE_EMAIL', 'hiwstack@gmail.com');
define('SITE_PHONE', '+256751884466');
define('SITE_WHATSAPP', '256751884466');
define('SITE_LOCATION', '7V2V+36W Bbanda, Mityana');

/* -------------------------------------------------------------------------
   3. Database
   ------------------------------------------------------------------------- */

if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_USER')) define('DB_USER', '');
if (!defined('DB_PASS')) define('DB_PASS', ''); /* set in app/config.local.php */
if (!defined('DB_NAME')) define('DB_NAME', '');

/* -------------------------------------------------------------------------
   4. Address

   Left empty, so url() builds addresses from the request and the site works
   as http://hiws/ and https://hiws/ without being edited. HOSTS pins which
   names may drive that, so a stray Host header cannot make HiWS hand out
   links to a foreign domain. On a live server, set BASE_URL to the real
   address instead and it wins.
   ------------------------------------------------------------------------- */

define('BASE_URL', 'https://hiws.dev/');
define('HOSTS', ['hiws.dev', 'www.hiws.dev', '*.hiws.dev']);

/* -------------------------------------------------------------------------
   5. Mail

   The contact form sends through SMTP. The app password is stored here on
   purpose for this machine; app/ is refused over HTTP, so it is not readable
   from the web. The live server keeps its own copy of this file.
   ------------------------------------------------------------------------- */

define('MAIL_FROM', 'hiwstack@gmail.com');
define('MAIL_ADMIN', 'hiwstack@gmail.com');
define('MAIL_SUBJECT_PREFIX', '[' . SITE_NAME . '] ');

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USER')) define('SMTP_USER', 'hiwstack@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', ''); /* set in app/config.local.php */
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', 'tls');

/* -------------------------------------------------------------------------
   6. The Bible

   /bible reads the `bible` table, one row per verse per translation. The
   keys here are the values in `bible.version`; the labels are what the
   switcher shows, and the list is what a ?v= may name. The site publishes
   one translation — the King James Version — because it is in the public
   domain, which is what lets the text be republished at all. Adding one
   back means a key here, a row loaded by database/bible.php, and nothing
   else: every page reads this list.
   ------------------------------------------------------------------------- */

define('BIBLE_VERSIONS', ['KJV' => 'King James Version']);
define('BIBLE_DEFAULT', 'KJV');

/* -------------------------------------------------------------------------
   7. Housekeeping
   ------------------------------------------------------------------------- */

/* true while working on the site, so PHP shows every error on screen. */
define('SHOW_ERRORS', false);

/* -------------------------------------------------------------------------
   8. OpenGraph & Twitter Cards
   ------------------------------------------------------------------------- */

/* Default social image when no page-specific image is set. */
define('OG_IMAGE', 'https://hiws.dev/images/logo-social.png');

/* The site name for og:site_name and twitter:site. */
define('OG_SITE_NAME', SITE_NAME);

/* Twitter handle for twitter:site. Leave empty if none. */
define('TWITTER_HANDLE', '@hiswillkato');

/* Verses shown per page of Bible search results. */
define('PER_PAGE', 20);

/* Seconds a tool waits for another machine before giving up and saying so.
   The web server abandons a page at sixty seconds, so this is sized for the
   whole page rather than one call: the shortener tries three services, and
   three waits still leave the page time to answer instead of dying as a 504. */
define('WAIT', 12);
