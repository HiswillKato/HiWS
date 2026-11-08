<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('ulid');

/* Crockford's Base32, used by ULID (no I, L, O, U). */
function ulid_encode(int $value, int $length): string
{
    $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out = $alphabet[$value % 32] . $out;
        $value = intdiv($value, 32);
    }

    return $out;
}

function ulid_generate(): string
{
    $time = (int) floor(microtime(true) * 1000);
    $rand = random_bytes(10);
    $value = 0;
    for ($i = 0; $i < 10; $i++) {
        $value = ($value << 8) | ord($rand[$i]);
    }
    $rand_part = '';
    $alphabet = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    for ($i = 0; $i < 16; $i++) {
        $rand_part = $alphabet[$value % 32] . $rand_part;
        $value = intdiv($value, 32);
    }

    return ulid_encode($time, 10) . $rand_part;
}

$count = 5;
$ids = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count = (int) posted('count');
    if ($count < 1 || $count > 100) {
        $error = 'Choose a whole number from 1 to 100.';
        $count = 5;
    } else {
        for ($i = 0; $i < $count; $i++) {
            $ids[] = ulid_generate();
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="count">How many</label>
    <div class="input-group" style="max-width: 16rem;">
        <input class="form-control" id="count" name="count" type="number" min="1" max="100" value="<?= e((string) $count) ?>">
        <button class="btn btn-primary" type="submit">Generate</button>
    </div>
    <p class="form-text mb-0 mt-2">ULIDs are 26-character, time-ordered identifiers — sortable by creation time, unlike random UUIDs.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($ids !== []): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">ULIDs</span>
            <?= clip(implode("\n", $ids), 'Copy all') ?>
        </div>
        <ul class="list-group list-group-flush">
            <?php foreach ($ids as $id): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                    <code class="text-break"><?= e($id) ?></code>
                    <?= clip($id) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
