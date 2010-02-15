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


require_once 'Exposition/Compiler/Desktop/W3c.php';

/**
 * Opera Widgets Compiler.
 */
final class Exposition_Compiler_Desktop_Opera extends Exposition_Compiler_Desktop_W3c
{
    /**
     * Archive Format of the widget
     *
     * @var string
     */
    protected $_archiveFormat = 'zip';

    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Opera2';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-dashboard.css';

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
    protected $_height = 600;

    /**
     * Platform Name.
     *
     * @var string
     */
    protected $_platform = 'opera';

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
    protected $_mimeType = 'application/x-opera-widgets';

    protected function buildArchive()
    {
        // Add the widget skeleton to the archive
        $ressourcePath = Exposition_Load::getConfig('compiler', 'ressourcePath');
        if (!is_readable($ressourcePath)) {
            throw new Exception('UWA ressources directory is not readable.');
        }

        $this->addDirToArchive($ressourcePath . '/opera');

        // Replace the default icon if a rich icon is given
        $richIcon = $this->_widget->getRichIcon();
        if (!empty($richIcon) && preg_match('/\\.png$/i', $richIcon)) {
            $this->addDistantFileToArchive($richIcon, 'Icon.png');
        }

        // Add other widget files
        $this->addFileFromStringToArchive('index.html', $this->getHtml());
        $this->addFileFromStringToArchive('config.xml', $this->_getManifest());
    }

    protected function _getManifest()
    {
        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();

        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8" ?>';

        $l[] = '<widget network="public" xmlns="http://xmlns.opera.com/2006/widget">';

        $l[] = '<widgetname>' . htmlspecialchars($title) . '</widgetname>';
        $l[] = '<width>' . $this->_width . '</width>';
        $l[] = '<height>' . $this->_height . '</height>';

        if (isset($this->options['uwaId'])) {
            $l[] = '<id>';
            $l[] = '<host>' . UWA_MODULES . '</host>';
            $l[] = '<name>' . $this->options['uwaId'] . '</name>';
            $l[] = '<revised>' . date('Y-m') . '</revised>';
            $l[] = '</id>';
        }

        if (isset($metas['author'])) {
            $l[] = '<author>';
            $l[] = '<name>' . htmlspecialchars($metas['author']) . '</name>';
            if (isset($metas['website'])) {
                $l[] = '<link>' . htmlspecialchars($metas['website']) . '</link>';
            }
            if (isset($metas['email'])) {
                $l[] = '<email>' . htmlspecialchars($metas['email']) . '</email>';
            }
            $l[] = '</author>';
        }

        if (isset($metas['description'])) {
            $l[] = '<description>' . htmlspecialchars($metas['description']) . '</description>';
        }

        $l[] = "<icon>" . 'Icon.png' . "</icon>";

        $l[] = '</widget>';

        return implode("\n", $l);
    }
}

