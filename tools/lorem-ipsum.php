<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('lorem-ipsum');

/* The classic scrambled Latin, as a word bank to draw from. */
const WORDS = 'lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor '
    . 'incididunt ut labore et dolore magna aliqua enim ad minim veniam quis nostrud exercitation '
    . 'ullamco laboris nisi aliquip ex ea commodo consequat duis aute irure in reprehenderit '
    . 'voluptate velit esse cillum eu fugiat nulla pariatur excepteur sint occaecat cupidatat non '
    . 'proident sunt culpa qui officia deserunt mollit anim id est laborum';

const UNITS = ['paragraphs' => 'Paragraphs', 'sentences' => 'Sentences', 'words' => 'Words'];

/* A sentence of eight to fourteen words, opening with a capital and closing
   with a full stop. */
function sentence(array $pool): string
{
    $length = random_int(8, 14);
    $picked = [];

    for ($i = 0; $i < $length; $i++) {
        $picked[] = $pool[random_int(0, count($pool) - 1)];
    }

    return ucfirst(implode(' ', $picked)) . '.';
}

function paragraph(array $pool): string
{
    $count = random_int(3, 6);
    $sentences = [];

    for ($i = 0; $i < $count; $i++) {
        $sentences[] = sentence($pool);
    }

    return implode(' ', $sentences);
}

$amount = posted('amount');
$unit = array_key_exists(posted('unit'), UNITS) ? posted('unit') : 'paragraphs';
$lead = isset($_POST['lead']);
$result = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $amount = '3';
    $unit = 'paragraphs';
    $lead = true;
} else {
    $amount = $amount === '' ? '3' : $amount;

    if (!ctype_digit($amount) || (int) $amount < 1 || (int) $amount > 50) {
        $error = 'Choose a whole number from 1 to 50.';
    } else {
        $pool = explode(' ', WORDS);
        $count = (int) $amount;
        $parts = [];

        for ($i = 0; $i < $count; $i++) {
            $parts[] = $unit === 'sentences' ? sentence($pool) : paragraph($pool);
        }

        /* A bare word list is drawn word by word; the prose styles draw a block
           at a time and are joined with a line break or a space. */
        if ($unit === 'words') {
            $words = [];

            for ($i = 0; $i < $count; $i++) {
                $words[] = $pool[random_int(0, count($pool) - 1)];
            }

            $result = ucfirst(implode(' ', $words));
        } else {
            $result = $unit === 'paragraphs' ? implode("\n\n", $parts) : implode(' ', $parts);

            /* The opening is only put in front of prose, never a bare word list. */
            if ($lead) {
                $result = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. ' . $result;
            }
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col-sm-3">
            <label class="form-label fw-semibold" for="amount">How many</label>
            <input class="form-control" id="amount" name="amount" inputmode="numeric"
                   value="<?= e($amount === '' ? '3' : $amount) ?>">
        </div>
        <div class="col-sm-3">
            <label class="form-label fw-semibold" for="unit">Of what</label>
            <select class="form-select" id="unit" name="unit">
                <?php foreach (UNITS as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $unit === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="lead" name="lead" <?= $lead ? 'checked' : '' ?>>
                <label class="form-check-label" for="lead">Start with "Lorem ipsum dolor sit amet…"</label>
            </div>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Generate</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($result !== null && $result !== ''): ?>
    <?= output($result, 'Placeholder text', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
