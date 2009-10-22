<?php
/**
 * Copyright Netvibes 2006-2009.
 * This file is part of Exposition PHP Lib.
 *
 * Exposition PHP Lib is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exposition PHP Lib is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Exposition PHP Lib.  If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Exposition Loader.
 */
class Exposition
{
    /**
     *
     */
    protected static $_defaultConfig = array(

        'inlineWidgets' => false,

        'compiler'  => array(
            'tmpPath'   => '/tmp/',
            'cache'     => array(),
        ),

        'endpoint'  => array(
            'proxy'     => 'http://nvmodules.netvibes.com/proxy',
            'widget'    => 'http://nvmodules.netvibes.com/proxy',
            'js'        => 'http://cdn.netvibes.com/js/c',
            'css'       => 'http://cdn.netvibes.com//themes/exposition-blueberry/',
        ),

        'js'    => array(
            'version'       => 'preview3',
            'compressed'    => true,
        ),

        'css'   => array(
            'version'       => 'preview3',
            'compressed'    => true,
        ),

    );

    /**
     *
     */
    protected static $_config = array();

    /**
     * Set config of current bean instance
     *
     * @param void $config can be an array, an Zend_Config instance of a filename
     * @param void $environment config environment value (e.g production, staging, development,...)
     *
     * @return object Zend_Config instance
     */
    public function setConfig($config, $environment = null)
    {
        if (is_string($config)) {
            $config = $this->_loadConfigFromFile($config, $environment);
        } elseif ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } else if (!is_array($config)) {
            throw new Exposition_Exception('Invalid config provided; must be location of config file, a config object, or an array');
        }

        // merge with default and set has current
        self::$_config = array_merge(self::$_defaultConfig, $config);

        return self::$_config;
    }

    /**
     * Load config from file
     *
     * @param void $file config file path
     * @param void $environment config environment value (e.g production, staging, development,...)
     *
     * @return object Zend_Config instance
     */
    protected function _loadConfigFromFile($file, $environment)
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'ini':
                $config = new Zend_Config_Ini($file, $environment);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($file, $environment);
                break;

            case 'php':
            case 'inc':
                $config = include $file;
                if (!is_array($config)) {
                    throw new Exposition_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
                break;

            default:
                throw new Exposition_Exception('Invalid configuration file provided; unknown config type');
        }

        return $config->toArray();;
    }

    public static function getConfig($key, $value)
    {
    }
}

