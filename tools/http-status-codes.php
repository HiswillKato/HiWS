<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('http-status-codes');

$codes = [
    [100, 'Continue', 'The server received the request headers; the client should send the body.'],
    [101, 'Switching Protocols', 'The server is switching protocols as requested by the client.'],
    [200, 'OK', 'The request succeeded. The meaning depends on the HTTP method.'],
    [201, 'Created', 'The request succeeded and a new resource was created.'],
    [202, 'Accepted', 'The request was accepted for processing but is not finished yet.'],
    [204, 'No Content', 'The request succeeded and there is no content to return.'],
    [206, 'Partial Content', 'The server is delivering only part of the resource (range request).'],
    [301, 'Moved Permanently', 'The resource has moved permanently to a new URL.'],
    [302, 'Found', 'The resource is temporarily at a different URL.'],
    [303, 'See Other', 'The client should GET the result from another URL.'],
    [304, 'Not Modified', 'The cached version is still valid; no body is sent.'],
    [307, 'Temporary Redirect', 'Temporary redirect that preserves the request method.'],
    [308, 'Permanent Redirect', 'Permanent redirect that preserves the request method.'],
    [400, 'Bad Request', 'The server cannot process the request because it is malformed.'],
    [401, 'Unauthorized', 'Authentication is required and has not been provided or failed.'],
    [403, 'Forbidden', 'The server understood the request but refuses to authorize it.'],
    [404, 'Not Found', 'The server cannot find the requested resource.'],
    [405, 'Method Not Allowed', 'The request method is known but not allowed for this resource.'],
    [408, 'Request Timeout', 'The server timed out waiting for the request.'],
    [409, 'Conflict', 'The request conflicts with the current state of the resource.'],
    [410, 'Gone', 'The resource is no longer available and will not return.'],
    [413, 'Content Too Large', 'The request body is larger than the server allows.'],
    [415, 'Unsupported Media Type', 'The media format of the request is not supported.'],
    [422, 'Unprocessable Content', 'The request was well-formed but semantically invalid.'],
    [429, 'Too Many Requests', 'The client has sent too many requests in a given time.'],
    [500, 'Internal Server Error', 'The server hit an unexpected condition.'],
    [501, 'Not Implemented', 'The server does not support the functionality required.'],
    [502, 'Bad Gateway', 'The server got an invalid response from an upstream server.'],
    [503, 'Service Unavailable', 'The server is not ready to handle the request right now.'],
    [504, 'Gateway Timeout', 'The server acting as a gateway did not get a timely response.'],
];

$query = trim((string) ($_GET['q'] ?? ''));
$shown = $codes;
if ($query !== '') {
    $q = mb_strtolower($query, 'UTF-8');
    $shown = array_values(array_filter($codes, static function (array $row) use ($q): bool {
        return str_contains((string) $row[0], $q)
            || str_contains(mb_strtolower($row[1], 'UTF-8'), $q)
            || str_contains(mb_strtolower($row[2], 'UTF-8'), $q);
    }));
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="get" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="q">Search status codes</label>
    <div class="input-group">
        <input class="form-control" id="q" name="q" value="<?= e($query) ?>" placeholder="404, redirect, unauthorized…" autofocus>
        <button class="btn btn-primary" type="submit">Search</button>
    </div>
</form>

<div class="card bg-body rounded-4 border shadow-sm mt-4">
    <div class="card-header bg-transparent">
        <span class="fw-semibold"><?= count($shown) ?></span> status code<?= count($shown) === 1 ? '' : 's' ?>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col" style="width:5rem">Code</th>
                    <th scope="col">Name</th>
                    <th scope="col">Meaning</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shown as [$code, $name, $desc]): ?>
                    <tr>
                        <td><code><?= e((string) $code) ?></code></td>
                        <td class="fw-semibold"><?= e($name) ?></td>
                        <td class="text-body-secondary"><?= e($desc) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($shown === []): ?>
                    <tr><td colspan="3" class="text-body-secondary">No status codes matched that search.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
