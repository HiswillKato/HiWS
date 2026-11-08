<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('json-to-xml');

function json_to_xml_node(mixed $data, SimpleXMLElement $xml): void
{
    if (!is_array($data)) {
        $xml[0] = is_bool($data) ? ($data ? 'true' : 'false') : ($data === null ? '' : (string) $data);
        return;
    }
    foreach ($data as $key => $value) {
        if (is_string($key) && str_starts_with($key, '@')) {
            $xml->addAttribute(substr($key, 1), is_scalar($value) ? (string) $value : json_encode($value));
            continue;
        }
        if ($key === '#text') { $xml[0] = is_scalar($value) ? (string) $value : ''; continue; }
        $name = is_int($key) ? 'item' : preg_replace('/[^A-Za-z0-9_\-:.]/', '_', (string) $key);
        if ($name === '' || preg_match('/^[0-9.\-]/', $name)) $name = 'item';
        if (is_array($value) && array_is_list($value)) {
            foreach ($value as $item) {
                $child = $xml->addChild($name);
                if ($child !== null) json_to_xml_node($item, $child);
            }
        } else {
            $child = $xml->addChild($name);
            if ($child !== null) json_to_xml_node($value, $child);
        }
    }
}

$given = posted('json');
$root = posted('root') ?: 'root';
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some JSON.';
    } else {
        $decoded = json_decode($given, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'That is not valid JSON: ' . json_last_error_msg() . '.';
        } else {
            $rootName = preg_replace('/[^A-Za-z0-9_\-:.]/', '_', $root) ?: 'root';
            if (preg_match('/^[0-9.\-]/', $rootName)) $rootName = 'root';
            try {
                $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $rootName . '/>');
                /* If decoded is {rootName: ...} unwrap one level for convenience */
                $payload = (is_array($decoded) && count($decoded) === 1 && isset($decoded[$rootName])) ? $decoded[$rootName] : $decoded;
                json_to_xml_node($payload, $xml);
                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $dom->loadXML($xml->asXML() ?: '<root/>');
                $output = $dom->saveXML() ?: '';
            } catch (Throwable $e) {
                $error = 'Could not convert that JSON to XML.';
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="root">Root element name</label>
        <input class="form-control font-monospace" id="root" name="root" value="<?= e($root) ?>" placeholder="root">
    </div>
    <label class="form-label fw-semibold" for="json">JSON</label>
    <textarea class="form-control font-monospace" id="json" name="json" rows="10" autofocus
              placeholder='{"item":{"@id":"1","#text":"Hello"}}'><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Convert to XML</button>
</form>

<?php if ($error !== ''): ?><?= notice($error) ?>
<?php elseif ($output !== ''): ?><?= output($output, 'XML', true) ?>
<p class="text-body-secondary small mt-2 mb-0">Object keys become element names, arrays become repeated elements, <code>@name</code> becomes an attribute, and <code>#text</code> becomes text content. Invalid XML names are sanitized.</p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
