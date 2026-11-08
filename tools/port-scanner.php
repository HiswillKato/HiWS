<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('port-scanner');

const SCAN_SERVICES = [
    21 => 'FTP', 22 => 'SSH', 23 => 'Telnet', 25 => 'SMTP', 53 => 'DNS', 80 => 'HTTP',
    110 => 'POP3', 143 => 'IMAP', 443 => 'HTTPS', 465 => 'SMTPS', 587 => 'Submission',
    993 => 'IMAPS', 995 => 'POP3S', 3306 => 'MySQL', 3389 => 'RDP', 5432 => 'PostgreSQL',
    6379 => 'Redis', 8080 => 'HTTP alt', 8443 => 'HTTPS alt', 27017 => 'MongoDB',
];

function scan_parse_ports(string $input, int $limit, ?string &$error): array
{
    $ports = [];
    $input = trim($input);
    if ($input === '') {
        $error = 'Enter one or more ports, such as 22,80,443 or 1-1024.';
        return [];
    }

    foreach (explode(',', $input) as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }
        if (preg_match('/^(\d{1,5})-(\d{1,5})$/', $part, $m)) {
            $start = (int) $m[1];
            $end = (int) $m[2];
            if ($start < 1 || $end > 65535 || $start > $end) {
                $error = 'Port ranges must be between 1 and 65535, with the start before the end.';
                return [];
            }
            if (($end - $start + 1) > $limit) {
                $error = 'That range is too large. Scan up to ' . $limit . ' ports at a time.';
                return [];
            }
            for ($p = $start; $p <= $end; $p++) {
                $ports[$p] = true;
                if (count($ports) > $limit) {
                    $error = 'Too many ports. Scan up to ' . $limit . ' ports at a time.';
                    return [];
                }
            }
        } elseif (ctype_digit($part)) {
            $p = (int) $part;
            if ($p < 1 || $p > 65535) {
                $error = 'Ports must be numbers from 1 to 65535.';
                return [];
            }
            $ports[$p] = true;
            if (count($ports) > $limit) {
                $error = 'Too many ports. Scan up to ' . $limit . ' ports at a time.';
                return [];
            }
        } else {
            $error = 'Could not read "' . $part . '". Use numbers, commas, or ranges like 20-25.';
            return [];
        }
    }

    $out = array_keys($ports);
    sort($out);

    if ($out === []) {
        $error = 'Enter at least one port.';
    }

    return $out;
}

$given = posted('host');
$ports_in = posted('ports');
$error = '';
$rows = [];
$summary = ['open' => 0, 'closed' => 0, 'total' => 0];
$resolved = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ports = scan_parse_ports($ports_in, 50, $error);

    if ($error === '' && $given === '') {
        $error = 'Enter a host to scan.';
    }

    if ($error === '' && $ports !== []) {
        $resolve = filter_var($given, FILTER_VALIDATE_IP) !== false ? $given : (string) @gethostbyname($given);
        if ($resolve === '' || filter_var($resolve, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $error = 'That host does not resolve to a public address. This tool only scans public hosts you have permission to test.';
        } else {
            $resolved = $resolve === $given ? '' : $resolve;
            foreach ($ports as $number) {
                $start = microtime(true);
                $socket = @stream_socket_client(
                    'tcp://' . $given . ':' . $number,
                    $code,
                    $reason,
                    1.5,
                    STREAM_CLIENT_CONNECT
                );
                $elapsed = (int) round((microtime(true) - $start) * 1000);
                $open = $socket !== false;
                if ($open) {
                    fclose($socket);
                }
                $rows[] = [
                    'port'    => $number,
                    'service' => SCAN_SERVICES[$number] ?? '',
                    'state'   => $open ? 'open' : ($elapsed >= 1400 ? 'filtered/timeout' : 'closed'),
                    'time'    => $elapsed,
                ];
                $summary['total']++;
                if ($open) {
                    $summary['open']++;
                } else {
                    $summary['closed']++;
                }
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<div class="alert alert-warning d-flex gap-2" role="alert">
    <?= icon('fa-solid fa-triangle-exclamation') ?>
    <div>
        <strong>Only scan servers you own or have explicit permission to test.</strong>
        Unauthorized port scanning can be unwanted or illegal. This tool is limited to public hosts and a small number of ports per run.
    </div>
</div>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-7">
            <label class="form-label fw-semibold" for="host">Host</label>
            <input class="form-control" id="host" name="host" value="<?= e($given) ?>"
                   placeholder="example.com or 93.184.216.34" autocomplete="off" autofocus>
        </div>
        <div class="col-md-5">
            <label class="form-label fw-semibold" for="ports">Ports</label>
            <input class="form-control font-monospace" id="ports" name="ports" value="<?= e($ports_in) ?>"
                   placeholder="22,80,443,8000-8010">
        </div>
    </div>
    <p class="form-text mb-0 mt-2">Enter ports separated by commas and/or ranges. Limited to 50 ports per scan with a short timeout.</p>
    <button class="btn btn-primary mt-3" type="submit">Scan ports</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($rows !== []): ?>
    <div class="d-flex gap-2 mt-4 flex-wrap align-items-center">
        <span class="badge text-bg-primary"><?= e((string) $summary['total']) ?> scanned</span>
        <span class="badge text-bg-success"><?= e((string) $summary['open']) ?> open</span>
        <span class="badge text-bg-secondary"><?= e((string) $summary['closed']) ?> closed/filtered</span>
        <?php if ($resolved !== ''): ?><span class="text-body-secondary small">Resolved to <code><?= e($resolved) ?></code></span><?php endif; ?>
    </div>

    <div class="card bg-body rounded-4 border shadow-sm mt-3">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Port</th>
                        <th scope="col">Service</th>
                        <th scope="col">State</th>
                        <th scope="col">Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><code><?= e((string) $row['port']) ?></code></td>
                            <td><?= e($row['service']) ?></td>
                            <td>
                                <?php if ($row['state'] === 'open'): ?>
                                    <span class="badge text-bg-success">Open</span>
                                <?php elseif ($row['state'] === 'filtered/timeout'): ?>
                                    <span class="badge text-bg-warning">Filtered / timeout</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Closed</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-body-secondary"><?= e((string) $row['time']) ?> ms</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
