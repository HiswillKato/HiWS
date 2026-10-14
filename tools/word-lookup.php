<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('word-lookup');

/* Wiktionary keeps an entry for a word in every language it knows, and its
   REST definition endpoint hands back the English senses already divided by
   part of speech. The entries are wiki markup, so the tags come off and the
   text is escaped again on the way out. */
const ENTRY = 'https://en.wiktionary.org/api/rest_v1/page/definition/';

$given = posted('word');
$senses = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word = trim($given);

    if (!preg_match('/^[\p{L}\p{M}\'’ -]{1,60}$/u', $word)) {
        $error = 'Enter a single word.';
    } else {
        $answer = json(ENTRY . rawurlencode($word), WAIT);
        $senses = $answer['en'] ?? [];

        if ($senses === []) {
            $error = 'No English entry for “' . $word . '”. Check the spelling, or try the plural.';
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <label class="form-label fw-semibold" for="word">Word</label>
    <div class="input-group">
        <input class="form-control" id="word" name="word" value="<?= e($given) ?>"
               placeholder="serendipity" required autofocus>
        <button class="btn btn-primary" type="submit">Look up</button>
    </div>
</form>

<?php if ($error !== ''): ?>
    <div class="alert alert-warning mt-4 mb-0">
        <?= icon('fa-solid fa-circle-exclamation me-1') ?><?= e($error) ?>
    </div>
<?php elseif ($senses !== []): ?>
    <?php foreach ($senses as $sense): ?>
        <div class="card bg-body rounded-4 border shadow-sm mt-4">
            <div class="card-header bg-transparent">
                <span class="badge bg-primary-subtle text-primary-emphasis rounded-3">
                    <?= e((string) ($sense['partOfSpeech'] ?? 'entry')) ?>
                </span>
            </div>

            <ol class="list-group list-group-flush">
                <?php foreach ($sense['definitions'] ?? [] as $definition): ?>
                    <li class="list-group-item bg-transparent">
                        <p class="mb-1">
                            <?= e(html_entity_decode(strip_tags((string) ($definition['definition'] ?? '')), ENT_QUOTES | ENT_HTML5)) ?>
                        </p>

                        <?php if (($definition['examples'] ?? []) !== []): ?>
                            <ul class="list-unstyled small text-body-secondary mb-0">
                                <?php foreach ($definition['examples'] as $example): ?>
                                    <li class="fst-italic">
                                        <?= e(html_entity_decode(strip_tags((string) $example), ENT_QUOTES | ENT_HTML5)) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    <?php endforeach; ?>

    <p class="text-body-secondary small mt-3 mb-0">
        Definitions and examples are from
        <a href="https://en.wiktionary.org/wiki/<?= e(rawurlencode(trim($given))) ?>" rel="noopener">Wiktionary</a>,
        which stays the place to go for the full entry.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
