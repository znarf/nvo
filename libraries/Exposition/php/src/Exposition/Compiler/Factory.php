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
 * Defines an interface for creating a compiler.
 */
class Exposition_Compiler_Factory
{
    /**
     * Known compilers list.
     *
     * @var array
    */
    protected static $_compilers = array(
        'Uwa'        => 'Exposition_Compiler_Uwa',
        'Frame'      => 'Exposition_Compiler_Frame',
        'Google'     => 'Exposition_Compiler_Google',
        'Live'       => 'Exposition_Compiler_Live',
        'Opera'      => 'Exposition_Compiler_Desktop_Opera',
        'Dashboard'  => 'Exposition_Compiler_Desktop_Dashboard',
        'Screenlets' => 'Exposition_Compiler_Desktop_Screenlets',
        'Jil'        => 'Exposition_Compiler_Desktop_Jil',
        'Vista'      => 'Exposition_Compiler_Desktop_Vista',

    );

    /**
     * Creates a compiler for the appropriate platform.
     *
     * @param  string  $environment
     * @param  Widget  $widget
     * @return Compiler A compiler for the selected platform
     */
    public static function getCompiler($environment, Exposition_Widget $widget, array $options = array())
    {
        if (isset(self::$_compilers[$environment])) {
            $class = self::$_compilers[$environment];
        } else {
            $class = 'Exposition_Compiler_' . ucfirst($environment);
        }

        try {
            return new $class($widget, $options);
        } catch(Exception $e) {
            throw new Exposition_Exception('Unsupported platform : ' . $environment);
        }
    }
}
