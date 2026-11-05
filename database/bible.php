<?php
declare(strict_types=1);

/* =========================================================================
   database/bible.php — fills the `bible` table.

       php database/bible.php

   The King James Version is in the public domain, which is what lets this
   site publish it. eBible.org hosts the text as a "verse per line" archive:
   every line reads `GEN 1:1 In the beginning...`. The script downloads the
   zip (about 5 MB), reads the text out of it and writes one row per verse —
   around 31,000 rows in a few seconds.

   Nothing is stored between runs, and the script is safe to run again,
   because a translation is deleted before it is written back.

   It reaches the network for the archive, unless you already have it — put
   the .zip file, or the .txt file extracted from it, in one folder
   and name that folder:

       php database/bible.php /path/to/folder

   The archives also carry the Apocrypha (TOB, JDT, WIS …). This site's canon
   is 66 books, so those lines are skipped on the way past.
   ========================================================================= */

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/functions.php';

/* The 66 books in canon order. Two things are read off this list: the USFM
   code each archive leads a line with, and the name the site shows the book
   under. The order itself is the third — a code's position in it, plus one,
   is the book's `number`, which is how every passage is looked up. */
const BOOKS = [
    'GEN' => 'Genesis',        'EXO' => 'Exodus',       'LEV' => 'Leviticus',
    'NUM' => 'Numbers',        'DEU' => 'Deuteronomy',  'JOS' => 'Joshua',
    'JDG' => 'Judges',         'RUT' => 'Ruth',         '1SA' => '1 Samuel',
    '2SA' => '2 Samuel',       '1KI' => '1 Kings',      '2KI' => '2 Kings',
    '1CH' => '1 Chronicles',   '2CH' => '2 Chronicles', 'EZR' => 'Ezra',
    'NEH' => 'Nehemiah',       'EST' => 'Esther',       'JOB' => 'Job',
    'PSA' => 'Psalms',         'PRO' => 'Proverbs',     'ECC' => 'Ecclesiastes',
    'SOL' => 'Song of Solomon','ISA' => 'Isaiah',       'JER' => 'Jeremiah',
    'LAM' => 'Lamentations',   'EZE' => 'Ezekiel',      'DAN' => 'Daniel',
    'HOS' => 'Hosea',          'JOE' => 'Joel',         'AMO' => 'Amos',
    'OBA' => 'Obadiah',        'JON' => 'Jonah',        'MIC' => 'Micah',
    'NAH' => 'Nahum',          'HAB' => 'Habakkuk',     'ZEP' => 'Zephaniah',
    'HAG' => 'Haggai',         'ZEC' => 'Zechariah',    'MAL' => 'Malachi',
    'MAT' => 'Matthew',        'MAR' => 'Mark',         'LUK' => 'Luke',
    'JOH' => 'John',           'ACT' => 'Acts',         'ROM' => 'Romans',
    '1CO' => '1 Corinthians',  '2CO' => '2 Corinthians','GAL' => 'Galatians',
    'EPH' => 'Ephesians',      'PHI' => 'Philippians',  'COL' => 'Colossians',
    '1TH' => '1 Thessalonians','2TH' => '2 Thessalonians','1TI' => '1 Timothy',
    '2TI' => '2 Timothy',      'TIT' => 'Titus',        'PHM' => 'Philemon',
    'HEB' => 'Hebrews',        'JAM' => 'James',        '1PE' => '1 Peter',
    '2PE' => '2 Peter',        '1JO' => '1 John',       '2JO' => '2 John',
    '3JO' => '3 John',         'JUD' => 'Jude',         'REV' => 'Revelation',
];

/* Translation key (a key of BIBLE_VERSIONS) => the eBible archive name. One
   entry per translation the site publishes; the site publishes one. */
const SOURCES = ['KJV' => 'eng-kjv'];

/* Pull one text out of an eBible archive. open() answers true on success and
   an error code otherwise, so it is compared against true — there is no
   ZipArchive::OK constant to name, which is what this used to test for and
   why the importer stopped at the first download. */
