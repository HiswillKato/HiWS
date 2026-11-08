<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('json-yaml');

function yaml_scalar(mixed $v): string
{
    if ($v === null) { return 'null'; }
    if (is_bool($v)) { return $v ? 'true' : 'false'; }
    if (is_int($v) || is_float($v)) { return (string) $v; }
    $s = (string) $v;
    if ($s === '' || preg_match('/[:#\[\]{},&*!|>\'"%@`\n\r]|^\s|\s$|^(true|false|null|yes|no|on|off|-?\d+(\.\d+)?([eE][+-]?\d+)?)$/i', $s)) {
        return '"' . str_replace(['\\', '"', "\n", "\r", "\t"], ['\\\\', '\\"', '\\n', '\\r', '\\t'], $s) . '"';
    }
    return $s;
}

function json_to_yaml(mixed $data, int $indent = 0): string
{
    $pad = str_repeat('  ', $indent);
    if (is_array($data)) {
        if ($data === []) { return $pad . "[]\n"; }
        $isList = array_is_list($data);
        $out = '';
        foreach ($data as $k => $v) {
            $prefix = $isList ? ($pad . '- ') : ($pad . yaml_scalar((string) $k) . ': ');
            if (is_array($v)) {
                if ($v === []) {
                    $out .= $prefix . "[]\n";
                } else {
                    $out .= $isList ? ($pad . '- ' . ltrim(json_to_yaml_item($v, $indent + 1))) : ($prefix . "\n" . json_to_yaml($v, $indent + 1));
                    if (!$isList) { /* already added */ }
                }
            } else {
                $out .= $prefix . yaml_scalar($v) . "\n";
            }
        }
        return $out;
    }
    return $pad . yaml_scalar($data) . "\n";
}

function json_to_yaml_item(mixed $data, int $indent): string
{
    $pad = str_repeat('  ', $indent);
    if (!is_array($data) || $data === []) { return yaml_scalar($data) . "\n"; }
    $isList = array_is_list($data);
    $out = '';
    $first = true;
    foreach ($data as $k => $v) {
        $p = $first ? '' : $pad;
        $first = false;
        $key = $isList ? '- ' : (yaml_scalar((string) $k) . ': ');
        if (is_array($v) && $v !== []) {
            $out .= $p . $key . "\n" . json_to_yaml($v, $indent + 1);
        } elseif (is_array($v)) {
            $out .= $p . $key . "[]\n";
        } else {
            $out .= $p . $key . yaml_scalar($v) . "\n";
        }
    }
    return $out;
}

function simple_yaml_to_json(string $yaml): mixed
{
    /* A minimal parser for common YAML (mappings, sequences, scalars). Not a full YAML 1.2 parser. */
    $lines = preg_split('/\R/', $yaml) ?: [];
    $stack = [['indent' => -1, 'type' => 'map', 'value' => []]];
    foreach ($lines as $raw) {
        if (preg_match('/^\s*(#|$)/', $raw)) { continue; }
        if (!preg_match('/^(\s*)(.*)$/', $raw, $m)) { continue; }
        $indent = strlen(str_replace("\t", '  ', $m[1]));
        $content = rtrim($m[2]);
        while (count($stack) > 1 && $indent <= $stack[count($stack) - 1]['indent']) { array_pop($stack); }
        $parent = &$stack[count($stack) - 1]['value'];
        $parseScalar = static function (string $s): mixed {
            $s = trim($s);
            if ($s === 'null' || $s === '~' || $s === '') { return null; }
            if ($s === 'true') { return true; }
            if ($s === 'false') { return false; }
            if ($s === '[]') { return []; }
            if ($s === '{}') { return (object) []; }
            if (preg_match('/^-?\d+$/', $s)) { return (int) $s; }
            if (preg_match('/^-?\d+\.\d+$/', $s)) { return (float) $s; }
            if ((str_starts_with($s, '"') && str_ends_with($s, '"')) || (str_starts_with($s, "'") && str_ends_with($s, "'"))) {
                $inner = substr($s, 1, -1);
                if ($s[0] === '"') { $inner = stripcslashes($inner); }
                return $inner;
            }
            return $s;
        };
        if (preg_match('/^-\s*(.*)$/', $content, $mm)) {
            if (!is_array($parent) || !array_is_list($parent)) {
                /* convert if needed */
            }
            $rest = $mm[1];
            if ($rest === '' || $rest === null) {
                $item = [];
                $parent[] = $item;
                $stack[] = ['indent' => $indent, 'type' => 'map', 'value' => &$parent[count($parent) - 1]];
            } elseif (preg_match('/^([^:]+):\s*(.*)$/', $rest, $kv)) {
                $item = [$kv[1] => $kv[2] === '' ? [] : $parseScalar($kv[2])];
                $parent[] = $item;
                if ($kv[2] === '') { $stack[] = ['indent' => $indent, 'type' => 'map', 'value' => &$parent[count($parent) - 1]]; }
            } else {
                $parent[] = $parseScalar($rest);
            }
        } elseif (preg_match('/^([^:]+):\s*(.*)$/', $content, $kv)) {
            $key = trim($kv[1], '"\'');
            $rest = $kv[2];
            if ($rest === '') {
                $parent[$key] = [];
                $stack[] = ['indent' => $indent, 'type' => 'map', 'value' => &$parent[$key]];
            } else {
                $parent[$key] = $parseScalar($rest);
            }
        }
        unset($parent);
    }
    return $stack[0]['value'];
}

$given = posted('data');
$direction = posted('direction') ?: 'json-to-yaml';
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste some JSON or YAML.';
    } elseif ($direction === 'json-to-yaml') {
        $decoded = json_decode($given, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'That is not valid JSON: ' . json_last_error_msg() . '.';
        } else {
            $output = rtrim(json_to_yaml($decoded), "\n");
        }
    } elseif ($direction === 'yaml-to-json') {
        try {
            $data = simple_yaml_to_json($given);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                $error = 'Could not encode the parsed YAML as JSON.';
            } else {
                $output = $json;
            }
        } catch (Throwable $e) {
            $error = 'Could not parse that YAML. This converter handles common mappings, sequences and scalars.';
        }
    } else {
        $error = 'Choose a direction.';
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="direction">Direction</label>
        <select class="form-select" id="direction" name="direction">
            <option value="json-to-yaml"<?= $direction === 'json-to-yaml' ? ' selected' : '' ?>>JSON to YAML</option>
            <option value="yaml-to-json"<?= $direction === 'yaml-to-json' ? ' selected' : '' ?>>YAML to JSON</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold" for="data">Input</label>
        <textarea class="form-control font-monospace" id="data" name="data" rows="10" autofocus
                  placeholder='{"name":"HiWS","tools":["json","yaml"]}'><?= e($given) ?></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, $direction === 'json-to-yaml' ? 'YAML' : 'JSON', true) ?>
    <?php if ($direction === 'yaml-to-json'): ?>
        <p class="text-body-secondary small mt-2 mb-0">YAML parsing here covers common cases (mappings, sequences, scalars). Complex YAML features like anchors, multi-line block scalars and tags may not parse.</p>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
