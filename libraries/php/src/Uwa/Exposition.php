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
class Uwa_Exposition
{
    public static function load()
    {
        // Netvibes constants
        $constants = array(
            'EXPOSITION' => dirname(__FILE__),
            'LIB'        => dirname(__FILE__) . '/..',
            'NV_HOST'    => 'www.netvibes.com',
            'NV_STATIC'  => 'cdn.netvibes.com',
            'NV_MODULES' => 'nvmodules.netvibes.com',
            'NV_AVATARS' => 'avatars.netvibes.com',
            'NV_REST'    => 'rest.netvibes.com',
            'NV_ECO'     => 'eco.netvibes.com'
        );
        foreach ($constants as $name => $value) {
            if (false === defined($name)) {
                define($name, $value);
                if ($name == 'EXPOSITION' || $name == 'LIB') {
                    set_include_path($value . PATH_SEPARATOR . get_include_path());
                }
            }
        }

        // Zend Loader
        require_once 'Zend/Loader.php';
        Zend_Loader::registerAutoload();

        // Exposition Registry Values
        $registryDefaultValues = array(
            'jsVersion'       => 'preview3',
            'cssVersion'      => 'preview3',
            'useCompressedJs' => true,
            'useMergedCss'    => true,
            'inlineWidgets'   => false,
            'uwaJsDir'        => 'http://' . NV_STATIC  . '/js/c/',
            'uwaCssDir'       => 'http://' . NV_STATIC  . '/themes/exposition-blueberry/',
            'uwaImgDir'       => 'http://' . NV_STATIC  . '/img/',
            'proxyEndpoint'   => 'http://' . NV_MODULES . '/proxy',
            'widgetEndpoint'  => 'http://' . NV_MODULES . '/widget',
            'tmpDir'          => '/tmp/'
        );
        foreach ($registryDefaultValues as $key => $value) {
            if (!Zend_Registry::isRegistered($key)) {
                Zend_Registry::set($key, $value);
            }
        }
    }
}
