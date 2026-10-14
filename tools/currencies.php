<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('currencies');

/* The names of the currencies the service quotes, kept beside the app so the
   two selects read "KES — Kenyan Shilling" rather than two bare codes. */
$currencies = json_decode((string) file_get_contents(__DIR__ . '/../app/currencies.json'), true) ?: [];

$amount = posted('amount');
$from = posted('from');
$to = posted('to');

$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $amount === '' ? '1' : $amount;

    if (!isset($currencies[$from], $currencies[$to])) {
        $error = 'Choose both currencies.';
    } elseif (!is_numeric($amount) || (float) $amount <= 0) {
        $error = 'Enter an amount greater than zero.';
    } else {
        /* The feed quotes one base at a time, which keeps the sum honest: the
           rate shown is the rate used. */
        $feed = json('https://open.er-api.com/v6/latest/' . rawurlencode($from), WAIT);
        $rate = $feed['rates'][$to] ?? null;

        if (($feed['result'] ?? '') !== 'success' || $rate === null) {
            $error = 'The rates feed did not answer. Try again in a moment.';
        } else {
            $result = [
                'amount' => (float) $amount,
                'rate'   => (float) $rate,
                'total'  => (float) $amount * (float) $rate,
                'date'   => (string) ($feed['time_last_update_utc'] ?? ''),
            ];
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="amount">Amount</label>
            <input class="form-control" id="amount" name="amount" inputmode="decimal"
                   value="<?= e($amount === '' ? '1' : $amount) ?>" required autofocus>
        </div>

        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="from">From</label>
            <select class="form-select" id="from" name="from">
                <?php foreach ($currencies as $code => $name): ?>
                    <option value="<?= e($code) ?>" <?= $code === $from ? 'selected' : '' ?>>
                        <?= e($code . ' — ' . $name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="to">To</label>
            <select class="form-select" id="to" name="to">
                <?php foreach ($currencies as $code => $name): ?>
                    <option value="<?= e($code) ?>" <?= $code === $to ? 'selected' : '' ?>>
                        <?= e($code . ' — ' . $name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <div class="alert alert-warning mt-4 mb-0">
        <?= icon('fa-solid fa-circle-exclamation me-1') ?><?= e($error) ?>
    </div>
<?php elseif ($result !== null): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-body text-center">
            <p class="text-body-secondary small text-uppercase mb-2">
                <?= e(number_format($result['amount'], 2)) ?> <?= e($from) ?> is
            </p>
            <p class="display-6 fw-semibold mb-3">
                <?= e(number_format($result['total'], 2)) ?> <?= e($to) ?>
            </p>
            <p class="text-body-secondary small mb-0">
                1 <?= e($from) ?> = <?= e(number_format($result['rate'], 4)) ?> <?= e($to) ?>
                <?php if ($result['date'] !== ''): ?>
                    &middot; rates of <?= e($result['date']) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <p class="text-body-secondary small mt-3 mb-0 text-center">
        Rates come from <a href="https://www.exchangerate-api.com/" rel="noopener">ExchangeRate-API</a>
        and are a daily reference, not a dealing price.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