function unzip(string $file, string $stem): string
{
    $zip = new ZipArchive();

    if ($zip->open($file) !== true) {
        throw new RuntimeException($file . ' is not a zip archive');
    }

    $text = $zip->getFromName($stem . '_vpl.txt');
    $zip->close();

    if (!is_string($text)) {
        throw new RuntimeException($stem . '_vpl.txt was missing from ' . $file);
    }

    /* A byte order mark, if the archive carries one, would end up in Genesis. */
    return str_starts_with($text, "\xEF\xBB\xBF") ? substr($text, 3) : $text;
}

/* One translation's text: from $folder when it holds a copy, from eBible.org
   otherwise. */
function archive(string $stem, ?string $folder): string
{
    if ($folder !== null) {
        $folder = rtrim($folder, '/');
        $plain = $folder . '/' . $stem . '_vpl.txt';

        if (is_file($plain)) {
            echo '  reading ' . $plain . PHP_EOL;

            return file_get_contents($plain);
        }

        $zip = $folder . '/' . $stem . '_vpl.zip';

        if (is_file($zip)) {
            echo '  reading ' . $zip . PHP_EOL;

            return unzip($zip, $stem);
        }
    }

    $url = 'https://ebible.org/Scriptures/' . $stem . '_vpl.zip';
    echo '  fetching ' . $url . PHP_EOL;

    $body = fetch($url, 180);

    if ($body === null) {
        throw new RuntimeException('could not download ' . $url);
    }

    $file = tempnam(sys_get_temp_dir(), 'bible');

    file_put_contents($file, $body);

    try {
        return unzip($file, $stem);
    } finally {
        unlink($file);
    }
}

/* One translation, written to the table. Returns [verses, chapters, books]. */
function store(string $version, string $archive): array
{
    $clear = db()->prepare('DELETE FROM bible WHERE version = ?');
    $clear->bind_param('s', $version);
    $clear->execute();

    $place = db()->prepare(
        'INSERT INTO bible (version, number, book, chapter, verse, text) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $place->bind_param('sisiis', $version, $number, $book, $chapter, $verse, $text);

    $order = array_flip(array_keys(BOOKS));
    $verses = 0;
    $chapters = [];
    $seen = [];

    db()->begin_transaction();

    foreach (preg_split('/\R/', $archive) as $line) {
        if (!preg_match('/^([A-Z0-9]{3}) (\d+):(\d+) (.+)$/', $line, $found)) {
            continue;
        }

        if (!isset($order[$found[1]])) {
            continue;                   // the Apocrypha, which this site does not show
        }

        $number = $order[$found[1]] + 1;
        $book = BOOKS[$found[1]];
        $chapter = (int) $found[2];
        $verse = (int) $found[3];

        /* The KJV marks a printed paragraph with a pilcrow at the start of a
           verse. A printed page wants it; a web reader does not, and this is
           the one tidy-up either archive needs. */
        $text = preg_replace('/^¶\s*/u', '', trim($found[4]));

        $place->execute();

        $verses++;
        $seen[$number] = true;
        $chapters[$number . ':' . $chapter] = true;
    }

    db()->commit();

    return [$verses, count($chapters), count($seen)];
}

/* An optional folder holding the archives, so a machine without a way out to
   the network can still be given the text. */
$folder = $argv[1] ?? null;

echo 'Loading ' . count(SOURCES) . ' translation' . (count(SOURCES) === 1 ? '' : 's') . " into `bible` …" . PHP_EOL;

try {
    foreach (SOURCES as $version => $stem) {
        [$verses, $chapters, $books] = store($version, archive($stem, $folder));

        printf(
            "  %-4s %6d verses  %4d chapters  %2d books%s",
            $version,
            $verses,
            $chapters,
            $books,
            PHP_EOL
        );
    }
} catch (Throwable $problem) {
    fwrite(STDERR, PHP_EOL . 'Stopped: ' . $problem->getMessage() . PHP_EOL);
    exit(1);
}

echo 'Done. The text is public domain, from eBible.org.' . PHP_EOL;
