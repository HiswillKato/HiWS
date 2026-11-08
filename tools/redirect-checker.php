<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('redirect-checker');

$address = posted('url');
$error = '';
$hops = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $address;
    $seen = [];
    for ($i = 0; $i < 10; $i++) {
        $safe = public_url($current);
        if ($safe === null) {
            $error = $i === 0
                ? 'That address cannot be fetched. Enter a full public http:// or https:// address.'
                : 'Redirect target is not a public URL: ' . $current;
            break;
        }
        if (isset($seen[$safe])) {
            $hops[] = ['url' => $safe, 'status' => 'loop', 'location' => 'Redirect loop detected'];
            break;
        }
        $seen[$safe] = true;

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => WAIT,
                'follow_location' => 0,
                'ignore_errors' => true,
                'header' => 'Accept: */*' . "\r\n" . 'User-Agent: ' . SITE_NAME . " (web tool)\r\n",
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $body = @file_get_contents($safe, false, $context);
        $headers = $http_response_header ?? [];
        $status = '';
        $location = '';
        foreach ($headers as $line) {
            if (str_starts_with($line, 'HTTP/')) {
                $status = $line;
            } elseif (stripos($line, 'Location:') === 0) {
                $location = trim(substr($line, 9));
            }
        }
        if ($status === '') {
            $error = 'No response from ' . $safe . '.';
            break;
        }
        $code = preg_match('~\s(\d{3})\s~', $status, $m) ? (int) $m[1] : 0;
        $hops[] = ['url' => $safe, 'status' => $status, 'location' => $location, 'code' => $code];

        if ($location !== '' && $code >= 300 && $code < 400) {
            $next = $location;
            if (preg_match('~^/~', $location)) {
                $p = parse_url($safe);
                $next = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '') . $location;
            } elseif (!preg_match('~^https?://~i', $location)) {
                $p = parse_url($safe);
                $base = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');
                $path = preg_replace('~/[^/]*$~', '/', $p['path'] ?? '/') ?: '/';
                $next = $base . $path . $location;
            }
            $current = $next;
            continue;
        }
        break;
    }
    if ($error === '' && count($hops) >= 10) {
        $error = 'Stopped after 10 redirects. Check for a redirect loop or an unusually long chain.';
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">URL</label>
    <div class="input-group">
        <input class="form-control" id="url" name="url" type="url" value="<?= e($address) ?>"
               placeholder="https://example.com/" required autofocus>
        <button class="btn btn-primary" type="submit">Check redirects</button>
    </div>
    <p class="form-text mb-0 mt-2">Follows the redirect chain (up to 10 hops) for a public URL and shows each status and <code>Location</code>.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($hops !== []): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th scope="col">#</th><th scope="col">URL</th><th scope="col">Status</th><th scope="col">Location</th></tr></thead>
                <tbody>
                    <?php foreach ($hops as $i => $hop):
                        $code = $hop['code'] ?? 0;
                        $class = ($code >= 200 && $code < 300) ? 'text-bg-success' : (($code >= 300 && $code < 400) ? 'text-bg-warning' : 'text-bg-secondary');
                    ?>
                        <tr>
                            <td class="text-body-secondary"><?= e((string) ($i + 1)) ?></td>
                            <td class="text-break"><code><?= e($hop['url']) ?></code></td>
                            <td><span class="badge <?= $class ?>"><?= e($hop['status']) ?></span></td>
                            <td class="text-break"><?= $hop['location'] !== '' ? '<code>' . e($hop['location']) . '</code>' : '<span class="text-body-secondary">—</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
