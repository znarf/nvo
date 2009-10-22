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
