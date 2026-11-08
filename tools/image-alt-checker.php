<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('image-alt-checker');

$address = posted('url');
$error = '';
$images = [];
$counts = ['total' => 0, 'missing' => 0, 'empty' => 0, 'present' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $safe = public_url($address);
    if ($safe === null) {
        $error = 'That address cannot be fetched. Enter a full public http:// or https:// address.';
    } else {
        $body = fetch($safe, WAIT);
        if ($body === null) {
            $error = 'Nothing came back from ' . $safe . '. The site may be down, or it may refuse this server.';
        } else {
            $body = substr($body, 0, 800000);
            $prev = libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $body, LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
            foreach ($dom->getElementsByTagName('img') as $img) {
                $counts['total']++;
                $src = trim((string) $img->getAttribute('src'));
                $has_alt = $img->hasAttribute('alt');
                $alt = trim((string) $img->getAttribute('alt'));
                $status = 'present';
                if (!$has_alt) { $status = 'missing'; $counts['missing']++; }
                elseif ($alt === '') { $status = 'empty'; $counts['empty']++; }
                else { $counts['present']++; }
                $images[] = ['src' => mb_substr($src, 0, 220), 'alt' => mb_substr($alt, 0, 220), 'status' => $status, 'has_alt' => $has_alt];
                if (count($images) >= 300) break;
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
        <button class="btn btn-primary" type="submit">Check images</button>
    </div>
    <p class="form-text mb-0 mt-2">Fetches a public page and lists <code>&lt;img&gt;</code> elements with missing or empty <code>alt</code> attributes.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === ''): ?>
    <div class="d-flex gap-2 mt-4 flex-wrap">
        <span class="badge text-bg-primary"><?= e((string) $counts['total']) ?> images</span>
        <span class="badge text-bg-success"><?= e((string) $counts['present']) ?> with alt text</span>
        <span class="badge text-bg-warning"><?= e((string) $counts['empty']) ?> empty alt</span>
        <span class="badge text-bg-danger"><?= e((string) $counts['missing']) ?> missing alt</span>
    </div>
    <?php if ($counts['missing'] > 0): ?>
        <div class="alert alert-warning mt-3 mb-0">Images missing <code>alt</code> may be inaccessible to screen readers and miss SEO/context opportunities. Decorative images should usually use <code>alt=""</code>.</div>
    <?php endif; ?>

    <?php if ($images !== []): ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-3">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th scope="col">Status</th><th scope="col">Alt</th><th scope="col">Source</th></tr></thead>
                    <tbody>
                        <?php foreach ($images as $img): ?>
                            <tr>
                                <td>
                                    <?php if ($img['status'] === 'present'): ?><span class="badge text-bg-success">Present</span>
                                    <?php elseif ($img['status'] === 'empty'): ?><span class="badge text-bg-warning">Empty</span>
                                    <?php else: ?><span class="badge text-bg-danger">Missing</span><?php endif; ?>
                                </td>
                                <td class="text-break"><?= $img['has_alt'] ? e($img['alt'] === '' ? '(empty)' : $img['alt']) : '<span class="text-body-secondary">(no alt attribute)</span>' ?></td>
                                <td class="text-break"><code><?= e($img['src'] !== '' ? $img['src'] : '(no src)') ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
