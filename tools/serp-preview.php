<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('serp-preview');

$title = posted('title');
$url = posted('url');
$description = posted('description');
$show = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $show = ($title !== '' || $url !== '' || $description !== '');
}

$title_len = mb_strlen($title);
$desc_len = mb_strlen($description);
$display_url = $url !== '' ? $url : 'https://example.com/page';
$display_title = $title !== '' ? $title : 'Page title';
$display_desc = $description !== '' ? $description : 'Add a meta description to show how this result might look in search.';

$title_hint = $title_len === 0 ? 'secondary' : ($title_len <= 60 ? 'success' : ($title_len <= 70 ? 'warning' : 'danger'));
$desc_hint = $desc_len === 0 ? 'secondary' : ($desc_len <= 160 ? 'success' : ($desc_len <= 170 ? 'warning' : 'danger'));

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="title">Title tag</label>
        <input class="form-control" id="title" name="title" value="<?= e($title) ?>" placeholder="A clear, specific page title" autofocus>
        <div class="form-text"><span class="badge text-bg-<?= $title_hint ?>"><?= e((string) $title_len) ?></span> characters. Search engines often show roughly 50–60 characters, but display varies.</div>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold" for="url">Page URL</label>
        <input class="form-control" id="url" name="url" value="<?= e($url) ?>" placeholder="https://example.com/page">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold" for="description">Meta description</label>
        <textarea class="form-control" id="description" name="description" rows="3" placeholder="A short summary of what the page offers."><?= e($description) ?></textarea>
        <div class="form-text"><span class="badge text-bg-<?= $desc_hint ?>"><?= e((string) $desc_len) ?></span> characters. Search engines often show roughly 150–160 characters, but display varies.</div>
    </div>
    <button class="btn btn-primary" type="submit">Preview snippet</button>
</form>

<?php if ($show): ?>
    <h2 class="h5 mt-4 mb-3">Preview (approximate)</h2>
    <div class="card bg-body rounded-4 border shadow-sm">
        <div class="card-body" style="max-width: 42rem;">
            <div class="small text-body-secondary text-truncate mb-1"><?= e($display_url) ?></div>
            <div class="h5 text-primary mb-1" style="font-weight: 400;"><?= e(mb_strlen($display_title) > 65 ? mb_substr($display_title, 0, 62) . '…' : $display_title) ?></div>
            <div class="text-body-secondary small"><?= e(mb_strlen($display_desc) > 165 ? mb_substr($display_desc, 0, 162) . '…' : $display_desc) ?></div>
        </div>
    </div>
    <p class="text-body-secondary small mt-3 mb-0">This is only a rough preview. Actual search snippets depend on the query, device, and what the search engine chooses to display.</p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
