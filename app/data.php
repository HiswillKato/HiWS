<?php
declare(strict_types=1);

/* =========================================================================
   Reads. Every query the site makes lives here, so a page never writes SQL
   and a change to the schema touches one file.

   The tools reads arrive with the tools pages, so nothing sits here unused.
   ========================================================================= */

/* The projects shown on the home page. Only the rows with status = 1 are
   public, which is how a project that is still being built is hidden without
   deleting its row. */
function projects(): array
{
    return db()->query(
        'SELECT name, description, url, icon FROM projects WHERE status = 1 ORDER BY id'
    )->fetch_all(MYSQLI_ASSOC);
}
