<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('http-status');

$given = posted('url');
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($given === '') {
        $error = 'Enter a web address.';
    } else {
        /* A bare domain is the commonest thing typed, so https:// is assumed
           when no scheme is given. public_url() then refuses anything that is
           not a public http(s) address. */
        $try = preg_match('~^[a-z][a-z0-9+.-]*://~i', $given) ? $given : 'https://' . $given;
        $safe = public_url($try);

        if ($safe === null) {
            $error = 'That address cannot be fetched. Enter a full public http:// or https:// address.';
        } else {
            $start = microtime(true);
            $body = fetch($safe, WAIT, $headers);
            $elapsed = (int) round((microtime(true) - $start) * 1000);

            if ($body === null && $headers === []) {
                $error = 'Nothing came back from ' . $safe . '. The site may be down, or it may refuse this server.';
            } else {
                /* fetch() follows redirects, so $headers holds one block of
                   response lines per hop. The first line is where it started
                   and the last is where it ended. */
                $lines = array_values(array_filter(array_map('trim', $headers), static fn(string $line): bool => $line !== ''));
                $hops = 0;
                $meta = [];

                foreach ($lines as $line) {
                    if (str_starts_with($line, 'HTTP/')) {
                        $hops++;
                    }

                    $pair = explode(':', $line, 2);

                    if (count($pair) === 2) {
                        $meta[strtolower(trim($pair[0]))] = trim($pair[1]);
                    }
                }

                preg_match('~^HTTP/\S+\s+(\d{3})\s*(.*)$~', $lines[0] ?? '', $status);

                $result = [
                    'URL'            => $safe,
                    'Status'         => trim(($status[1] ?? '?') . ' ' . ($status[2] ?? '')),
                    'Redirects'      => (string) max(0, $hops - 1),
                    'Server'         => $meta['server'] ?? '',
                    'Content type'   => $meta['content-type'] ?? '',
                    'Content length' => $meta['content-length'] ?? '',
                    'Location'       => $meta['location'] ?? '',
                    'Powered by'     => $meta['x-powered-by'] ?? '',
                    'Cache control'  => $meta['cache-control'] ?? '',
                    'Time'           => $elapsed . ' ms',
                ];
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">Web address</label>
    <div class="input-group">
        <input class="form-control" id="url" name="url" value="<?= e($given) ?>"
               placeholder="example.com" autocomplete="off" autofocus>
        <button class="btn btn-primary" type="submit">Check</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <?= values($result) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
