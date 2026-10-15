<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('email-checker');

$given = posted('email');
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter an email address.';
    } elseif (filter_var($given, FILTER_VALIDATE_EMAIL) === false) {
        $error = 'That does not look like an email address.';
    } else {
        $domain = (string) substr((string) strrchr($given, '@'), 1);
        $mx = [];
        $hasMx = checkdnsrr($domain, 'MX');
        $hasA = checkdnsrr($domain, 'A');

        if ($hasMx) {
            getmxrr($domain, $mx);
        }

        $result = [
            'Address'      => $given,
            'Domain'       => $domain,
            'Syntax'       => 'Looks like a valid address',
            'MX records'   => implode(', ', $mx),
            'A record'     => $hasA ? 'Yes' : 'No',
            'Can receive'  => $hasMx
                ? 'Yes — the domain publishes mail servers'
                : ($hasA ? 'Unclear — no MX record, so mail would fall back to the domain itself' : 'No — the domain has no mail or address record'),
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="email">Email address</label>
    <div class="input-group">
        <input class="form-control" id="email" name="email" type="email" value="<?= e($given) ?>"
               placeholder="name@example.com" autocomplete="off" autofocus>
        <button class="btn btn-primary" type="submit">Check</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= values($result) ?>

    <p class="text-body-secondary small mt-3 mb-0">
        This checks the address and the domain's mail records. It cannot tell
        whether a particular mailbox exists.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
