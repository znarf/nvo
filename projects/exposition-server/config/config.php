<?php
/**
 * Copyright (c) 2008 Netvibes (http://www.netvibes.org/).
 *
 * This file is part of Netvibes Widget Platform.
 *
 * Netvibes Widget Platform is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Netvibes Widget Platform is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Netvibes Widget Platform.  If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Configuration file.
 */

define('APPLICATION', dirname(__FILE__) . '/../application');

if (is_dir(dirname(__FILE__) . '/../lib')) {
    // Standalone Exposition Server
    define('EXPOSITION', dirname(__FILE__) . '/../lib/Exposition');
    define('LIB', dirname(__FILE__) . '/../lib');
} else if (is_dir(dirname(__FILE__) . '/../../../libraries')) {
    // SVN repository
    define('EXPOSITION', dirname(__FILE__) . '/../../../libraries/php/src');
    define('LIB', dirname(__FILE__) . '/../../../libraries/php/lib');
} else {
    throw new Exception('The PHP librairies could not be found.');
}

// Include Path
set_include_path(APPLICATION . PATH_SEPARATOR . EXPOSITION . PATH_SEPARATOR . LIB . PATH_SEPARATOR . get_include_path());
