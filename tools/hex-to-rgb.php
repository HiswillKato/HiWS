<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('hex-to-rgb');

$given = posted('hex');
$channels = null;
$colour = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hex = ltrim(strtolower(trim($given)), '#');

    if (!preg_match('/^[0-9a-f]{3}$|^[0-9a-f]{6}$/', $hex)) {
        $error = 'Enter a hex colour such as #7C3AED, or the shorthand #7C3.';
    } else {
        /* Three digits are shorthand: each stands for itself twice, so
           "#abc" is "#aabbcc". */
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $colour = '#' . $hex;

        $channels = [
            'Red'   => hexdec(substr($hex, 0, 2)),
            'Green' => hexdec(substr($hex, 2, 2)),
            'Blue'  => hexdec(substr($hex, 4, 2)),
        ];
    }
}

$rgb = $channels === null ? '' : sprintf('rgb(%d, %d, %d)', ...array_values($channels));

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="hex">Hex colour</label>
    <div class="input-group">
        <span class="input-group-text">#</span>
        <input class="form-control" id="hex" name="hex" value="<?= e(ltrim($given, '#')) ?>"
               placeholder="7C3AED" autofocus>
        <button class="btn btn-primary" type="submit">Convert</button>
    </div>
    <p class="form-text mb-0 mt-2">Three digits or six, with or without the #.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($channels !== null): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-body d-flex flex-wrap align-items-center gap-4">
            <span class="border rounded-3 d-inline-block" style="background:<?= e($colour) ?>;width:5rem;height:5rem"
                  role="img" aria-label="A swatch of the colour <?= e($colour) ?>"></span>

            <span>
                <span class="d-block text-body-secondary small text-uppercase">RGB</span>
                <code class="fs-4"><?= e($rgb) ?></code>
            </span>

            <button class="btn btn-outline-secondary ms-auto" type="button" data-copy="<?= e($rgb) ?>">
                <?= icon('fa-regular fa-copy') ?> Copy
            </button>
        </div>

        <div class="table-responsive border-top">
            <table class="table table-sm align-middle mb-0">
                <tbody>
                    <?php foreach ($channels as $name => $value): ?>
                        <tr>
                            <th scope="row" class="text-body-secondary fw-normal w-50"><?= e($name) ?></th>
                            <td><code><?= e((string) $value) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th scope="row" class="text-body-secondary fw-normal">Hex</th>
                        <td><code><?= e($colour) ?></code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
