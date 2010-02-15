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


require_once 'Exposition/Compiler.php';

/**
 * UWA compilation utilities.
 */
class Exposition_Compiler_Iphone  extends Exposition_Compiler_Uwa
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Standalone';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-iphone.css';
}

