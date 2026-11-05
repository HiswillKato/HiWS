<?php
/* The strip every Bible page carries: the reader's four sections, and the
   translation being read. Required after header.php, like a second masthead.

   The switcher is a bare query link, so it keeps the address it is clicked on
   — a reader looking at John 3:16 in one translation changes to the other
   without losing the passage. translation() does the remembering. With one
   translation in BIBLE_VERSIONS there is nothing to switch between, so the
   buttons stay out of the way and the label is printed as a word instead. */

$version = translation();

/* Order is the order a reader moves through them. */
$sections = [
    'index'  => ['Read', url('bible')],
    'search' => ['Search', url('bible/search')],
    'facts'  => ['Facts', url('bible/facts')],
    'jics'   => ['Coming soon', url('bible/jics')],
];

/* A book or a chapter is still "Read", even though its script is read.php. */
$script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '.php');
$active = $script === 'read' ? 'index' : $script;

/* The switcher keeps the address it is clicked on, so a reader in the middle
   of a chapter, or a search, changes translation without losing their place.
   REQUEST_URI is the address as typed: its query is the visitor's own, not the
   one a rewrite added. */
$several = count(BIBLE_VERSIONS) > 1;

[$path, $given] = array_pad(explode('?', (string) ($_SERVER['REQUEST_URI'] ?? '/'), 2), 2, '');
parse_str($given, $kept);
unset($kept['v']);
?>
<div class="bg-body border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 py-2">
            <ul class="nav nav-pills">
                <?php /* Walked by key: the parts sit above a page's own content,
                     and a loop that spread a list into ordinary variables would
                     leave the page's own holding the last one it read. */ ?>
                <?php foreach (array_keys($sections) as $key): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $key === $active ? ' active' : '' ?>"
                           href="<?= e($sections[$key][1]) ?>">
                            <?= e($sections[$key][0]) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($several): ?>
                <div class="btn-group btn-group-sm" role="group" aria-label="Translation">
                    <?php foreach (BIBLE_VERSIONS as $key => $label): ?>
                        <a class="btn btn-outline-secondary<?= $key === $version ? ' active' : '' ?>"
                           href="<?= e($path . '?' . http_build_query($kept + ['v' => $key])) ?>"
                           title="<?= e($label) ?>"><?= e($key) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php /* One translation: the name is a fact, not a choice. */ ?>
                <span class="text-body-secondary small fw-semibold"
                      title="<?= e(BIBLE_VERSIONS[$version]) ?>"><?= e($version) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
