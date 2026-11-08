<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('chmod-calculator');

/* Build a numeric mode and symbolic string from read/write/execute flags. */
function chmod_parts(array $flags): array
{
    $map = ['owner' => 0, 'group' => 0, 'other' => 0];
    $sym = '';
    foreach (['owner', 'group', 'other'] as $who) {
        $r = !empty($flags[$who]['r']);
        $w = !empty($flags[$who]['w']);
        $x = !empty($flags[$who]['x']);
        $map[$who] = ($r ? 4 : 0) + ($w ? 2 : 0) + ($x ? 1 : 0);
        $sym .= ($r ? 'r' : '-') . ($w ? 'w' : '-') . ($x ? 'x' : '-');
    }

    $octal = (string) $map['owner'] . (string) $map['group'] . (string) $map['other'];

    return ['octal' => $octal, 'symbolic' => $sym, 'values' => $map];
}

/* Parse an octal mode like 755 or 0755 into flags. */
function chmod_from_octal(string $value): ?array
{
    $value = trim($value);
    if (!preg_match('/^0?[0-7]{3}$/', $value)) {
        return null;
    }
    $value = substr($value, -3);
    $flags = [];
    $who = ['owner', 'group', 'other'];
    for ($i = 0; $i < 3; $i++) {
        $n = (int) $value[$i];
        $flags[$who[$i]] = ['r' => (bool) ($n & 4), 'w' => (bool) ($n & 2), 'x' => (bool) ($n & 1)];
    }

    return $flags;
}

$defaults = [
    'owner' => ['r' => true, 'w' => true, 'x' => true],
    'group' => ['r' => true, 'w' => false, 'x' => true],
    'other' => ['r' => true, 'w' => false, 'x' => true],
];
$flags = $defaults;
$octal_input = posted('octal');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = posted('mode');
    if ($mode === 'octal' && $octal_input !== '') {
        $parsed = chmod_from_octal($octal_input);
        if ($parsed === null) {
            $error = 'Enter a three-digit octal mode such as 755 or 0644.';
        } else {
            $flags = $parsed;
        }
    } else {
        foreach (['owner', 'group', 'other'] as $who) {
            $flags[$who] = [
                'r' => isset($_POST[$who . '_r']),
                'w' => isset($_POST[$who . '_w']),
                'x' => isset($_POST[$who . '_x']),
            ];
        }
    }
}

$result = chmod_parts($flags);

require __DIR__ . '/../app/parts/tool.php';
?>

<div class="row g-4">
    <div class="col-lg-7">
        <form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
            <input type="hidden" name="mode" value="flags">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col" class="text-center">Read</th>
                            <th scope="col" class="text-center">Write</th>
                            <th scope="col" class="text-center">Execute</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (['owner' => 'Owner', 'group' => 'Group', 'other' => 'Others'] as $key => $label): ?>
                            <tr>
                                <th scope="row" class="fw-semibold"><?= e($label) ?></th>
                                <?php foreach (['r', 'w', 'x'] as $perm): ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" name="<?= e($key . '_' . $perm) ?>"
                                               aria-label="<?= e($label . ' ' . $perm) ?>"<?= !empty($flags[$key][$perm]) ? ' checked' : '' ?>>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary mt-3" type="submit">Calculate from boxes</button>
        </form>

        <form method="post" class="card bg-body rounded-4 border shadow-sm p-4 mt-4">
            <input type="hidden" name="mode" value="octal">
            <label class="form-label fw-semibold" for="octal">Or enter an octal mode</label>
            <div class="input-group">
                <input class="form-control font-monospace" id="octal" name="octal" value="<?= e($octal_input !== '' ? $octal_input : $result['octal']) ?>" placeholder="755">
                <button class="btn btn-outline-primary" type="submit">Apply</button>
            </div>
            <p class="form-text mb-0 mt-2">Three digits, each 0–7, such as <code>755</code> or <code>0644</code>.</p>
        </form>
        <?php if ($error !== ''): ?><?= notice($error) ?><?php endif; ?>
    </div>

    <div class="col-lg-5">
        <div class="card bg-body rounded-4 border shadow-sm p-4 h-100">
            <h2 class="h6 text-uppercase text-body-secondary mb-3">Result</h2>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div class="text-body-secondary small">Octal</div>
                    <div class="display-6 fw-semibold font-monospace"><?= e($result['octal']) ?></div>
                </div>
                <?= clip($result['octal'], 'Copy') ?>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div class="text-body-secondary small">Symbolic</div>
                    <div class="h4 fw-semibold font-monospace mb-0"><?= e($result['symbolic']) ?></div>
                </div>
                <?= clip($result['symbolic'], 'Copy') ?>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-body-secondary small">Command</div>
                    <code>chmod <?= e($result['octal']) ?> file</code>
                </div>
                <?= clip('chmod ' . $result['octal'] . ' file', 'Copy') ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
