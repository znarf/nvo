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
 * Prism Widgets Compiler.
 */
final class Exposition_Compiler_Desktop_Prism extends Exposition_Compiler_Desktop
{
    /**
     * Archive Format of the widget.
     *
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
     * Extension.
     *
     * @var string
     */
    protected $_extension = 'webapp';

    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/x-webrunner';

    protected function buildArchive()
    {
        // Add the widget skeleton to the archive
        $ressourcePath = Exposition_Load::getConfig('compiler', 'ressourcePath');
        if (!is_readable($ressourcePath)) {
            throw new Exception('UWA ressources directory is not readable.');
        }

        $this->addDirToArchive($ressourcePath . '/prism');

        $this->addFileFromStringToArchive('override.ini', $this->_getOverrideConfig());
        $this->addFileFromStringToArchive('webapp.ini', $this->_getWebappConfig());
    }

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

        return $compiler->render();
    }

    protected function _getOverrideConfig()
    {
        $content = array(
            '[App]',
            'Vendor=Prism',
            'Name=' . $this->getNormalizedTitle(),
        );

        return implode("\n", $content);
    }

    protected function _getWebappConfig()
    {
        $widgetEndpoint = Exposition_Load::getConfig('endpoint', 'widget');

        $content = array(
            '[Parameters]',
            'id=' . $this->getNormalizedId(),
            'name=' . $this->getNormalizedTitle(),
            'uri=' . $widgetEndpoint . '/frame?uwaUrl=' . urlencode($this->_widget->getUrl()) . '&header=1',
            'icon=webapp',
            //'splashscreen=webapp.png',
            'status=true',
            'location=false',
            'sidebar=false',
            'navigation=false',
            'trayicon=false',
        );

        return implode("\n", $content);
    }

    public function getFileName()
    {
        return $this->getNormalizedTitle() . '.' . $this->_extension;
    }

    public function getNormalizedId()
    {
        return strtolower($this->getNormalizedTitle()) . '@prism.app';
    }

    public function getNormalizedTitle()
    {
        $filename = preg_replace('/[^a-z0-9]/i', '', $this->_widget->getTitle());
        if (!empty($filename)) {
            return $filename;
        } else {
            return 'widget';
        }
    }

    public function getFileMimeType()
    {
        return $this->_mimeType;
    }
}

