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

//
// http://help.adobe.com/en_US/AIR/1.5/devappshtml/WS5b3ccc516d4fbf351e63e3d118666ade46-7ecc.html#WS5b3ccc516d4fbf351e63e3d118666ade46-7fae

require_once 'Exposition/Compiler/Desktop.php';

/**
 * Chrome Widgets Compiler.
 */
final class Exposition_Compiler_Desktop_Air extends Exposition_Compiler_Desktop_W3c
{
    /**
     * Enable  widget static contents (JS+CSS) storage into archive
     *
     * @var bool
     */
    protected $_storeStaticIntoArchive = true;

    /**
     * Index of widget static contents
     *
     * @var array
     */
    protected $_archiveStaticContents = array();

    /**
     * Preffix of widget static content
     *
     * @var string
     */
    protected $_archiveStaticContentPreffix = 'source';

    /**
     * Archive Format of the widget.
     *
     * @var string
     */
    protected $_archiveFormat = 'zip';

    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Air';

    /**
     * Platform Name.
     *
     * @var string
     */
    protected $_platform = 'Air';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-webkit.css';

    /**
     * Extension.
     *
     * @var string
     */
    protected $_extension = 'zip';

    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/vnd.adobe.air-application-installer-package+zip';

    /**
     * Width of the widget.
     *
     * @var string
     */
    protected $_width = 358;

    /**
     * Height of the widget.
     *
     * @var string
     */
    protected $_height = 625;

    /**
     * Build Desktop Archive file
     */
    protected function buildArchive()
    {
        // Add the widget skeleton to the archive
        $ressourcePath = Exposition_Load::getConfig('compiler', 'ressourcePath');
        if (!is_readable($ressourcePath)) {
            throw new Exception('UWA ressources directory is not readable.');
        }

        $this->addDirToArchive($ressourcePath . '/air');

        // use safari for debug or another webkit powered browser
        //echo $this->getHtml();
        //die();

        // Add other widget files
        $this->addFileFromStringToArchive('source/index.html', $this->getHtml());
        $this->addFileFromStringToArchive('application.xml', $this->_getManifest());
    }

    protected function _getManifest()
    {
        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();
        $fileName = $this->getFileName();

        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8" ?>';

        $l[] = '<application xmlns="http://ns.adobe.com/air/application/1.0">';

        $l[] = '    <id>com.uwa.widget.' . $fileName . '</id>';
        $l[] = '    <filename>' . $fileName . '</filename>';

        $l[] = '    <name>' . $title . '</name>';
        $l[] = '    <description>' . htmlspecialchars($metas['description']) . '</description>';
        $l[] = '    <copyright>' . htmlspecialchars($metas['author']) . '</copyright>';
        $l[] = '    <version>' . (isset($metas['version']) ? $metas['version'] : '1.0') . '</version>';

        $l[] = '    <initialWindow>';
        $l[] = '        <content>source/index.html</content>';
        $l[] = '        <title/>';
        $l[] = '        <systemChrome>none</systemChrome>';
        $l[] = '        <transparent>true</transparent>';
        $l[] = '        <visible>true</visible>';
        $l[] = '        <minimizable>true</minimizable>';
        $l[] = '        <maximizable>true</maximizable>';
        $l[] = '        <resizable>true</resizable>';
        $l[] = '        <width>' . $this->_width . '</width>';
        $l[] = '        <height>' . $this->_height . '</height>';
        $l[] = '        <x>200</x>';
        $l[] = '        <y>200</y>';
        $l[] = '    </initialWindow>';

        $l[] = '    <icon>';
        $l[] = '        <image16x16>source/icons/Icon16.png</image16x16>';
        $l[] = '        <image32x32>source/icons/Icon32.png</image32x32>';
        $l[] = '        <image48x48>source/icons/Icon48.png</image48x48>';
        $l[] = '        <image128x128>source/icons/Icon128.png</image128x128>';
        $l[] = '    </icon>';

        $l[] = '</application>';

        return implode("\n", $l);
    }

    protected function _getJavascripts($options = array())
    {
        $javascripts = parent::_getJavascripts($options);
        $javascripts[] = 'js/AIRAliases.js';
        return $javascripts;
    }
}

