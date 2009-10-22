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

//---------------------------------------------------------------------------
// Set PHP Errors Reporting

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'on');

//---------------------------------------------------------------------------
// Locale settings

ini_set('mbstring.internal_encoding', 'utf-8');
ini_set('mbstring.script_encoding', 'utf-8');
date_default_timezone_set('Europe/Paris');

//---------------------------------------------------------------------------
// Define usefull paths

define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));

define('APPLICATION_PATH', BASE_PATH . '/application');
define('FILE_CACHE_PATH', BASE_PATH . '/tmp');
define('LIBRARY_PATH', BASE_PATH . '/../../libraries');
define('LIBRARY_EXPOSITION_PATH', LIBRARY_PATH . '/Exposition');
define('LIBRARY_ZENDFRAMEWORK_PATH', LIBRARY_PATH . '/ZendFramework');

//---------------------------------------------------------------------------
// External variable env

define('BASE_URL', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : trim(`hostname -f`)));
define('BASE_URL_SCHEME', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://'));
define('MAIN_URL', BASE_URL_SCHEME . BASE_URL);

//---------------------------------------------------------------------------
// Debug options

define('DEBUG_REMOTE_TOKEN', 'debug_me');
define('DEBUG_ENABLE', (isset($_POST[DEBUG_REMOTE_TOKEN]) || isset($_GET[DEBUG_REMOTE_TOKEN]) || isset($_COOKIE[DEBUG_REMOTE_TOKEN]) ? true : false));
define('DEBUG', true);

//---------------------------------------------------------------------------
// file inclusion & autoload

set_include_path(

    // frameworks
    LIBRARY_ZENDFRAMEWORK_PATH . '/library' . PATH_SEPARATOR .
    LIBRARY_EXPOSITION_PATH . '/php/src' .  PATH_SEPARATOR .

    // load others lib
    LIBRARY_PATH . PATH_SEPARATOR .

    get_include_path()
);

