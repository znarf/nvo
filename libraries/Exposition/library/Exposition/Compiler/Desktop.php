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
require_once 'Zend/Http/Client.php';

/**
 * Desktop Compiler.
 */
abstract class Exposition_Compiler_Desktop extends Exposition_Compiler
{
    /**
     * Enable  widget static contents (JS+CSS) storage into archive
     *
     * @var bool
     */
    protected $_storeStaticIntoArchive = false;

    /**
     * Index of widget static contents
     *
     * @var array
     */
    protected $_archiveStaticContents = array();

    /**
     * Preffix path of widget static content
     *
     * @var string
     */
    protected $_archiveStaticContentPath = 'source';

    /**
     * Archive Format of the widget (zip by default).
     *
     * @var string
     */
    protected $_archiveFormat = 'zip';

    public function getArchive()
    {
        static $archive;

        // Temporary file path to build the archive
        if (!isset($archive)) {
            $archive = Exposition_Archive::newArchive($this->_archiveFormat);
        }

        return $archive;
    }

    /**
     * Retrieves the content of the archive.
     *
     * @return string
     */
    public function getFileContent()
    {

        // Archive filling according to the selected compiler
        $this->buildArchive();

        // use $this->_widget->getCheckSum() for build cache
        // use get_class($this); too ;)

        // Archive closing
        $this->getArchive()->createArchive();

        return $this->getArchive()->getArchiveContent();
    }

    /**
     * Renders the compiled widget with the correct mime type and file name.
     */
    public function render()
    {
        header('Pragma: public');
        header('Content-type: ' . $this->getFileMimeType());
        header('Content-Disposition: attachment; filename="' . $this->getFileName() . '"');

        echo $this->getFileContent();
    }

    /*** ARCHIVE FUNCTIONS ***/

    /**
     * Adds a file to the Archive.
     *
     * @param  string  $path The path of the file to add
     * @param  string  $EntryName The ZIP archive name
     * @return boolean True if the file has been successfully added, otherwise false
     */
    protected function addFileFromStringToArchive($EntryName, $contents)
    {
        return $this->getArchive()->addFileFromString($EntryName, $contents);
    }

    /**
     * Adds a directory to the ZIP archive.
     *
     * @param string $directory The directory name to add
     * @param string $root The root destination within the archive
     */
    protected function addDirToArchive($directory, $root = '')
    {
        return $this->getArchive()->addFiles($directory, $root);
    }

    /**
     * Adds a file to the Archive.
     *
     * @param  string  $path The path of the file to add
     * @param  string  $EntryName The archive name
     * @return boolean True if the file has been successfully added, otherwise false
     */
    protected function addFileToArchive($path, $EntryName)
    {
        return $this->getArchive()->addFile($path, $EntryName);
    }

    /**
     * Adds a distant file to the Archive.
     *
     * @param  string $url
     * @param  string $filePath
     * @param  string $dirPath
     * @return string The file path into the archive.
     */
    protected function addDistantFileToArchive($url, $filePath = '', $dirPath = '')
    {
        $fileInfo = parse_url($url);
        if(!isset($fileInfo['host'])) {
            $urlInfo = parse_url($this->_url);
            $url = $urlInfo['scheme'] . '://' . $urlInfo['host'] . dirname( $urlInfo['path'] ) . '/' . $url;
        }

        if (empty($filePath) && empty($dirPath)) {
            $filePath = 'widget/' . basename($fileInfo['path']);
        } else if (!empty($dirPath)) {
            $filePath = $dirPath . basename($fileInfo['path']);
        }

        $proxy = new Exposition_Proxy($url);
        $fileContent = $proxy->getResponse();

        // free proxy instance
        unset($proxy);

        if ($this->addFileFromStringToArchive($filePath, $fileContent)) {
            return $filePath;
        } else {
            throw new Exception(sprintf('Unable to add distant file "%s" into archive', $url));
        }
    }

    /**
     * Retrieves the list of the stylesheets used in a widget.
     *
     * @return array
     */
    public function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();

        if($this->_storeStaticIntoArchive) {
            $stylesheets = $this->_addStaticContentToArchive($stylesheets);
        }

