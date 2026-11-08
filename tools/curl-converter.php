<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('curl-converter');

function curl_parse_command(string $cmd): array
{
    $cmd = trim($cmd);
    $cmd = preg_replace('/\\\\\r?\n/', ' ', $cmd) ?? $cmd;
    $tokens = [];
    $len = strlen($cmd);
    $i = 0;
    while ($i < $len) {
        $c = $cmd[$i];
        if (ctype_space($c)) { $i++; continue; }
        if ($c === "'" || $c === '"') {
            $q = $c; $i++; $buf = '';
            while ($i < $len) {
                $ch = $cmd[$i];
                if ($ch === '\\' && $q === '"' && $i + 1 < $len) { $buf .= $cmd[$i + 1]; $i += 2; continue; }
                if ($ch === $q) { $i++; break; }
                $buf .= $ch; $i++;
            }
            $tokens[] = $buf;
            continue;
        }
        $buf = '';
        while ($i < $len && !ctype_space($cmd[$i])) { $buf .= $cmd[$i]; $i++; }
        $tokens[] = $buf;
    }
    $out = ['method' => 'GET', 'url' => '', 'headers' => [], 'data' => null];
    $n = count($tokens);
    for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];
        if ($i === 0 && strtolower($t) === 'curl') { continue; }
        if (in_array($t, ['-X', '--request'], true) && $i + 1 < $n) { $out['method'] = strtoupper($tokens[++$i]); continue; }
        if (in_array($t, ['-H', '--header'], true) && $i + 1 < $n) {
            $h = $tokens[++$i]; $p = strpos($h, ':');
            if ($p !== false) { $out['headers'][trim(substr($h, 0, $p))] = trim(substr($h, $p + 1)); }
            continue;
        }
        if (in_array($t, ['-d', '--data', '--data-raw', '--data-binary', '--data-urlencode'], true) && $i + 1 < $n) {
            $out['data'] = $tokens[++$i];
            if ($out['method'] === 'GET') { $out['method'] = 'POST'; }
            continue;
        }
        if (in_array($t, ['-u', '--user'], true) && $i + 1 < $n) {
            $out['headers']['Authorization'] = 'Basic ' . base64_encode($tokens[++$i]);
            continue;
        }
        if (in_array($t, ['-A', '--user-agent'], true) && $i + 1 < $n) { $out['headers']['User-Agent'] = $tokens[++$i]; continue; }
        if (in_array($t, ['-I', '--head'], true)) { $out['method'] = 'HEAD'; continue; }
        if (in_array($t, ['-L', '--location', '-k', '--insecure', '-s', '--silent', '-S', '--show-error', '-i', '--include', '-v', '--verbose', '--compressed'], true)) { continue; }
        if (str_starts_with($t, '-')) {
            if (str_starts_with($t, '--url=') || str_starts_with($t, '--url')) { /* handled below */ }
            continue;
        }
        if ($out['url'] === '' && (str_contains($t, '://') || str_starts_with($t, 'http'))) { $out['url'] = $t; }
        elseif ($out['url'] === '' && !str_starts_with($t, '-')) { $out['url'] = $t; }
    }
    return $out;
}

function curl_to_fetch(array $r): string
{
    $opt = ['method' => $r['method']];
    if ($r['headers'] !== []) { $opt['headers'] = $r['headers']; }
    if ($r['data'] !== null) { $opt['body'] = $r['data']; }
    return "fetch(" . json_encode($r['url'], JSON_UNESCAPED_SLASHES) . ", " . json_encode($opt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ")\n  .then(r => r.text())\n  .then(console.log);";
}

function curl_to_python(array $r): string
{
    $lines = ["import requests", "", "url = " . var_export($r['url'], true)];
    if ($r['headers'] !== []) {
        $lines[] = "headers = " . json_encode($r['headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    $args = ["url"];
    if ($r['headers'] !== []) { $args[] = "headers=headers"; }
    if ($r['data'] !== null) { $args[] = "data=" . var_export($r['data'], true); }
    $lines[] = "";
    $lines[] = "response = requests." . strtolower($r['method']) . "(" . implode(', ', $args) . ")";
    $lines[] = "print(response.text)";
    return implode("\n", $lines);
}

function curl_to_php(array $r): string
{
    $lines = ['<?php', '$ch = curl_init(' . var_export($r['url'], true) . ');', 'curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);', 'curl_setopt($ch, CURLOPT_CUSTOMREQUEST, ' . var_export($r['method'], true) . ');'];
    if ($r['headers'] !== []) {
        $h = [];
        foreach ($r['headers'] as $k => $v) { $h[] = $k . ': ' . $v; }
        $lines[] = 'curl_setopt($ch, CURLOPT_HTTPHEADER, ' . var_export($h, true) . ');';
    }
    if ($r['data'] !== null) { $lines[] = 'curl_setopt($ch, CURLOPT_POSTFIELDS, ' . var_export($r['data'], true) . ');'; }
    $lines[] = '$response = curl_exec($ch);';
    $lines[] = 'curl_close($ch);';
    $lines[] = 'echo $response;';
    return implode("\n", $lines);
}

$given = posted('curl');
$error = '';
$parsed = null;
$outputs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Paste a curl command.';
    } else {
        $parsed = curl_parse_command($given);
        if ($parsed['url'] === '') {
            $error = 'Could not find a URL in that curl command.';
        } else {
            $outputs = [
                'JavaScript (fetch)' => curl_to_fetch($parsed),
                'Python (requests)' => curl_to_python($parsed),
                'PHP (cURL)' => curl_to_php($parsed),
            ];
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="curl">curl command</label>
    <textarea class="form-control font-monospace" id="curl" name="curl" rows="8" autofocus
              placeholder="curl -X POST https://api.example.com/items -H 'Content-Type: application/json' -d '{&quot;name&quot;:&quot;HiWS&quot;}'"><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($outputs !== []): ?>
    <?= values([
        'Method' => $parsed['method'],
        'URL' => $parsed['url'],
        'Headers' => count($parsed['headers']) . ' header(s)',
        'Body' => $parsed['data'] !== null ? 'Yes' : 'No',
    ]) ?>
    <?php foreach ($outputs as $label => $code): ?>
        <?= output($code, $label, true) ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
