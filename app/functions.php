<?php
declare(strict_types=1);

/* =========================================================================
   Shared helpers. Every function here earns its place by being called from
   more than one page — anything used once lives on that page instead.
   ========================================================================= */

/* -------------------------------------------------------------------------
   1. Text and addresses
   ------------------------------------------------------------------------- */

/* The one escaping point. Anything printed into HTML goes through here, so a
   stray quote in a project title cannot break the page. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* An absolute address. With BASE_URL empty the name comes from the request,
   so the site answers to hiws, www.hiws or the real domain alike — but only
   for names on HOSTS, so a forged Host header cannot rewrite our links. */
function url(string $path = ''): string
{
    if (BASE_URL !== '') {
        $base = rtrim(BASE_URL, '/');
    } else {
        $name = trim(preg_replace('~:\d{1,5}$~', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        $known = HOSTS === [] || in_array($name, HOSTS, true);

        if ($name === '' || !$known) {
            $name = 'localhost';
        }

        $scheme = (($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['SERVER_PORT'] ?? null) === '443')
            ? 'https'
            : 'http';

        $base = $scheme . '://' . $name;
    }

    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/* A Font Awesome icon by its full class name, e.g. icon('fa-solid fa-lock').
   The name is escaped like any other value, so the helper is safe to call
   with a string that came out of the database. */
function icon(string $name): string
{
    return '<i class="' . e($name) . '" aria-hidden="true"></i>';
}

/* The light/dark switch. The choice belongs to the browser, so this only
   prints the button — theme.js flips what it points at and remembers it. The
   masthead holds two copies, one for each size of screen. */
function toggle(string $extra = ''): string
{
    $class = trim('btn btn-ghost rounded-circle theme-toggle ' . $extra);

    return '<button type="button" class="' . e($class) . '" aria-label="Switch theme">'
        . icon('fa-solid fa-moon') . icon('fa-solid fa-sun') . '</button>';
}

/* A URL-safe version of a title, used for project and tool addresses. */
function slug(string $text): string
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($text))) ?? '', '-');
}

/* Where a book lives: its name written as an address, so "Song of Solomon"
   reads song-of-solomon and "1 John" reads 1-john. */
function address(array $book): string
{
    return url('bible/' . slug($book['book']));
}

/* The bare host at the front of whatever a visitor typed, so that
   "https://example.com/page" and "EXAMPLE.com." both become "example.com". */
function host(string $value): string
{
    $value = strtolower(trim($value));
    $value = (string) preg_replace('~^[a-z][a-z0-9+.-]*://~', '', $value);

    return rtrim((string) strtok($value, '/'), '.');
}

/* A row's icon column as markup. The column holds a bare Font Awesome name
   ("lock"), or a full "fa-brands fa-github" for a brand mark, so the style
   prefix is added only when the value does not carry one. A row with nothing
   in the column gets a cube rather than an empty box. */
function mark(string $name): string
{
    $name = trim($name);

    if ($name === '') {
        $name = 'fa-cube';
    }

    return icon(str_starts_with($name, 'fa-brands') ? $name : 'fa-solid ' . $name);
}

/* The first part of a longer piece, cut on a word where one is found. */
function cut(string $text, int $length = 150): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    $short = mb_substr($text, 0, $length);
    $space = mb_strrpos($short, ' ');

    return ($space === false ? $short : mb_substr($short, 0, $space)) . '…';
}

/* Mark the current page in the navigation. Compares the path, not the script,
   because a sub-app's scripts are named read.php, index.php and the rest —
   what the visitor is on is the section, /bible or /tools, not the file. */
function here(string $page): bool
{
    $path = (string) ($_SERVER['SCRIPT_NAME'] ?? '/');

    if ($page === 'index') {
        return $path === '/' || $path === '/index.php';
    }

    return $path === '/' . $page . '.php' || str_starts_with($path, '/' . $page . '/');
}

