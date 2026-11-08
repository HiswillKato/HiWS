<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('random-word-generator');

$words = [
    'ability','about','above','accept','across','action','active','actual','adapt','address','advice','affect','after','again','agent','agree','ahead','allow','almost','alone','along','already','amount','animal','answer','appear','apple','apply','area','argue','around','arrive','artist','aspect','assist','assume','attack','attend','author','autumn','avoid','awake','balance','basic','beauty','become','before','begin','behind','belief','belong','better','beyond','border','bottle','bottom','branch','breath','bridge','bright','bring','broken','budget','build','butter','button','camera','cancer','carbon','career','castle','catch','cause','center','chance','change','charge','check','choice','choose','circle','class','clean','clear','climb','clock','close','cloud','coach','coast','color','common','cookie','corner','cotton','count','course','create','credit','crisis','custom','dance','decide','deeply','define','degree','depend','design','detail','detect','device','differ','dinner','direct','doctor','double','dragon','dream','driver','during','easily','effect','effort','either','emerge','enable','energy','engine','enjoy','enough','ensure','entire','escape','estate','ethics','evening','event','every','expert','factor','family','famous','father','fellow','figure','final','finish','flight','flower','follow','forest','forget','formal','format','former','friend','future','garden','gather','gentle','global','golden','ground','growth','guard','handle','happen','harbor','health','hidden','honest','impact','income','indeed','inform','inside','island','itself','jacket','junior','keeper','kindly','ladder','launch','leader','learn','lesson','letter','likely','listen','little','living','local','lovely','manage','manner','market','master','matter','member','memory','mental','method','middle','minute','mirror','mobile','modern','moment','mostly','mother','motion','mountain','museum','narrow','nation','native','nature','nearby','nearly','nobody','normal','notice','number','object','office','online','option','orange','origin','parent','partly','people','period','person','phrase','planet','player','please','plenty','pocket','poetry','policy','prefer','pretty','price','pride','prince','public','purple','puzzle','quiet','rather','reader','really','reason','recent','record','reduce','region','relate','relief','remain','remote','remove','repair','repeat','report','rescue','result','return','reveal','review','reward','riddle','river','rocket','safety','sample','school','screen','search','season','second','secret','select','senior','series','settle','shadow','signal','silent','silver','simple','single','sister','smooth','social','source','speech','spirit','spring','square','stable','stream','street','strong','studio','sudden','summer','supply','symbol','system','talent','target','temple','thanks','theory','thirty','thread','ticket','timber','tissue','travel','treaty','twelve','unique','update','useful','valley','value','vendor','vision','visual','volume','wander','wealth','window','winter','wisdom','wonder','writer','yellow'
];

$count = posted('count') !== '' ? posted('count') : '10';
$case = posted('case') ?: 'lower';
$sep = posted('sep') ?: 'space';
$error = '';
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!ctype_digit($count) || (int) $count < 1 || (int) $count > 500) {
        $error = 'Choose a whole number from 1 to 500.';
    } else {
        $picked = [];
        $max = count($words) - 1;
        for ($i = 0; $i < (int) $count; $i++) {
            $w = $words[random_int(0, $max)];
            $w = match ($case) {
                'upper' => strtoupper($w),
                'title' => ucfirst($w),
                default => $w,
            };
            $picked[] = $w;
        }
        $join = match ($sep) {
            'comma' => ', ',
            'newline' => "\n",
            default => ' ',
        };
        $output = implode($join, $picked);
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="count">How many</label>
            <input class="form-control font-monospace" id="count" name="count" value="<?= e($count) ?>" inputmode="numeric">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="case">Case</label>
            <select class="form-select" id="case" name="case">
                <option value="lower"<?= $case === 'lower' ? ' selected' : '' ?>>lowercase</option>
                <option value="title"<?= $case === 'title' ? ' selected' : '' ?>>Title Case</option>
                <option value="upper"<?= $case === 'upper' ? ' selected' : '' ?>>UPPERCASE</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold" for="sep">Separator</label>
            <select class="form-select" id="sep" name="sep">
                <option value="space"<?= $sep === 'space' ? ' selected' : '' ?>>Space</option>
                <option value="comma"<?= $sep === 'comma' ? ' selected' : '' ?>>Comma and space</option>
                <option value="newline"<?= $sep === 'newline' ? ' selected' : '' ?>>New line</option>
            </select>
        </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Generate words</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($output !== ''): ?>
    <?= output($output, 'Random words', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
