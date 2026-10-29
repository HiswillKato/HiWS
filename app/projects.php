<?php
declare(strict_types=1);

/* =========================================================================
   THE PROJECTS — which ones have a home of their own.

   Each name matches a row in projects.url and a folder under the docroot:
   "tools" is both /var/www/hiws/tools and https://tools.hiws.dev. url() reads
   this list, so a project address works from any page, and here() uses it to
   mark the right navigation item on a subdomain.

   This lives here rather than in config.php because the server keeps its own
   config.php out of git. A list the code depends on has to ship with the code.
   Add a name when its folder and its vhost name exist.
   ========================================================================= */

const PROJECTS = ['tools', 'bible', 'sifm'];
