<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('ssl-check');

/* Reach the host on port 443, ask for its certificate, and come back with it.
   Verification is deliberately off here: the point of this page is to read
   what the site presents, including the days when it presents something
   broken, so that a visitor can see why a browser complains. */
function certificate(string $host, int $port = 443): ?array
{
    $context = stream_context_create([
        'ssl' => [
            'capture_peer_cert'       => true,
            'capture_peer_cert_chain' => true,
            'verify_peer'             => false,
            'verify_peer_name'        => false,
            'allow_self_signed'       => true,
            'SNI_enabled'             => true,
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
    $meta = stream_get_meta_data($stream);
    fclose($stream);

    $cert = $params['options']['ssl']['peer_certificate'] ?? null;
    if ($cert === null) {
        return null;
    }

    return [
        'cert'   => $cert,
        'chain'  => $params['options']['ssl']['peer_certificate_chain'] ?? [],
        'crypto' => is_array($meta['crypto'] ?? null) ? $meta['crypto'] : [],
    ];
}

/* Turn an X.509 name array into a readable line, keeping the fields that
   usually matter and in a stable order. */
function x509_name(array $name): string
{
    $parts = [];
    foreach (['CN' => 'CN', 'O' => 'O', 'OU' => 'OU', 'L' => 'L', 'ST' => 'ST', 'C' => 'C'] as $key => $label) {
        $value = $name[$key] ?? '';
        if (is_array($value)) {
            $value = implode(', ', array_map('strval', $value));
        }
        $value = trim((string) $value);
        if ($value !== '') {
            $parts[] = $label . '=' . $value;
        }
    }

    return $parts === [] ? '' : implode(', ', $parts);
}

/* Split a comma-separated SAN extension into one name per line, dropping the
   type prefix noise only where it stays clear. */
function san_list(string $value): string
{
    $items = preg_split('/\s*,\s*/', trim($value)) ?: [];
    $items = array_values(array_filter(array_map('trim', $items), static fn(string $v): bool => $v !== ''));

    return implode("\n", $items);
}

/* Public key details in plain language. */
function public_key_info(OpenSSLCertificate $cert): array
{
    $key = openssl_pkey_get_public($cert);
    if ($key === false) {
        return ['type' => '', 'bits' => '', 'detail' => ''];
    }

    $details = openssl_pkey_get_details($key);
    if (!is_array($details)) {
        return ['type' => '', 'bits' => '', 'detail' => ''];
    }

    $bits = (string) ($details['bits'] ?? '');
    $type = '';
    $detail = '';

    if (isset($details['rsa'])) {
        $type = 'RSA';
        $detail = $bits !== '' ? 'RSA ' . $bits . '-bit' : 'RSA';
    } elseif (isset($details['ec'])) {
        $type = 'EC';
        $curve = (string) ($details['ec']['curve_name'] ?? $details['ec']['curve_oid'] ?? '');
        $detail = trim('EC ' . $bits . ($curve !== '' ? ' (' . $curve . ')' : ''));
    } elseif (isset($details['dsa'])) {
        $type = 'DSA';
        $detail = $bits !== '' ? 'DSA ' . $bits . '-bit' : 'DSA';
    } elseif (isset($details['dh'])) {
        $type = 'DH';
        $detail = $bits !== '' ? 'DH ' . $bits . '-bit' : 'DH';
    } else {
        $type = (string) ($details['type'] ?? '');
        $detail = $type;
    }

    return ['type' => $type, 'bits' => $bits, 'detail' => $detail];
}

/* Pull URLs out of Authority Information Access / CRL distribution text. */
function extension_urls(string $value, string $label): array
{
    $urls = [];
    if (preg_match_all('/' . preg_quote($label, '/') . '\s*-\s*URI:([^\s,\n]+)/i', $value, $found)) {
        foreach ($found[1] as $url) {
            $url = trim($url);
            if ($url !== '') {
                $urls[] = $url;
            }
        }
    }

    return array_values(array_unique($urls));
}

$host = host(posted('host'));
$details = null;
$chain = [];
$crypto = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!preg_match('~^([a-z0-9-]+\.)+[a-z]{2,}$~', $host)) {
        $error = 'That does not look like a host name. Try example.com';
    } else {
        $result = certificate($host);

        if ($result === null) {
            $error = 'Could not open a secure connection to ' . $host . '. It may not serve HTTPS at all.';
        } else {
            $cert = $result['cert'];
            $crypto = $result['crypto'];
            $parsed = openssl_x509_parse($cert, false);
            $subject = $parsed['subject'] ?? [];
            $issuer = $parsed['issuer'] ?? [];
            $extensions = $parsed['extensions'] ?? [];

            $until = (int) ($parsed['validTo_time_t'] ?? 0);
            $since = (int) ($parsed['validFrom_time_t'] ?? 0);
            $left = (int) floor(($until - time()) / 86400);
            $pub = public_key_info($cert);

            $aia = (string) ($extensions['authorityInfoAccess'] ?? '');
            $crl = (string) ($extensions['crlDistributionPoints'] ?? '');
            $ocsp = extension_urls($aia, 'OCSP');
            $ca_issuers = extension_urls($aia, 'CA Issuers');
            $crl_urls = [];
            if (preg_match_all('~URI:([^\s,\n]+)~i', $crl, $found)) {
                $crl_urls = array_values(array_unique(array_map('trim', $found[1])));
            }

            $version = isset($parsed['version']) ? ((int) $parsed['version'] + 1) : null;
            $san = san_list((string) ($extensions['subjectAltName'] ?? ''));

            $details = [
                'host'                => $host,
                'address'             => gethostbyname($host),
                'protocol'            => (string) ($crypto['protocol'] ?? ''),
                'cipher'              => (string) ($crypto['cipher_name'] ?? ''),
                'cipher_bits'         => isset($crypto['cipher_bits']) ? (string) $crypto['cipher_bits'] : '',
                'issued'              => (string) ($subject['CN'] ?? ''),
                'subject'             => x509_name($subject),
                'issuer'              => (string) ($issuer['CN'] ?? ''),
                'issuer_name'         => x509_name($issuer),
                'serial'              => (string) ($parsed['serialNumberHex'] ?? $parsed['serialNumber'] ?? ''),
                'version'             => $version === null ? '' : ('v' . $version),
                'sha1'                => strtoupper((string) openssl_x509_fingerprint($cert, 'sha1')),
                'sha256'              => strtoupper((string) openssl_x509_fingerprint($cert, 'sha256')),
                'signature'           => (string) ($parsed['signatureTypeSN'] ?? ''),
                'signature_long'      => (string) ($parsed['signatureTypeLN'] ?? ''),
                'public_key'          => $pub['detail'],
                'names'               => $san,
                'key_usage'           => (string) ($extensions['keyUsage'] ?? ''),
                'extended_key_usage'  => (string) ($extensions['extendedKeyUsage'] ?? ''),
                'basic_constraints'   => (string) ($extensions['basicConstraints'] ?? ''),
                'ocsp'                => implode("\n", $ocsp),
                'ca_issuers'          => implode("\n", $ca_issuers),
                'crl'                 => implode("\n", $crl_urls),
                'since'               => $since === 0 ? '' : date('j F Y H:i:s T', $since),
                'until'               => $until === 0 ? '' : date('j F Y H:i:s T', $until),
                'days'                => $left,
                'life'                => $since === 0 || $until === 0 ? 0 : (int) floor(($until - $since) / 86400),
            ];

            foreach ($result['chain'] as $item) {
                if (!$item instanceof OpenSSLCertificate) {
                    continue;
                }
                $p = openssl_x509_parse($item, false);
                if (!is_array($p)) {
                    continue;
                }
                $s = $p['subject'] ?? [];
                $i = $p['issuer'] ?? [];
                $u = (int) ($p['validTo_time_t'] ?? 0);
                $chain[] = [
                    'subject' => (string) ($s['CN'] ?? x509_name($s)),
                    'issuer'  => (string) ($i['CN'] ?? x509_name($i)),
                    'until'   => $u === 0 ? '' : date('j F Y', $u),
                    'sha256'  => strtoupper((string) openssl_x509_fingerprint($item, 'sha256')),
                ];
            }
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
    <p class="form-text mb-0 mt-2">The certificate is read from port 443, the port HTTPS uses. The connection also reports the TLS protocol and cipher in use.</p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
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
                        'Host'                => $details['host'],
                        'Resolved address'    => $details['address'],
                        'TLS protocol'        => $details['protocol'],
                        'Cipher suite'        => $details['cipher'] . ($details['cipher_bits'] !== '' ? ' (' . $details['cipher_bits'] . ' bits)' : ''),
                        'Issued to (CN)'      => $details['issued'],
                        'Subject'             => $details['subject'],
                        'Issuer (CN)'         => $details['issuer'],
                        'Issuer'              => $details['issuer_name'],
                        'Certificate version' => $details['version'],
                        'Serial number'       => $details['serial'],
                        'Valid from'          => $details['since'],
                        'Valid until'         => $details['until'],
                        'Public key'          => $details['public_key'],
                        'Signature'           => trim($details['signature'] . ($details['signature_long'] !== '' ? ' (' . $details['signature_long'] . ')' : '')),
                        'SHA-256 fingerprint' => $details['sha256'],
                        'SHA-1 fingerprint'   => $details['sha1'],
                        'Key usage'           => $details['key_usage'],
                        'Extended key usage'  => $details['extended_key_usage'],
                        'Basic constraints'   => $details['basic_constraints'],
                        'OCSP responder'      => $details['ocsp'],
                        'CA issuers'          => $details['ca_issuers'],
                        'CRL distribution'    => $details['crl'],
                        'Subject alternate names' => $details['names'],
                    ];

                    foreach ($rows as $label => $value):
                        if (trim((string) $value) === '') {
                            continue;
                        }
                        ?>
                        <tr>
                            <th scope="row" class="text-body-secondary fw-normal w-50"><?= e($label) ?></th>
                            <td class="text-break"><code style="white-space: pre-wrap;"><?= e((string) $value) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($chain !== []): ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-4">
            <div class="card-header bg-transparent fw-semibold">Certificate chain</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="text-body-secondary fw-normal">#</th>
                            <th scope="col" class="text-body-secondary fw-normal">Subject</th>
                            <th scope="col" class="text-body-secondary fw-normal">Issuer</th>
                            <th scope="col" class="text-body-secondary fw-normal">Valid until</th>
                            <th scope="col" class="text-body-secondary fw-normal">SHA-256</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chain as $i => $item): ?>
                            <tr>
                                <td><?= e((string) ($i + 1)) ?></td>
                                <td class="text-break"><?= e($item['subject']) ?></td>
                                <td class="text-break"><?= e($item['issuer']) ?></td>
                                <td><?= e($item['until']) ?></td>
                                <td class="text-break"><code><?= e($item['sha256']) ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
