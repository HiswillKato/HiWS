<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('ascii-converter');

$given = posted('text');
$direction = posted('direction') ?: 'encode';
$separator = posted('separator') ?: 'space';
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter some text or ASCII codes.';
    } elseif ($direction === 'encode') {
        $codes = [];
        $len = mb_strlen($given, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($given, $i, 1, 'UTF-8');
            $codes[] = (string) mb_ord($ch, 'UTF-8');
        }
        $join = match ($separator) {
            'comma' => ', ',
            'newline' => "\n",
            default => ' ',
        };
        $output = implode($join, $codes);
    } elseif ($direction === 'decode') {
        $parts = preg_split('/[\s,;]+/', trim($given)) ?: [];
        $chars = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (!preg_match('/^-?\d+$/', $part)) {
                $error = 'ASCII codes must be whole numbers separated by spaces, commas or new lines.';
                break;
            }
            $code = (int) $part;
            if ($code < 0 || $code > 0x10FFFF) {
                $error = 'Code ' . $code . ' is outside the valid Unicode range.';
                break;
            }
            $chars[] = mb_chr($code, 'UTF-8');
        }
        if ($error === '') {
            $output = implode('', $chars);
        }
    } else {
        $error = 'Choose a direction.';
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="direction">Direction</label>
            <select class="form-select" id="direction" name="direction">
                <option value="encode"<?= $direction === 'encode' ? ' selected' : '' ?>>Text to codes</option>
                <option value="decode"<?= $direction === 'decode' ? ' selected' : '' ?>>Codes to text</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="separator">Separator (text to codes)</label>
            <select class="form-select" id="separator" name="separator">
                <option value="space"<?= $separator === 'space' ? ' selected' : '' ?>>Space</option>
                <option value="comma"<?= $separator === 'comma' ? ' selected' : '' ?>>Comma and space</option>
                <option value="newline"<?= $separator === 'newline' ? ' selected' : '' ?>>New line</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <label class="form-label fw-semibold" for="text">Input</label>
        <textarea class="form-control font-monospace" id="text" name="text" rows="8" autofocus
                  placeholder="Hello"><?= e($given) ?></textarea>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, $direction === 'encode' ? 'ASCII / Unicode codes' : 'Text', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
