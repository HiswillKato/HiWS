<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('heading-checker');

$address = posted('url');
$error = '';
$headings = [];
$counts = ['h1' => 0, 'h2' => 0, 'h3' => 0, 'h4' => 0, 'h5' => 0, 'h6' => 0];
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
            $body = substr($body, 0, 500000);
            $prev = libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $body, LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
            foreach ($dom->getElementsByTagName('*') as $node) {
                $name = strtolower($node->nodeName);
                if (!isset($counts[$name])) {
                    continue;
                }
                $text = trim(preg_replace('/\s+/', ' ', $node->textContent) ?? '');
                if ($text === '') {
                    continue;
                }
                $counts[$name]++;
                $headings[] = ['level' => strtoupper($name), 'text' => mb_substr($text, 0, 240)];
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
        <button class="btn btn-primary" type="submit">Check headings</button>
    </div>
    <p class="form-text mb-0 mt-2">Fetches a public page and lists its H1–H6 headings in document order.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($page !== ''): ?>
    <div class="d-flex gap-2 mt-4 flex-wrap">
        <?php foreach ($counts as $level => $n): ?>
            <span class="badge text-bg-<?= $level === 'h1' && $n === 1 ? 'success' : ($level === 'h1' && $n !== 1 ? 'warning' : 'secondary') ?>"><?= e(strtoupper($level)) ?>: <?= e((string) $n) ?></span>
        <?php endforeach; ?>
    </div>
    <?php if ($counts['h1'] === 0): ?>
        <div class="alert alert-warning mt-3 mb-0">No H1 heading was found. Most pages benefit from one clear H1.</div>
    <?php elseif ($counts['h1'] > 1): ?>
        <div class="alert alert-warning mt-3 mb-0">More than one H1 was found. That can be valid, but it’s worth checking the page structure.</div>
    <?php endif; ?>

    <?php if ($headings === []): ?>
        <div class="alert alert-secondary mt-3 mb-0">No H1–H6 headings were found.</div>
    <?php else: ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-3">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th scope="col">Level</th><th scope="col">Text</th></tr></thead>
                    <tbody>
                        <?php foreach ($headings as $h): ?>
                            <tr>
                                <td><span class="badge text-bg-light border"><?= e($h['level']) ?></span></td>
                                <td class="text-break"><?= e($h['text']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
