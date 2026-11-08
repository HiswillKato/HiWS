<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('html-beautifier');

function html_beautify(string $html): string
{
    $html = trim(str_replace(["\r\n", "\r"], "\n", $html));
    $html = preg_replace('/>\s+</', ">\n<", $html) ?? $html;
    $lines = explode("\n", $html);
    $out = [];
    $indent = 0;
    $void = ['area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') { continue; }
        $isClosing = (bool) preg_match('/^<\//', $line);
        $isComment = str_starts_with($line, '<!--');
        $tag = '';
        if (preg_match('/^<\/?([a-zA-Z0-9:-]+)/', $line, $m)) { $tag = strtolower($m[1]); }
        $selfClosing = str_ends_with($line, '/>') || in_array($tag, $void, true) || $isComment || str_starts_with($line, '<!');
        $opensAndCloses = (bool) preg_match('/^<[^\/!][^>]*>.*<\/[^>]+>$/', $line);
        if ($isClosing) { $indent = max(0, $indent - 1); }
        $out[] = str_repeat('  ', $indent) . $line;
        if (!$isClosing && !$selfClosing && !$opensAndCloses && $tag !== '') { $indent++; }
    }
    return implode("\n", $out) . "\n";
}

$given = posted('html');
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some HTML.';
    } else {
        $output = html_beautify($given);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="html">HTML</label>
    <textarea class="form-control font-monospace" id="html" name="html" rows="10" autofocus
              placeholder="<div><p>Hello</p></div>"><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Beautify HTML</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'Beautified HTML', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