/* -------------------------------------------------------------------------
   2. Rendering the answer

   Three shapes cover nearly every tool: the amber box that says the input did
   not make sense, the card of labelled values a lookup answers with, and the
   card of copyable text a converter answers with. Naming them here keeps the
   markup in one place, so a tool page reads as its logic and nothing else.
   ------------------------------------------------------------------------- */

/* The amber warning a tool shows when the input is wrong. $text is escaped,
   because it usually carries something the visitor typed back to them — an
   address that would not resolve, a hash that was too short. */
function notice(string $text): string
{
    return '<div class="alert alert-warning mt-4 mb-0" role="alert">'
        . icon('fa-solid fa-circle-exclamation me-1') . e($text) . '</div>';
}

/* A copy button. The value rides in a data attribute and copy.js, loaded by
   the footer, does the rest. $label adds a visible word beside the icon;
   without one the button is icon-only and carries a hidden label for screen
   readers. Named clip() rather than copy(), which PHP already has. */
function clip(string $value, string $label = ''): string
{
    $word = $label === ''
        ? '<span class="visually-hidden">Copy</span>'
        : '<span>' . e($label) . '</span>';

    return '<button class="btn btn-sm btn-outline-secondary" type="button" data-copy="' . e($value) . '">'
        . icon('fa-regular fa-copy') . $word . '</button>';
}

/* A card holding one piece of text — the shape an encode, decode or convert
   tool answers with. $label names the box; $tall caps its height so a long
   answer scrolls inside the card instead of stretching the page.

   The visible code keeps its quotes — a minified `class="card"` should read
   as written, not as `class=&quot;card&quot;` — so only the three characters
   that could start markup are escaped here. The copy button carries the full
   escaping, because its value sits inside an HTML attribute. */
function output(string $text, string $label = '', bool $tall = false): string
{
    $head = $label === ''
        ? ''
        : '<div class="card-header bg-transparent d-flex align-items-center justify-content-between">'
            . '<span class="fw-semibold">' . e($label) . '</span>' . clip($text) . '</div>';

    return '<div class="card bg-body rounded-4 border shadow-sm mt-4">' . $head
        . '<div class="card-body">'
        . '<pre class="mb-0' . ($tall ? ' code-window' : '') . '"><code>'
        . htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8')
        . '</code></pre>'
        . '</div></div>';
}

/* A card holding a table of labelled values — the shape a lookup answers with.
   Empty values drop out, so a page can hand in everything it found and let the
   table show only what came back. $caption is developer-written HTML and is
   trusted; a value that came from the visitor is escaped. */
function values(array $pairs, string $caption = ''): string
{
    $rows = '';

    foreach ($pairs as $label => $value) {
        if (is_bool($value)) {
            $value = $value ? 'Yes' : 'No';
        }

        $value = trim((string) $value);

        if ($value === '') {
            continue;
        }

        $rows .= '<tr>'
            . '<th scope="row" class="text-body-secondary fw-normal w-50">' . e((string) $label) . '</th>'
            . '<td class="text-break">' . e($value) . '</td>'
            . '</tr>';
    }

    if ($rows === '') {
        return '';
    }

    return '<div class="card bg-body rounded-4 border shadow-sm mt-4">'
        . '<div class="table-responsive"><table class="table table-sm align-middle mb-0"><tbody>'
        . $rows
        . '</tbody></table></div></div>'
        . ($caption === '' ? '' : '<p class="text-body-secondary small mt-3 mb-0 text-center">' . $caption . '</p>');
}

/* A byte count in units a person reads — 2.4 KB rather than 2458. */
function bytes(int $size): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $power = $size > 0 ? min((int) floor(log($size, 1024)), count($units) - 1) : 0;

    return ($power === 0 ? (string) $size : number_format($size / (1024 ** $power), 1)) . ' ' . $units[$power];
}

/* A number with no trailing zeros — 3 rather than 3.000000, 0.5 not 0.500000.
   Very large and very small values fall back to scientific notation so they
   stay readable. */
