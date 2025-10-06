<?php
/*
 * Copyright (C) 2017-2025 CRLibre <https://crlibre.org>
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

function doTest()
{
    return "Test :)";
}

function uploadCert()
{
    modules_loader("files");
    $dets = files_upload("hacienda", false, "p12");
    return $dets;
}

function uploadXml()
{
    modules_loader("files");
    $dets = files_upload("hacienda", false, "xml");
    return $dets;
}
