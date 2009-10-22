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
// Config and bootstrapping
require_once dirname(__FILE__) . '/../config/config.php';
require_once APPLICATION_PATH . '/Bootstrap.php';

//---------------------------------------------------------------------------
// Start Zend Loader and check Zend Framework availability

if(!@include_once('Zend/Loader/Autoloader.php')) {
    trigger_error(sprintf('Unable to load Zend Framework "Zend/Loader/Autoloader.php" file with LIBRARY_ZENDFRAMEWORK_PATH as value "%s".', LIBRARY_ZENDFRAMEWORK_PATH), E_USER_ERROR);
}

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);
$autoloader->suppressNotFoundWarnings(true);


// Server host and base path
$host = $_SERVER['HTTP_HOST'];
$dirname = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $dirname == '/' ? '' : $dirname;

// Temporary directory
$tmpDir = dirname(__FILE__) . '/../tmp/';



Bootstrap::prepare();

Bootstrap::$registry->set('tmpDir', $tmpDir);
Bootstrap::setCache('File', array('cache_dir' => $tmpDir));

Bootstrap::$registry->set('proxyEndpoint', 'http://' . $host .'/proxy');
Bootstrap::$registry->set('widgetEndpoint', 'http://' . $host . $basePath);

Bootstrap::$registry->set('uwaCssDir', 'http://' . $host . '/css/');
Bootstrap::$registry->set('uwaJsDir', 'http://' . $host . '/js/c/');
Bootstrap::$registry->set('useMergedCss', false);

Bootstrap::$registry->set('uwaRessourcesDir', dirname(__FILE__) . '/../ressources/');

Bootstrap::run();
