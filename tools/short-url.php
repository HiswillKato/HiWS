<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('short-url');

/* Three free shorteners, each one plain and keyless, tried in turn: a service
   being down (or refusing a URL it has already shortened) is not this site's
   problem to report. The first that answers with a link wins. */
function shorten(string $url): ?string
{
    $services = [
        'https://is.gd/create.php?format=simple&url=',
        'https://v.gd/create.php?format=simple&url=',
        'https://tinyurl.com/api-create.php?url=',
    ];

    foreach ($services as $service) {
        $answer = trim((string) fetch($service . rawurlencode($url), WAIT));

        if (preg_match('~^https?://\S+$~', $answer)) {
            return $answer;
        }
    }

    return null;
}

$address = posted('url');
$short = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (filter_var($address, FILTER_VALIDATE_URL) === false) {
        $error = 'Enter a full address, including the https:// at the front.';
    } else {
        $short = shorten($address);

        if ($short === null) {
            $error = 'None of the shortening services answered. Try again in a moment.';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">Long address</label>
    <div class="input-group">
        <input class="form-control" id="url" name="url" type="url" value="<?= e($address) ?>"
               placeholder="https://example.com/a/very/long/path?with=queries" required autofocus>
        <button class="btn btn-primary" type="submit">Shorten</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <div class="alert alert-warning mt-4 mb-0">
        <?= icon('fa-solid fa-circle-exclamation me-1') ?><?= e($error) ?>
    </div>
<?php elseif ($short !== null): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <span class="badge bg-primary-subtle text-primary-emphasis rounded-3 p-2 fs-5">
                <?= icon('fa-solid fa-link') ?>
            </span>

            <span class="flex-grow-1 text-break">
                <a class="fs-5" href="<?= e($short) ?>" rel="noopener"><?= e($short) ?></a>
            </span>

            <button class="btn btn-outline-secondary" type="button" data-copy="<?= e($short) ?>">
                <?= icon('fa-regular fa-copy') ?> Copy
            </button>
        </div>

        <div class="card-footer bg-transparent text-body-secondary small text-break">
            <strong>Original:</strong> <?= e($address) ?>
        </div>
    </div>

    <p class="text-body-secondary small mt-3 mb-0">
        These links belong to the shortener, not to this site, and they keep
        whatever expiry the service sets.
    </p>
<?php endif; ?>

<script src="<?= asset('js/copy.js') ?>" defer></script>

<?php require __DIR__ . '/../app/parts/explain.php';
