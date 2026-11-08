<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('open-graph-checker');

$address = posted('url');
$error = '';
$rows = [];
$page = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $safe = public_url($address);
    if ($safe === null) {
        $error = 'That address cannot be fetched. Enter a full public http:// or https:// address.';
    } else {
        $body = fetch($safe, WAIT);
        if ($body === null) {
            $error = 'Nothing came back from ' . $safe . '. The site may be down, or it may refuse this server.';
        } else {
            $body = substr($body, 0, 500000);
            $prev = libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $body, LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
            $xp = new DOMXPath($dom);

            $title = $xp->query('//title')->item(0);
            $page['Title'] = $title ? trim($title->textContent) : '';

            foreach ($xp->query('//meta[@name or @property or @http-equiv]') ?: [] as $meta) {
                $name = strtolower(trim((string) ($meta->getAttribute('property') ?: $meta->getAttribute('name') ?: $meta->getAttribute('http-equiv'))));
                $content = trim((string) $meta->getAttribute('content'));
                if ($name === '' || $content === '') {
                    continue;
                }
                $interesting = ['description', 'robots', 'canonical', 'viewport', 'author'];
                if (in_array($name, $interesting, true) || str_starts_with($name, 'og:') || str_starts_with($name, 'twitter:') || str_starts_with($name, 'article:')) {
                    $rows[] = ['name' => $name, 'content' => $content];
                }
            }
            foreach ($xp->query('//link[@rel="canonical"]') ?: [] as $link) {
                $href = trim((string) $link->getAttribute('href'));
                if ($href !== '') {
                    $rows[] = ['name' => 'link:canonical', 'content' => $href];
                }
            }
            $page['URL fetched'] = $safe;
            $page['Meta tags found'] = (string) count($rows);
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
        <button class="btn btn-primary" type="submit">Check tags</button>
    </div>
    <p class="form-text mb-0 mt-2">Fetches a public page and reads its title, description, canonical, robots, Open Graph and Twitter tags.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($page !== []): ?>
    <?= values($page) ?>

    <?php if ($rows === []): ?>
        <div class="alert alert-warning mt-4 mb-0">No common SEO/social meta tags were found.</div>
    <?php else: ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-4">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th scope="col">Tag</th><th scope="col">Content</th></tr></thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <th scope="row" class="text-body-secondary fw-normal"><code><?= e($row['name']) ?></code></th>
                                <td class="text-break"><?= e($row['content']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
