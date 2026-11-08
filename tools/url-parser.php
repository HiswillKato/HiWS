<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('url-parser');

$input = posted('url');
$error = '';
$parts = [];
$query = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($input === '') {
        $error = 'Enter a URL to parse.';
    } else {
        $parsed = parse_url($input);
        if ($parsed === false) {
            $error = 'That URL could not be parsed.';
        } else {
            $map = [
                'scheme' => 'Scheme', 'host' => 'Host', 'port' => 'Port', 'user' => 'User',
                'pass' => 'Password', 'path' => 'Path', 'query' => 'Query string', 'fragment' => 'Fragment',
            ];
            foreach ($map as $key => $label) {
                if (isset($parsed[$key]) && $parsed[$key] !== '') {
                    $parts[] = ['label' => $label, 'value' => (string) $parsed[$key]];
                }
            }
            if (isset($parsed['query'])) {
                parse_str((string) $parsed['query'], $query);
            }
            if ($parts === []) {
                $error = 'No URL components were found. Try including a scheme, such as https://example.com/path?name=value.';
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="url">URL</label>
    <div class="input-group">
        <input class="form-control font-monospace" id="url" name="url" value="<?= e($input) ?>"
               placeholder="https://user@example.com:8080/path?name=value#section" autofocus>
        <button class="btn btn-primary" type="submit">Parse</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($parts !== []): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <tbody>
                    <?php foreach ($parts as $row): ?>
                        <tr>
                            <th scope="row" class="text-body-secondary fw-normal w-25"><?= e($row['label']) ?></th>
                            <td class="text-break"><code><?= e($row['value']) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($query !== []): ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-4">
            <div class="card-header bg-transparent fw-semibold">Query parameters</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th scope="col">Name</th><th scope="col">Value</th></tr></thead>
                    <tbody>
                        <?php
                        $flat = function (string $prefix, $value) use (&$flat) {
                            if (is_array($value)) {
                                foreach ($value as $k => $v) {
                                    $flat($prefix . '[' . $k . ']', $v);
                                }
                                return;
                            }
                            ?>
                            <tr>
                                <td><code><?= e($prefix) ?></code></td>
                                <td class="text-break"><code><?= e((string) $value) ?></code></td>
                            </tr>
                            <?php
                        };
                        foreach ($query as $name => $value) {
                            $flat((string) $name, $value);
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
