<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('source-code');

$address = posted('url');
$code = null;
$origin = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $safe = public_url($address);

    if ($safe === null) {
        $error = 'That address cannot be fetched. Enter a full public http:// or https:// address.';
    } else {
        $body = fetch($safe, WAIT);

        if ($body === null) {
            $error = 'Nothing came back from ' . $safe . '. The site may be down, or it may refuse this server.';
        } else {
            $code = $body;
            $origin = $safe;
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
        <button class="btn btn-primary" type="submit">View source</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <div class="alert alert-warning mt-4 mb-0">
        <?= icon('fa-solid fa-circle-exclamation me-1') ?><?= e($error) ?>
    </div>
<?php elseif ($code !== null): ?>
    <p class="text-body-secondary mt-4 mb-2">
        <?= e(number_format(strlen($code))) ?> bytes,
        <?= e(number_format(substr_count($code, "\n") + 1)) ?> lines, from <code><?= e($origin) ?></code>.
    </p>

    <div class="card bg-body rounded-4 border shadow-sm code-window">
        <pre class="mb-0 p-3 small"><?= e($code) ?></pre>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
