<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('css-minifier');

$given = posted('css');
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some CSS.';
    } else {
        /* Comments first, then every run of whitespace, then the spaces that
           sit around the punctuation CSS does not need them for. */
        $result = (string) preg_replace('~/\*.*?\*/~s', '', $given);
        $result = (string) preg_replace('~\s+~', ' ', $result);
        $result = (string) preg_replace('~\s*([{}:;,>])\s*~', '$1', $result);
        $result = trim(str_replace(';}', '}', $result));
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="css">Stylesheet</label>
    <textarea class="form-control font-monospace" id="css" name="css" rows="9"
              placeholder=".card {&#10;    color: red;&#10;}" autofocus><?= e($given) ?></textarea>

    <button class="btn btn-primary mt-4" type="submit">Minify</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= values([
        'Original' => bytes(strlen($given)),
        'Minified' => bytes(strlen($result)),
        'Saved'    => strlen($given) > 0 ? (string) round((1 - strlen($result) / strlen($given)) * 100) . '%' : '',
    ]) ?>
    <?= output($result, 'Minified CSS', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
