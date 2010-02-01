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

class Exposition_Archive
{
    /**
     * Available archive formats indexed by default extentions
     *
     * @var array
     */
    protected static $_availableFormats = array(
        'bz'    => 'bzip',
        'zip'   => 'zip',
        'tar'   => 'tar',
        'gz'    => 'gzip',
        'crx'   => 'crx',
    );

    /**
     * Create a new archive
     *
     * @param string $format archive format see $_availableFormats property values
     * @param string $filePath archive file path, if null it create archive in memory
     * @param array $options archive builder options
     *
     * @return object instance of Exposition_Archive_Abstract extended class
     */
    static public function newArchive($format, $filePath = null, array $options = array())
    {
        $className = self::_getClassNameByFormat($format);

        return new $className($filePath, $options);
    }

    /**
     * Extract an archive
     *
     * @param string $filePath archive file path
     * @param string $outputPath output archive path, if null it extract archive in memory
     * @param string $format archive format see $_availableFormats property values
     * @param array $options archive builder options
     *
     * @return object instance of Exposition_Archive_Abstract extended class
     */
    static public function extractArchive($filePath, $outputPath = null, $format = null, array $options = array())
    {
        // detect format
        if (is_null($format)) {
            $format = self::getArchiveFormat($filePath);
        }

        $archive = self::newArchive($format, $filePath, $options);
        $archive->extractArchive($outputPath);

        return $archive;
    }

    /**
     * Detect archive format
     *
     * @param string $filePath archive file path
     *
     * @return string archive format see $_availableFormats property values
     */
    static public function getArchiveFormat($filePath)
    {
        if (!is_readable($filePath)) {
            throw new Exposition_Archive_Exception(sprintf('Could not open file "%s"', $filePath));
        }

        $extention = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = null;

        // use $minType only if mime_content_type is available
        if (function_exists('mime_content_type')) {
            $mimeType = trim(mime_content_type($filePath));
        }

        // compare mine types
        foreach (self::$_availableFormats as $formatExtention => $format) {

            $className = self::_getClassNameByFormat($format);
            $formatMimeType = call_user_func($className .'::getFileMimeType');

            if (!is_null($mimeType) && $formatMimeType == $mimeType) {
                return $format;
            } else if ($extention == $formatExtention) {
                return $format;
            }
        }

        // no mine types match
        if (!is_null($mimeType)) {
            throw new Exposition_Archive_Exception(sprintf('Could not detect archive format for file "%s", with mine type "%s"', $filePath, $mimeType));
        } else {
            throw new Exposition_Archive_Exception(sprintf('Could not detect archive format for file "%s"', $filePath));
        }
    }

    /**
     *
     */
     static public function getAvailableFormats()
     {
         return self::$_availableFormats;
     }

    /**
     * Get class name and load class from archive format name
     *
     * @param string $format archive format see $_availableFormats property values
     *
     * @return string class name of Exposition_Archive_Abstract extended class
     */
    static private function _getClassNameByFormat($format)
    {
        if (in_array($format, self::$_availableFormats) === false) {
            throw new Exposition_Archive_Exception(sprintf('Invalid archive format "%s", see %s::$_availableFormats value for available form', $format, __CLASS__));
        }

        $className = __CLASS__ . '_' . ucfirst(strtolower($format));

        // prevent stack error
        Zend_Loader::loadClass($className);

        return $className;
    }
}

