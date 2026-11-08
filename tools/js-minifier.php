<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('js-minifier');

function js_minify_basic(string $js): string
{
    $out = ''; $i = 0; $len = strlen($js); $in = ''; $last = '';
    while ($i < $len) {
        $ch = $js[$i]; $next = $i + 1 < $len ? $js[$i+1] : '';
        if ($in !== '') {
            $out .= $ch; $last = $ch;
            if ($ch === '\\' && $next !== '') { $out .= $next; $last = $next; $i += 2; continue; }
            if (($in === '"' || $in === "'" || $in === '`') && $ch === $in) $in = '';
            $i++; continue;
        }
        if ($ch === '"' || $ch === "'" || $ch === '`') { $in = $ch; $out .= $ch; $last = $ch; $i++; continue; }
        if ($ch === '/' && $next === '/') { $end = strpos($js, "\n", $i); $i = $end===false?$len:$end; continue; }
        if ($ch === '/' && $next === '*') { $end = strpos($js, '*/', $i+2); $i = $end===false?$len:$end+2; continue; }
        /* best-effort regex literal detection */
        if ($ch === '/' && preg_match('/[=(:,\[!&|?+\-*%^~;\n{]/', $last) && $next !== '/' && $next !== '*') {
            $in = '/'; $out .= $ch; $last = $ch; $i++; continue;
        }
        if ($in === '/' && $ch === '/') { $in = ''; $out .= $ch; $last = $ch; $i++; continue; }
        if (ctype_space($ch)) {
            $j = $i; while ($j < $len && ctype_space($js[$j])) $j++;
            $nch = $j < $len ? $js[$j] : '';
            $keep = preg_match('/[A-Za-z0-9_$]/', $last) && preg_match('/[A-Za-z0-9_$]/', $nch);
            if ($keep) { $out .= ' '; $last = ' '; }
            $i = $j; continue;
        }
        $out .= $ch; $last = $ch; $i++;
    }
    return trim($out);
}

$given = posted('js');
$error = '';
$output = '';
$summary = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') $error = 'Paste some JavaScript.';
    elseif (strlen($given) > 500000) $error = 'JavaScript is too large (limit 500,000 characters).';
    else {
        $output = js_minify_basic($given);
        $saved = strlen($given) - strlen($output);
        $pct = strlen($given) > 0 ? round(($saved / strlen($given)) * 100, 1) : 0;
        $summary = bytes(strlen($given)) . ' → ' . bytes(strlen($output)) . ' (' . $pct . '% saved)';
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="js">JavaScript</label>
    <textarea class="form-control font-monospace" id="js" name="js" rows="10" autofocus
              placeholder="function hi() { console.log('hello'); }"><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Minify JavaScript</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <p class="text-body-secondary mt-4 mb-0"><?= e($summary) ?></p>
    <?= output($output, 'Minified JavaScript', true) ?>
    <p class="text-body-secondary small mt-2 mb-0">This is a basic minifier (comments/whitespace). It is not a full parser and may mishandle edge cases like complex regex literals or automatic semicolon insertion. Test the output before using it in production.</p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
