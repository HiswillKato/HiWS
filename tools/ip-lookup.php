<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('ip-lookup');

/* The fields to ask ip-api for, in the order they are shown. Naming them keeps
   the answer small: the full record carries a good deal nobody reads. */
const FIELDS = [
    'query'       => 'Address',
    'continent'   => 'Continent',
    'country'     => 'Country',
    'countryCode' => 'Country code',
    'regionName'  => 'Region',
    'district'    => 'District',
    'city'        => 'City',
    'zip'         => 'Postal code',
    'lat'         => 'Latitude',
    'lon'         => 'Longitude',
    'timezone'    => 'Time zone',
    'currency'    => 'Currency',
    'isp'         => 'Internet service provider',
    'org'         => 'Organisation',
    'as'          => 'Autonomous system',
    'proxy'       => 'Proxy or VPN',
];

$ip = trim(posted('ip'));
$details = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* An empty box means "mine", which is the commonest thing anyone wants. */
    $ip = $ip === '' ? (string) ($_SERVER['REMOTE_ADDR'] ?? '') : $ip;

    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        $error = 'That is not an IP address.';
    } else {
        /* The free service is plain HTTP only, which is fine: nothing secret
           is sent, and the answer is only read, never trusted. status and
           message are asked for alongside the rest — the service leaves them
           out unless they are named, and without them a good answer reads as a
           bad one. */
        $asked = implode(',', array_merge(['status', 'message'], array_keys(FIELDS)));
        $answer = json('http://ip-api.com/json/' . rawurlencode($ip) . '?fields=' . $asked);

        if (($answer['status'] ?? '') !== 'success') {
            $error = (string) ($answer['message'] ?? 'The lookup service did not answer. Try again in a moment.');
        } else {
            $details = $answer;
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="ip">IP address</label>
    <div class="input-group">
        <input class="form-control" id="ip" name="ip" value="<?= e($ip) ?>"
               placeholder="8.8.8.8" autocomplete="off">
        <button class="btn btn-primary" type="submit">Look up</button>
    </div>
    <p class="form-text mb-0 mt-2">
        Leave it empty to look up your own address, which this server sees as
        <strong><?= e((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) ?></strong>.
    </p>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($details !== null): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <tbody>
                    <?php foreach (FIELDS as $key => $label): ?>
                        <?php
                        $value = $details[$key] ?? '';

                        if ($key === 'proxy') {
                            $value = $value ? 'Yes' : 'No';
                        }

                        if ($value === '') {
                            continue;
                        }
                        ?>
                        <tr>
                            <th scope="row" class="text-body-secondary fw-normal w-50"><?= e($label) ?></th>
                            <td><?= e((string) $value) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-body-secondary small mt-3 mb-0 text-center">
        The details come from <a href="https://ip-api.com/" rel="noopener">IP-API.com</a>.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
