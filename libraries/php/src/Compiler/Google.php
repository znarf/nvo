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
class Compiler_Google extends Compiler
{
    /**
     * Environment.
     *
     * @var string
     */
    protected $_environment = 'Google';

    /**
     * Main rendering function.
     *
     * @return string
     */
    public function render()
    {
        $l = array();

        $l[] = '<Module>';

        $metas = $this->_widget->getMetas();

        $defaultScreenshot = 'http://www.netvibes.com/img/uwa-screenshot.png';
        $defaultThumbnail  = 'http://www.netvibes.com/img/uwa-thumbnail.png';

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
            $modulePrefs .= " $key=\"$value\"";
        }
        $modulePrefs .= '>';

        $l[] = $modulePrefs;

        // $l[] = '<Require feature="uwa" />';
        $l[] = '<Require feature="setprefs" />';
        $l[] = '<Require feature="dynamic-height" />';
        $l[] = '<Require feature="settitle" />';

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

            $l[] = '<div id="moduleStatus" class="moduleStatus">';
            $l[] = '<a href="http://eco.netvibes.com/share/?url=' . str_replace('.', '%2E', urlencode($this->_widget->getUrl())) . '" title="Share this widget" class="share" target="_blank"><img src="'. Zend_Registry::get('uwaImgDir') .'share.png" alt="Share this widget"/></a>';
            $l[] = '<a href="http://www.netvibes.com/" class="powered" target="_blank">powered by netvibes</a>';
            $l[] = '</div>';

            $l[] = '<script type="text/javascript">';
            $l[] = "var NV_HOST = '" . NV_HOST . "', NV_PATH = '/', NV_STATIC = '" . NV_STATIC . "', " .
                "NV_MODULES = '". NV_MODULES ."', NV_AVATARS = '". NV_AVATARS ."';";
            $l[] = '</script>';

            foreach ($this->_getJavascripts() as $javascript) {
                $l[] = '<script type="text/javascript" src="' . $javascript . '"></script>';
            }

            $l[] = '<script type="text/javascript">';
            $l[] = $this->_getFrameScript();
            $l[] = '</script>';

            $l[] = ']]></Content>';

        } else if ($this->options['type'] == 'url') {

            $url = Zend_Registry::get('widgetEndpoint') . '/frame?uwaUrl=' . urlencode($this->_widget->getUrl());

            $l[] = '<Content type="url" href="' . $url . '"></Content>';

        }

        $l[] = '</Module>';

        return implode("\n", $l);
    }

    private function _getFrameScript()
    {
        $l = array();

        $proxies = array(
            'ajax' => "http://" . NV_MODULES . "/proxy/ajax",
            'feed' => "http://" . NV_MODULES . "/proxy/feed"
        );

        $l[] = sprintf('UWA.proxies = %s;', Zend_Json::encode($proxies));

        // $l[] = 'Environment = new UWA.Environment();';
        // $l[] = 'widget = Environment.getModule();';

        $script = $this->_widget->getScript();
        if ( !empty($script) ) {
            if (isset($this->options['uwaId'])) {
                $l[] = sprintf("UWA.Scripts['%s'](widget);", $this->options['uwaId']);
            } else {
                $l[] = "UWA.script(widget);";
            }
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
