<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('hreflang-generator');

$rows_in = posted('rows');
$default = posted('x_default');
$output = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lines = preg_split('/\R/', $rows_in) ?: [];
    $tags = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = preg_split('/\s*\|\s*|\t+/', $line, 2);
        if (!$parts || count($parts) !== 2) {
            $error = 'Each line should be: language-or-region | URL (for example, en-US | https://example.com/en-us/).';
            break;
        }
        $lang = trim($parts[0]);
        $url = trim($parts[1]);
        if ($lang === '' || $url === '') {
            $error = 'Each line needs both a hreflang value and a URL.';
            break;
        }
        $parsed = parse_url($url);
        if ($parsed === false || !in_array($parsed['scheme'] ?? '', ['http', 'https'], true) || !isset($parsed['host'])) {
            $error = 'Use full http:// or https:// URLs. Problem: ' . $url;
            break;
        }
        $esc = static fn(string $v): string => str_replace(['"', '<', '>'], ["'", '', ''], $v);
        $tags[] = '<link rel="alternate" hreflang="' . $esc($lang) . '" href="' . $esc($url) . '">';
    }
    if ($error === '' && $default !== '') {
        $parsed = parse_url($default);
        if ($parsed !== false && in_array($parsed['scheme'] ?? '', ['http', 'https'], true) && isset($parsed['host'])) {
            $esc = static fn(string $v): string => str_replace(['"', '<', '>'], ["'", '', ''], $v);
            $tags[] = '<link rel="alternate" hreflang="x-default" href="' . $esc($default) . '">';
        } else {
            $error = 'The x-default URL must be a full http:// or https:// URL.';
        }
    }
    if ($error === '' && $tags === []) {
        $error = 'Add at least one hreflang mapping.';
    }
    if ($error === '') {
        $output = implode("\n", $tags);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="rows">Language / URL mappings (one per line)</label>
        <textarea class="form-control font-monospace" id="rows" name="rows" rows="8" autofocus
                  placeholder="en | https://example.com/en/&#10;en-GB | https://example.com/en-gb/&#10;fr | https://example.com/fr/"><?= e($rows_in) ?></textarea>
        <div class="form-text">Format: <code>hreflang | URL</code>. Use BCP 47 language/region codes where appropriate.</div>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold" for="x_default">x-default URL (optional)</label>
        <input class="form-control" id="x_default" name="x_default" value="<?= e($default) ?>" placeholder="https://example.com/">
    </div>
    <p class="form-text mb-0 mt-2">Hreflang tags should be reciprocal (each alternate page should reference the others) and match the canonical strategy for those pages.</p>
    <button class="btn btn-primary mt-3" type="submit">Generate hreflang tags</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'Hreflang tags', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
