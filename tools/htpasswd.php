<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('htpasswd');

/* Apache 2.4 reads bcrypt hashes directly, so that is the default. The older
   SHA-1 form is kept because it is what many shared hosts still expect. */
const ALGORITHMS = [
    'bcrypt' => 'bcrypt — Apache 2.4 and up',
    'sha1'   => 'SHA-1 — the older form',
];

$user = posted('user');
$secret = posted('pass');
$algorithm = array_key_exists(posted('algo'), ALGORITHMS) ? posted('algo') : 'bcrypt';
$line = null;
$snippet = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user === '' || $secret === '') {
        $error = 'Enter both a username and a password.';
    } elseif (preg_match('/^[A-Za-z0-9._@-]{1,64}$/', $user) !== 1) {
        $error = 'Keep the username to letters, digits and . _ @ -';
    } else {
        $hash = $algorithm === 'sha1'
            ? '{SHA}' . base64_encode(sha1($secret, true))
            : (string) password_hash($secret, PASSWORD_BCRYPT);

        $line = $user . ':' . $hash;
        $snippet = "AuthType Basic\n"
            . "AuthName \"Restricted\"\n"
            . "AuthUserFile /full/path/to/.htpasswd\n"
            . "Require valid-user";
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-sm-5">
            <label class="form-label fw-semibold" for="user">Username</label>
            <input class="form-control" id="user" name="user" value="<?= e($user) ?>"
                   autocomplete="off" autofocus>
        </div>
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="pass">Password</label>
            <input class="form-control" id="pass" name="pass" type="text" autocomplete="off">
        </div>
        <div class="col-sm-3">
            <label class="form-label fw-semibold" for="algo">Hash</label>
            <select class="form-select" id="algo" name="algo">
                <?php foreach (ALGORITHMS as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $algorithm === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Generate</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($line !== null): ?>
    <h2 class="h5 mt-5 mb-0">Add this line to .htpasswd</h2>
    <?= output($line, '.htpasswd') ?>

    <h2 class="h5 mt-5 mb-0">Point .htaccess at it</h2>
    <?= output($snippet, '.htaccess') ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
