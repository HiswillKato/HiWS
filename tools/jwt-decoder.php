<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('jwt-decoder');

/* Decode a base64url string. JWTs use the URL-safe alphabet and often omit
   padding, which base64_decode needs restored. */
function jwt_base64_decode(string $value): string
{
    $value = strtr($value, '-_', '+/');
    $pad = strlen($value) % 4;
    if ($pad !== 0) {
        $value .= str_repeat('=', 4 - $pad);
    }

    $decoded = base64_decode($value, true);

    return $decoded === false ? '' : $decoded;
}

/* Read a JSON object into a pretty string, or return the raw text when it is
   not JSON. */
function jwt_json(string $value): string
{
    $data = json_decode($value, true);
    if (!is_array($data)) {
        return $value;
    }

    $pretty = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return $pretty === false ? $value : $pretty;
}

/* Format common Unix-time claims so a person can read them at a glance. */
function jwt_times(array $payload): array
{
    $rows = [];
    foreach (['exp' => 'Expires', 'nbf' => 'Not before', 'iat' => 'Issued at'] as $key => $label) {
        if (!isset($payload[$key]) || !is_numeric($payload[$key])) {
            continue;
        }
        $time = (int) $payload[$key];
        $rows[] = [
            'label' => $label,
            'time'  => $time,
            'date'  => gmdate('j F Y H:i:s T', $time),
        ];
    }

    return $rows;
}

$token = posted('token');
$error = '';
$header = '';
$payload = '';
$times = [];
$signature = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($token === '') {
        $error = 'Paste a JSON Web Token to decode.';
    } else {
        $parts = explode('.', $token);
        if (count($parts) < 2 || count($parts) > 3) {
            $error = 'A JWT has three parts separated by dots: header, payload and signature.';
        } else {
            $header_raw = jwt_base64_decode($parts[0]);
            $payload_raw = jwt_base64_decode($parts[1]);
            $signature = $parts[2] ?? '';

            if ($header_raw === '' || $payload_raw === '') {
                $error = 'Could not decode that token. Check that it is a complete JWT.';
            } else {
                $header = jwt_json($header_raw);
                $payload = jwt_json($payload_raw);
                $payload_data = json_decode($payload_raw, true);
                $times = is_array($payload_data) ? jwt_times($payload_data) : [];

                if (isset($payload_data['exp']) && is_numeric($payload_data['exp'])) {
                    $exp = (int) $payload_data['exp'];
                    $status = $exp < time() ? 'expired' : 'active';
                }
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="token">JSON Web Token</label>
    <textarea class="form-control font-monospace" id="token" name="token" rows="5" autofocus
              placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."><?= e($token) ?></textarea>
    <p class="form-text mb-0 mt-2">The token is decoded in your browser session only. Its signature is <strong>not verified</strong> — this reads the contents, it does not prove they are genuine.</p>
    <button class="btn btn-primary mt-3" type="submit">Decode</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($header !== ''): ?>
    <?php if ($status !== ''): ?>
        <div class="alert alert-<?= $status === 'expired' ? 'warning' : 'success' ?> mt-4 d-flex align-items-center gap-2">
            <?= icon($status === 'expired' ? 'fa-solid fa-clock' : 'fa-solid fa-circle-check') ?>
            <span class="mb-0">
                <strong><?= $status === 'expired' ? 'Expired' : 'Not expired' ?></strong>
                — based on the <code>exp</code> claim. The signature was not checked.
            </span>
        </div>
    <?php endif; ?>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="card bg-body rounded-4 border shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Header</span>
                    <?= clip($header, 'Copy') ?>
                </div>
                <pre class="mb-0 p-3 small overflow-auto"><?= e($header) ?></pre>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card bg-body rounded-4 border shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Payload</span>
                    <?= clip($payload, 'Copy') ?>
                </div>
                <pre class="mb-0 p-3 small overflow-auto"><?= e($payload) ?></pre>
            </div>
        </div>
    </div>

    <?php if ($times !== []): ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-4">
            <div class="card-header bg-transparent fw-semibold">Time claims</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        <?php foreach ($times as $row): ?>
                            <tr>
                                <th scope="row" class="text-body-secondary fw-normal w-25"><?= e($row['label']) ?></th>
                                <td><code><?= e((string) $row['time']) ?></code></td>
                                <td><?= e($row['date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Signature</span>
            <?php if ($signature !== ''): ?><?= clip($signature, 'Copy') ?><?php endif; ?>
        </div>
        <div class="p-3">
            <code class="text-break"><?= e($signature === '' ? '(none)' : $signature) ?></code>
            <p class="text-body-secondary small mb-0 mt-2">The signature is shown as-is. Verifying it requires the signing key or secret and is not done here.</p>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
