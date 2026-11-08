<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('sql-formatter');

function sql_format_basic(string $sql): string
{
    $sql = trim(str_replace(["\r\n", "\r"], "\n", $sql));
    $sql = preg_replace('/\s+/', ' ', $sql) ?? $sql;
    $major = ['SELECT', 'FROM', 'WHERE', 'GROUP BY', 'ORDER BY', 'HAVING', 'LIMIT', 'OFFSET',
        'INSERT INTO', 'VALUES', 'UPDATE', 'SET', 'DELETE FROM',
        'LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN', 'OUTER JOIN', 'CROSS JOIN', 'JOIN',
        'UNION ALL', 'UNION', 'ON', 'AND', 'OR'];
    usort($major, static fn($a, $b) => strlen($b) <=> strlen($a));
    foreach ($major as $kw) {
        $sql = preg_replace('/\b' . preg_quote($kw, '/') . '\b/i', "\n" . $kw . ' ', $sql) ?? $sql;
    }
    $lines = array_filter(array_map('trim', explode("\n", $sql)), static fn($l) => $l !== '');
    $out = [];
    foreach ($lines as $line) {
        $upper = strtoupper($line);
        $indent = '';
        if (preg_match('/^(AND|OR|ON)\b/', $upper)) { $indent = '  '; }
        elseif (preg_match('/^(JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN|OUTER JOIN|CROSS JOIN)\b/', $upper)) { $indent = ''; }
        $line = preg_replace('/\s+/', ' ', $line) ?? $line;
        $line = preg_replace('/\s*,\s*/', ",\n  ", $line) ?? $line;
        $out[] = $indent . $line;
    }
    return trim(implode("\n", $out));
}

$given = posted('sql');
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste an SQL query.';
    } else {
        $output = sql_format_basic($given);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="sql">SQL</label>
    <textarea class="form-control font-monospace" id="sql" name="sql" rows="10" autofocus
              placeholder="SELECT id, name FROM users WHERE active = 1 ORDER BY name;"><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Format SQL</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'Formatted SQL', true) ?>
    <p class="text-body-secondary small mt-2 mb-0">This is a lightweight formatter. It handles common keywords and indentation but does not fully parse SQL dialects.</p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
