<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('utm-builder');

$base = posted('url');
$source = posted('source');
$medium = posted('medium');
$campaign = posted('campaign');
$term = posted('term');
$content = posted('content');
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($base === '') {
        $error = 'Enter the page URL.';
    } else {
        $parts = parse_url($base);
        if ($parts === false || !isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            $error = 'Enter a full URL starting with http:// or https://';
        } elseif ($source === '' || $medium === '' || $campaign === '') {
            $error = 'Source, medium and campaign are required for standard UTM links.';
        } else {
            $query = [];
            if (isset($parts['query'])) { parse_str($parts['query'], $query); }
            $query['utm_source'] = $source;
            $query['utm_medium'] = $medium;
            $query['utm_campaign'] = $campaign;
            if ($term !== '') { $query['utm_term'] = $term; }
            if ($content !== '') { $query['utm_content'] = $content; }
            $rebuilt = $parts['scheme'] . '://' . ($parts['host'] ?? '');
            if (isset($parts['port'])) { $rebuilt .= ':' . $parts['port']; }
            $rebuilt .= $parts['path'] ?? '';
            $rebuilt .= '?' . http_build_query($query);
            if (isset($parts['fragment']) && $parts['fragment'] !== '') { $rebuilt .= '#' . $parts['fragment']; }
            $output = $rebuilt;
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">Website URL</label>
    <input class="form-control font-monospace" id="url" name="url" value="<?= e($base) ?>" placeholder="https://example.com/landing-page" autofocus>
    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="source">Campaign source *</label>
            <input class="form-control" id="source" name="source" value="<?= e($source) ?>" placeholder="newsletter">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="medium">Campaign medium *</label>
            <input class="form-control" id="medium" name="medium" value="<?= e($medium) ?>" placeholder="email">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="campaign">Campaign name *</label>
            <input class="form-control" id="campaign" name="campaign" value="<?= e($campaign) ?>" placeholder="spring_sale">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="term">Campaign term <span class="text-body-secondary fw-normal">(optional)</span></label>
            <input class="form-control" id="term" name="term" value="<?= e($term) ?>" placeholder="running shoes">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="content">Campaign content <span class="text-body-secondary fw-normal">(optional)</span></label>
            <input class="form-control" id="content" name="content" value="<?= e($content) ?>" placeholder="header_button">
        </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Build UTM URL</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'UTM URL') ?>
    <p class="mt-3 mb-0"><a href="<?= e($output) ?>" rel="noopener noreferrer">Open generated link</a></p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
