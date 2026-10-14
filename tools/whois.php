<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('whois');

/* One question to one WHOIS server, over the port the protocol uses. */
function ask(string $server, string $query): string
{
    $socket = @fsockopen($server, 43, $code, $reason, WAIT);

    if ($socket === false) {
        return '';
    }

    stream_set_timeout($socket, WAIT);
    fwrite($socket, $query . "\r\n");

    $answer = (string) stream_get_contents($socket);
    fclose($socket);

    return $answer;
}

/* whois.iana.org keeps the map of who keeps what. Asked about an ending — com,
   ug — it names the registry that holds names under it, on a line reading
   "whois: whois.verisign-grs.com". Asked about an address it names the registry
   for the block, usually on a line reading "refer: whois.arin.net". */
function referral(string $answer): string
{
    return preg_match('/^(?:refer|whois):\s*(\S+)/mi', $answer, $found) ? trim($found[1]) : '';
}

/* What IANA should be asked about: an address as it stands, and a name by its
   ending alone. IANA keeps endings, not names — it knows who runs "com", not
   who holds "example.com" — so asking it the whole name answers nothing. */
function ending(string $query): string
{
    $dot = strrchr($query, '.');

    return $dot === false ? $query : ltrim($dot, '.');
}

/* Registries pad an answer with comment lines that begin with # or %. They
   carry nothing about the name, so they are dropped; everything else is kept
   exactly as it came, because whois output is the record. */
function useful(string $answer): string
{
    $lines = preg_split('/\R/', $answer) ?: [];
    $lines = array_filter(
        $lines,
        static fn(string $line): bool => !str_starts_with(ltrim($line), '#') && !str_starts_with(ltrim($line), '%')
    );

    return trim(implode("\n", $lines));
}

$query = host(posted('domain'));
$answer = '';
$server = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_ip = filter_var($query, FILTER_VALIDATE_IP) !== false;

    if (!$is_ip && !preg_match('~^([a-z0-9-]+\.)+[a-z]{2,}$~', $query)) {
        $error = 'Enter a domain name or an IP address.';
    } else {
        /* Two questions, in the order the protocol is meant to be used: IANA
           about the ending, then the registry it names about the name itself.
           A registry that does not answer leaves IANA's own record, which for
           a name like example.com is the whole of it. */
        $iana = ask('whois.iana.org', $is_ip ? $query : ending($query));
        $server = referral($iana);

        $answer = $server === '' ? useful($iana) : useful(ask($server, $query));

        if ($answer === '') {
            $error = 'No record came back for ' . $query . '. The service may be busy; try again.';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="domain">Domain name or IP address</label>
    <div class="input-group">
        <input class="form-control" id="domain" name="domain" value="<?= e($query) ?>"
               placeholder="example.com" required autofocus>
        <button class="btn btn-primary" type="submit">Look up</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <div class="alert alert-warning mt-4 mb-0">
        <?= icon('fa-solid fa-circle-exclamation me-1') ?><?= e($error) ?>
    </div>
<?php elseif ($answer !== ''): ?>
    <p class="text-body-secondary mt-4 mb-2">
        <?= e($query) ?>, answered by <code><?= e($server === '' ? 'whois.iana.org' : $server) ?></code>.
    </p>

    <div class="card bg-body rounded-4 border shadow-sm">
        <pre class="mb-0 p-3 small overflow-auto"><?= e($answer) ?></pre>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
