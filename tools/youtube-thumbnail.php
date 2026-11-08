<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('youtube-thumbnail');

function yt_id(string $input): ?string
{
    $input = trim($input);
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) { return $input; }
    $parts = parse_url($input);
    if (!is_array($parts)) { return null; }
    $host = strtolower($parts['host'] ?? '');
    if ($host === 'youtu.be') {
        $id = trim($parts['path'] ?? '', '/');
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
    }
    if (str_contains($host, 'youtube.com')) {
        if (isset($parts['query'])) {
            parse_str($parts['query'], $q);
            if (isset($q['v']) && preg_match('/^[A-Za-z0-9_-]{11}$/', $q['v'])) { return $q['v']; }
        }
        $path = $parts['path'] ?? '';
        if (preg_match('~/(?:embed|shorts|live)/([A-Za-z0-9_-]{11})~', $path, $m)) { return $m[1]; }
    }
    return null;
}

$given = posted('url');
$error = '';
$id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter a YouTube video URL or ID.';
    } else {
        $id = yt_id($given);
        if ($id === null) {
            $error = 'Could not find a YouTube video ID in that input.';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">YouTube URL or video ID</label>
    <input class="form-control font-monospace" id="url" name="url" value="<?= e($given) ?>"
           placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ" autofocus>
    <button class="btn btn-primary mt-3" type="submit">Get thumbnails</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($id !== null): ?>
    <?php
    $sizes = [
        'Max resolution' => "https://img.youtube.com/vi/{$id}/maxresdefault.jpg",
        'Standard definition' => "https://img.youtube.com/vi/{$id}/sddefault.jpg",
        'High quality' => "https://img.youtube.com/vi/{$id}/hqdefault.jpg",
        'Medium quality' => "https://img.youtube.com/vi/{$id}/mqdefault.jpg",
        'Default' => "https://img.youtube.com/vi/{$id}/default.jpg",
    ];
    ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent fw-semibold">Video ID: <code><?= e($id) ?></code></div>
        <div class="card-body">
            <img src="<?= e($sizes['Max resolution']) ?>" alt="YouTube thumbnail preview" class="img-fluid rounded border mb-3" loading="lazy">
            <div class="list-group">
                <?php foreach ($sizes as $label => $url): ?>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer">
                        <span><?= e($label) ?></span>
                        <span class="small text-body-secondary font-monospace"><?= e($url) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
