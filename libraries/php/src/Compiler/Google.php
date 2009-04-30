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
 * Google Compiler to render a widget as a Google Gadget specification
 */
class Compiler_Google extends Compiler
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Google';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-iframe.css';

    /**
     * Platform Name.
     *
     * @var string
     */
    protected $_platform = 'google';

    /**
     * Main rendering function.
     *
     * @return string
     */
    public function render()
    {
        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8"?>';

        $l[] = '<Module>';

        $metas = $this->_widget->getMetas();

        $defaultScreenshot = Zend_Registry::get('uwaImgDir') . 'uwa-screenshot.png';
        $defaultThumbnail  = Zend_Registry::get('uwaImgDir') . 'uwa-thumbnail.png';

        $googleMetas = array(
            'title'        => $this->_widget->getTitle(),
            'height'       => 200,
            'description'  => isset($metas['description']) ? $metas['description'] : '',
            'author'       => isset($metas['author']) ? $metas['author'] : '',
            'author_email' => isset($metas['email']) ? $metas['email'] : '',
            'screenshot'   => isset($metas['screenshot']) ? $metas['screenshot'] : $defaultScreenshot,
            'thumbnail'    => isset($metas['thumbnail']) ? $metas['thumbnail'] : $defaultThumbnail
        );

        $modulePrefs = '<ModulePrefs';
        foreach ($googleMetas as $key => $value) {
            $k = htmlspecialchars($key); 
            $v = htmlspecialchars($value); 
            $modulePrefs .= " $k=\"$v\"";
        }
        $modulePrefs .= '>';

        $l[] = $modulePrefs;

        $l[] = '<Require feature="setprefs"/>';
        $l[] = '<Require feature="dynamic-height"/>';
        $l[] = '<Require feature="settitle"/>';

        $l[] = '</ModulePrefs>';

        $l[] = $this->_getPreferences();

        if ($this->options['type'] == 'html') {

            $l[] = '<Content type="html"><![CDATA[';

            $l[] = '<style type="text/css">';
            foreach ($this->_getStylesheets() as $stylesheet) {
                $l[] = '@import "' . $stylesheet . '";';
            }
            $l[] = 'body,td,div,span,p{font-family:inherit;}';
            $l[] = 'a {color:inherit;}a:visited {color:inherit;}a:active {color:inherit;}';
            $l[] = 'body{margin: auto;padding: auto;background-color:transparent;}';
            $l[] = '</style>';

            $l[] = '<div id="moduleContent" class="moduleContent">';
            $l[] = $this->_widget->getBody();
            $l[] = '</div>';

            $l[] = $this->_getHtmlStatus();

            $l[] = $this->_getJavascriptConstants();

            $javascripts = $this->_getJavascripts( array('platform' => $this->_platform) );
            
            foreach ($javascripts as $javascript) {
                $l[] = '<script type="text/javascript" src="' . $javascript . '"></script>';
            }

            $l[] = '<script type="text/javascript">';
            $l[] = $this->_getFrameScript();
            $l[] = '</script>';

            $l[] = ']]></Content>';

        } else if ($this->options['type'] == 'url') {

            $url = Zend_Registry::get('widgetEndpoint') . '/frame?platform=igoogle&uwaUrl=' . urlencode($this->_widget->getUrl());

            $l[] = '<Content type="url" href="' . htmlspecialchars($url) . '"></Content>';

        }

        $l[] = '</Module>';

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

        $script = $this->_widget->getScript();
        if (isset($this->options['uwaId'])) {
            $l[] = sprintf("UWA.Scripts['%s'](widget);", $this->options['uwaId']);
        } else {
            $l[] = "UWA.script(widget);";
        }

        $l[] = "Environment.launchModule();";

        return implode("\n", $l);
    }

    private function _getPreferences()
    {
        $string = '';
        $dom = new DOMDocument('1.0', 'utf-8');
        $preferences = $this->_widget->getPreferences();
        foreach($preferences as $preference) {
            $preference = $preference->toArray();
            $element = $dom->createElement('UserPref');
            $element->setAttribute('name', $preference['name']);
            if (isset($preference['defaultValue'])) $element->setAttribute('default_value', $preference['defaultValue']);
            if (isset($preference['label'])) $element->setAttribute('display_name', $preference['label']);
            switch($preference['type']) {
                case 'list':
                    $element->setAttribute('datatype', 'enum');
                    if (isset($preference['options']) && count($preference['options']) > 0) {
                        foreach ($preference['options'] as $option) {
                            $value = $dom->createElement('EnumValue');
                            $value->setAttribute('value', $option['value']);
                            $value->setAttribute('display_value', $option['label']);
                            $element->appendChild($value);
                        }
                    }
                    break;
                case 'range':
                    $element->setAttribute('datatype', 'enum');
                    for($i = $preference['min']; $i <= $preference['max']; $i += $preference['step'] ) {
                        $value = $dom->createElement('EnumValue');
                        $value->setAttribute('value', $i);
                        $element->appendChild($value);
                    }
                    break;
                case 'boolean':
                    $element->setAttribute('datatype', 'bool');
                    break;
                case 'hidden':
                    $element->setAttribute('datatype', 'hidden');
                    break;
                case 'string':
                default:
                    $element->setAttribute('datatype', 'string');
                    break;
            }
            $string .= $dom->saveXML($element) . "\n";
        }
        return $string;
    }
}
