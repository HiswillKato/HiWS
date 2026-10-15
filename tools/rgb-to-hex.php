<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('rgb-to-hex');

$given = posted('rgb');
$hex = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* Accept the three shapes people paste: "255,255,255", "(255,255,255)"
       and "rgb(255,255,255)". Splitting on everything that is not a digit
       handles all three at once, and drops the "rgb(" in front of them. */
    $parts = preg_split('/[^0-9]+/', $given, -1, PREG_SPLIT_NO_EMPTY) ?: [];

    if (count($parts) !== 3) {
        $error = 'Enter three values, such as 255,255,255 or rgb(255,255,255).';
    } else {
        $channels = array_map('intval', $parts);

        if (array_filter($channels, static fn(int $value): bool => $value > 255) !== []) {
            $error = 'Each value has to be between 0 and 255.';
        } else {
            $hex = sprintf('#%02X%02X%02X', ...$channels);
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="rgb">RGB colour</label>
    <div class="input-group">
        <input class="form-control" id="rgb" name="rgb" value="<?= e($given) ?>"
               placeholder="255,255,255" autofocus>
        <button class="btn btn-primary" type="submit">Convert</button>
    </div>
    <p class="form-text mb-0 mt-2">
        255,255,255 &middot; (255,255,255) &middot; rgb(255,255,255) — any of the three.
    </p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($hex !== ''): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-body d-flex flex-wrap align-items-center gap-4">
            <span class="border rounded-3 d-inline-block" style="background:<?= e($hex) ?>;width:5rem;height:5rem"
                  role="img" aria-label="A swatch of the colour <?= e($hex) ?>"></span>

            <span>
                <span class="d-block text-body-secondary small text-uppercase">Hex</span>
                <code class="fs-4"><?= e($hex) ?></code>
            </span>

            <button class="btn btn-outline-secondary ms-auto" type="button" data-copy="<?= e(strtolower($hex)) ?>">
                <?= icon('fa-regular fa-copy') ?> Copy
            </button>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
