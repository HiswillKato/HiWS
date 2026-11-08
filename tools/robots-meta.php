<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('robots-meta');

$bots = ['robots', 'googlebot'];
$directives = [
    'index' => 'index / noindex', 'follow' => 'follow / nofollow',
];
$flags = ['noarchive' => 'noarchive', 'nosnippet' => 'nosnippet', 'noimageindex' => 'noimageindex', 'notranslate' => 'notranslate', 'noai' => 'noai, noimageai (AI crawlers, where supported)'];

$state = [
    'bot' => posted('bot') !== '' ? posted('bot') : 'robots',
    'index' => posted('index') !== '' ? posted('index') : 'index',
    'follow' => posted('follow') !== '' ? posted('follow') : 'follow',
    'max_snippet' => posted('max_snippet'),
    'max_image_preview' => posted('max_image_preview') !== '' ? posted('max_image_preview') : '',
    'max_video_preview' => posted('max_video_preview'),
];
foreach (array_keys($flags) as $f) {
    $state[$f] = $_SERVER['REQUEST_METHOD'] !== 'POST' ? false : isset($_POST[$f]);
}

$tag = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!in_array($state['bot'], $bots, true)) {
        $state['bot'] = 'robots';
    }
    $parts = [];
    $parts[] = $state['index'] === 'noindex' ? 'noindex' : 'index';
    $parts[] = $state['follow'] === 'nofollow' ? 'nofollow' : 'follow';
    foreach (['noarchive', 'nosnippet', 'noimageindex', 'notranslate'] as $f) {
        if (!empty($state[$f])) {
            $parts[] = $f;
        }
    }
    if (!empty($state['noai'])) {
        $parts[] = 'noai';
        $parts[] = 'noimageai';
    }
    if ($state['max_snippet'] !== '' && preg_match('/^-?\d+$/', $state['max_snippet'])) {
        $parts[] = 'max-snippet:' . $state['max_snippet'];
    }
    if (in_array($state['max_image_preview'], ['none', 'standard', 'large'], true)) {
        $parts[] = 'max-image-preview:' . $state['max_image_preview'];
    }
    if ($state['max_video_preview'] !== '' && preg_match('/^-?\d+$/', $state['max_video_preview'])) {
        $parts[] = 'max-video-preview:' . $state['max_video_preview'];
    }
    $esc = static fn(string $v): string => str_replace(['"', '<', '>'], ["'", '', ''], $v);
    $content = implode(', ', $parts);
    $tag = '<meta name="' . $esc($state['bot']) . '" content="' . $esc($content) . '">';
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="bot">User agent</label>
            <select class="form-select" id="bot" name="bot">
                <?php foreach ($bots as $b): ?><option value="<?= e($b) ?>"<?= $state['bot'] === $b ? ' selected' : '' ?>><?= e($b) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="index">Indexing</label>
            <select class="form-select" id="index" name="index">
                <option value="index"<?= $state['index'] === 'index' ? ' selected' : '' ?>>index</option>
                <option value="noindex"<?= $state['index'] === 'noindex' ? ' selected' : '' ?>>noindex</option>
            </select>
        </div>
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="follow">Links</label>
            <select class="form-select" id="follow" name="follow">
                <option value="follow"<?= $state['follow'] === 'follow' ? ' selected' : '' ?>>follow</option>
                <option value="nofollow"<?= $state['follow'] === 'nofollow' ? ' selected' : '' ?>>nofollow</option>
            </select>
        </div>
    </div>

    <div class="mt-3">
        <span class="form-label fw-semibold d-block">Extra directives</span>
        <?php foreach ($flags as $f => $label): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="<?= e($f) ?>" id="<?= e($f) ?>"<?= !empty($state[$f]) ? ' checked' : '' ?>>
                <label class="form-check-label" for="<?= e($f) ?>"><?= e($label) ?></label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="max_snippet">max-snippet</label>
            <input class="form-control" id="max_snippet" name="max_snippet" value="<?= e($state['max_snippet']) ?>" placeholder="-1, 0, or chars">
        </div>
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="max_image_preview">max-image-preview</label>
            <select class="form-select" id="max_image_preview" name="max_image_preview">
                <option value=""<?= $state['max_image_preview'] === '' ? ' selected' : '' ?>>— none —</option>
                <?php foreach (['none', 'standard', 'large'] as $v): ?><option value="<?= e($v) ?>"<?= $state['max_image_preview'] === $v ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="max_video_preview">max-video-preview</label>
            <input class="form-control" id="max_video_preview" name="max_video_preview" value="<?= e($state['max_video_preview']) ?>" placeholder="-1 or seconds">
        </div>
    </div>

    <p class="form-text mb-0 mt-2">Meta robots tags are suggestions to crawlers; not all crawlers honor every directive. Use <code>robots.txt</code> for crawl access rules.</p>
    <button class="btn btn-primary mt-3" type="submit">Generate tag</button>
</form>

<?php if ($tag !== ''): ?>
    <?= output($tag, 'Robots meta tag', false) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
