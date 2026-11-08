<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('structured-data');

$address = posted('url');
$error = '';
$blocks = [];
$page = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $safe = public_url($address);
    if ($safe === null) {
        $error = 'That address cannot be fetched. Enter a full public http:// or https:// address.';
    } else {
        $body = fetch($safe, WAIT);
        if ($body === null) {
            $error = 'Nothing came back from ' . $safe . '. The site may be down, or it may refuse this server.';
        } else {
            $page = $safe;
            $body = substr($body, 0, 800000);
            $prev = libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $body, LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
            $xp = new DOMXPath($dom);
            foreach ($xp->query('//script[@type="application/ld+json"]') ?: [] as $node) {
                $raw = trim($node->textContent);
                if ($raw === '') {
                    continue;
                }
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $raw;
                    $type = '';
                    if (is_array($decoded)) {
                        $t = $decoded['@type'] ?? ($decoded['@graph'][0]['@type'] ?? null);
                        if (is_array($t)) {
                            $t = implode(', ', array_map('strval', $t));
                        }
                        $type = is_string($t) ? $t : '';
                    }
                    $blocks[] = ['type' => $type, 'json' => $pretty, 'valid' => true, 'raw' => $raw];
                } else {
                    $blocks[] = ['type' => '', 'json' => $raw, 'valid' => false, 'error' => json_last_error_msg(), 'raw' => $raw];
                }
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">Page URL</label>
    <div class="input-group">
        <input class="form-control" id="url" name="url" type="url" value="<?= e($address) ?>"
               placeholder="https://example.com/" required autofocus>
        <button class="btn btn-primary" type="submit">Extract structured data</button>
    </div>
    <p class="form-text mb-0 mt-2">Fetches a public page and extracts <code>&lt;script type="application/ld+json"&gt;</code> blocks (JSON-LD).</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($page !== ''): ?>
    <?php if ($blocks === []): ?>
        <div class="alert alert-warning mt-4 mb-0">No JSON-LD (<code>application/ld+json</code>) blocks were found.</div>
    <?php else: ?>
        <p class="text-body-secondary mt-4 mb-0">Found <?= e((string) count($blocks)) ?> JSON-LD block<?= count($blocks) === 1 ? '' : 's' ?>.</p>
        <?php foreach ($blocks as $i => $block): ?>
            <div class="card bg-body rounded-4 border shadow-sm mt-3">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center gap-2">
                    <span class="fw-semibold">Block <?= e((string) ($i + 1)) ?><?= $block['type'] !== '' ? ' — ' . e($block['type']) : '' ?></span>
                    <span class="d-flex align-items-center gap-2">
                        <?php if ($block['valid']): ?><span class="badge text-bg-success">Valid JSON</span><?php else: ?><span class="badge text-bg-danger">Invalid JSON</span><?php endif; ?>
                        <?= clip($block['json'], 'Copy') ?>
                    </span>
                </div>
                <?php if (!$block['valid']): ?>
                    <div class="px-3 pt-3"><div class="alert alert-danger mb-0 py-2"><?= e($block['error'] ?? 'Invalid JSON') ?></div></div>
                <?php endif; ?>
                <pre class="mb-0 p-3 small overflow-auto"><?= e($block['json']) ?></pre>
            </div>
        <?php endforeach; ?>
        <p class="text-body-secondary small mt-3 mb-0">This checks that JSON-LD blocks parse as JSON and shows their content. Full Schema.org/rich-result eligibility depends on required properties and Google/consumer guidelines.</p>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
