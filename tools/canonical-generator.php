<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('canonical-generator');

$url = posted('url');
$tag = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($url);
    if ($url === '') {
        $error = 'Enter the canonical URL for the page.';
    } else {
        $parts = parse_url($url);
        if ($parts === false || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || !isset($parts['host'])) {
            $error = 'Enter a full absolute http:// or https:// URL.';
        } else {
            $safe = str_replace(['"', '<', '>'], ["'", '', ''], $url);
            $tag = '<link rel="canonical" href="' . $safe . '">';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">Canonical URL</label>
    <div class="input-group">
        <input class="form-control" id="url" name="url" type="url" value="<?= e($url) ?>"
               placeholder="https://example.com/page" required autofocus>
        <button class="btn btn-primary" type="submit">Generate tag</button>
    </div>
    <p class="form-text mb-0 mt-2">Use the preferred, absolute URL for the page (usually the clean HTTPS version you want indexed).</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($tag !== ''): ?>
    <?= output($tag, 'Canonical tag') ?>
    <div class="alert alert-info mt-4 mb-0">
        <?= icon('fa-solid fa-circle-info') ?>
        Make sure the canonical URL is indexable, returns 200 (or the intended final response), and matches your site’s preferred host/protocol/path format.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
