<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('uuid');

/* A version 4 UUID: sixteen random bytes with the version and variant bits
   stamped into place, printed in the familiar 8-4-4-4-12 groups. random_bytes
   draws from the system's crypto source, so the values are unguessable. */
function uuid(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}

$count = posted('count');
$list = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $count = '5';
} else {
    $count = $count === '' ? '1' : $count;

    if (!ctype_digit($count) || (int) $count < 1 || (int) $count > 100) {
        $error = 'Choose a whole number from 1 to 100.';
    } else {
        $uuids = [];

        for ($i = 0, $size = (int) $count; $i < $size; $i++) {
            $uuids[] = uuid();
        }

        $list = implode("\n", $uuids);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="count">How many</label>
            <input class="form-control" id="count" name="count" inputmode="numeric"
                   value="<?= e($count === '' ? '5' : $count) ?>" autofocus>
        </div>
        <div class="col-sm-8">
            <button class="btn btn-primary" type="submit">Generate</button>
        </div>
    </div>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($list !== null): ?>
    <?= output($list, 'UUID version 4', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
