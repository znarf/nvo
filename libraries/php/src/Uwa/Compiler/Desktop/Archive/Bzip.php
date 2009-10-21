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
 * Compiler Desktop Archive Builder for Bzip Format.
 */
class Uwa_Compiler_Desktop_Archive_Bzip extends Compiler_Desktop_Archive_Tar
{
    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/x-bzip2';

    /**
     * Build archive for current format
     */
    protected function buildArchive()
    {
        throw new Exception(sprintf('%s::%s function is not yet implemented', __CLASS__, __FUNCTION__));
    }

    /**
     * Extract archive for current format
     */
    public function extractArchive($outputDir)
    {
        throw new Exception(sprintf('%s::%s function is not yet implemented', __CLASS__, __FUNCTION__));
    }
}
