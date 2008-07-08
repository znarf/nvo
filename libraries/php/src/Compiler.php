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


/**
 * Compiler main class.
 */
abstract class Compiler
{
    /**
     * Widget instance.
     *
     * @var Widget
     */
    protected $_widget;

    /**
     * JavaScript environment name.
     *
     * @var string
     */
    protected $_environment;

    /**
     * JavaScript core libraries.
     *
     * @var array
     */
    protected $_coreLibraries;

    /**
     * Compiler options.
     *
     * @var array
     */
    public $options = array();

    /**
     * Constructor.
     *
     * @param Widget $widget
     */
    public function __construct($widget)
    {
        $this->_widget = $widget;

        $this->_coreLibraries['uwa'] = array(
            'UWA.js',
            'Drivers/UWA-alone.js',
            'Drivers/UWA-legacy.js',
            'String.js',
            'Array.js',
            'Element.js',
            'Data.js',
            'Environment.js',
            'Widget.js',
            'Utils.js',
            'Utils/Client.js',
        );

        $this->_coreLibraries['uwa-mootools'] = array(
            '../lib/mootools.js',
            'UWA.js',
            'Drivers/UWA-mootools.js',
            'String.js',
            'Array.js',
            'Element.js',
            'Data.js',
            'Environment.js',
            'Widget.js',
            'Utils.js',
            'Utils/Client.js',
        );
    }

    /**
     * Sets the options.
     *
     * @param  array    $options
     * @return Compiler Object instance
     */
    public function setOptions($options = array())
    {
        foreach ($options as $k => $v) {
            $this->options[$k] = $v;
        }
        return $this;
    }

    /**
     * Retrieves the list of the stylesheets used in a widget.
     *
     * @return array
     */
    protected function _getStylesheets()
    {
        $stylesheets = array();

        // Netvibes stylesheets
        $NVstylesheets = array(Zend_Registry::get('uwaCssDir') . 'uwa-iframe.css?v=' . Zend_Registry::get('cssVersion'));
        if (isset($_GET['NVthemeUrl'])) {
            $NVstylesheets[] = 'http://' . NV_STATIC . $_GET['NVthemeUrl'];
        }
        foreach ($NVstylesheets as $stylesheet) {
            if (Zend_Registry::get('useMergedCss')) {
                $stylesheet = str_replace('.css', '.css.m.css', $stylesheet);
            }
            $stylesheets[] = $stylesheet . '?v=' . Zend_Registry::get('jsVersion');
        }

        // Widget style
        $style = $this->_widget->getStyle();
        if ( !empty($style) ) {
            $cssBaseUrl = Zend_Registry::get('widgetEndpoint') . '/css';
            if (isset($this->options['uwaId'])) {
                $stylesheets[] = $cssBaseUrl . '/' . urlencode($this->options['uwaId']);
            } else {
                $stylesheets[] = $cssBaseUrl . '?uwaUrl=' . urlencode($this->_widget->getUrl());
            }
        }

        // Widget external stylesheets
        return array_merge($stylesheets, $this->_widget->getExternalStylesheets());
    }

    /**
     * Retrieves the list of the JavaScript core libraries used in a widget.
     *
     * @param string $name
     * @return array
     */
    protected function _getCoreLibraries($name = 'uwa')
    {
        $javascripts = array();

        $coreLibraryName = $this->_widget->getCoreLibrary();

        $version = Zend_Registry::get('jsVersion');

        if (Zend_Registry::get('useCompressedJs')) {
            switch ($coreLibraryName) {
                case 'uwa':
                    $javascripts[] = Zend_Registry::get('uwaJsDir') . 'UWA_' . $this->_environment . '.js?v=' . $version;
                    break;
                case 'uwa-mootools':
                    $javascripts[] = Zend_Registry::get('uwaJsDir') . 'UWA_' . $this->_environment . '_Mootools.js?v=' . $version;
                    break;
                default:
                    throw new Exception('CoreLibrary name not known.');

            }
        } else {
            if (empty($this->_coreLibraries[$coreLibraryName])) {
                throw new Exception('CoreLibrary name not known.');
            }
            foreach ($this->_coreLibraries[$coreLibraryName] as $js) {
                $javascripts[] = Zend_Registry::get('uwaJsDir') . $js . '?v=' . $version;
            }
            if (isset($this->_environment)) {
                $javascripts[] = Zend_Registry::get('uwaJsDir') . 'Environments/' . $this->_environment . '.js?v=' . $version;
            }
        }

        return $javascripts;
    }

    /**
     * Retrieves the list of the JavaScript libraries used in a widget (both UWA and specific ones).
     *
     * @param string $name
     * @return array
     */
    protected function _getJavascripts()
    {
        $javascripts = $this->_getCoreLibraries();

        // Widget script
        $script = $this->_widget->getScript();
        if (!empty($script)) {
            $jsBaseUrl = Zend_Registry::get('widgetEndpoint')  . '/js';
            if (isset($this->options['uwaId'])) {
                $javascripts[] = $jsBaseUrl . '/' . urlencode($this->options['uwaId']);
            } else {
                $javascripts[] = $jsBaseUrl . '?uwaUrl=' . urlencode($this->_widget->getUrl());
            }
        }

        // Merge with external scripts
        return array_merge($javascripts, $this->_widget->getExternalScripts());
    }

    /*** ABSTRACT FUNCTIONS ***/

    abstract public function render();
}
