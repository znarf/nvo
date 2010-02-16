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


require_once 'Exposition/Compiler.php';

/**
 * UWA compilation utilities.
 */
class Exposition_Compiler_Uwa  extends Exposition_Compiler
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Standalone';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-standalone.css';

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
        $l[] = '<html xmlns="http://www.w3.org/1999/xhtml"'.
            ' xmlns:widget="http://www.netvibes.com/ns/">';

        $l[] = '<head>';
        $l[] = '<title>' . $this->_widget->getTitle() . '</title>';
        $l[] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        // Add Widget Metas
        $metas = $this->_widget->getMetadata();
        foreach ($metas as $key => $value) {
            $l[] = '<meta name="' . $key . '" content="' . $value . '" />';
        }

        // Add Widget Icon
        $icon = $this->_widget->getIcon();
        if (!empty($icon) && strlen($icon) > 0) {
            $l[] = '<link rel="icon" type="image/png" href="' . $icon . '" />';
        }

        // Add Widget stylesheet
        $stylesheets = $this->getStylesheets();
        foreach ($stylesheets as $stylesheet) {
            $l[] = '<link rel="stylesheet" type="text/css"'. ' href="' . $stylesheet . '"/>';
        }

        // May Require for debuging
        $l[] = '<script type="text/javascript">';
        $l[] = '//<![CDATA[';
        $l[] = 'window.onerror = function(msg, url, linenumber){';
        $l[] = '    if (typeof console != \'undefined\' && console.log){';
        $l[] = '        console.log("JS error: \'" + msg + "\'\ in " + url + " (line " + linenumber + ")");';
        $l[] = '    }';
        $l[] = '    return true;';
        $l[] = '}';
        $l[] = '//]]>';
        $l[] = '</script>';

        // Add Widget javascript constants
        $l[] =  $this->_getJavascriptConstants();

        // Add Widget javascripts
        $javascripts = $this->_getJavascripts();
        foreach ($javascripts as $javascript) {
            $l[] = '<script type="text/javascript" src="' . $javascript . '"></script>';
        }

        // Add Widget Preferences
        $preferences = $this->_widget->getPreferences();
        if (isset($preferences) && count($preferences) > 0) {
            $l[] = '<widget:preferences>';
            foreach ($preferences as $pref) {
                $l[] = $this->_getPreferenceXml($pref);
            }
            $l[] = '</widget:preferences>';
        }

        // Add Widget Styles
        $style = $this->_widget->getStyle();
        if (isset($style) && strlen($style) > 0) {
            $l[] = '<style type="text/css">'. "\n" . $style . '</style>';
        }

        // Add Widget Javascript
        $script = $this->_widget->getCompressedScript();
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

        $l[] = 'if (typeof UWA == "undefined") var UWA = {};';

        // Render Widget With Id
        if (isset($this->options['uwaId'])) {
            $l[] = 'if (typeof UWA.Scripts == "undefined") UWA.Scripts = [];';
            $l[] = sprintf("UWA.Scripts['%s']=UWA.script=function(widget){", $this->options['uwaId']);

        // Render Widget Without Id
        } else {
            $l[] = 'UWA.script=function(widget){';
        }

        $l[] = sprintf('widget.uwaUrl = %s;', Zend_Json::encode($this->_widget->getUrl()));

        // Render Widget Javascript
        $l[] = $this->_widget->getCompressedScript();

        // Render Widget Metas
        $metas = $this->_widget->getMetas();
        $l[] = sprintf('widget.setMetas(%s);', Zend_Json::encode($metas));

        // Render Widget Preferences
        $preferences = $this->_widget->getPreferencesArray();
        if (count($preferences) > 0) {
            $l[] = sprintf('widget.setPreferences(%s);', Zend_Json::encode($preferences));
        }

        // Render Widget Body
        $body = $this->_widget->getBody();
        if (!empty($body) && $body != '<p>Loading...</p>') {
            $l[] = sprintf('widget.setBody(%s);', Zend_Json::encode($body));
        }

        $l[] = 'return widget;';
        $l[] = '}';

        return implode("\n", $l);
    }

    /**
     * Renders the controller as a JavaScript class.
     */
    public function renderJavaScriptClass()
    {
        $className = preg_replace('/[^a-z0-9]/i', '', $this->options['className']);

        $l = array();
        $l[] = 'if (typeof UWA == "undefined") var UWA = {};';
        $l[] = 'if (typeof UWA.Classes == "undefined") UWA.Classes = {};';
        $l[] = 'UWA.Classes.' . $className . ' = function(Environment, classOptions) {';
        $l[] = '    var schema = {"title":"string","icon":"string","metas":"object","preferences":"object","style":"string","body":"string","template":"object","feeds":"object"};';
        $l[] = '    var options = {"preferences":true,"title":true,"style":false,"body":true,"script":true,"template":true,"metas":true,"icon":true};';
        $l[] = '    UWA.extend(options, classOptions);';
        $l[] = '    var widget = Environment.getModule();';

        $icon = $this->_widget->getIcon();
        if (empty($icon)) {
            $staticEndPoint = Exposition_Load::getConfig('endpoint', 'static');
            $icon = 'http://' . $staticEndPoint . '/icon.png';
        }

        // build skeleton var
        $skeleton = (object) array(
            'title'         => $this->_widget->getTitle(),
            'icon'          => $icon,
            'metas'         => $this->_widget->getMetas(),
            'preferences'   => $this->_widget->getPreferencesArray(),
            'body'          => $this->_widget->getBody(),
            'style'         => $this->_widget->getStyle(),
        );

        $l[] = '    var skeleton = ' . Zend_Json::encode($skeleton);
        $l[] = '    for (var key in options) {';
        $l[] = '        if(key == "script" && options[key]) {';
        $l[] = '            if(skeleton.inline) {';
        $l[] = '                widget.onLoad = null;';
        $l[] = '                widget.preferences = [];';
        $l[] = '            };';

        $l[] = $this->_widget->getScript();

        $l[] = '            continue;';
        $l[] = '        } else if(options[key] && typeof skeleton[key] == schema[key]) {';
        $l[] = '            var fnName = "set" + key.capitalize();';
        $l[] = '            if(widget[fnName]) widget[fnName](skeleton[key]);';
        $l[] = '        }';
        $l[] = '    };';
        $l[] = '    Environment.launchModule()';
        $l[] = '}';

        return implode("\n", $l);
    }

    /**
     * Renders the controller as a JavaScript closure.
     */
    public function renderJs()
    {
        if (isset($this->options['className'])) {
            return $this->renderJavaScriptClass();
        } else {
            return $this->renderJavaScriptFunction();
        }
    }

    /**
     * Renders the styles information.
     */
    public function renderCss()
    {
        return $this->_widget->getStyle();
    }
}