        return $stylesheets;
    }

    /**
     * Retrieves the list of the JavaScript libraries used in a widget (both UWA and specific ones).
     *
     * @param string $name
     * @return array
     */
    protected function _getJavascripts($options = array())
    {
        $javascripts = parent::_getJavascripts();

        if($this->_storeStaticIntoArchive) {
            $javascripts = $this->_addStaticContentToArchive($javascripts);
        }

        return $javascripts;
    }

    /**
     * Retrieves the list of the JavaScript libraries used in a widget (both UWA and specific ones).
     *
     * @param string $name
     * @return array
     */
    protected function _getWidgetJavascripts($options = array())
    {
        $widgetJavascript = parent::_getWidgetJavascripts($options = array());

        if($this->_storeStaticIntoArchive) {
            $widgetJavascript = $this->_addStaticContentToArchive($widgetJavascript);
        }

        return $widgetJavascript;
    }

    /*** ARCHIVE STATIC STORAGE FUNCTIONS ***/

    /**
     * Import static content into archive
     *
     * @return void array if params is array else string
     */
    protected function _addStaticContentToArchive($contents) {

        // support string params
        if (!is_array($contents)) {
            $originalContentString = $contents;
            $contents = array($contents);
        }

        $archiveContents = array();
        foreach ($contents as $content) {

            if (!$contentArchivePath = in_array($content, $this->_archiveStaticContents)) {

                $contentStaticPath = $this->_getStaticContentPath($content);
                $contentArchivePath = $this->_archiveStaticContentPath . DIRECTORY_SEPARATOR . $contentStaticPath;

                // import static content dependendy from Stylesheets (example @import into css and css images)
                if (strpos($content, '.css') !== false) {

                    $staticContentDependencies = self::_parseStylesheetsToArrray($content);

                    $this->_addStaticContentToArchive($staticContentDependencies['import']);
                    $this->_addStaticContentToArchive($staticContentDependencies['images']);

                    $this->addDistantFileToArchive($content, $contentArchivePath);

                } else {

                    $this->addDistantFileToArchive($content, $contentArchivePath);
                }

                $this->_archiveStaticContents[$contentArchivePath] = $contentStaticPath;
            }

            $archiveContents[] = $contentStaticPath;
        }


        if (isset($originalContentString)) {
            $archiveContents = current($archiveContents);
        }

        return $archiveContents;
    }

    /**
     * Generate a archive path for static content should be imported
     *
     * @param string $staticUrl
     * @return string
     */
    protected function _getStaticContentPath($staticUrl) {

        // @todo improve url secutity and test

        $urlParts = parse_url($staticUrl);
        $archiveFilePath = $urlParts['path'];

        // @todo detect format then overide $archivefilePath value


        switch ($archiveFilePath) {

            case '/widget/css':
                $archiveFilePath = 'css/Widget.css';
                break;

            case '/widget/js':
                $archiveFilePath = 'js/Widget.js';
                break;

            default:
                // remove first "/"
                $archiveFilePath = substr($archiveFilePath, 1);
        }

        return $archiveFilePath;
    }

    /**
     * Extract from static content dependendy static contents
     *
     * @param string $staticUrl
     * @return array
     */
    protected function _getStaticDependency($staticUrl) {

        $staticContentDependencies = array();

        return $staticContentDependencies;
    }

    /**
     *
     *
     *
     */
    protected static function _parseStylesheetsToArrray($stylesheetUrl)
    {
        $urls = array(
            'import'   => array(),
            'images' => array(),
        );

        $proxy = new Exposition_Proxy($stylesheetUrl);
        $stylesheetContent = $proxy->getResponse();

        // free proxy instance
        unset($proxy);

        // get stylesheet path info
        $stylesheetUrlParts = parse_url($stylesheetUrl);
        $stylesheetUrlDomain = $stylesheetUrlParts['scheme'] . '://' . $stylesheetUrlParts['host'];
        $stylesheetUrlDirname = dirname($stylesheetUrlParts['path']);
        $stylesheetUrlPath = $stylesheetUrlDomain . $stylesheetUrlDirname;


        $url_pattern     = '(([^\\\\\'", \(\)]*(\\\\.)?)+)';
        $urlfunc_pattern = 'url\(\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
        $pattern         = '/(' .
             '(@import\s*[\'"]' . $url_pattern     . '[\'"])' .
            '|(@import\s*'      . $urlfunc_pattern . ')'      .
            '|('                . $urlfunc_pattern . ')'      .  ')/iu';

        $matches = array();
        if ( !preg_match_all( $pattern, $stylesheetContent, $matches)) {
            return $urls;
        }

        // @import '...'
        // @import "..."
        foreach ( $matches[3] as $match ) {
            if ( !empty($match) ) {
                $urls['import'][] = preg_replace( '/\\\\(.)/u', '\\1', $match );
            }
        }

        // @import url(...)
        // @import url('...')
        // @import url("...")
        foreach ( $matches[7] as $match ) {
            if ( !empty($match) ) {
                $urls['import'][] = preg_replace( '/\\\\(.)/u', '\\1', $match );
            }
        }

        // url(...)
        // url('...')
        // url("...")
        foreach ( $matches[11] as $match ) {
            if ( !empty($match) ) {
                $urls['images'][] = preg_replace( '/\\\\(.)/u', '\\1', $match );
            }
        }

        // add right path
        foreach ($urls['images'] as $key => $imageImport) {

            // detect if content is at "/"
            if (strpos($imageImport, '/') == 0) {
                $urls['images'][$key] =  $stylesheetUrlDomain . $imageImport;
            } else {
                $urls['images'][$key] = $stylesheetUrlPath . DIRECTORY_SEPARATOR . $imageImport;
            }
        }

        foreach ($urls['import'] as $key => $stylesheetImport) {

            // detect if content is at "/"
            if (strpos($stylesheetImport, '/') == 0) {
                $urls['import'][$key] =  $stylesheetUrlDomain . $stylesheetImport;
            } else {
                $urls['import'][$key] = $stylesheetUrlPath . DIRECTORY_SEPARATOR . $stylesheetImport;
            }

            $urls = array_merge_recursive($urls, self::_parseStylesheetsToArrray($urls['import'][$key]));
        }


        return $urls;
    }

    /*** ABSTRACT FUNCTIONS ***/

    /**
     * Build Desktop Archive file
     */
    abstract protected function buildArchive();

    /**
     * Get widget file name
     *
     * @return string widget file name
     */
    abstract public function getFileName();

    /**
     * Get widget minetype header value
     *
     * @return string minetype header value
     */
    abstract public function getFileMimeType();
}

