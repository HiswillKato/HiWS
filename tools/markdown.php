<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('markdown');

$given = posted('markdown');
$html = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Write some Markdown.';
    } else {
        /* Safe mode escapes any raw HTML in the input, so a page previewing
           stranger's text cannot run script. */
        $html = (new Parsedown())->setSafeMode(true)->text($given);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="markdown">Markdown</label>
    <textarea class="form-control font-monospace" id="markdown" name="markdown" rows="10"
              placeholder="# Heading&#10;&#10;Some *emphasis* and a [link](https://example.com)." autofocus><?= e($given) ?></textarea>

    <button class="btn btn-primary mt-4" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($html !== null): ?>
    <?= output($html, 'HTML', true) ?>

    <h2 class="h5 mt-5 mb-3">Preview</h2>
    <div class="card bg-body rounded-4 border shadow-sm">
        <div class="card-body"><?= $html ?></div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
