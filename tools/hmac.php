<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('hmac');

$wanted = ['sha256', 'sha1', 'sha224', 'sha384', 'sha512', 'sha3-256', 'sha3-512', 'md5', 'ripemd160'];
$algorithms = array_values(array_intersect($wanted, hash_hmac_algos()));
if ($algorithms === []) {
    $algorithms = array_slice(hash_hmac_algos(), 0, 8);
}

$text = posted('text');
$secret = posted('secret');
$algorithm = posted('algorithm');
if (!in_array($algorithm, $algorithms, true)) {
    $algorithm = in_array('sha256', $algorithms, true) ? 'sha256' : ($algorithms[0] ?? 'sha256');
}

$digest = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($text === '') {
        $error = 'Enter the text to sign.';
    } elseif ($secret === '') {
        $error = 'Enter a secret key. HMAC needs a shared secret.';
    } elseif (!in_array($algorithm, hash_hmac_algos(), true)) {
        $error = 'That algorithm is not available on this server.';
    } else {
        $digest = hash_hmac($algorithm, $text, $secret);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="text">Text</label>
        <textarea class="form-control" id="text" name="text" rows="4" autofocus
                  placeholder="The message to authenticate…"><?= e($text) ?></textarea>
    </div>
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label fw-semibold" for="secret">Secret key</label>
            <input class="form-control font-monospace" id="secret" name="secret" type="text"
                   value="<?= e($secret) ?>" placeholder="Shared secret">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="algorithm">Algorithm</label>
            <select class="form-select" id="algorithm" name="algorithm">
                <?php foreach ($algorithms as $name): ?>
                    <option value="<?= e($name) ?>"<?= $name === $algorithm ? ' selected' : '' ?>><?= e(strtoupper($name)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Generate HMAC</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($digest !== ''): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">HMAC-<?= e(strtoupper($algorithm)) ?></span>
            <?= clip($digest, 'Copy') ?>
        </div>
        <div class="p-3">
            <code class="text-break"><?= e($digest) ?></code>
        </div>
    </div>
    <p class="text-body-secondary small mt-3 mb-0">Anyone with the same text and secret gets the same digest. Change either and the digest changes completely.</p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
