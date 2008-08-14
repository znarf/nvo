<?php
/**
 * Copyright (c) 2008 Netvibes (http://www.netvibes.org/).
 *
 * This file is part of Netvibes Widget Platform.
 *
 * Netvibes Widget Platform is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Netvibes Widget Platform is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Netvibes Widget Platform.  If not, see <http://www.gnu.org/licenses/>.
 */


require_once 'Compiler/Desktop/W3c.php';

/**
 * Opera Widgets Compiler.
 */
final class Compiler_Desktop_Opera extends Compiler_Desktop_W3c
{
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
        $ressourcesDir = Zend_Registry::get('uwaRessourcesDir');
        if (!is_readable($ressourcesDir)) {
            throw new Exception('UWA ressources directory is not readable.');
        }
        $this->addDirToZip($ressourcesDir . 'opera');

        // Replace the default icon if a rich icon is given
        $richIcon = $this->_widget->getRichIcon();
        if (!empty($richIcon) && preg_match('/\\.png$/i', $richIcon)) {
            $this->addDistantFileToZip($richIcon, 'Icon.png');
        }

        $this->_zip->addFromString('index.html', $this->getHtml());

        $this->_zip->addFromString('config.xml', $this->_getXmlManifest());
    }

    protected function _getXmlManifest()
    {
        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();

        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8" ?>';

        $l[] = '<widget xmlns="http://xmlns.opera.com/2006/widget">';

        $l[] = '<widgetname>' . htmlspecialchars($title) . '</widgetname>';
        $l[] = '<width>' . $this->_width . '</width>';
        $l[] = '<height>' . $this->_height . '</height>';

        if (isset($this->options['uwaId'])) {
            $l[] = '<id>';
            $l[] = '<host>' . NV_MODULES . '</host>';
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
