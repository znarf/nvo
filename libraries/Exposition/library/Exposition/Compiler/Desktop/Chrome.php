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
    protected $_extension = 'zip';

    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/x-binary';

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

    protected function getHtml()
    {
        $compiler = Exposition_Compiler_Factory::getCompiler($this->_platform, $this->_widget);

        $options = array(
            'displayHeader' => 1,
            'displayStatus' => 1,
            'properties'    => array(
                'id' => time(),
            ),
        );

        $compiler->setOptions($options);

        return $compiler->render();
    }

    protected function getToolstripsHtml()
    {
        return '';
    }

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

    public function getFileName()
    {
        return $this->getNormalizedTitle() . '.' . $this->_extension;
    }

    public function getNormalizedTitle()
    {
        $filename = preg_replace('/[^a-z0-9]/i', '', $this->_widget->getTitle());
        if (!empty($filename)) {
            return $filename;
        } else {
            return 'Widget';
        }
    }

    public function getFileMimeType()
    {
        return $this->_mimeType;
    }
}

