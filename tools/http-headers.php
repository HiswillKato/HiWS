<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('http-headers');

$address = posted('url');
$status = '';
$rows = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $safe = public_url($address);

    if ($safe === null) {
        $error = 'That address cannot be fetched. Enter a full public http:// or https:// address.';
    } else {
        $lines = [];

        if (fetch($safe, 20, $lines) === null) {
            $error = 'Nothing came back from ' . $safe . '. The site may be down, or it may refuse this server.';
        } else {
            /* A redirect leaves several blocks of headers behind. Only the
               last one describes what was finally served, so the rows are
               taken from the final "HTTP/…" line onwards. */
            $last = 0;

            foreach ($lines as $number => $line) {
                if (str_starts_with($line, 'HTTP/')) {
                    $last = $number;
                }
            }

            foreach (array_slice($lines, $last) as $line) {
                if (str_starts_with($line, 'HTTP/')) {
                    $status = $line;
                } elseif (str_contains($line, ':')) {
                    [$name, $value] = explode(':', $line, 2);
                    $rows[] = ['name' => trim($name), 'value' => trim($value)];
                }
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">Web address</label>
    <div class="input-group">
        <input class="form-control" id="url" name="url" type="url" value="<?= e($address) ?>"
               placeholder="https://example.com/" required autofocus>
        <button class="btn btn-primary" type="submit">View headers</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <div class="alert alert-warning mt-4 mb-0">
        <?= icon('fa-solid fa-circle-exclamation me-1') ?><?= e($error) ?>
    </div>
<?php elseif ($status !== ''): ?>
    <p class="text-body-secondary mt-4 mb-2">
        <?= e($address) ?> answered <code><?= e($status) ?></code> with
        <?= count($rows) ?> headers.
    </p>

    <div class="card bg-body rounded-4 border shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Header</th>
                        <th scope="col">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <th scope="row" class="text-body-secondary fw-normal"><?= e($row['name']) ?></th>
                            <td class="text-break"><code><?= e($row['value']) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
