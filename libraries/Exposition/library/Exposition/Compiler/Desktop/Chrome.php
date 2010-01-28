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
final class Exposition_Compiler_Desktop_Chrome extends Exposition_Compiler_Desktop
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Frame';

    /**
     * Archive Format of the widget.
     *
     * @todo should be switched to 'crx'
     * @var string
     */
    protected $_archiveFormat = 'zip';

    /**
     * Compiler Name.
     *
     * @var string
     */
    protected $_platform = 'frame';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-chrome.css';

    /**
     * Extension.
     *
     * @todo should be switched to 'crx'
     * @var string
     */
    protected $_extension = 'crx';

    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/x-binary';

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

        $this->addDirToArchive($ressourcePath . '/chrome');

        // add archive files
        $this->addFileFromStringToArchive('widget.html', $this->getHtml());
        $this->addFileFromStringToArchive('manifest.json', $this->_getJsonManifest());
    }

    /**
     * Render Frame compiler
     *
     * @return string HTML of widget.html file
     */
    protected function getHtml()
    {
        $compiler = Exposition_Compiler_Factory::getCompiler($this->_platform, $this->_widget);

        $options = array(
            'displayHeader'         => true,
            'displayStatus'         => true,
            'forceJsonRequest'      => true,
            'properties'            => array(
                'id' => time(),
            ),
        );

        $compiler->setOptions($options);

        $compiler->setStylesheet($this->_stylesheet);

        return $compiler->render();
    }

    /**
     * Get manifest.json content
     *
     * @return string manifest.json content for current wigdet
     */
    protected function _getJsonManifest()
    {
        // se details on http://code.google.com/chrome/extensions/getstarted.html
        // http://gist.github.com/142422 crx scripts

        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();

        $manifest = array(
            'name'              => $title,
            'icon'              => 'Icon.png',
            'description'       => $metas['description'],
            'version'           => (isset($metas['version']) ? $metas['version'] : '1.0'),
            'browser_action'    => array(
                'default_icon' => 'Icon.png',
                'popup' => 'widget.html',
            ),

            'permissions'       => array(
                'http://*/*'
            ),
        );

        return Zend_Json::encode($manifest);
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
            return 'Widget';
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

