<?php
/**
 * Copyright Netvibes 2006-2009.
 * This file is part of Exposition La-Distribution Package.
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


class Bootstrap
{
    protected static $_front;

    protected static $_registry;

    public static function run($dir = null)
    {
        self::prepare($dir);

        self::dispatch();
    }

    public static function getSite()
    {
        return Zend_Registry::get('site');
    }

    public static function prepare($dir)
    {
        self::$_registry = Zend_Registry::getInstance();

        self::$_registry['instance'] = self::$_registry['site']->getInstance($dir);

        self::$_front = Zend_Controller_Front::getInstance();

        self::setupRoutes();
        self::setupMvc();
        self::setupCache();
        self::setupLocales();

        if ( get_magic_quotes_gpc() ) {
            $fn = array('self', '_stripslashesDeep');
            $_GET = array_map($fn, $_GET);
            $_POST = array_map($fn, $_POST);
            $_COOKIE = array_map($fn, $_COOKIE);
            $_REQUEST = array_map($fn, $_REQUEST);
        }
    }

    public static function setupRoutes()
    {
        $config = new Zend_Config_Ini(dirname(__FILE__)  .'/routes.ini');
        $router = self::$_front->getRouter();
        $router->addConfig($config);
    }

    public static function setupCache()
    {
        $cacheDirectory = LD_TMP_DIR . '/cache/';

        Ld_Files::createDirIfNotExists($cacheDirectory);

        if (file_exists($cacheDirectory) && is_writable($cacheDirectory)) {
            $frontendOptions = array(
               'lifetime' => 60, // cache lifetime of 1 minute
               'automatic_serialization' => true
            );
            $backendOptions = array(
                'cache_dir' => $cacheDirectory
            );
            self::$_registry['cache'] = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        }
    }

    /**
     * Dispatch the request.
     */
    public static function dispatch()
    {
        self::$_front->dispatch();
    }

}
