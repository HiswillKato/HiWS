<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('subtitle-converter');

function vtt_time_to_srt(string $t): string
{
    $t = trim(str_replace('.', ',', $t));
    /* VTT may be mm:ss.mmm; SRT needs hh:mm:ss,mmm */
    $parts = explode(':', $t);
    if (count($parts) === 2) { $t = '00:' . $t; }
    if (!str_contains($t, ',')) { $t .= ',000'; }
    return $t;
}

function srt_time_to_vtt(string $t): string
{
    return str_replace(',', '.', trim($t));
}

function vtt_to_srt(string $vtt): string
{
    $vtt = preg_replace('/^\xEF\xBB\xBF/', '', $vtt) ?? $vtt;
    $vtt = str_replace(["\r\n", "\r"], "\n", $vtt);
    $vtt = preg_replace('/^WEBVTT[^\n]*\n+/i', '', $vtt) ?? $vtt;
    /* Remove NOTE/STYLE/REGION blocks */
    $vtt = preg_replace('/^(NOTE|STYLE|REGION)(\n| .*\n)(.*\n)*?\n/m', '', $vtt) ?? $vtt;
    $blocks = preg_split('/\n\n+/', trim($vtt)) ?: [];
    $out = [];
    $i = 1;
    foreach ($blocks as $block) {
        $lines = explode("\n", trim($block));
        if ($lines === []) { continue; }
        /* drop cue id if present */
        if (!str_contains($lines[0], '-->') && isset($lines[1]) && str_contains($lines[1], '-->')) {
            array_shift($lines);
        }
        if (!isset($lines[0]) || !str_contains($lines[0], '-->')) { continue; }
        $timing = preg_replace_callback('/([0-9:.]+)/', static fn($m) => vtt_time_to_srt($m[1]), $lines[0]) ?? $lines[0];
        $timing = preg_replace('/\s+.*/', '', $timing) ?? $timing; // strip cue settings from end if mangled
        /* better: rebuild timing line */
        if (preg_match('/([0-9:.]+)\s*-->\s*([0-9:.]+)/', $lines[0], $m)) {
            $timing = vtt_time_to_srt($m[1]) . ' --> ' . vtt_time_to_srt($m[2]);
        }
        $text = implode("\n", array_slice($lines, 1));
        $text = preg_replace('/<\/?[^>]+>/', '', $text) ?? $text; // strip simple tags
        $out[] = $i++ . "\n" . $timing . "\n" . $text;
    }
    return implode("\n\n", $out) . ($out ? "\n" : '');
}

function srt_to_vtt(string $srt): string
{
    $srt = preg_replace('/^\xEF\xBB\xBF/', '', $srt) ?? $srt;
    $srt = str_replace(["\r\n", "\r"], "\n", $srt);
    $blocks = preg_split('/\n\n+/', trim($srt)) ?: [];
    $out = ["WEBVTT", ""];
    foreach ($blocks as $block) {
        $lines = explode("\n", trim($block));
        if ($lines === []) { continue; }
        if (ctype_digit(trim($lines[0]))) { array_shift($lines); }
        if (!isset($lines[0]) || !str_contains($lines[0], '-->')) { continue; }
        if (preg_match('/([0-9:,]+)\s*-->\s*([0-9:,]+)/', $lines[0], $m)) {
            $lines[0] = srt_time_to_vtt($m[1]) . ' --> ' . srt_time_to_vtt($m[2]);
        }
        $out[] = implode("\n", $lines);
        $out[] = '';
    }
    return implode("\n", $out);
}

$given = posted('text');
$direction = posted('direction') ?: 'vtt-to-srt';
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($given) === '') {
        $error = 'Paste subtitle content.';
    } elseif (strlen($given) > 500000) {
        $error = 'Subtitle content is too large (limit 500,000 characters).';
    } else {
        $output = $direction === 'srt-to-vtt' ? srt_to_vtt($given) : vtt_to_srt($given);
        if (trim($output) === '' || trim($output) === 'WEBVTT') {
            $error = 'Could not find subtitle cues in that input. Check the format and direction.';
            $output = '';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="direction">Direction</label>
        <select class="form-select" id="direction" name="direction">
            <option value="vtt-to-srt"<?= $direction === 'vtt-to-srt' ? ' selected' : '' ?>>VTT to SRT</option>
            <option value="srt-to-vtt"<?= $direction === 'srt-to-vtt' ? ' selected' : '' ?>>SRT to VTT</option>
        </select>
    </div>
    <label class="form-label fw-semibold" for="text">Subtitles</label>
    <textarea class="form-control font-monospace" id="text" name="text" rows="12" autofocus
              placeholder="WEBVTT&#10;&#10;00:00:01.000 --> 00:00:03.000&#10;Hello world"><?= e($given) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Convert</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, $direction === 'vtt-to-srt' ? 'SRT' : 'VTT', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
