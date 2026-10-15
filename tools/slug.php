<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('slug');

$title = posted('title');
$separator = posted('separator') === '_' ? '_' : '-';
$base = posted('base');
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($title) === '') {
        $error = 'Enter a title to turn into a slug.';
    } else {
        /* Accents are folded to plain letters first — "Crème Brûlée" should
           read creme-brulee, not lose the letters entirely. */
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);

        if ($ascii === false) {
            $ascii = $title;
        }

        $result = slug($ascii);

        if ($separator === '_') {
            $result = str_replace('-', '_', $result);
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col">
            <label class="form-label fw-semibold" for="title">Title</label>
            <input class="form-control" id="title" name="title" value="<?= e($title) ?>"
                   placeholder="10 Ways to Speed Up Your Website" autofocus>
        </div>
        <div class="col-sm-3">
            <label class="form-label fw-semibold" for="separator">Separator</label>
            <select class="form-select" id="separator" name="separator">
                <option value="-" <?= $separator === '-' ? 'selected' : '' ?>>Hyphen (-)</option>
                <option value="_" <?= $separator === '_' ? 'selected' : '' ?>>Underscore (_)</option>
            </select>
        </div>
    </div>

    <div class="mt-3">
        <label class="form-label fw-semibold" for="base">Base URL <span class="text-body-secondary fw-normal">(optional)</span></label>
        <input class="form-control" id="base" name="base" value="<?= e($base) ?>" placeholder="https://example.com/blog">
    </div>

    <button class="btn btn-primary mt-4" type="submit">Make slug</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= output($result, 'Slug') ?>

    <?php if ($base !== ''): ?>
        <?= output(rtrim($base, '/') . '/' . $result, 'Full address') ?>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
