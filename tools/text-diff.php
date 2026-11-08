<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('text-diff');

/* A simple line diff based on longest common subsequence. Kept small and
   predictable rather than clever. */
function diff_lines(array $a, array $b): array
{
    $n = count($a);
    $m = count($b);
    $limit = 300;
    if ($n > $limit || $m > $limit) {
        return [['type' => 'info', 'text' => 'Inputs are too large for a detailed diff (limit ' . $limit . ' lines each). Try smaller excerpts.']];
    }

    $dp = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
    for ($i = $n - 1; $i >= 0; $i--) {
        for ($j = $m - 1; $j >= 0; $j--) {
            $dp[$i][$j] = $a[$i] === $b[$j] ? $dp[$i + 1][$j + 1] + 1 : max($dp[$i + 1][$j], $dp[$i][$j + 1]);
        }
    }

    $out = [];
    $i = 0;
    $j = 0;
    while ($i < $n && $j < $m) {
        if ($a[$i] === $b[$j]) {
            $out[] = ['type' => 'same', 'text' => $a[$i]];
            $i++; $j++;
        } elseif ($dp[$i + 1][$j] >= $dp[$i][$j + 1]) {
            $out[] = ['type' => 'del', 'text' => $a[$i]];
            $i++;
        } else {
            $out[] = ['type' => 'add', 'text' => $b[$j]];
            $j++;
        }
    }
    while ($i < $n) { $out[] = ['type' => 'del', 'text' => $a[$i++]]; }
    while ($j < $m) { $out[] = ['type' => 'add', 'text' => $b[$j++]]; }

    return $out;
}

$left = posted('left');
$right = posted('right');
$rows = [];
$error = '';
$stats = ['add' => 0, 'del' => 0, 'same' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($left === '' && $right === '') {
        $error = 'Paste text into both boxes to compare.';
    } elseif (strlen($left) > 300000 || strlen($right) > 300000) {
        $error = 'One of the inputs is too large for this diff (limit 300,000 characters each).';
    } else {
        $a = preg_split('/\R/', $left) ?: [];
        $b = preg_split('/\R/', $right) ?: [];
        $rows = diff_lines($a, $b);
        foreach ($rows as $row) {
            if (isset($stats[$row['type']])) {
                $stats[$row['type']]++;
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="left">Original</label>
            <textarea class="form-control font-monospace" id="left" name="left" rows="10" placeholder="Original text…"><?= e($left) ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="right">Changed</label>
            <textarea class="form-control font-monospace" id="right" name="right" rows="10" placeholder="Changed text…"><?= e($right) ?></textarea>
        </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Compare</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($rows !== []): ?>
    <div class="d-flex gap-2 mt-4 flex-wrap">
        <span class="badge text-bg-success">+<?= e((string) $stats['add']) ?> added</span>
        <span class="badge text-bg-danger">-<?= e((string) $stats['del']) ?> removed</span>
        <span class="badge text-bg-secondary"><?= e((string) $stats['same']) ?> unchanged</span>
    </div>
    <div class="card bg-body rounded-4 border shadow-sm mt-3 overflow-auto">
        <table class="table table-sm mb-0 font-monospace small">
            <tbody>
                <?php foreach ($rows as $row):
                    $class = match ($row['type']) {
                        'add' => 'table-success',
                        'del' => 'table-danger',
                        'info' => 'table-warning',
                        default => '',
                    };
                    $mark = match ($row['type']) { 'add' => '+', 'del' => '-', 'info' => '!', default => ' ' };
                ?>
                    <tr class="<?= $class ?>">
                        <td class="text-body-secondary" style="width:1%; white-space:pre; user-select:none;"><?= e($mark) ?></td>
                        <td style="white-space:pre-wrap; word-break:break-word;"><?= e($row['text']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
