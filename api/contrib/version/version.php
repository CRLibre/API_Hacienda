<?php
/*
 * Copyright (C) 2017-2020 CRLibre <https://crlibre.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
