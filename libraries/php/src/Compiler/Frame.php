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
        $scripts = $this->_getJavascripts();

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

        $className = 'moduleIframe';
        if (isset($this->options['chromeColor'])) {
            $className .= ' ' .  $this->options['chromeColor'] . '-module';
        }
        $l[] = '<body class="' . $className . '">';

        // Widget header
        if (isset($this->options['displayHeader']) && $this->options['displayHeader'] == '1') {
            $l[] = '<div class="moduleHeader" id="moduleHeader">';
            $l[] = '<a id="editLink" class="edit" href="#">Edit</a>';
            $l[] = '<div class="ico" id="moduleIcon">&nbsp;</div>';
            $l[] = '<div id="moduleTitle" class="title">' . $this->_widget->getTitle() . '</div>';
            $l[] = '</div>';

            $l[] = '<div class="editContent optionContent configureContent" id="editContent" style="display: none;"></div>';
        }

        // Widget content
        $l[] = '<div class="moduleContent" id="moduleContent">';
        $l[] = $this->_widget->getBody();
        $l[] = '</div>';

        // Widget status
        if (isset($this->options['displayStatus']) && $this->options['displayStatus'] == '1') {
            $l[] = '<div id="moduleStatus" class="moduleStatus">';
            $l[] = '<a href="http://eco.netvibes.com/share/?url=' . str_replace('.', '%2E', urlencode($this->_widget->getUrl())) . '" title="Share this widget" class="share" target="_blank"><img src="'. Zend_Registry::get('uwaImgDir') .'share.png" alt="Share this widget"/></a>';
            $l[] = '<a href="http://www.netvibes.com/" class="powered" target="_blank">powered by netvibes</a>';
            $l[] = '</div>';
        }

        // Scripts
        $l[] = '<script type="text/javascript">';
        $l[] = "var NV_HOST = '" . NV_HOST . "', NV_PATH = '/', NV_STATIC = 'http://" . NV_STATIC . "', " .
            "NV_MODULES = '". NV_MODULES ."', NV_AVATARS = '". NV_AVATARS ."';";
        $l[] = '</script>';

        foreach ($scripts as $script) {
            $l[] = '<script type="text/javascript" src="' . $script . '"></script>';
        }

        $l[] = '<script type="text/javascript">';
        $l[] = $this->_getFrameScript();
        $l[] = '</script>';

        $l[] = '</body>';
        $l[] = '</html>';

        return implode("\n", $l);
    }

    protected function _getJavascripts()
    {
        $javascripts = parent::_getJavascripts();

        if (isset($this->options['displayHeader']) && $this->options['displayHeader'] == '1') {
            // Temporary - should be switched to uwaJsDir when it will be available there
            $javascripts[] = 'http://cdn.netvibes.com/js/c/UWA_Controls_PrefsForm.js';
        }

        if (isset($_GET['libs'])) {
            $libraries = split(',', $_GET['libs']);
            foreach ($libraries as $script) {
                if (preg_match('@^[a-z0-9/._-]+$@i', $script) && !preg_match('@([.][.])|([.]/)|(//)@', $script)) {
                    $javascripts[] = "http://www.google.com/ig/f/$script";
                }
            }
        }

        return $javascripts;
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
