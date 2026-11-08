<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('xml-to-json');

function xml_node(SimpleXMLElement $node): mixed
{
    $out = [];
    foreach ($node->attributes() as $k => $v) { $out['@' . $k] = (string) $v; }
    foreach ($node->namespaces(true) as $prefix => $ns) {
        foreach ($node->attributes($ns) as $k => $v) { $out['@' . ($prefix ? $prefix . ':' : '') . $k] = (string) $v; }
    }
    $children = [];
    foreach ($node->children() as $k => $child) { $children[$k][] = xml_node($child); }
    foreach ($node->namespaces(true) as $prefix => $ns) {
        foreach ($node->children($ns) as $k => $child) { $children[($prefix ? $prefix . ':' : '') . $k][] = xml_node($child); }
    }
    foreach ($children as $k => $vals) { $out[$k] = count($vals) === 1 ? $vals[0] : $vals; }
    $text = trim((string) $node);
    if ($text !== '') { $out = $out === [] ? $text : ($out + ['#text' => $text]); }
    return $out === [] ? '' : $out;
}

$given = posted('xml');
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some XML.';
    } elseif (strlen($given) > 500000) {
        $error = 'XML is too large for this converter (limit 500,000 characters).';
    } else {
        $prev = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($given, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        if ($xml === false) {
            $errs = libxml_get_errors();
            $msg = $errs ? trim($errs[0]->message) : 'Could not parse XML.';
            $error = 'XML parse error: ' . $msg;
        } else {
            $data = [$xml->getName() => xml_node($xml)];
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $output = $json !== false ? $json : '';
            if ($output === '') { $error = 'Could not encode the XML as JSON.'; }
        }
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="xml">XML</label>
    <textarea class="form-control font-monospace" id="xml" name="xml" rows="10" autofocus
              placeholder="<root><item id=&quot;1&quot;>Hello</item></root>"><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Convert to JSON</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($output !== ''): ?><?= output($output, 'JSON', true) ?>
<p class="text-body-secondary small mt-2 mb-0">Conversion uses a simple mapping: attributes become <code>@name</code>, text becomes <code>#text</code> when mixed with children. Complex XML (ordering, mixed content, processing instructions) may not round-trip perfectly.</p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
