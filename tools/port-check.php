<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('port-check');

/* The ports people actually ask about, so an answer reads "HTTPS" rather than
   a bare number. Anything else is simply unlabelled. */
const SERVICES = [
    21 => 'FTP', 22 => 'SSH', 23 => 'Telnet', 25 => 'SMTP', 53 => 'DNS',
    80 => 'HTTP', 110 => 'POP3', 143 => 'IMAP', 443 => 'HTTPS', 465 => 'SMTPS',
    587 => 'Mail submission', 993 => 'IMAPS', 995 => 'POP3S', 3306 => 'MySQL',
    5432 => 'PostgreSQL', 6379 => 'Redis', 8080 => 'HTTP (alternative)', 8443 => 'HTTPS (alternative)',
];

$given = posted('host');
$port = posted('port');
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '' || $port === '') {
        $error = 'Enter a host and a port.';
    } elseif (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
        $error = 'The port has to be a number from 1 to 65535.';
    } else {
        /* Resolve the host first and refuse anything private or reserved, so a
           stranger cannot use this page to poke at the server's own network. */
        $resolve = filter_var($given, FILTER_VALIDATE_IP) !== false ? $given : (string) @gethostbyname($given);

        if ($resolve === '' || filter_var($resolve, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $error = 'That host does not resolve to a public address, which this tool will not probe.';
        } else {
            $number = (int) $port;
            $start = microtime(true);
            $socket = @fsockopen($given, $number, $code, $reason, 5);
            $elapsed = (int) round((microtime(true) - $start) * 1000);
            $open = $socket !== false;

            if ($open) {
                fclose($socket);
            }

            $result = [
                'Host'    => $given,
                'Address' => $resolve === $given ? '' : $resolve,
                'Port'    => (string) $number,
                'Service' => SERVICES[$number] ?? 'Unlabelled port',
                'State'   => $open ? 'Open — something accepted the connection' : 'Closed, or filtered by a firewall',
                'Time'    => $elapsed . ' ms',
            ];
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col-sm-8">
            <label class="form-label fw-semibold" for="host">Host</label>
            <input class="form-control" id="host" name="host" value="<?= e($given) ?>"
                   placeholder="example.com or 93.184.216.34" autocomplete="off" autofocus>
        </div>
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="port">Port</label>
            <input class="form-control" id="port" name="port" inputmode="numeric" value="<?= e($port) ?>" placeholder="443">
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Check port</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= values($result) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
