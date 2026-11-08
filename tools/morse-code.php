<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('morse-code');

$MORSE = [
    'A' => '.-', 'B' => '-...', 'C' => '-.-.', 'D' => '-..', 'E' => '.', 'F' => '..-.',
    'G' => '--.', 'H' => '....', 'I' => '..', 'J' => '.---', 'K' => '-.-', 'L' => '.-..',
    'M' => '--', 'N' => '-.', 'O' => '---', 'P' => '.--.', 'Q' => '--.-', 'R' => '.-.',
    'S' => '...', 'T' => '-', 'U' => '..-', 'V' => '...-', 'W' => '.--', 'X' => '-..-',
    'Y' => '-.--', 'Z' => '--..',
    '0' => '-----', '1' => '.----', '2' => '..---', '3' => '...--', '4' => '....-',
    '5' => '.....', '6' => '-....', '7' => '--...', '8' => '---..', '9' => '----.',
    '.' => '.-.-.-', ',' => '--..--', '?' => '..--..', "'" => '.----.', '!' => '-.-.--',
    '/' => '-..-.', '(' => '-.--.', ')' => '-.--.-', '&' => '.-...', ':' => '---...',
    ';' => '-.-.-.', '=' => '-...-', '+' => '.-.-.', '-' => '-....-', '_' => '..--.-',
    '"' => '.-..-.', '$' => '...-..-', '@' => '.--.-.', ' ' => '/',
];
$FROM_MORSE = array_flip($MORSE);

$given = posted('text');
$direction = posted('direction') ?: 'encode';
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter some text or Morse code.';
    } elseif ($direction === 'encode') {
        $parts = [];
        $upper = mb_strtoupper($given, 'UTF-8');
        $len = mb_strlen($upper, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($upper, $i, 1, 'UTF-8');
            if ($ch === "\n" || $ch === "\r") {
                continue;
            }
            $parts[] = $MORSE[$ch] ?? '?';
        }
        $output = implode(' ', $parts);
        $output = preg_replace('~( / )+~', ' / ', $output) ?? $output;
    } elseif ($direction === 'decode') {
        $words = preg_split('~\s*/\s*~', trim($given)) ?: [];
        $decoded = [];
        foreach ($words as $word) {
            $letters = preg_split('/\s+/', trim($word)) ?: [];
            $chars = [];
            foreach ($letters as $letter) {
                if ($letter === '') {
                    continue;
                }
                $chars[] = $FROM_MORSE[$letter] ?? '?';
            }
            $decoded[] = implode('', $chars);
        }
        $output = implode(' ', $decoded);
    } else {
        $error = 'Choose a direction.';
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="direction">Direction</label>
        <select class="form-select" id="direction" name="direction">
            <option value="encode"<?= $direction === 'encode' ? ' selected' : '' ?>>Text to Morse code</option>
            <option value="decode"<?= $direction === 'decode' ? ' selected' : '' ?>>Morse code to text</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold" for="text">Input</label>
        <textarea class="form-control font-monospace" id="text" name="text" rows="8" autofocus
                  placeholder="SOS"><?= e($given) ?></textarea>
        <div class="form-text">In Morse, separate letters with spaces and words with <code>/</code>.</div>
    </div>
    <button class="btn btn-primary" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, $direction === 'encode' ? 'Morse code' : 'Text', true) ?>
    <?php if (str_contains($output, '?')): ?>
        <div class="alert alert-warning mt-3 mb-0">Characters not in the Morse table were replaced with <code>?</code>.</div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
