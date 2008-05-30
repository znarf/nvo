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
 * UWA compilation utilities.
 */
class Compiler_Uwa extends Compiler
{    
    /**
     * Main rendering function.
     *
     * @return string
     */
    public function render()
    {
        $style = $this->_widget->getStyle();
        $script = $this->_widget->getCompressedScript();
        $preferences = $this->_widget->getPreferences();
        
        $l = array();
        
        $l[] = '<?xml version="1.0" encoding="utf-8"?>';
        $l[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
            ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $l[] = '<html xmlns="http://www.w3.org/1999/xhtml"'.
            ' xmlns:widget="http://www.netvibes.com/ns/">';
        $l[] = '<head>';
        $l[] = '<title>' . $this->_widget->getTitle() . '</title>';
        $l[] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        
        foreach ($this->_widget->getMetadata() as $key => $value) {
            $l[] = '<meta name="' . $key . '" content="' . $value . '" />';
        }
        
        $l[] = '<link rel="stylesheet" type="text/css"'.
            ' href="http://' . NV_HOST . '/themes/uwa/style.css"/>';
        
        $coreLibrary = $this->_widget->getCoreLibrary();
        $externalScripts = $this->_widget->getExternalScripts();
        
        if (empty($externalScripts) ) {
            $library = 'http://' . NV_HOST . '/js/UWA/load.js.php?env=Standalone';
        } else if ($coreLibrary == 'uwa') {
            $library = 'http://' . NV_HOST . '/js/c/UWA_Standalone.js';
        } else if ($coreLibrary == 'uwa-mootools') {
            $library = 'http://' . NV_HOST . '/js/c/UWA_Standalone_Mootools.js';
        }

        $l[] = '<script type="text/javascript" src="' . $library .'"></script>';

        foreach ($externalScripts as $javascript) {
            $l[] = '<script type="text/javascript" src="' . $javascript . '"></script>';
        }

        if (isset($preferences) && count($preferences) > 0) {
            $l[] = '<widget:preferences>';
            foreach ($preferences as $pref) {
                $l[] = $this->_getPreferenceXml($pref);
            }
            $l[] = '</widget:preferences>';
        }
        if (isset($style) && strlen($style) > 0) {
            $l[] = '<style type="text/css">'. "\n" . $style . '</style>';
        }
        if (isset($script) && strlen($script) > 0) {
            $l[] = '<script type="text/javascript"><![CDATA[';
            $l[] = $script;
            $l[] = ']]></script>';
        }
        $l[] = '</head>';
        $l[] = '<body>';
        $l[] = $this->_widget->getBody();
        $l[] = '</body>';
        $l[] = '</html>';

        return implode("\n", $l);
    }

    public function _getPreferenceXml($preference)
    {
        $preference = $preference->toArray();
        $xml = "<preference";
        foreach($preference as $key => $value) {
            if ($key != "options") $xml .= " $key=\"$value\"";
        }
        switch ($preference['type']) {
            case 'list':
                $xml .= ">\n";
                if (isset($preference['options']) && count($preference['options']) > 0) {
                    foreach ($preference['options'] as $opt) {
                        $xml .= sprintf("  <option value=\"%s\" label=\"%s\" />\n", $opt['value'], $opt['label']);
                    }
                }
                $xml .= "</preference>";
                break;
            default:
                $xml .= "/>";
                break;

        }
        return $xml;
    }

    /**
     * Renders the controller within a JavaScript closure.
     */
    public function renderJavaScriptRaw()
    {
        $l = array();
        
        $l[] = "(function(){";
        $l[] = $this->_widget->getCompressedScript();
        $l[] = "widget.setMetas(" . $this->_widget->getMetadataJson() . ");";
        $l[] = "widget.setPreferences(" . $this->_widget->getPreferencesJson() . ");";
        $l[] = "})();";
        
        return implode("\n", $l);
    }

    /**
     * Renders the controller as a JavaScript class.
     */
    public function renderJavaScriptFunction()
    {
        $l = array();
        
        if (isset($this->options['uwaId'])) {
            $l[] = sprintf("UWA.Scripts['%s']=UWA.script=function(widget){", $this->options['uwaId']);
        } else {
            $l[] = "UWA.script=function(widget){";
            // echo "UWA.Widgets['" . md5($this->_widget->getUrl()) . "'] = ";  
        }
        
        $l[] = $this->_widget->getCompressedScript();
        $l[] = "widget.setMetas(" . $this->_widget->getMetadataJson() . ");";
        $l[] = "widget.setPreferences(" . $this->_widget->getPreferencesJson() . ");";
        $l[] = "return widget;";
        
        $l[] = "}";
        
        return implode("\n", $l);
    }
    
    /**
     * Renders the controller as a JavaScript closure.
     */
    public function renderJs()
    {
        return $this->renderJavaScriptFunction();
    }

    /**
     * Renders the styles information.
     */
    public function renderCss()
    {
        return $this->_widget->getStyle();
    }
}
