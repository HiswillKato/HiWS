<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('basic-auth');

$username = posted('username');
$password = posted('password');
$header_in = posted('header');
$generated = '';
$decoded = null;
$error = '';
$mode = posted('mode');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'decode') {
        $raw = trim($header_in);
        $raw = preg_replace('/^Authorization:\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/^Basic\s+/i', '', $raw) ?? $raw;
        $raw = trim($raw);
        if ($raw === '') {
            $error = 'Paste a Basic authorization value to decode.';
        } else {
            $bytes = base64_decode($raw, true);
            if ($bytes === false) {
                $error = 'That value is not valid Base64.';
            } else {
                $pos = strpos($bytes, ':');
                $decoded = [
                    'username' => $pos === false ? $bytes : substr($bytes, 0, $pos),
                    'password' => $pos === false ? '' : substr($bytes, $pos + 1),
                    'raw'      => $bytes,
                ];
            }
        }
    } else {
        if ($username === '' && $password === '') {
            $error = 'Enter a username or password to generate a header.';
        } elseif (str_contains($username, ':')) {
            $error = 'HTTP Basic usernames cannot contain a colon.';
        } else {
            $token = base64_encode($username . ':' . $password);
            $generated = 'Authorization: Basic ' . $token;
            $mode = 'generate';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<div class="row g-4">
    <div class="col-lg-6">
        <form method="post" class="card bg-body rounded-4 border shadow-sm p-4 h-100">
            <h2 class="h6 fw-semibold mb-3">Generate header</h2>
            <input type="hidden" name="mode" value="generate">
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" id="username" name="username" value="<?= e($username) ?>" autocomplete="off">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control font-monospace" id="password" name="password" value="<?= e($password) ?>" autocomplete="off">
            </div>
            <button class="btn btn-primary" type="submit">Generate</button>
            <?php if ($generated !== ''): ?>
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-body-secondary small">Header</span>
                        <?= clip($generated, 'Copy') ?>
                    </div>
                    <code class="d-block text-break p-2 bg-body-tertiary rounded"><?= e($generated) ?></code>
                </div>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-lg-6">
        <form method="post" class="card bg-body rounded-4 border shadow-sm p-4 h-100">
            <h2 class="h6 fw-semibold mb-3">Decode header</h2>
            <input type="hidden" name="mode" value="decode">
            <label class="form-label" for="header">Authorization value</label>
            <textarea class="form-control font-monospace" id="header" name="header" rows="3" placeholder="Authorization: Basic dXNlcjpwYXNz"><?= e($header_in) ?></textarea>
            <button class="btn btn-outline-primary mt-3" type="submit">Decode</button>
            <?php if ($decoded !== null): ?>
                <table class="table table-sm mt-3 mb-0">
                    <tbody>
                        <tr><th scope="row" class="text-body-secondary fw-normal">Username</th><td><code><?= e($decoded['username']) ?></code></td></tr>
                        <tr><th scope="row" class="text-body-secondary fw-normal">Password</th><td><code><?= e($decoded['password']) ?></code></td></tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($error !== ''): ?><?= notice($error) ?><?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
