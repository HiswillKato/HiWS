<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('sitemap-generator');

$input = posted('urls');
$changefreq = posted('changefreq');
$priority = posted('priority');
$xml = '';
$error = '';
$freqs = ['', 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lines = preg_split('/\R/', $input) ?: [];
    $urls = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = parse_url($line);
        if ($parts === false || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || !isset($parts['host'])) {
            $error = 'Each line must be a full http:// or https:// URL. Problem: ' . $line;
            break;
        }
        $urls[] = $line;
        if (count($urls) > 1000) {
            $error = 'Limit 1,000 URLs per sitemap file.';
            break;
        }
    }
    if ($error === '' && $urls === []) {
        $error = 'Enter at least one URL, one per line.';
    }
    if ($error === '' && $changefreq !== '' && !in_array($changefreq, $freqs, true)) {
        $error = 'Choose a valid change frequency.';
    }
    if ($error === '' && $priority !== '') {
        if (!is_numeric($priority) || (float) $priority < 0 || (float) $priority > 1) {
            $error = 'Priority must be a number between 0.0 and 1.0.';
        } else {
            $priority = number_format((float) $priority, 1, '.', '');
        }
    }

    if ($error === '') {
        $esc = static fn(string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $out = [];
        $out[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $out[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            $out[] = '  <url>';
            $out[] = '    <loc>' . $esc($u) . '</loc>';
            if ($changefreq !== '') {
                $out[] = '    <changefreq>' . $esc($changefreq) . '</changefreq>';
            }
            if ($priority !== '') {
                $out[] = '    <priority>' . $esc($priority) . '</priority>';
            }
            $out[] = '  </url>';
        }
        $out[] = '</urlset>';
        $xml = implode("\n", $out);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="urls">URLs (one per line)</label>
        <textarea class="form-control font-monospace" id="urls" name="urls" rows="10" autofocus
                  placeholder="https://example.com/&#10;https://example.com/about&#10;https://example.com/contact"><?= e($input) ?></textarea>
    </div>
    <div class="row g-3">
        <div class="col-sm-6">
            <label class="form-label fw-semibold" for="changefreq">Change frequency (optional)</label>
            <select class="form-select" id="changefreq" name="changefreq">
                <?php foreach ($freqs as $f): ?>
                    <option value="<?= e($f) ?>"<?= $changefreq === $f ? ' selected' : '' ?>><?= $f === '' ? '— None —' : e($f) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6">
            <label class="form-label fw-semibold" for="priority">Priority (optional, 0.0–1.0)</label>
            <input class="form-control" id="priority" name="priority" value="<?= e($priority) ?>" placeholder="0.8">
        </div>
    </div>
    <p class="form-text mb-0 mt-2">Generates a simple <code>urlset</code> sitemap. For large or complex sites, generate sitemaps from your CMS/build process and split them if needed.</p>
    <button class="btn btn-primary mt-3" type="submit">Generate sitemap</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($xml !== ''): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">sitemap.xml</span>
            <?= clip($xml, 'Copy') ?>
        </div>
        <pre class="mb-0 p-3 small overflow-auto"><?= e($xml) ?></pre>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
