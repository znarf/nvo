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


require_once 'Compiler/Desktop.php';

/**
 * Jil Widgets Compiler.
 */
final class Exposition_Compiler_Desktop_Jil extends Exposition_Compiler_Desktop
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
    protected $_extension = 'wgt';

    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/x-binary';

    protected function buildArchive()
    {
        // Add the widget skeleton to the archive
        $ressourcesDir = Zend_Registry::get('uwaRessourcesDir');
        if (!is_readable($ressourcesDir)) {
            throw new Exception('UWA ressources directory is not readable.');
        }

        $this->addDirToArchive($ressourcesDir . 'jil');

        // Replace the default icon if a rich icon is given
        $richIcon = $this->_widget->getRichIcon();
        if (!empty($richIcon) && preg_match('/\\.png$/i', $richIcon)) {
            $this->addDistantFileToArchive($richIcon, 'Icon.png');
        }

        $this->addFileFromStringToArchive('widget.html', $this->getHtml());

        $this->addFileFromStringToArchive('config.xml', $this->_getXmlManifest());
    }

    protected function getHtml()
    {
        $compiler = Compiler_Factory::getCompiler($this->_platform, $this->_widget);

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

    protected function _getXmlManifest()
    {
        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();

        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8" ?>';

        $l[] = '<widget xmlns="http://www.jil.org/ns/widgets" id="dcc7c6bb-ba4d-4de9-8ce6-3cc2cc3b5bba" width="' . $this->_width . '" height="' . $this->_height . '" version="1.0.Beta">';

        $l[] = '<name>' . htmlspecialchars($title) . '</name>';

        if (isset($metas['description'])) {
            $l[] = '<description>' . htmlspecialchars($metas['description']) . '</description>';
        }

        $l[] = '<license href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution License</license>';
        $l[] = '<update href="http://www.jil.org/widgets/" period="2"/>';
        $l[] = '<icon src="Icon.png">';
        $l[] = '<content src="widget.html"/>';
        $l[] = '<billing required="false"/>';

        $l[] = '</widget>';

        return implode("\n", $l);
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
