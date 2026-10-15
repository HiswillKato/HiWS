<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('subnet-calculator');

$given = posted('ip');
$prefix = posted('prefix');
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $given = $given === '' ? '192.168.1.10' : $given;
    $prefix = $prefix === '' ? '24' : $prefix;

    $long = filter_var($given, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false ? ip2long($given) : false;

    if ($long === false) {
        $error = 'Enter an IPv4 address such as 192.168.1.10.';
    } elseif (!ctype_digit($prefix) || (int) $prefix > 32) {
        $error = 'The prefix has to be a whole number from 0 to 32.';
    } else {
        $bits = (int) $prefix;
        $mask = $bits === 0 ? 0 : (-1 << (32 - $bits)) & 0xFFFFFFFF;
        $network = $long & $mask;
        $broadcast = $network | (~$mask & 0xFFFFFFFF);
        $span = 2 ** (32 - $bits);
        $hosts = $span <= 2 ? 0 : $span - 2;

        $result = [
            'Address'      => $given,
            'Prefix'       => '/' . $bits,
            'Netmask'      => long2ip($mask),
            'Wildcard'     => long2ip(~$mask & 0xFFFFFFFF),
            'Network'      => long2ip($network) . '/' . $bits,
            'Broadcast'    => long2ip($broadcast),
            'First host'   => $hosts > 0 ? long2ip($network + 1) : '',
            'Last host'    => $hosts > 0 ? long2ip($broadcast - 1) : '',
            'Addresses'    => number_format($span),
            'Usable hosts' => $hosts > 0 ? number_format($hosts) : 'None — a /31 or /32',
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col-sm-8">
            <label class="form-label fw-semibold" for="ip">IPv4 address</label>
            <input class="form-control" id="ip" name="ip" value="<?= e($given === '' ? '192.168.1.10' : $given) ?>"
                   placeholder="192.168.1.10" autocomplete="off" autofocus>
        </div>
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="prefix">Prefix</label>
            <div class="input-group">
                <span class="input-group-text">/</span>
                <input class="form-control" id="prefix" name="prefix" inputmode="numeric"
                       value="<?= e($prefix === '' ? '24' : $prefix) ?>" placeholder="24">
            </div>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Calculate</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= values($result) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
