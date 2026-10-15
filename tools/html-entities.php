<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('html-entities');

$given = posted('text');
$way = posted('way') === 'decode' ? 'decode' : 'encode';
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter some text or markup.';
    } elseif ($way === 'encode') {
        /* Both quotes are escaped, so the result is safe inside an attribute as\n           well as between tags. */
        $result = htmlspecialchars($given, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    } else {
        $result = html_entity_decode($given, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-sm-auto">
            <label class="form-label fw-semibold" for="way">Direction</label>
            <select class="form-select w-auto" id="way" name="way">
                <option value="encode" <?= $way === 'encode' ? 'selected' : '' ?>>Encode</option>
                <option value="decode" <?= $way === 'decode' ? 'selected' : '' ?>>Decode</option>
            </select>
        </div>
        <div class="col">
            <label class="form-label fw-semibold" for="text">Input</label>
            <textarea class="form-control font-monospace" id="text" name="text" rows="5"
                      placeholder="&lt;p&gt;Hello &amp; welcome&lt;/p&gt;" autofocus><?= e($given) ?></textarea>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null && $result !== ''): ?>
    <?= output($result, $way === 'encode' ? 'Encoded entities' : 'Decoded text', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