function trimmed(float $value): string
{
    $abs = abs($value);

    if ($abs !== 0.0 && ($abs >= 1e12 || $abs < 1e-6)) {
        return rtrim(rtrim(sprintf('%.6e', $value), '0'), '.');
    }

    return rtrim(rtrim(number_format($value, 6, '.', ''), '0'), '.') ?: '0';
}

/* -------------------------------------------------------------------------
   3. Talking to other machines

   The tools that reach out — a dictionary, a shortener, a screenshot service,
   a currency feed — and the four that are handed a URL all come through here,
   so timeouts, the user agent, and the refusal to poke at private addresses
   are decided once instead of sixteen times.
   ------------------------------------------------------------------------- */

/* A value a form just posted, or an empty string. */
function posted(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

/* GET a URL and return its body, or null if anything at all went wrong —
   a timeout, a TLS failure, a 404. The caller decides what to tell the
   visitor; nothing here prints. Pass $headers to also receive the response
   lines, which is what the header viewer reads. */
function fetch(string $url, int $timeout = WAIT, ?array &$headers = null): ?string
{
    $context = stream_context_create([
        'http' => [
            'timeout' => $timeout,
            'follow_location' => 1,
            'header' => 'Accept: */*' . "\r\n" . 'User-Agent: ' . SITE_NAME . " (web tool)\r\n",
        ],
    ]);

    $body = @file_get_contents($url, false, $context);

    /* The HTTP wrapper leaves the response lines in this local variable, and
       only in the scope that made the call — so it has to be read here, on
       both the good answer and the refusal. */
    $headers = $http_response_header ?? [];

    return $body === false ? null : $body;
}

/* The same, decoded. null for a failed request or a body that is not JSON. */
function json(string $url, int $timeout = WAIT): ?array
{
    $body = fetch($url, $timeout);

    if ($body === null) {
        return null;
    }

    $decoded = json_decode($body, true);

    return is_array($decoded) ? $decoded : null;
}

/* A URL a visitor typed in, allowed only if it is http(s) and its host does
   not resolve to this machine or to any private or reserved address.

   source-code, http-headers, code-text-ratio and screenshot all hand a
   stranger's URL to the network, and without this check "http://127.0.0.1/"
   or the cloud metadata address would fetch the server's own secrets back to
   them. One guard, called by all four. */
function public_url(string $url): ?string
{
    $url = trim($url);
    $parts = parse_url($url);

    if ($parts === false || !isset($parts['host']) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
        return null;
    }

    $host = $parts['host'];

    /* An IP literal is its own answer; a name has to be resolved first, because
       a name can point anywhere. gethostbynamel() answers with IPv4 addresses
       or false, so a name that has only an IPv6 address is refused rather than
       half-checked. */
    $addresses = filter_var($host, FILTER_VALIDATE_IP) !== false ? [$host] : gethostbynamel($host);

    if ($addresses === false) {
        return null;
    }

    foreach ($addresses as $address) {
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }
    }

    return $url;
}

/* Which translation /bible is reading. A ?v= in the address wins and is kept
   for the rest of the visit, so a link to a verse can be shared in one
   translation while the choice still sticks for the next page. */
function translation(): string
{
    $asked = strtoupper((string) ($_GET['v'] ?? ''));

    if (isset(BIBLE_VERSIONS[$asked])) {
        $_SESSION['translation'] = $asked;

        return $asked;
    }

    $kept = (string) ($_SESSION['translation'] ?? '');

    return isset(BIBLE_VERSIONS[$kept]) ? $kept : BIBLE_DEFAULT;
}

/* -------------------------------------------------------------------------
   4. The contact form
   ------------------------------------------------------------------------- */

/* One hidden field, and the matching check. The token lives in the session,
   so a form posted from another site — which has no session — is refused. */
function token(): string
{
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['token'];
}

function token_field(): string
{
    return '<input type="hidden" name="token" value="' . e(token()) . '">';
}

function token_ok(?string $sent): bool
{
    return is_string($sent) && $sent !== '' && hash_equals(token(), $sent);
}
