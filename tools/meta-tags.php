<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('meta-tags');

/* A value goes inside a quoted attribute, so a double quote in it would end
   the attribute early. Only the values pass through here — the tag's own
   quotes and brackets stay as they are. A plain ampersand is left alone,
   because the code block escapes it for display and a browser reads a bare
   one without trouble. */
function attr(string $value): string
{
    return str_replace(['"', '<', '>'], ["'", '', ''], trim($value));
}

$title = posted('title');
$description = posted('description');
$address = posted('url');
$image = posted('image');
$site = posted('site');
$handle = posted('twitter');
$tags = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($title) === '') {
        $error = 'A title is the one tag worth having — everything else is optional.';
    } else {
        $lines = [
            '<title>' . attr($title) . '</title>',
            '<meta name="description" content="' . attr($description) . '">',
            '<meta name="viewport" content="width=device-width, initial-scale=1">',
            '<meta property="og:type" content="website">',
            '<meta property="og:title" content="' . attr($title) . '">',
            '<meta property="og:description" content="' . attr($description) . '">',
            '<meta name="twitter:card" content="' . ($image !== '' ? 'summary_large_image' : 'summary') . '">',
            '<meta name="twitter:title" content="' . attr($title) . '">',
            '<meta name="twitter:description" content="' . attr($description) . '">',
        ];

        if ($address !== '') {
            $lines[] = '<link rel="canonical" href="' . attr($address) . '">';
            $lines[] = '<meta property="og:url" content="' . attr($address) . '">';
        }

        if ($image !== '') {
            $lines[] = '<meta property="og:image" content="' . attr($image) . '">';
            $lines[] = '<meta name="twitter:image" content="' . attr($image) . '">';
        }

        if ($site !== '') {
            $lines[] = '<meta property="og:site_name" content="' . attr($site) . '">';
        }

        if ($handle !== '') {
            $lines[] = '<meta name="twitter:site" content="' . attr($handle) . '">';
        }

        $tags = implode("\n", $lines);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold" for="title">Page title <span class="text-primary">*</span></label>
            <input class="form-control" id="title" name="title" value="<?= e($title) ?>" autofocus>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold" for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="2"><?= e($description) ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="url">Canonical URL</label>
            <input class="form-control" id="url" name="url" value="<?= e($address) ?>" placeholder="https://example.com/page">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="image">Social image URL</label>
            <input class="form-control" id="image" name="image" value="<?= e($image) ?>" placeholder="https://example.com/card.png">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="site">Site name</label>
            <input class="form-control" id="site" name="site" value="<?= e($site) ?>" placeholder="HiWS">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="twitter">Twitter handle</label>
            <input class="form-control" id="twitter" name="twitter" value="<?= e($handle) ?>" placeholder="@hiswillkato">
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Generate</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($tags !== null): ?>
    <h2 class="h5 mt-5 mb-3">Search result, roughly</h2>
    <div class="card bg-body rounded-4 border shadow-sm">
        <div class="card-body">
            <p class="text-primary mb-1"><?= e($address !== '' ? $address : 'https://example.com/page') ?></p>
            <p class="h5 mb-1"><?= e($title) ?></p>
            <p class="text-body-secondary mb-0"><?= e($description) ?></p>
        </div>
    </div>

    <?= output($tags, 'Meta tags', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
