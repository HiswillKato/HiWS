<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('what-is-my-ip');

/* Nothing to fill in: the address is the one the request arrived on, so the
   page carries an answer the moment it is opened. */
$ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
$host = $ip === '' ? '' : (string) @gethostbyaddr($ip);

require __DIR__ . '/../app/parts/tool.php';
?>

<p class="text-body-secondary">
    This is the address the server behind HiWS sees when your browser asks for a
    page, with the handful of details that travel alongside it.
</p>

<?= values([
    'Address'     => $ip,
    'Protocol'    => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false ? 'IPv6' : 'IPv4',
    'Reverse DNS' => $host === $ip ? '' : $host,
    'Port'        => (string) ($_SERVER['SERVER_PORT'] ?? ''),
    'Scheme'      => (($_SERVER['HTTPS'] ?? '') === 'on') ? 'HTTPS' : 'HTTP',
    'Host'        => (string) ($_SERVER['HTTP_HOST'] ?? ''),
    'User agent'  => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
    'Language'    => (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''),
]) ?>

<p class="text-body-secondary small mt-3 mb-0">
    <a href="<?= url('tools/what-is-my-ip') ?>">Refresh</a> to look again. A VPN or a
    proxy puts a different address in front of you — this is only what
    <em>this</em> server can see.
</p>

<?php require __DIR__ . '/../app/parts/explain.php';
