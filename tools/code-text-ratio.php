<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('code-text-ratio');

$address = posted('url');
$figures = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $safe = public_url($address);

    if ($safe === null) {
        $error = 'That address cannot be fetched. Enter a full public http:// or https:// address.';
    } else {
        $html = fetch($safe, WAIT);

        if ($html === null) {
            $error = 'Nothing came back from ' . $safe . '. The site may be down, or it may refuse this server.';
        } else {
            /* Script and style bodies are not text a reader ever sees, but
               strip_tags() alone would count them as prose, so they go first. */
            $bare = (string) preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', '', $html);
            $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($bare)));

            $total = max(1, strlen($html));
            $letters = strlen($text);
            $markup = max(0, $total - $letters);

            $figures = [
                'bytes' => $total,
                'text'  => $letters,
                'code'  => $markup,
                'text_share' => round($letters / $total * 100, 1),
                'code_share' => round($markup / $total * 100, 1),
            ];
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">Web address</label>
    <div class="input-group">
        <input class="form-control" id="url" name="url" type="url" value="<?= e($address) ?>"
               placeholder="https://example.com/" required autofocus>
        <button class="btn btn-primary" type="submit">Calculate</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <div class="alert alert-warning mt-4 mb-0">
        <?= icon('fa-solid fa-circle-exclamation me-1') ?><?= e($error) ?>
    </div>
<?php elseif ($figures !== null): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between small text-body-secondary mb-1">
                <span><?= icon('fa-solid fa-align-left me-1') ?>Text <?= e((string) $figures['text_share']) ?>%</span>
                <span>Code <?= e((string) $figures['code_share']) ?>% <?= icon('fa-solid fa-code ms-1') ?></span>
            </div>

            <div class="progress" role="progressbar" aria-label="Text against code"
                 aria-valuenow="<?= e((string) $figures['text_share']) ?>" aria-valuemin="0" aria-valuemax="100"
                 style="height:1.5rem">
                <div class="progress-bar" style="width:<?= e((string) $figures['text_share']) ?>%">
                    <?= e((string) $figures['text_share']) ?>%
                </div>
            </div>

            <table class="table table-sm align-middle mt-4 mb-0">
                <tbody>
                    <tr>
                        <th scope="row" class="text-body-secondary fw-normal w-50">Page size</th>
                        <td><?= e(number_format($figures['bytes'])) ?> bytes</td>
                    </tr>
                    <tr>
                        <th scope="row" class="text-body-secondary fw-normal">Text</th>
                        <td><?= e(number_format($figures['text'])) ?> bytes (<?= e((string) $figures['text_share']) ?>%)</td>
                    </tr>
                    <tr>
                        <th scope="row" class="text-body-secondary fw-normal">Code</th>
                        <td><?= e(number_format($figures['code'])) ?> bytes (<?= e((string) $figures['code_share']) ?>%)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-body-secondary small mt-3 mb-0">
        Scripts and stylesheets are counted as code, not text, which is why a
        modern page often reads higher here than a hand-written one.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
