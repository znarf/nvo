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
// Define usefull paths

define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));
define('APPLICATION_PATH', BASE_PATH . '/application');
define('CONFIG_PATH', APPLICATION_PATH . '/configs');
define('LIBRARY_PATH', BASE_PATH . '/../../libraries');
define('ZF_PATH', LIBRARY_PATH . '/ZendFramework');

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
// Application options

define('APPLICATION_ENV', 'production');
define('APPLICATION_CONFIG', CONFIG_PATH . '/application.ini');

//---------------------------------------------------------------------------
// file inclusion & autoload for ZendFramework

set_include_path( ZF_PATH . '/library' . PATH_SEPARATOR . get_include_path());

//---------------------------------------------------------------------------
// Start Zend Loader and check Zend Framework availability

if(!@include_once('Zend/Loader/Autoloader.php')) {
    trigger_error(sprintf('Unable to load Zend Framework "Zend/Loader/Autoloader.php" file with ZF_PATH as value "%s".', ZF_PATH), E_USER_ERROR);
}

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);
$autoloader->suppressNotFoundWarnings(true);

// Initialize Application Configuration and Environment
//---------------------------------------------------------------------------

$application = new Zend_Application(APPLICATION_ENV, APPLICATION_CONFIG);
$application->bootstrap();
$application->run();

/*
// Server host and base path
//---------------------------------------------------------------------------

Bootstrap::prepare();
Bootstrap::$registry->set('tmpDir', FILE_CACHE_PATH);
Bootstrap::setCache('File', array('cache_dir' => FILE_CACHE_PATH));
Bootstrap::$registry->set('proxyEndpoint', MAIN_URL . '/proxy');
Bootstrap::$registry->set('widgetEndpoint', MAIN_URL . '/widget');
Bootstrap::$registry->set('useMergedCss', false);
Bootstrap::$registry->set('uwaCssDir', MAIN_URL . '/css/');
Bootstrap::$registry->set('uwaJsDir', MAIN_URL . '/js/c/');
Bootstrap::$registry->set('uwaRessourcesDir', LIBRARY_EXPOSITION_PATH . '/ressources');
Bootstrap::run();
*/
