<?php
/* The foot of a tool page: the explainer the row carries, then the tags
   tool.php left open and the footer.

   The explainer is markdown and the rows are written so its first line is a
   heading — that line becomes the summary a visitor clicks, and the rest
   stays folded away until they ask for it. Keeping it in the page rather than
   behind a fetch means search engines read it without running anything.
   Parsedown comes from the autoloader init.php pulls in. */

if (trim((string) $tool['info']) !== '') {
    $lines = preg_split('/\R/', trim($tool['info']), 2);
    $summary = trim(ltrim($lines[0], "# \t"));
    $body = isset($lines[1]) ? (new Parsedown())->text($lines[1]) : '';
    ?>
    <details class="mt-5 bg-body border rounded-4 p-4">
        <summary class="fw-semibold text-primary">
            <?= icon('fa-solid fa-circle-info me-1') ?> <?= e($summary) ?>
        </summary>
        <div class="pt-3 text-body-secondary">
            <?= $body ?>
        </div>
    </details>
    <?php
}
?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/footer.php';
