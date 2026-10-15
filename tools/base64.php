<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('base64');

$given = posted('text');
$way = posted('way') === 'decode' ? 'decode' : 'encode';
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter some text.';
    } elseif ($way === 'encode') {
        $result = base64_encode($given);
    } else {
        /* Line breaks are what a wrapped Base64 block arrives with, so they go
           before the strict decode attempt. */
        $decoded = base64_decode((string) preg_replace('~\s+~', '', $given), true);

        if ($decoded === false) {
            $error = 'That is not valid Base64 — check for a stray character.';
        } else {
            $result = $decoded;
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
                <option value="encode" <?= $way === 'encode' ? 'selected' : '' ?>>Encode text</option>
                <option value="decode" <?= $way === 'decode' ? 'selected' : '' ?>>Decode Base64</option>
            </select>
        </div>
        <div class="col">
            <label class="form-label fw-semibold" for="text">Input</label>
            <textarea class="form-control font-monospace" id="text" name="text" rows="6"
                      placeholder="Type or paste here" autofocus><?= e($given) ?></textarea>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null && $result !== ''): ?>
    <?= output($result, $way === 'encode' ? 'Base64' : 'Decoded text', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
