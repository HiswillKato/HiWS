<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('rot');

/* ROT47 turns every printable ASCII character into another one 47 places
   along, the set running from "!" to "~" — 94 characters in all. */
function rot47(string $text): string
{
    $out = '';

    foreach (str_split($text) as $char) {
        $code = ord($char);
        $out .= $code >= 33 && $code <= 126 ? chr(33 + ($code - 33 + 47) % 94) : $char;
    }

    return $out;
}

/* ROT18 is ROT13 on the letters and ROT5 on the digits. */
function rot18(string $text): string
{
    return (string) preg_replace_callback(
        '/[0-9]/',
        static fn(array $found): string => (string) (((int) $found[0] + 5) % 10),
        str_rot13($text)
    );
}

$text = posted('text');

$results = $text === '' ? [] : [
    'ROT13' => str_rot13($text),
    'ROT18' => rot18($text),
    'ROT47' => rot47($text),
];

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="text">Text to encode or decode</label>
    <textarea class="form-control" id="text" name="text" rows="6" autofocus
              placeholder="Type or paste anything…"><?= e($text) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Convert</button>
    <p class="form-text mb-0 mt-2">
        All three are their own inverse, so running the same one twice brings the
        text back.
    </p>
</form>

<?php foreach ($results as $name => $result): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
            <span class="fw-semibold"><?= e($name) ?></span>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-copy="<?= e($result) ?>">
                <?= icon('fa-regular fa-copy') ?>
                <span class="visually-hidden">Copy the <?= e($name) ?> result</span>
            </button>
        </div>
        <div class="card-body">
            <p class="mb-0 text-break"><code><?= e($result) ?></code></p>
        </div>
    </div>
<?php endforeach; ?>

<script src="<?= asset('js/copy.js') ?>" defer></script>

<?php require __DIR__ . '/../app/parts/explain.php';
