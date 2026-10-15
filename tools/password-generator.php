<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('password-generator');

/* Fisher–Yates over the system's random source. str_shuffle() would do the
   same job from a sequence an observer could predict, which is the one thing
   a password generator must never be. */
function secure_shuffle(array $items): array
{
    for ($i = count($items) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
    }

    return $items;
}

/* The look-alike characters — l, I, 1, O, 0 — are left out, so a password can
   be read off a screen and typed without a mistake. */
const SETS = [
    'lower'   => 'abcdefghijkmnopqrstuvwxyz',
    'upper'   => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
    'digits'  => '23456789',
    'symbols' => '!@#$%^&*()-_=+[]{};:,.?',
];

$length = posted('length');
$chosen = [];

foreach (array_keys(SETS) as $kind) {
    $chosen[$kind] = isset($_POST['use-' . $kind]);
}

$password = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $length = '20';
    $chosen = ['lower' => true, 'upper' => true, 'digits' => true, 'symbols' => false];
} else {
    $length = $length === '' ? '20' : $length;
    $sets = array_values(array_filter(array_map(
        static fn(string $kind): string => $chosen[$kind] ? SETS[$kind] : '',
        array_keys(SETS)
    )));

    if (!ctype_digit($length) || (int) $length < 4 || (int) $length > 128) {
        $error = 'The length has to be a whole number from 4 to 128.';
    } elseif ($sets === []) {
        $error = 'Choose at least one kind of character.';
    } elseif ((int) $length < count($sets)) {
        $error = 'A length of ' . $length . ' cannot hold every kind you chose.';
    } else {
        $pool = implode('', $sets);
        $chars = [];

        /* One character from every chosen set first, so the password is
           guaranteed to contain each kind the visitor asked for. */
        foreach ($sets as $set) {
            $chars[] = $set[random_int(0, strlen($set) - 1)];
        }

        while (count($chars) < (int) $length) {
            $chars[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        $password = implode('', secure_shuffle($chars));
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="length">Length</label>
            <input class="form-control" id="length" name="length" inputmode="numeric"
                   value="<?= e($length === '' ? '20' : $length) ?>">
        </div>
        <div class="col-sm-8">
            <span class="form-label fw-semibold d-block">Include</span>
            <div class="d-flex flex-wrap gap-3">
                <?php foreach (['lower' => 'Lowercase', 'upper' => 'Uppercase', 'digits' => 'Digits', 'symbols' => 'Symbols'] as $kind => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="use-<?= $kind ?>" name="use-<?= $kind ?>"
                            <?= $chosen[$kind] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="use-<?= $kind ?>"><?= e($label) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Generate</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($password !== ''): ?>
    <?= output($password, 'Password') ?>

    <p class="text-body-secondary small mt-3 mb-0">
        Drawn from the system's random source. Store it in a password manager —
        nothing here records it.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
