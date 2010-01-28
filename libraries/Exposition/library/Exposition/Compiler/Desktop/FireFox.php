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


require_once 'Exposition/Compiler/Desktop.php';

/**
 * Chrome Widgets Compiler.
 */
final class Exposition_Compiler_Desktop_FireFox extends Exposition_Compiler_Desktop
{
    /**
     * Archive Format of the widget.
     *
     * @var string
     */
    protected $archiveFormat = 'zip';

    /**
     * Width of the widget.
     *
     * @var string
     */
    protected $_width = 330;

    /**
     * Height of the widget.
     *
     * @var string
     */
    protected $_height = 370;

    /**
     * Compiler Name.
     *
     * @var string
     */
    protected $_platform = 'frame';

    /**
     * Extension.
     *
     * @var string
     */
    protected $_extension = 'xpi';

    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/x-xpinstall';

    protected function buildArchive()
    {
        // Add the widget skeleton to the archive
        $ressourcePath = Exposition_Load::getConfig('compiler', 'ressourcePath');
        if (!is_readable($ressourcePath)) {
            throw new Exception('UWA ressources directory is not readable.');
        }

        $this->addDirToArchive($ressourcePath . '/firefox');
        $this->addFileFromStringToArchive('chrome.manifest', $this->_getChromeManifest());
        $this->addFileFromStringToArchive('install.rdf', $this->_getInstallRdf());
        $this->addFileFromStringToArchive('defaults/preferences/' . $this->getChromePath() . '.js', $this->_getWidgetPreferences());

    }

    protected function _getInstallRdf()
    {
        $metas = $this->_widget->getMetas();

        $content = array(
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<RDF xmlns="http://www.w3.org/1999/02/22-rdf-syntax-ns#"',
            ' xmlns:em="http://www.mozilla.org/2004/em-rdf#">',
            '  <Description about="urn:mozilla:install-manifest">',
            '    <em:id>' . $this->getNormalizedId() . '</em:id>',
            '    <em:name>' . $this->_widget->getTitle() . '</em:name>',
            '    <em:version>' . (isset($metas['version']) ? $metas['version'] : '1.0') . '</em:version>',
            '    <em:creator>' . $metas['author'] . '</em:creator>',
            '    <em:contributor>' . $metas['author'] . '</em:contributor>',
            '    <em:contributor>Exposition Compiler</em:contributor>',
            '    <em:description>' . $metas['description'] . '</em:description>',
            //'    <em:homepageURL>http://google.com</em:homepageURL>',
            //'    <em:updateURL>http://google.com</em:updateURL>',
            '    <em:iconURL>chrome://' . $this->getChromePath() . '/content/icon.png</em:iconURL>',
            '    <em:targetApplication>',
            '      <Description>',
            '        <em:id>{ec8030f7-c20a-464f-9b0e-13a3a9e97384}</em:id> <!-- firefox -->',
            '        <em:minVersion>1.5</em:minVersion>',
            '        <em:maxVersion>3.5.*</em:maxVersion>',
            '      </Description>',
            '    </em:targetApplication>',
            '    <em:targetApplication>',
            '      <Description>',
            '        <em:id>{3550f703-e582-4d05-9a08-453d09bdfdc6}</em:id> <!-- thunderbird -->',
            '        <em:minVersion>1.5</em:minVersion>',
            '        <em:maxVersion>2.0.0.*</em:maxVersion>',
            '      </Description>',
            '    </em:targetApplication>',
            '  </Description>',
            '</RDF>',
        );

        return implode("\n", $content);
    }

    protected function _getChromeManifest()
    {
        $chromePath = $this->getChromePath();

        $content = array(
            'content	' . $chromePath . ' chrome/content/',
            'locale	    ' . $chromePath . '	en-US	locale/en-US/',
            'skin	    ' . $chromePath . '	classic/1.0	skin/',
            'overlay	chrome://browser/content/browser.xul	chrome://' . $chromePath . '/content/firefoxOverlay.xul',
            'overlay	chrome://messenger/content/messenger.xul	chrome://' . $chromePath . '/content/thunderbirdOverlay.xul',
        );

        return implode("\n", $content);
    }

    protected function _getWidgetPreferences()
    {
    }

    public function getNormalizedId()
    {
        return strtolower($this->getWidgetName()) . '@uwa.widget';
    }

    public function getChromePath()
    {
        return strtolower($this->getWidgetName());
    }

    /**
     * Get extensions file name
     *
     * @return string chrome extensions file name
     */
    public function getFileName()
    {
        return $this->getWidgetName() . '.' . $this->_extension;
    }

    /**
     * Get clean widget name
     *
     * @return string a clean widget name
     */
    public function getWidgetName()
    {
        $filename = preg_replace('/[^a-z0-9]/i', '', $this->_widget->getTitle());
        if (!empty($filename)) {
            return $filename;
        } else {
            return 'widget';
        }
    }

    /**
     * Get widget minetype header value
     *
     * @return string minetype header value
     */
    public function getFileMimeType()
    {
        return $this->_mimeType;
    }
}

