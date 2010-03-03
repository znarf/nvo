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
 * Apple Dashboard Widgets Compiler.
 */
final class Exposition_Compiler_Desktop_Dashboard extends Exposition_Compiler_Desktop_W3c
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
    protected $_environment = 'Dashboard2';

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
    protected $_platform = 'dashboard';

    /**
     * Extension.
     *
     * @var string
     */
    protected $_extension = 'wdgt.zip';

    protected function buildArchive()
    {
        $dirname = $this->getNormalizedTitle();
        $dirname = preg_replace('/[^a-z0-9:;,?.()[]{}=@ _-]/i', '', $dirname) . '.wdgt/';

        // Add the widget skeleton to the archive
        $ressourcePath = Exposition_Load::getConfig('compiler', 'ressourcePath');
        if (!is_readable($ressourcePath)) {
            throw new Exception('UWA ressources directory is not readable.');
        }

        $this->addDirToArchive($ressourcePath . '/dashboard', $dirname);

        // Replace the default icon if a rich icon is given
        $richIcon = $this->_widget->getRichIcon();
        if (!empty($richIcon) && preg_match('/\\.png$/i', $richIcon)) {
            $this->addDistantFileToArchive($richIcon, $dirname . 'Icon.png');
        }

        // use safari for debug or another webkit powered browser
        //echo $this->getHtml();
        //die();

        // Add other widget files
        $this->addFileFromStringToArchive($dirname . 'index.html', $this->getHtml());
        $this->addFileFromStringToArchive($dirname . 'Info.plist', $this->_getManifest());
    }

    protected function _getManifest()
    {
        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();
        $identifier = preg_replace('/[^a-z0-9]/i', '', $this->_widget->getTitle());

        $options = array(
            'AllowNetworkAccess'    => true,
            'AllowInternetPlugins'  => true,
            'MainHTML'              => 'index.html',
            'Width'                 => $this->_width,
            'Height'                => $this->_height,
            'CloseBoxInsetX'        => 15,
            'CloseBoxInsetY'        => 5,
            'CFBundleIdentifier'    => 'com.uwa.widget.' . $identifier,
            'CFBundleDisplayName'   => $title,
            'CFBundleName'          => $title,
            'CFBundleVersion'       => isset($metas['version']) ? $metas['version'] : '1.0'
        );

        $l = array();

        $l[] = '<!DOCTYPE plist PUBLIC "-//Apple Computer//DTD PLIST 1.0//EN"
            "http://www.apple.com/DTDs/PropertyList-1.0.dtd">';

        $l[] = '<plist version="1.0">';
        $l[] = '<dict>';

        foreach ($options as $key => $value) {
            $l[] = '<key>' . htmlspecialchars($key) . '</key>';
            if (is_bool($value)) {
                $l[] = $value ? '<true/>' : '<false/>';
            } elseif (is_int($value)) {
                $l[] = '<integer>' . $value . '</integer>';
            } else {
                $l[] = '<string>' . htmlspecialchars($value) . '</string>';
            }
        }

        $l[] = '</dict>';
        $l[] = '</plist>';

        return implode("\n", $l);
    }

    protected function _getJavascripts($options = array())
    {
        $javascripts = parent::_getJavascripts($options);
        $javascripts[] = '/System/Library/WidgetResources/AppleClasses/AppleInfoButton.js';
        $javascripts[] = '/System/Library/WidgetResources/AppleClasses/AppleAnimator.js';
        $javascripts[] = '/System/Library/WidgetResources/AppleClasses/AppleButton.js';
        return $javascripts;
    }
}

