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
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment;

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet;

    /**
     * Platform Name.
     *
     * @var string
     */
    protected $_platform;
        
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

        $baseLibraries = array(
            'String.js',
            'Array.js',
            'Element.js',
            'Data.js',
            'Environment.js',
            'Widget.js',
            'Utils.js',
            'Utils/Client.js',
            'Controls/PrefsForm.js'
        );

        $this->_coreLibraries['uwa'] = array_merge(
            array('UWA.js', 'Drivers/UWA-alone.js', 'Drivers/UWA-legacy.js'), $baseLibraries);

        $this->_coreLibraries['uwa-mootools'] = array_merge(
            array('../lib/mootools.js', 'UWA.js', 'Drivers/UWA-mootools.js'), $baseLibraries);
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
     * Set the UWA javascript environment
     *
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->_environment = $environment;
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
        $NVstylesheets = array(Zend_Registry::get('uwaCssDir') . $this->_stylesheet);
        if (isset($_GET['NVthemeUrl'])) {
            $NVstylesheets[] = 'http://' . NV_STATIC . $_GET['NVthemeUrl'];
        }
        foreach ($NVstylesheets as $stylesheet) {
            if (Zend_Registry::get('useMergedCss')) {
                $stylesheet = str_replace('.css', '.css.m.css', $stylesheet);
            }
            $stylesheets[] = $stylesheet . '?v=' . Zend_Registry::get('cssVersion');
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
                    $javascripts[] = Zend_Registry::get('uwaJsDir') .
                        'UWA_' . ucfirst($this->_environment) . '.js?v=' . $version;
                    break;
                case 'uwa-mootools':
                    $javascripts[] = Zend_Registry::get('uwaJsDir') .
                        'UWA_' . ucfirst($this->_environment) . '_Mootools.js?v=' . $version;
                    break;
                default:
                    throw new Exception('CoreLibrary name not known.');

            }
        } else {
            if (empty($this->_coreLibraries[$coreLibraryName])) {
                throw new Exception('CoreLibrary name not known.');
            }
            foreach ($this->_coreLibraries[$coreLibraryName] as $js) {
                $javascripts[] = Zend_Registry::get('uwaJsDir') .
                    $js . '?v=' . $version;
            }
            if (isset($this->_environment)) {
                $javascripts[] = Zend_Registry::get('uwaJsDir') .
                    'Environments/' . ucfirst($this->_environment) . '.js?v=' . $version;
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
    protected function _getJavascripts($options = array())
    {
        $javascripts = $this->_getCoreLibraries();

        // Widget script
        $jsBaseUrl = Zend_Registry::get('widgetEndpoint')  . '/js';
        if (isset($this->options['uwaId'])) {
            $widgetJs = $jsBaseUrl . '/' . urlencode($this->options['uwaId']);
            if (isset($options['platform'])) {
                $widgetJs .= '?platform=' . $options['platform'];
            }
        } else {
            $widgetJs = $jsBaseUrl . '?uwaUrl=' . urlencode($this->_widget->getUrl());
            if (isset($options['platform'])) {
                $widgetJs .= '&platform=' . $options['platform'];
            }
        }
        $javascripts[] = $widgetJs;

        // Merge with external scripts
        return array_merge($javascripts, $this->_widget->getExternalScripts());
    }

    /**
     * Retrieves widget HTML header (moduleHeader)
     *
     * @return string
     */
    protected function _getHtmlHeader()
    {   
        $icon = $this->_widget->getIcon();
        if (empty($icon)) {
            $icon = 'http://' . NV_STATIC . '/modules/uwa/icon.png';
        }
        
        $html  = '<div class="moduleHeaderContainer">' . "\n";
        $html .= '  <div class="moduleHeader" id="moduleHeader">' . "\n";
        $html .= '    <a id="editLink" class="edit" style="display:none" href="javascript:void(0)">Edit</a>' . "\n";
        $html .= '    <a id="moduleIcon" class="ico">' . "\n";
        $html .= '      <img class="hicon" width="16" height="16" src="' . $icon . '"/>' . "\n";
        $html .= '    </a>' . "\n";
        $html .= '    <span id="moduleTitle" class="title">' . $this->_widget->getTitle() . '</span>' . "\n";
        $html .= '  </div>' . "\n";
        $html .= '</div>' . "\n";

        $html .= '<div class="editContent" id="editContent" style="display:none"></div>';

        return $html;
    }

    /**
     * Retrieves widget HTML status bar (moduleStatus)
     *
     * @return string
     */
    protected function _getHtmlStatus()
    {
        $shareUrl = 'http://' . NV_ECO . '/share/?url=' . str_replace('.', '%2E', urlencode($this->_widget->getUrl()));

        $html  = '<div id="moduleStatus" class="moduleStatus">' . "\n";
        $html .= '<a href="' . $shareUrl . '" title="Share this widget" class="share" target="_blank">';
        $html .= '<img src="'. Zend_Registry::get('uwaImgDir') .'share.png" alt="Share this widget"/>';
        $html .= '</a>' . "\n";
        $html .= '<a href="http://www.netvibes.com/" class="powered" target="_blank">powered by netvibes</a>' . "\n";
        $html .= '</div>';

        return $html;
    }

    /**
     * Retrieves a script containing NV_* constants
     *
     * @return string
     */    
    protected function _getJavascriptConstants()
    {
        $html  = '<script type="text/javascript">' . "\n";
        $html .= "var NV_HOST = '" . NV_HOST . "', NV_PATH = 'http://" . NV_HOST . "/', NV_STATIC = 'http://" . NV_STATIC . "', " . "\n";
        $html .= "NV_MODULES = '". NV_MODULES ."', NV_AVATARS = '". NV_AVATARS ."';" . "\n";
        $html .= '</script>';
        
        return $html;
    }

    /*** ABSTRACT FUNCTIONS ***/

    abstract public function render();
}
