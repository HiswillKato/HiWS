<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('color-contrast');

/* Parse a hex colour (#abc or #aabbcc) into RGB channels, or null. */
function contrast_rgb(string $value): ?array
{
    $hex = ltrim(trim($value), '#');
    if (preg_match('/^[0-9a-fA-F]{3}$/', $hex)) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        return null;
    }

    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2)),
        'hex' => '#' . strtolower($hex),
    ];
}

/* Relative luminance as defined by WCAG 2.x. */
function contrast_luminance(array $rgb): float
{
    $channels = [];
    foreach (['r', 'g', 'b'] as $c) {
        $v = $rgb[$c] / 255;
        $channels[] = $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    }

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

/* WCAG contrast ratio between two colours. */
function contrast_ratio(array $a, array $b): float
{
    $l1 = contrast_luminance($a);
    $l2 = contrast_luminance($b);
    if ($l1 < $l2) {
        [$l1, $l2] = [$l2, $l1];
    }

    return ($l1 + 0.05) / ($l2 + 0.05);
}

function contrast_badge(bool $pass): string
{
    return $pass
        ? '<span class="badge text-bg-success">Pass</span>'
        : '<span class="badge text-bg-danger">Fail</span>';
}

$foreground = posted('foreground') !== '' ? posted('foreground') : '#1a1a1a';
$background = posted('background') !== '' ? posted('background') : '#ffffff';
$error = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fg = contrast_rgb($foreground);
    $bg = contrast_rgb($background);
    if ($fg === null || $bg === null) {
        $error = 'Enter two hex colours, such as #1a1a1a and #ffffff.';
    } else {
        $foreground = $fg['hex'];
        $background = $bg['hex'];
        $ratio = contrast_ratio($fg, $bg);
        $result = [
            'ratio'       => $ratio,
            'ratio_text'  => number_format($ratio, 2) . ':1',
            'aa_normal'   => $ratio >= 4.5,
            'aa_large'    => $ratio >= 3.0,
            'aaa_normal'  => $ratio >= 7.0,
            'aaa_large'   => $ratio >= 4.5,
            'fg'          => $fg['hex'],
            'bg'          => $bg['hex'],
        ];
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-sm-6">
            <label class="form-label fw-semibold" for="foreground">Foreground (text)</label>
            <div class="input-group">
                <span class="input-group-text p-1"><input type="color" class="form-control form-control-color border-0" value="<?= e($foreground) ?>" aria-label="Pick foreground" oninput="document.getElementById('foreground').value=this.value"></span>
                <input class="form-control font-monospace" id="foreground" name="foreground" value="<?= e($foreground) ?>" placeholder="#1a1a1a">
            </div>
        </div>
        <div class="col-sm-6">
            <label class="form-label fw-semibold" for="background">Background</label>
            <div class="input-group">
                <span class="input-group-text p-1"><input type="color" class="form-control form-control-color border-0" value="<?= e($background) ?>" aria-label="Pick background" oninput="document.getElementById('background').value=this.value"></span>
                <input class="form-control font-monospace" id="background" name="background" value="<?= e($background) ?>" placeholder="#ffffff">
            </div>
        </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Check contrast</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4 overflow-hidden">
        <div class="p-4 text-center" style="color: <?= e($result['fg']) ?>; background-color: <?= e($result['bg']) ?>;">
            <div class="h3 mb-1">Sample text</div>
            <div class="mb-0">The quick brown fox jumps over the lazy dog.</div>
            <div class="small mt-2"><strong style="font-size: 1.15rem;">Large bold sample (18pt+)</strong></div>
        </div>
        <div class="card-body border-top">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <div class="text-body-secondary small">Contrast ratio</div>
                    <div class="display-6 fw-semibold mb-0"><?= e($result['ratio_text']) ?></div>
                </div>
                <?= clip($result['ratio_text'], 'Copy ratio') ?>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Standard</th>
                            <th scope="col">Normal text</th>
                            <th scope="col">Large text</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row" class="fw-normal">WCAG AA <span class="text-body-secondary small">(4.5:1 / 3:1)</span></th>
                            <td><?= contrast_badge($result['aa_normal']) ?></td>
                            <td><?= contrast_badge($result['aa_large']) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="fw-normal">WCAG AAA <span class="text-body-secondary small">(7:1 / 4.5:1)</span></th>
                            <td><?= contrast_badge($result['aaa_normal']) ?></td>
                            <td><?= contrast_badge($result['aaa_large']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
