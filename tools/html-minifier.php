<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('html-minifier');

$given = posted('html');
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some HTML.';
    } else {
        /* Comments go, except the conditional kind older markup hides behind,
           then the space between tags, then any run of two or more spaces. */
        $result = (string) preg_replace('~<!--(?!\[if).*?-->~s', '', $given);
        $result = (string) preg_replace('~>\s+<~', '><', $result);
        $result = (string) preg_replace('~\s{2,}~', ' ', $result);
        $result = trim($result);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="html">HTML</label>
    <textarea class="form-control font-monospace" id="html" name="html" rows="9"
              placeholder="<div class=&quot;card&quot;>&#10;    <p>Hello</p>&#10;</div>" autofocus><?= e($given) ?></textarea>

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
    <?= output($result, 'Minified HTML', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
