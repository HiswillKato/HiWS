<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('hash');

/* The algorithms worth showing, filtered down to the ones this build of PHP
   actually has. hash_algos() also carries the many it does not need here. */
const WANTED = [
    'md5', 'sha1', 'sha224', 'sha256', 'sha384', 'sha512',
    'sha3-256', 'sha3-512', 'ripemd160', 'crc32b', 'xxh128',
];

$algorithms = array_values(array_intersect(WANTED, hash_algos()));

$text = posted('text');
$digests = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($algorithms as $algorithm) {
        $digests[$algorithm] = hash($algorithm, $text);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="text">Text to hash</label>
    <textarea class="form-control" id="text" name="text" rows="4" autofocus
              placeholder="Type or paste anything…"><?= e($text) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Generate hashes</button>
</form>

<?php if ($digests !== []): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Algorithm</th>
                        <th scope="col">Hash</th>
                        <th scope="col"><span class="visually-hidden">Copy</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($digests as $algorithm => $digest): ?>
                        <tr>
                            <td class="text-uppercase text-body-secondary"><?= e($algorithm) ?></td>
                            <td class="text-break"><code><?= e($digest) ?></code></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-copy="<?= e($digest) ?>">
                                    <?= icon('fa-regular fa-copy') ?>
                                    <span class="visually-hidden">Copy <?= e($algorithm) ?></span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-body-secondary small mt-3 mb-0">
        Every digest here is a one-way fingerprint of the same text — you cannot
        read the text back out of one.
    </p>
<?php endif; ?>

<script src="<?= asset('js/copy.js') ?>" defer></script>

<?php require __DIR__ . '/../app/parts/explain.php';
