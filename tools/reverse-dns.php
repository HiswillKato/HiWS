<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('reverse-dns');

$given = posted('host');
$address = '';
$names = [];
$records = 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter an IP address or a hostname.';
    } else {
        /* A name is resolved first, so a hostname and the address it points at
           both end up asking the same question of the reverse zone. */
        if (filter_var($given, FILTER_VALIDATE_IP) !== false) {
            $address = $given;
        } else {
            $list = @gethostbynamel($given);
            $address = is_array($list) ? (string) ($list[0] ?? '') : '';
        }

        if ($address === '') {
            $error = 'That did not resolve to an IPv4 address. Check the spelling, or try an IP.';
        } else {
            $name = (string) @gethostbyaddr($address);

            if ($name !== '' && $name !== $address) {
                $names[] = $name;
            }

            if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                $zone = implode('.', array_reverse(explode('.', $address))) . '.in-addr.arpa';
                $found = @dns_get_record($zone, DNS_PTR) ?: [];
                $records = count($found);

                foreach ($found as $record) {
                    $target = rtrim((string) ($record['target'] ?? ''), '.');

                    if ($target !== '' && !in_array($target, $names, true)) {
                        $names[] = $target;
                    }
                }
            }
        }
    }
}

$zone = filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
    ? implode('.', array_reverse(explode('.', $address))) . '.in-addr.arpa'
    : '';

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="host">IP address or hostname</label>
    <div class="input-group">
        <input class="form-control" id="host" name="host" value="<?= e($given) ?>"
               placeholder="8.8.8.8" autocomplete="off" autofocus>
        <button class="btn btn-primary" type="submit">Look up</button>
    </div>
    <p class="form-text mb-0 mt-2">A name is resolved first, then asked in reverse.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($address !== ''): ?>
    <?= values([
        'Address'      => $address,
        'Hostname'     => implode(', ', $names),
        'Reverse zone' => $zone,
        'PTR records'  => $records > 0 ? (string) $records : 'None — the address has no reverse record',
    ]) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
