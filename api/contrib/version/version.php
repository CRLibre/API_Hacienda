<?php

/*
 * Copyright (C) 2018 CRLibre <https://crlibre.org>
 * Licensed under the GNU Affero General Public 3.0 License
 * https://www.gnu.org/licenses/agpl-3.0.en.html
 */

function version_API()
{
   $commit = exec('git describe --long --match init --abbrev=7');
   $commit = trim($commit);
   if (strlen($commit) == 17)
   {
       $commit = preg_replace("/init\-([0-9]+)\-g/", "", $commit);
       if (strlen($commit) == 7)
            return "Version: {$commit}";
   }
   else if (file_exists(__DIR__ . '/VERSION'))
   {
       $commit  = file_get_contents(__DIR__ . '/VERSION');
       $commit = trim($commit);
       if (strlen($commit) == 7)
           return "Version: {$commit}";
   }

   return "No tiene soporte git.";
}

?>
