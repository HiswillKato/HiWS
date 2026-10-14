<?php
declare(strict_types=1);

/* =========================================================================
   Reads. Every query the site makes lives here, so a page never writes SQL
   and a change to the schema touches one file.
   ========================================================================= */

/* -------------------------------------------------------------------------
   The home page
   ------------------------------------------------------------------------- */

/* The project cards. Only the rows with status = 1 are public, which is how a
   project that is still being built stays hidden without losing its row. */
function projects(): array
{
    return db()->query(
        'SELECT name, description, url, icon FROM projects WHERE status = 1 ORDER BY id'
    )->fetch_all(MYSQLI_ASSOC);
}

/* -------------------------------------------------------------------------
   /tools
   ------------------------------------------------------------------------- */

/* Every published tool, already in the order the index groups them, so the
   index needs one query rather than one per category. */
function tools(): array
{
    return db()->query(
        'SELECT title, description, url, icon, category FROM tools WHERE status = 1 ORDER BY category, title'
    )->fetch_all(MYSQLI_ASSOC);
}

/* One tool, by the name in its address. Null when the name is not one we
   publish, which is how the page answers 404 instead of fatalling. */
function tool(string $url): ?array
{
    $statement = db()->prepare(
        'SELECT title, description, keywords, icon, info FROM tools WHERE url = ? AND status = 1 LIMIT 1'
    );
    $statement->bind_param('s', $url);
    $statement->execute();

    return $statement->get_result()->fetch_assoc() ?: null;
}

/* -------------------------------------------------------------------------
   /bible
   ------------------------------------------------------------------------- */

/* The books of one translation in canon order, with what each holds — the
   index shows all three numbers. */
function books(string $translation): array
{
    $statement = db()->prepare(
        'SELECT number, book, MAX(chapter) AS chapters, COUNT(*) AS verses
           FROM bible WHERE version = ? GROUP BY number, book ORDER BY number'
    );
    $statement->bind_param('s', $translation);
    $statement->execute();

    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

/* One book, by the name taken from its address ("song-of-solomon"). The
   comparison ignores case, because an address is not capitalised, and falls
   back to a beginning-of-name match so that someone typing "psalm" into the
   search box still lands on Psalms. */
function book(string $translation, string $asked): ?array
{
    $name = str_replace('-', ' ', trim($asked));

    $statement = db()->prepare(
        'SELECT number, book FROM bible WHERE version = ? AND book = ? LIMIT 1'
    );
    $statement->bind_param('ss', $translation, $name);
    $statement->execute();

    $row = $statement->get_result()->fetch_assoc();

    if ($row !== null || mb_strlen($name) < 3) {
        return $row;
    }

    $statement = db()->prepare(
        'SELECT number, book FROM bible WHERE version = ? AND book LIKE ? ORDER BY number LIMIT 1'
    );
    $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $name) . '%';
    $statement->bind_param('ss', $translation, $like);
    $statement->execute();

    return $statement->get_result()->fetch_assoc() ?: null;
}

/* One chapter, verse by verse. */
function chapter(string $translation, int $number, int $chapter): array
{
    $statement = db()->prepare(
        'SELECT verse, text FROM bible
          WHERE version = ? AND number = ? AND chapter = ?
          ORDER BY verse'
    );
    $statement->bind_param('sii', $translation, $number, $chapter);
    $statement->execute();

    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

/* How many chapters one book has, which is what the book page lays out and
   what stops a reader stepping past the last one. */
function chapters(string $translation, int $number): int
{
    $statement = db()->prepare('SELECT MAX(chapter) FROM bible WHERE version = ? AND number = ?');
    $statement->bind_param('si', $translation, $number);
    $statement->execute();

    return (int) $statement->get_result()->fetch_row()[0];
}

/* A LIKE pattern for a search term, with any wildcard the visitor typed
   treated as an ordinary character — otherwise searching for "50%" would
   match the whole Bible. */
function pattern(string $term): string
{
    return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';
}

/* Verses containing a word or phrase, one page at a time. */
function found(string $translation, string $term, int $limit, int $offset): array
{
    $statement = db()->prepare(
        'SELECT number, book, chapter, verse, text FROM bible
          WHERE version = ? AND text LIKE ?
          ORDER BY number, chapter, verse
          LIMIT ?, ?'
    );
    $statement->bind_param('ssii', $translation, pattern($term), $offset, $limit);
    $statement->execute();

    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

/* How much of one translation falls between two book numbers — the whole
   Bible, or one testament. The numbers are counted by the database so they
   follow whichever translation is being read. */
function measure(string $translation, int $from, int $to): array
{
    $statement = db()->prepare(
        'SELECT COUNT(*) AS verses,
                COUNT(DISTINCT number) AS books,
                COUNT(DISTINCT number, chapter) AS chapters
           FROM bible WHERE version = ? AND number BETWEEN ? AND ?'
    );
    $statement->bind_param('sii', $translation, $from, $to);
    $statement->execute();

    return $statement->get_result()->fetch_assoc();
}

/* The shortest verse and the longest one, which is a pair of facts nobody
   ever guesses. */
function extremes(string $translation): array
{
    $out = [];

    /* The direction is written here rather than taken from anything a visitor
       sends, so it is never a way into the query. */
    foreach (['ASC' => 'shortest', 'DESC' => 'longest'] as $order => $key) {
        $statement = db()->prepare(
            'SELECT number, book, chapter, verse, CHAR_LENGTH(text) AS size, text
               FROM bible WHERE version = ?
              ORDER BY size ' . $order . ' LIMIT 1'
        );
        $statement->bind_param('s', $translation);
        $statement->execute();

        $out[$key] = $statement->get_result()->fetch_assoc();
    }

    return $out;
}

/* How many verses the same search finds in total, for the pager. */
function found_total(string $translation, string $term): int
{
    $statement = db()->prepare(
        'SELECT COUNT(*) FROM bible WHERE version = ? AND text LIKE ?'
    );
    $statement->bind_param('ss', $translation, pattern($term));
    $statement->execute();

    return (int) $statement->get_result()->fetch_row()[0];
}
