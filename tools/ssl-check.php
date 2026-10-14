<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('ssl-check');

/* Reach the host on port 443, ask for its certificate, and come back with it.
   Verification is deliberately off here: the point of this page is to read
   what the site presents, including the days when it presents something
   broken, so that a visitor can see why a browser complains. */
function certificate(string $host, int $port = 443): ?OpenSSLCertificate
{
    $context = stream_context_create([
        'ssl' => [
            'capture_peer_cert'  => true,
            'verify_peer'        => false,
            'verify_peer_name'   => false,
            'allow_self_signed'  => true,
            'SNI_enabled'        => true,
        ],
    ]);

    $stream = @stream_socket_client(
        'ssl://' . $host . ':' . $port,
        $code,
        $reason,
        WAIT,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if ($stream === false) {
        return null;
    }

    $params = stream_context_get_params($stream);
    fclose($stream);

    return $params['options']['ssl']['peer_certificate'] ?? null;
}

$host = host(posted('host'));
$details = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!preg_match('~^([a-z0-9-]+\.)+[a-z]{2,}$~', $host)) {
        $error = 'That does not look like a host name. Try example.com';
    } else {
        $cert = certificate($host);

        if ($cert === null) {
            $error = 'Could not open a secure connection to ' . $host . '. It may not serve HTTPS at all.';
        } else {
            $parsed = openssl_x509_parse($cert, false);
            $subject = $parsed['subject'] ?? [];
            $issuer = $parsed['issuer'] ?? [];

            $until = (int) ($parsed['validTo_time_t'] ?? 0);
            $since = (int) ($parsed['validFrom_time_t'] ?? 0);
            $left = (int) floor(($until - time()) / 86400);

            $details = [
                'host'     => $host,
                'address'  => gethostbyname($host),
                'issued'   => (string) ($subject['CN'] ?? ''),
                'organisation' => (string) ($subject['O'] ?? ''),
                'issuer'   => (string) ($issuer['CN'] ?? ''),
                'issuer_organisation' => (string) ($issuer['O'] ?? ''),
                'issuer_country' => (string) ($issuer['C'] ?? ''),
                'serial'   => (string) ($parsed['serialNumberHex'] ?? $parsed['serialNumber'] ?? ''),
                'sha1'     => (string) openssl_x509_fingerprint($cert, 'sha1'),
                'signature' => (string) ($parsed['signatureTypeSN'] ?? ''),
                'names'    => (string) ($parsed['extensions']['subjectAltName'] ?? ''),
                'since'    => $since === 0 ? '' : date('j F Y', $since),
                'until'    => $until === 0 ? '' : date('j F Y', $until),
                'days'     => $left,
                'life'     => $since === 0 || $until === 0 ? 0 : (int) floor(($until - $since) / 86400),
            ];
        }
    }
}

/* How the certificate stands today, as a Bootstrap alert colour. */
$standing = 'success';
$saying = 'Valid';

if ($details !== null) {
    if ($details['days'] < 0) {
        $standing = 'danger';
        $saying = 'Expired';
    } elseif ($details['days'] < 21) {
        $standing = 'warning';
        $saying = 'About to expire';
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="host">Host name</label>
    <div class="input-group">
        <input class="form-control" id="host" name="host" value="<?= e($host) ?>"
               placeholder="example.com" required autofocus>
        <button class="btn btn-primary" type="submit">Check</button>
    </div>
    <p class="form-text mb-0 mt-2">The certificate is read from port 443, the port HTTPS uses.</p>
</form>

<?php if ($error !== ''): ?>
    <div class="alert alert-warning mt-4 mb-0">
        <?= icon('fa-solid fa-circle-exclamation me-1') ?><?= e($error) ?>
    </div>
<?php elseif ($details !== null): ?>
    <div class="alert alert-<?= e($standing) ?> mt-4 d-flex align-items-center gap-2">
        <?= icon($standing === 'success' ? 'fa-solid fa-lock' : 'fa-solid fa-triangle-exclamation') ?>
        <span class="mb-0">
            <strong><?= e($saying) ?></strong> —
            <?php if ($details['days'] < 0): ?>
                it ran out <?= e((string) abs($details['days'])) ?> days ago.
            <?php else: ?>
                <?= e((string) $details['days']) ?> days left of <?= e((string) $details['life']) ?>.
            <?php endif; ?>
        </span>
    </div>

    <div class="card bg-body rounded-4 border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <tbody>
                    <?php
                    $rows = [
                        'Host'               => $details['host'],
                        'Resolved address'   => $details['address'],
                        'Issued to'          => $details['issued'],
                        'Organisation'       => $details['organisation'],
                        'Issuer'             => $details['issuer'],
                        'Issuer organisation' => $details['issuer_organisation'],
                        'Issuer country'     => $details['issuer_country'],
                        'Valid from'         => $details['since'],
                        'Valid until'        => $details['until'],
                        'Serial number'      => $details['serial'],
                        'SHA-1 fingerprint'  => $details['sha1'],
                        'Signature'          => $details['signature'],
                        'Alternate names'    => $details['names'],
                    ];

                    foreach ($rows as $label => $value):
                        if (trim((string) $value) === '') {
                            continue;
                        }
                        ?>
                        <tr>
                            <th scope="row" class="text-body-secondary fw-normal w-50"><?= e($label) ?></th>
                            <td class="text-break"><code><?= e((string) $value) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
