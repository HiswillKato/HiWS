<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('case-converter');

const MODES = [
    'upper'    => 'UPPERCASE',
    'lower'    => 'lowercase',
    'title'    => 'Title Case',
    'sentence' => 'Sentence case',
    'camel'    => 'camelCase',
    'pascal'   => 'PascalCase',
    'snake'    => 'snake_case',
    'kebab'    => 'kebab-case',
    'constant' => 'CONSTANT_CASE',
];

$given = posted('text');
$mode = array_key_exists(posted('mode'), MODES) ? posted('mode') : 'title';
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Enter some text to convert.';
    } else {
        /* The word list the joined styles build from — split on punctuation,
           and again where a lower-case or digit meets an upper-case letter, so
           "myVariableName" comes apart into my, Variable, Name. */
        $words = preg_split('/[^\p{L}\p{N}]+|(?<=[a-z0-9])(?=[A-Z])/u', trim($given), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lower = array_map(static fn(string $word): string => mb_strtolower($word), $words);

        $result = match ($mode) {
            'upper'    => mb_strtoupper($given),
            'lower'    => mb_strtolower($given),
            'title'    => mb_convert_case($given, MB_CASE_TITLE, 'UTF-8'),
            'sentence' => (string) preg_replace_callback(
                '/(^|[.!?]\s+)(\p{L})/u',
                static fn(array $found): string => $found[1] . mb_strtoupper($found[2]),
                mb_strtolower($given)
            ),
            'camel'    => lcfirst(implode('', array_map('ucfirst', $lower))),
            'pascal'   => implode('', array_map('ucfirst', $lower)),
            'snake'    => implode('_', $lower),
            'kebab'    => implode('-', $lower),
            'constant' => implode('_', array_map(static fn(string $word): string => mb_strtoupper($word), $lower)),
        };
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-sm-4">
            <label class="form-label fw-semibold" for="mode">Style</label>
            <select class="form-select" id="mode" name="mode">
                <?php foreach (MODES as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $mode === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col">
            <label class="form-label fw-semibold" for="text">Text</label>
            <textarea class="form-control" id="text" name="text" rows="5" autofocus><?= e($given) ?></textarea>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null && $result !== ''): ?>
    <?= output($result, MODES[$mode], true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
