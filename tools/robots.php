<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('robots');

$agent = posted('agent');
$sitemap = posted('sitemap');
$paths = posted('paths');
$allowAll = isset($_POST['allow']);
$rules = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent = $agent === '' ? '*' : $agent;

    $lines = ['User-agent: ' . $agent];

    if ($allowAll) {
        /* An empty Disallow is how robots.txt says "this crawler may go
           anywhere", which is clearer than listing nothing at all. */
        $lines[] = 'Disallow:';
    } else {
        $blocked = array_filter(array_map('trim', preg_split('/\R/', $paths) ?: []), static fn(string $p): bool => $p !== '');

        foreach ($blocked as $path) {
            $lines[] = 'Disallow: ' . (str_starts_with($path, '/') ? $path : '/' . $path);
        }
    }

    if ($sitemap !== '') {
        $lines[] = '';
        $lines[] = 'Sitemap: ' . $sitemap;
    }

    $rules = implode("\n", $lines);
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="agent">User agent</label>
            <input class="form-control" id="agent" name="agent" value="<?= e($agent) ?>" placeholder="*" autofocus>
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold" for="sitemap">Sitemap URL</label>
            <input class="form-control" id="sitemap" name="sitemap" value="<?= e($sitemap) ?>" placeholder="https://example.com/sitemap.xml">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold" for="paths">Paths to keep out, one per line</label>
            <textarea class="form-control font-monospace" id="paths" name="paths" rows="4"
                      placeholder="/admin&#10;/cart"><?= e($paths) ?></textarea>
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="allow" name="allow" <?= $allowAll ? 'checked' : '' ?>>
                <label class="form-check-label" for="allow">Let every crawler in — nothing to block</label>
            </div>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Generate</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($rules !== null): ?>
    <?= output($rules, 'robots.txt', true) ?>

    <p class="text-body-secondary small mt-3 mb-0">
        Save it as <code>robots.txt</code> at the root of the site.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
