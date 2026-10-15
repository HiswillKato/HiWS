<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('binary');

$given = posted('text');
$way = posted('way') === 'decode' ? 'decode' : 'encode';
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter some text or binary.';
    } elseif ($way === 'encode') {
        /* Per byte, not per character, so a non-Latin letter becomes the two
           or three groups of eight bits it actually occupies. */
        $result = implode(' ', array_map(
            static fn(string $byte): string => str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT),
            str_split($given)
        ));
    } else {
        $bits = (string) preg_replace('/[^01]/', '', $given);

        if ($bits === '' || strlen($bits) % 8 !== 0) {
            $error = 'Binary has to be whole groups of eight bits. Spaces and line breaks are ignored.';
        } else {
            $result = '';

            foreach (str_split($bits, 8) as $byte) {
                $result .= chr((int) bindec($byte));
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-sm-auto">
            <label class="form-label fw-semibold" for="way">Direction</label>
            <select class="form-select w-auto" id="way" name="way">
                <option value="encode" <?= $way === 'encode' ? 'selected' : '' ?>>Text to binary</option>
                <option value="decode" <?= $way === 'decode' ? 'selected' : '' ?>>Binary to text</option>
            </select>
        </div>
        <div class="col">
            <label class="form-label fw-semibold" for="text">Input</label>
            <textarea class="form-control font-monospace" id="text" name="text" rows="5"
                      placeholder="<?= $way === 'encode' ? 'Hello' : '01001000 01101001' ?>" autofocus><?= e($given) ?></textarea>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null && $result !== ''): ?>
    <?= output($result, $way === 'encode' ? 'Binary' : 'Text', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
