<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('css-beautifier');

function css_beautify(string $css): string
{
    $css = trim(str_replace(["\r\n", "\r"], "\n", $css));
    $css = preg_replace('/\s+/', ' ', $css) ?? $css;
    $out = '';
    $indent = 0;
    $inString = '';
    $len = strlen($css);
    for ($i = 0; $i < $len; $i++) {
        $ch = $css[$i];
        $next = $i + 1 < $len ? $css[$i + 1] : '';
        if ($inString !== '') {
            $out .= $ch;
            if ($ch === '\\' && $next !== '') { $out .= $next; $i++; continue; }
            if ($ch === $inString) { $inString = ''; }
            continue;
        }
        if ($ch === '"' || $ch === "'") { $inString = $ch; $out .= $ch; continue; }
        if ($ch === '{') {
            $out = rtrim($out) . " {\n";
            $indent++;
            $out .= str_repeat('  ', $indent);
            continue;
        }
        if ($ch === '}') {
            $indent = max(0, $indent - 1);
            $out = rtrim($out) . "\n" . str_repeat('  ', $indent) . "}\n" . str_repeat('  ', $indent);
            continue;
        }
        if ($ch === ';') {
            $out .= ";\n" . str_repeat('  ', $indent);
            continue;
        }
        if ($ch === ':' && $next !== ':') {
            $out .= ': ';
            if ($next === ' ') { $i++; }
            continue;
        }
        $out .= $ch;
    }
    $out = preg_replace("/\n[ \t]+\n/", "\n\n", $out) ?? $out;
    $out = preg_replace("/[ \t]+\n/", "\n", $out) ?? $out;
    return trim($out) . "\n";
}

$given = posted('css');
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some CSS.';
    } else {
        $output = css_beautify($given);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="css">CSS</label>
    <textarea class="form-control font-monospace" id="css" name="css" rows="10" autofocus
              placeholder=".card{color:red;margin:0}"><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Beautify CSS</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'Beautified CSS', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
