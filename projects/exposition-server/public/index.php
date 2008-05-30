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


// Server host and base path
$host = $_SERVER['HTTP_HOST'];
$basePath = dirname($_SERVER['REQUEST_URI']);

// Config and bootstrapping
require_once dirname(__FILE__) . '/config/config.php';
require_once 'Bootstrap.php';

Bootstrap::prepare();

Bootstrap::setCache('File', array('cache_dir' => dirname(__FILE__) . '/../tmp/'));

Bootstrap::$registry->set('proxyEndpoint', 'http://' . $host . $basePath . '/proxy');
Bootstrap::$registry->set('widgetEndpoint', 'http://' . $host . $basePath . '/widget');

Bootstrap::run();
