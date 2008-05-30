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


require_once 'Compiler.php';

/**
 * Frame Compiler to render a UWA widget within an iframe.
 */
class Compiler_Frame extends Compiler
{
    /**
     * Environment.
     *
     * @var string
     */
    protected $_environment = 'Frame';
    
    /**
     * Main rendering function.
     *
     * @return string
     */
    public function render()
    {
        $l = array();
        
        $l[] = '<?xml version="1.0" encoding="utf-8"?>';
        $l[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
            ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $l[] = '<html xmlns="http://www.w3.org/1999/xhtml">';
        $l[] = '<head>';
        $l[] = '<title>' . $this->_widget->getTitle() . '</title>';
        $l[] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        
        foreach ($this->_getStylesheets() as $stylesheet) {
            $l[] = '<link rel="stylesheet" type="text/css" href="' . $stylesheet . '"/>';
        }
        
        $l[] = '</head>';
        $l[] = '<body class="moduleIframe">';
        
        $l[] = '<div class="moduleContent" id="moduleContent">';
        $l[] = $this->_widget->getBody();
        $l[] = '</div>';
        
        $l[] = '<script type="text/javascript">';
        $l[] = "var NV_HOST = '" . NV_HOST . "', NV_PATH = '/', NV_STATIC = 'http://" . NV_STATIC . "', " .
            "NV_MODULES = '". NV_MODULES ."', NV_AVATARS = '". NV_AVATARS ."';";
        $l[] = '</script>';
        
        foreach ($this->_getJavascripts() as $javascript) {
            $l[] = '<script type="text/javascript" src="' . $javascript . '"></script>';
        }
        
        $l[] = '<script type="text/javascript">';
        $l[] = $this->_getFrameScript();
        $l[] = '</script>';
        
        $l[] = '</body>';
        $l[] = '</html>';
        
        return implode("\n", $l);
    }
    
    private function _getFrameScript()
    {
        $l = array();
        
        $proxies = array(
            'ajax' => Zend_Registry::get('proxyEndpoint') . '/ajax',
            'feed' => Zend_Registry::get('proxyEndpoint') . '/feed'
        );
        
        $l[] = sprintf('UWA.proxies = %s;', Zend_Json::encode($proxies));
        
        foreach ($this->options['properties'] as $key => $value) {
            if (isset($value)) {
                $l[] = sprintf("widget.%s = %s;", $key, Zend_Json::encode($value));
            }
        }
        
        if (isset($this->options['data']) && count($this->options['data'])) {
            $l[] = sprintf("UWA.extend(widget.data, %s);", Zend_Json::encode($this->options['data']));
        }
        
        $script = $this->_widget->getScript();
        if ( !empty($script) ) {
            if (isset($this->options['uwaId'])) {
                $l[] = sprintf("UWA.Scripts['%s'](widget);", $this->options['uwaId']);
            } else {
                $l[] = "UWA.script(widget);";
            }
        }
        
        if (isset($this->options['ifproxyUrl'])) {
            $l[] = sprintf("Environment.ifproxyUrl = %s;", Zend_Json::encode($this->options['ifproxyUrl']));
        }
        
        $l[] = "Environment.launchModule();";
        
        return implode("\n", $l);
    }

}
