<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('ssl-generator');

/* A self-signed pair, made on the spot: a 2048-bit key, a request for the
   name, and a certificate the request signs itself. Good enough for a
   development server, which is what it is for — a browser will still warn,
   because nobody outside vouches for it. */
function pair(string $host): array
{
    $key = openssl_pkey_new([
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => 2048,
        'digest_alg'       => 'sha256',
    ]);

    if ($key === false) {
        return [];
    }

    $request = openssl_csr_new(
        ['commonName' => $host],
        $key,
        ['digest_alg' => 'sha256']
    );

    if ($request === false) {
        return [];
    }

    $cert = openssl_csr_sign($request, null, $key, 3650, ['digest_alg' => 'sha256'], random_int(1, PHP_INT_MAX));

    if ($cert === false) {
        return [];
    }

    openssl_x509_export($cert, $certificate);
    openssl_pkey_export($key, $private);

    return ['certificate' => $certificate, 'key' => $private];
}

$host = host(posted('host'));
$made = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!preg_match('~^([a-z0-9-]+\.)+[a-z]{2,}$~', $host)) {
        $error = 'That does not look like a host name. Try example.com';
    } else {
        $made = pair($host);

        if ($made === []) {
            $error = 'This server could not make the key. It may have no OpenSSL configuration.';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="host">Host name for the certificate</label>
    <div class="input-group">
        <input class="form-control" id="host" name="host" value="<?= e($host) ?>"
               placeholder="localhost" required autofocus>
        <button class="btn btn-primary" type="submit">Generate</button>
    </div>
    <p class="form-text mb-0 mt-2">
        Ten years, self-signed, 2048-bit RSA. Nothing is stored: the pair is made
        when you ask and forgotten when you leave.
    </p>
</form>

<?php if ($error !== ''): ?>
    <div class="alert alert-warning mt-4 mb-0">
        <?= icon('fa-solid fa-circle-exclamation me-1') ?><?= e($error) ?>
    </div>
<?php elseif ($made !== []): ?>
    <?php
    $blocks = [
        'Certificate' => ['file' => $host . '.crt', 'type' => 'application/x-x509-ca-cert', 'body' => $made['certificate']],
        'Private key' => ['file' => $host . '.key', 'type' => 'application/x-pem-file', 'body' => $made['key']],
    ];
    ?>

    <?php foreach ($blocks as $label => $block): ?>
        <?php /* A download link carrying the PEM itself, so nothing has to be written to the server to be handed out. */ ?>
        <?php $data = 'data:' . $block['type'] . ';base64,' . base64_encode($block['body']); ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-4">
            <div class="card-header bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="fw-semibold"><?= e($label) ?></span>

                <span class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-secondary" download="<?= e($block['file']) ?>" href="<?= e($data) ?>">
                        <?= icon('fa-solid fa-download') ?> Download
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-copy="<?= e($block['body']) ?>">
                        <?= icon('fa-regular fa-copy') ?>
                        <span class="visually-hidden">Copy the <?= e(strtolower($label)) ?></span>
                    </button>
                </span>
            </div>

            <textarea class="form-control border-0 font-monospace small" rows="9" readonly
                      aria-label="<?= e($label) ?>"><?= e($block['body']) ?></textarea>
        </div>
    <?php endforeach; ?>

    <p class="text-body-secondary small mt-3 mb-0">
        Point the server at the <code>.crt</code> and <code>.key</code>, and keep
        the key to yourself. A browser will still warn about it: a self-signed
        certificate is trusted by you alone.
    </p>
<?php endif; ?>

<script src="<?= asset('js/copy.js') ?>" defer></script>

<?php require __DIR__ . '/../app/parts/explain.php';
