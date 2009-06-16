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

// Server host and base path
$host = $_SERVER['HTTP_HOST'];
$dirname = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $dirname == '/' ? '' : $dirname;

// Temporary directory
$tmpDir = dirname(__FILE__) . '/../tmp/';

// Config and bootstrapping
require_once dirname(__FILE__) . '/../config/config.php';
require_once 'Bootstrap.php';

Bootstrap::prepare();

Bootstrap::$registry->set('tmpDir', $tmpDir);
Bootstrap::setCache('File', array('cache_dir' => $tmpDir));

Bootstrap::$registry->set('proxyEndpoint', 'http://' . $host . $basePath . '/../proxy');
Bootstrap::$registry->set('widgetEndpoint', 'http://' . $host . $basePath);

Bootstrap::$registry->set('uwaCssDir', 'http://' . $host . $basePath . '/../css/');
Bootstrap::$registry->set('uwaJsDir', 'http://' . $host . $basePath . '/../js/c/');
Bootstrap::$registry->set('useMergedCss', false);

Bootstrap::$registry->set('uwaRessourcesDir', dirname(__FILE__) . '/../ressources/');

Bootstrap::run();
