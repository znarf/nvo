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
 * Bootstrapping.
 */
class Bootstrap
{
    /**
     * Front controller.
     *
     * @var Zend_Controller_Front
     */
    public static $frontController;

    /**
     * Registry.
     *
     * @var Zend_Registry
     */
    public static $registry;

    /**
     * Cache instance.
     *
     * @var Zend_Cache
     */
    public static $cacheBackend;

    /**
     * Cache backend options.
     *
     * @var array
     */
    public static $cacheBackendOptions;

    /**
     * Main bootstrapping function.
     */
    public static function run()
    {
        self::prepare();
        self::dispatch();
    }

    /**
     * Retrieves the front controller and registry instances.
     */
    public static function prepare()
    {
        require_once 'Exposition.php';
        Exposition::load();

        Zend_Layout::startMvc(array('layoutPath' => APPLICATION . '/views/layouts'));
        Zend_Layout::getMvcInstance()->disableLayout();

        self::$frontController = Zend_Controller_Front::getInstance();
        self::$frontController->setControllerDirectory(APPLICATION . '/controllers');

        self::$registry = Zend_Registry::getInstance();

        self::initCache();
    }

    /**
     * Dispatch the request.
     */
    public static function dispatch()
    {
        self::$frontController->dispatch();
    }

    /**
     * Sets the cache type and associated options.
     *
     * @param string $backend
     * @param array  $backendOptions
     */
    public static function setCache($backend, $backendOptions)
    {
        self::$cacheBackend = $backend;
        self::$cacheBackendOptions = $backendOptions;
    }

    /**
     * Initializes the cache.
     */
    public static function initCache()
    {
        if (isset(self::$cacheBackend, self::$cacheBackendOptions)) {
            $frontendOptions = array('caching' => true, 'lifetime' => 300);
            self::$registry['cache'] = Zend_Cache::factory(
                'Core', self::$cacheBackend, $frontendOptions, self::$cacheBackendOptions);
        }
    }
}
