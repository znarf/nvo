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


require_once 'Compiler/Desktop.php';

/**
 * Apple Dashboard Widgets Compiler.
 */
abstract class Compiler_Desktop_W3c extends Compiler_Desktop
{

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
    protected $_mimeType = 'application/zip';

    public function getHtml()
    {
        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8"?>';
        $l[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $l[] = '<html xmlns="http://www.w3.org/1999/xhtml">';
        $l[] = '<head>';
        $l[] = '<title>' . $this->_widget->getTitle() . '</title>';
        $l[] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        foreach ($this->_getStylesheets() as $stylesheet) {
            $l[] = '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($stylesheet) . '"/>';
        }
        
        $l[] = '</head>';
        $l[] = '<body>';

        $l[] = $this->_getHtmlBody();

        $l[] = $this->_getJavascriptConstants();

        $javascripts = $this->_getJavascripts( array('platform' => $this->_platform) );

        foreach ($javascripts as $script) {
            $l[] = "<script type='text/javascript' src='" . htmlspecialchars($script) . "' charset='utf-8'/>";
        }

        $l[] = '<script type="text/javascript">';
        $l[] = $this->_getScript();
        $l[] = '</script>';

        $l[] = '</body>';
        $l[] = '</html>';

        return implode("\n", $l);
    }

    private function _getHtmlBody()
    {
        $l = array();

        $l[] = '<div class="module" id="wrapper">';
        $l[] =   $this->_getHtmlHeader();
        $l[] =   '<div id="contentWrapper">';
        $l[] =     '<div class="moduleContent" id="moduleContent">';
        $l[] =       $this->_widget->getBody();
        $l[] =     '</div>';
        $l[] =     $this->_getHtmlStatus();
        $l[] =   '</div>';
        $l[] =   '<div class="moduleFooter" id="moduleFooter"></div>';
        $l[] = '</div>';

        return implode("\n", $l);
    }

    private function _getScript()
    {
        $l = array();

        $proxies = array(
            'ajax' => Zend_Registry::get('proxyEndpoint') . '/ajax',
            'feed' => Zend_Registry::get('proxyEndpoint') . '/feed'
        );

        $l[] = sprintf('UWA.proxies = %s;', Zend_Json::encode($proxies));

        $l[] = "var id = window.widget ? widget.identifier : Math.round(Math.random() * 1000);";
        $l[] = "Environments[id] = new UWA.Environment();";
        $l[] = "Widgets[id] = Environments[id].getModule();";
        $l[] = sprintf('Widgets[id].uwaUrl = %s;', Zend_Json::encode($this->_widget->getUrl()));
        $l[] = "UWA.script(Widgets[id]);";
        $l[] = "Environments[id].launchModule();";

        return implode("\n", $l);
    }

    public function getFileName()
    {
        $filename = $this->getNormalizedTitle();
        if (!empty($filename)) {
            return $filename . '.' . $this->_extension;
        } else {
            return 'Widget' . '.' . $this->_extension;
        }
    }

    public function getNormalizedTitle()
    {
        return $this->_widget->getTitle();
    }

    public function getFileMimeType()
    {
        return $this->_mimeType;
    }

    /*** ABSTRACT FUNCTIONS ***/

    abstract protected function _getXmlManifest();

}
