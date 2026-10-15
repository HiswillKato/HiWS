<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('website-screenshot');

/* The picture is drawn by thum.io and the browser fetches it straight from
   there, so this server never touches it — and never becomes a way to fetch
   something it should not. */
const SERVICE = 'https://image.thum.io/get/width/1000/crop/1200/?url=';

$address = posted('url');
$image = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (filter_var($address, FILTER_VALIDATE_URL) === false) {
        $error = 'Enter a full address, including the https:// at the front.';
    } else {
        $image = SERVICE . rawurlencode($address);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">Web address</label>
    <div class="input-group">
        <input class="form-control" id="url" name="url" type="url" value="<?= e($address) ?>"
               placeholder="https://example.com/" required autofocus>
        <button class="btn btn-primary" type="submit">Take the shot</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($image !== null): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4 overflow-hidden">
        <img class="img-fluid w-100" src="<?= e($image) ?>" alt="A screenshot of <?= e($address) ?>"
             loading="lazy">

        <div class="card-footer bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-2 small">
            <span class="text-body-secondary text-break"><?= e($address) ?></span>
            <a class="btn btn-sm btn-outline-secondary" href="<?= e($address) ?>" rel="noopener">
                Open the page <?= icon('fa-solid fa-arrow-up-right-from-square ms-1') ?>
            </a>
        </div>
    </div>

    <p class="text-body-secondary small mt-3 mb-0">
        The picture is drawn live by thum.io and takes a few seconds to appear.
        It shows the top of the page, not the whole of it.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
