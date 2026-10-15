<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('dns-lookup');

/* Every record type worth showing, and the key its answer keeps the value in.
   dns_get_record() is asked once per type and hands back plain arrays. */
const TYPES = [
    'A'     => 'ip',
    'AAAA'  => 'ipv6',
    'CNAME' => 'target',
    'MX'    => 'target',
    'NS'    => 'target',
    'TXT'   => 'txt',
    'SOA'   => 'mname',
    'CAA'   => 'value',
];

$domain = host(posted('domain'));
$rows = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!preg_match('~^([a-z0-9_-]+\.)+[a-z]{2,}$~', $domain)) {
        $error = 'That does not look like a domain name. Try example.com';
    } else {
        foreach (TYPES as $type => $key) {
            $answers = @dns_get_record($domain, (int) constant('DNS_' . $type));

            foreach ($answers === false ? [] : $answers as $answer) {
                $rows[] = [
                    'type'  => $type,
                    'host'  => $answer['host'] ?? $domain,
                    'ttl'   => (string) ($answer['ttl'] ?? ''),
                    /* MX keeps its priority in its own key, and an SOA is
                       several facts in one row, so those two read differently
                       from the rest. */
                    'value' => match ($type) {
                        'MX'    => $answer['pri'] . ' ' . $answer['target'],
                        'SOA'   => $answer['mname'] . ', ' . $answer['rname'] . ' (serial ' . $answer['serial'] . ')',
                        default => (string) ($answer[$key] ?? ''),
                    },
                ];
            }
        }

        if ($rows === []) {
            $error = 'No records came back for ' . $domain . '. Check the spelling.';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="domain">Domain name</label>
    <div class="input-group">
        <input class="form-control" id="domain" name="domain" value="<?= e($domain) ?>"
               placeholder="example.com" required autofocus>
        <button class="btn btn-primary" type="submit">Look up</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($rows !== []): ?>
    <p class="text-body-secondary mt-4 mb-2">
        <?= count($rows) ?> records for <strong><?= e($domain) ?></strong>.
    </p>

    <div class="card bg-body rounded-4 border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Type</th>
                        <th scope="col">Host</th>
                        <th scope="col">TTL</th>
                        <th scope="col">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><span class="badge text-bg-secondary"><?= e($row['type']) ?></span></td>
                            <td class="text-break"><?= e($row['host']) ?></td>
                            <td class="text-body-secondary"><?= e($row['ttl']) ?></td>
                            <td class="text-break"><code><?= e($row['value']) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
