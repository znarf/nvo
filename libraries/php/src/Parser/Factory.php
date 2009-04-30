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
 * Defines an interface for creating a parser.
 */
class Parser_Factory
{
    /**
     * Creates a parser of the appropriate type.
     *
     * @param  string  $type  The skeleton type
     * @param  string  $url   The skeleton URL
     * @param  boolean $cache Either to use the cache or not
     * @return Parser A parser for the specified skeleton
     */
    public static function getParser($type, $url, $cache = true)
    {
        switch ($type) {
            case 'uwa':
                // UWA XHTML Widget
                require_once 'Parser/Uwa.php';
                return new Parser_Uwa($url, $cache);
                break;
            default:
                // Exception
                throw new Exception('Unrecognized type.');
                break;
        }
    }
}
