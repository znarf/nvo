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
    protected $_extension = 'air';

    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/vnd.adobe.air-application-installer-package+zip';

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

        echo $this->getHtml();
        die();

        // Add other widget files
        $this->addFileFromStringToArchive('source/index.html', $this->getHtml());
        $this->addFileFromStringToArchive('Application.xml', $this->_getManifest());
    }

    protected function _getManifest()
    {
        $title = $this->_widget->getTitle();
        $metas = $this->_widget->getMetas();
        $fileName = $this->getFileName();

        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8" ?>';

        $l[] = '<application xmlns="http://ns.adobe.com/air/application/1.0">';

        $l[] = '<name>' . $title . '</name>';
        $l[] = '<id>org.netvibes.uwa.' . $fileName . '</id>';
        $l[] = '<filename>' . $fileName . '</filename>';

        $l[] = '<version>1.0</version>';

        $l[] = '<initialWindow>';
        $l[] = '    <content>source/index.html</content>';
        $l[] = '    <title/>';
        $l[] = '    <systemChrome>none</systemChrome>';
        $l[] = '    <transparent>true</transparent>';
        $l[] = '    <visible>true</visible>';
        $l[] = '    <minimizable>true</minimizable>';
        $l[] = '    <maximizable>true</maximizable>';
        $l[] = '    <resizable>true</resizable>';
        $l[] = '    <width>' . $this->_width . '</width>';
        $l[] = '    <height>' . $this->_height . '</height>';
        $l[] = '    <x>200</x>';
        $l[] = '    <y>200</y>';
        $l[] = '  </initialWindow>';

        $l[] = '</application>';

         $l[] = '<icon>';
         $l[] = '    <image16x16>source/icons/Icon.png</image16x16>';
         $l[] = '    <image32x32>source/icons/Icon.png</image32x32>';
         $l[] = '    <image48x48>source/icons/Icon.png</image48x48>';
         $l[] = '    <image128x128>source/icons/Icon.png</image128x128>';
         $l[] = '</icon>';

        return implode("\n", $l);

        /*
       <?xml version="1.0" encoding="utf-8" ?>
<application
        xmlns="http://ns.adobe.com/air/application/1.5.3"
        minimumPatchLevel="0">
<!-- AIR Application Descriptor File. See http://www.adobe.com/go/learn_air_1.0_application_descriptor_en. -->
        <id>com.example.ExampleApplication</id>
        <name>
                <text xml:lang="en">Example Co. Example Application 1.0</text>
        </name>
        <version>1.0</version>
        <filename>Example Application</filename>
        <description>
                <text xml:lang="en">This is a sample Adobe AIR application.</text>
        </description>
        <copyright>Copyright 2009, Example Co., Inc.</copyright>
        <initialWindow>
                <content>ExampleApplication.swf</content>
                <title>Example Application</title>
                <systemChrome>standard</systemChrome>
                <transparent>false</transparent>
                <visible>false</visible>
                <minimizable>true</minimizable>
                <maximizable>true</maximizable>
                <resizable>true</resizable>
                <width>500</width>
                <height>500</height>
                <x>150</x>
                <y>150</y>
                <minSize>300 300</minSize>
                <maxSize>800 800</maxSize>
        </initialWindow>
        <installFolder>Example Company/Example Application</installFolder>
        <programMenuFolder>Example Company/Example Application</programMenuFolder>
        <icon>
                <image16x16>icons/AIRApp_16.png</image16x16>
                <image32x32>icons/AIRApp_32.png</image32x32>
                <image48x48>icons/AIRApp_48.png</image48x48>
                <image128x128>icons/AIRApp_128.png</image128x128>
        </icon>
        <customUpdateUI>false</customUpdateUI>
        <allowBrowserInvocation>false</allowBrowserInvocation>
        <fileTypes>
                <fileType>
                        <name>com.example</name>
                        <extension>xmpl</extension>
                        <description>Example file</description>
                        <contentType>example/x-data-type</contentType>
                        <icon>
                                <image16x16>icons/AIRApp_16.png</image16x16>
                                <image32x32>icons/AIRApp_32.png</image32x32>
                                <image48x48>icons/AIRApp_48.png</image48x48>
                                <image128x128>icons/AIRApp_128.png</image128x128>
                        </icon>
                </fileType>
        </fileTypes>
</application>

        */
    }
}

