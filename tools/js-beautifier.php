<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('js-beautifier');

function js_beautify_basic(string $js): string
{
    $js = str_replace(["\r\n", "\r"], "\n", $js);
    $out = ''; $indent = 0; $i = 0; $len = strlen($js); $in = ''; $newline = true;
    while ($i < $len) {
        $ch = $js[$i]; $next = $i + 1 < $len ? $js[$i+1] : '';
        if ($in !== '') {
            $out .= $ch;
            if ($ch === '\\') { if ($next !== '') { $out .= $next; $i++; } }
            elseif (($in === '"' || $in === "'" || $in === '`') && $ch === $in) $in = '';
            $i++; $newline = false; continue;
        }
        if ($ch === '/' && $next === '/') { $end = strpos($js, "\n", $i); $out .= $end===false?substr($js,$i):substr($js,$i,$end-$i); $i = $end===false?$len:$end; continue; }
        if ($ch === '/' && $next === '*') { $end = strpos($js, '*/', $i+2); $out .= $end===false?substr($js,$i):substr($js,$i,$end-$i+2); $i = $end===false?$len:$end+2; $out .= "\n".str_repeat('  ',$indent); $newline=true; continue; }
        if ($ch === '"' || $ch === "'" || $ch === '`') { $in = $ch; $out .= $ch; $i++; $newline=false; continue; }
        if ($ch === '{' || $ch === '[') { $out = rtrim($out). " $ch\n"; $indent++; $out .= str_repeat('  ',$indent); $i++; $newline=true; continue; }
        if ($ch === '}' || $ch === ']') { $indent=max(0,$indent-1); $out = rtrim($out)."\n".str_repeat('  ',$indent).$ch; if ($next===';'||$next===',') { $out.=$next; $i++; } $out .= "\n".str_repeat('  ',$indent); $i++; $newline=true; continue; }
        if ($ch === ';') { $out .= ";\n".str_repeat('  ',$indent); $i++; $newline=true; continue; }
        if ($ch === ',') { $out .= ",\n".str_repeat('  ',$indent); $i++; $newline=true; continue; }
        if ($ch === "\n" || $ch === "\t") { if (!$newline) { $out .= ' '; $newline=false; } $i++; continue; }
        if ($ch === ' ') { if (!$newline && $next !== ' ' && $next !== "\n") $out .= ' '; $i++; continue; }
        $out .= $ch; $i++; $newline=false;
    }
    $out = preg_replace("/\n[ \t]+\n/", "\n", $out) ?? $out;
    $out = preg_replace("/ \n/", "\n", $out) ?? $out;
    return trim($out) . "\n";
}

$given = posted('js');
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') $error = 'Paste some JavaScript.';
    else $output = js_beautify_basic($given);
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="js">JavaScript</label>
    <textarea class="form-control font-monospace" id="js" name="js" rows="10" autofocus
              placeholder="function hi(){console.log('hello');}"><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Beautify JavaScript</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($output !== ''): ?><?= output($output, 'Beautified JavaScript', true) ?>
<p class="text-body-secondary small mt-2 mb-0">This is a basic formatter, not a full JavaScript parser. Complex code (regex literals, template expressions, minified edge cases) may need manual cleanup.</p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
