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


require_once 'Compiler.php';

/**
 * Frame Compiler to render a UWA widget within an iframe.
 */
class Compiler_Frame extends Compiler
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Frame';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-iframe.css';
    
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
        foreach ($this->getStylesheets() as $stylesheet) {
            $l[] = '<link rel="stylesheet" type="text/css" href="' . $stylesheet . '"/>';
        }
        $l[] = '</head>';

        $className = 'moduleIframe';
        if (isset($this->options['chromeColor'])) {
            $className .= ' ' .  $this->options['chromeColor'] . '-module';
        }  
        $l[] = '<body class="' . $className . '">';

        $l[] = $this->getHtmlBody();

        $l[] = '</body>';
        $l[] = '</html>';

        return implode("\n", $l);
    }
    
    public function getHtmlBody()
    {
        $l = array();

        if (isset($this->options['displayHeader']) && $this->options['displayHeader'] == '1') {
            $l[] = $this->_getHtmlHeader();
        }

        $l[] = '<div class="moduleContent" id="moduleContent">';
        $l[] = $this->_widget->getBody();
        $l[] = '</div>';

        if (isset($this->options['displayStatus']) && $this->options['displayStatus'] == '1') {
            $l[] = $this->_getHtmlStatus();
        }

        $l[] = $this->_getJavascriptConstants();

        foreach ($this->_getJavascripts() as $script) {
            $l[] = '<script type="text/javascript" src="' . $script . '"></script>';
        }

        $l[] = '<script type="text/javascript">';
        $l[] = $this->_getFrameScript();
        $l[] = '</script>';

        return implode("\n", $l);
    }

    public function getStylesheets()
    {
        return $this->_getStylesheets();
    }

    private function _getFrameScript()
    {
        $l = array();

        $proxies = array(
            'ajax' => Zend_Registry::get('proxyEndpoint') . '/ajax',
            'feed' => Zend_Registry::get('proxyEndpoint') . '/feed'
        );

        $l[] = sprintf('UWA.proxies = %s;', Zend_Json::encode($proxies));

        if (isset($this->options['properties'])) {
            foreach ($this->options['properties'] as $key => $value) {
                if (isset($value)) {
                    $l[] = sprintf("widget.%s = %s;", $key, Zend_Json::encode($value));
                }
            }
        }

        if (isset($this->options['data']) && count($this->options['data'])) {
            $l[] = sprintf("UWA.extend(widget.data, %s);", Zend_Json::encode($this->options['data']));
        }

        $script = $this->_widget->getScript();
        if (!empty($script)) {
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
