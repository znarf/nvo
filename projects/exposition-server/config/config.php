<?php
/**
 * Copyright Netvibes 2006-2009.
 * This file is part of Exposition PHP Server.
 * 
 * Exposition PHP Server is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Exposition PHP Server is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with Exposition PHP Server. If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Configuration file.
 */

// Be strict or be dead
error_reporting(E_ALL | E_STRICT);

// Active debugging output ?
if (false === defined('DEBUG')) {
    define('DEBUG', false);
}

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
