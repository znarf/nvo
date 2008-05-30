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
 * Defines an interface for creating a compiler.
 */
class Compiler_Factory
{
    /**
     * Widget compilers list.
     *
     * @var array
     */
    protected static $_compilers = array(
        'uwa'    => 'Compiler_Uwa',                  // UWA Rendering functionality
        'frame'  => 'Compiler_Frame',                // Frame
        'google' => 'Compiler_Google'                // Google Gadget
    );

    /**
     * Creates a compiler for the appropriate platform.
     *
     * @param  string  $environment
     * @param  Widget  $widget
     * @return Compiler A compiler for the selected platform
     */
    public static function getCompiler($environment, Widget $widget)
    {
        if (isset(self::$_compilers[$environment])) {
            $classFile = str_replace('_', DIRECTORY_SEPARATOR, self::$_compilers[$environment]) . '.php';
            require_once $classFile;
            $class = ucfirst(self::$_compilers[$environment]);

            if (class_exists($class)) {
                return new $class($widget);
            }
        }

        // Exception
        throw new Exception('Unsupported platform.');
    }
}
