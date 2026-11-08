<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('regex-tester');

$pattern = posted('pattern');
$text = posted('text');
$flags_in = isset($_POST['flags']) && is_array($_POST['flags']) ? $_POST['flags'] : (($_SERVER['REQUEST_METHOD'] === 'POST') ? [] : ['m']);
$allowed_flags = ['i' => 'Case insensitive (i)', 'm' => 'Multiline ^$ (m)', 's' => 'Dot matches newline (s)', 'u' => 'Unicode (u)', 'x' => 'Ignore whitespace (x)'];
$flags_in = array_values(array_intersect(array_keys($allowed_flags), array_map('strval', $flags_in)));

$error = '';
$matches = [];
$count = 0;
$full_pattern = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($pattern === '') {
        $error = 'Enter a regular expression pattern.';
    } elseif ($text === '') {
        $error = 'Enter some text to test against.';
    } elseif (strlen($text) > 200000) {
        $error = 'Text is too long for this tester (limit 200,000 characters).';
    } else {
        $flags = implode('', $flags_in);
        $full_pattern = '/' . str_replace('/', '\\/', $pattern) . '/' . $flags;
        $prev_bt = ini_get('pcre.backtrack_limit');
        $prev_rc = ini_get('pcre.recursion_limit');
        ini_set('pcre.backtrack_limit', '1000000');
        ini_set('pcre.recursion_limit', '100000');
        $ok = @preg_match_all($full_pattern, $text, $found, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        if ($prev_bt !== false) {
            ini_set('pcre.backtrack_limit', $prev_bt);
        }
        if ($prev_rc !== false) {
            ini_set('pcre.recursion_limit', $prev_rc);
        }

        if ($ok === false) {
            $err = preg_last_error_msg();
            $error = 'That pattern could not be used: ' . $err . '. Check the syntax.';
        } else {
            $count = $ok;
            foreach ($found as $index => $set) {
                $groups = [];
                foreach ($set as $key => $value) {
                    $groups[] = [
                        'name'   => is_int($key) ? ($key === 0 ? 'Full match' : 'Group ' . $key) : $key,
                        'value'  => (string) ($value[0] ?? ''),
                        'offset' => (int) ($value[1] ?? -1),
                    ];
                }
                $matches[] = ['index' => $index + 1, 'groups' => $groups];
                if (count($matches) >= 200) {
                    break;
                }
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="pattern">Pattern (PCRE, without delimiters)</label>
        <div class="input-group">
            <span class="input-group-text font-monospace">/</span>
            <input class="form-control font-monospace" id="pattern" name="pattern" value="<?= e($pattern) ?>"
                   placeholder="[A-Z]+-\d+" autofocus>
            <span class="input-group-text font-monospace">/<?= e(implode('', $flags_in)) ?></span>
        </div>
    </div>
    <div class="mb-3">
        <span class="form-label fw-semibold d-block">Flags</span>
        <?php foreach ($allowed_flags as $flag => $label): ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="flags[]" value="<?= e($flag) ?>" id="flag-<?= e($flag) ?>"<?= in_array($flag, $flags_in, true) ? ' checked' : '' ?>>
                <label class="form-check-label" for="flag-<?= e($flag) ?>"><?= e($label) ?></label>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold" for="text">Test text</label>
        <textarea class="form-control font-monospace" id="text" name="text" rows="8" placeholder="Paste text to test…"><?= e($text) ?></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Test pattern</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $pattern !== ''): ?>
    <div class="alert alert-<?= $count > 0 ? 'success' : 'warning' ?> mt-4 mb-0">
        <?= icon($count > 0 ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-info') ?>
        <strong><?= e((string) $count) ?></strong> match<?= $count === 1 ? '' : 'es' ?> for <code><?= e($full_pattern) ?></code>.
        <?php if ($count > count($matches)): ?> Showing the first <?= e((string) count($matches)) ?>.<?php endif; ?>
    </div>

    <?php if ($matches !== []): ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-4">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Group</th>
                            <th scope="col">Value</th>
                            <th scope="col">Offset</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matches as $match): foreach ($match['groups'] as $group): ?>
                            <tr>
                                <td class="text-body-secondary"><?= e((string) $match['index']) ?></td>
                                <td><?= e($group['name']) ?></td>
                                <td class="text-break"><code><?= e($group['value']) ?></code></td>
                                <td class="text-body-secondary"><?= $group['offset'] >= 0 ? e((string) $group['offset']) : '' ?></td>
                            </tr>
                        <?php endforeach; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
