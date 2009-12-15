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


/**
 * Compiler main class.
 */
abstract class Exposition_Compiler
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
    public function __construct($widget, array $options = array())
    {
        $this->_widget = $widget;

        $this->setOptions($options);

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
        $cssEndPoint = Exposition_Load::getConfig('endpoint', 'css');
        $cssCompressed = Exposition_Load::getConfig('css', 'compressed');
        $cssVersion = Exposition_Load::getConfig('css', 'version');
        $widgetEndPoint = Exposition_Load::getConfig('endpoint', 'widget');

        $defaultStylesheets = array($cssEndPoint . '/'  . $this->_stylesheet);
        if (isset($_GET['NVthemeUrl'])) {

            $staticEndPoint = Exposition_Load::getConfig('endpoint', 'static');
            $defaultStylesheets[] = 'http://' . $staticEndPoint . $_GET['NVthemeUrl'];
        }

        $stylesheets = array();
        foreach ($defaultStylesheets as $defaultStylesheet) {

            if ($cssVersion) {
                $cssEndPoint = str_replace('.css', '.css.m.css', $defaultStylesheet);
            }

            $stylesheets[] = $defaultStylesheet . '?v=' . $cssVersion;
        }

        // Widget style
        $style = $this->_widget->getStyle();
        if (!empty($style)) {

            $cssBaseUrl = $widgetEndPoint . '/css';

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

        $jsEndPoint = Exposition_Load::getConfig('endpoint', 'js');
        $jsCompressed = Exposition_Load::getConfig('js', 'compressed');
        $jsVersion = Exposition_Load::getConfig('js', 'version');

        if ($jsCompressed) {

            switch ($coreLibraryName) {
                case 'uwa':
                    $javascripts[] = $jsEndPoint . '/UWA_' . ucfirst($this->_environment) . '.js?v=' . $jsVersion;
                    break;
                case 'uwa-mootools':
                    $javascripts[] = $jsEndPoint . '/UWA_' . ucfirst($this->_environment) . '_Mootools.js?v=' . $jsVersion;
                    break;
                default:
                    throw new Exception('CoreLibrary name not known.');
            }

        } else {

            if (empty($this->_coreLibraries[$coreLibraryName])) {
                throw new Exposition_Exception('CoreLibrary name not known.');
            }

            foreach ($this->_coreLibraries[$coreLibraryName] as $js) {
                $javascripts[] = $jsEndPoint . $js . '?v=' . $jsVersion;
            }

            if (isset($this->_environment)) {
                $javascripts[] = $jsEndPoint . '/Environments/' . ucfirst($this->_environment) . '.js?v=' . $jsVersion;
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
        $widgetEndPoint = Exposition_Load::getConfig('endpoint', 'widget');
        $jsBaseUrl = $widgetEndPoint . '/js';

        $urlOptions = array();
        if (isset($this->options['uwaId'])) {
            $jsBaseUrl = $jsBaseUrl . '/' . urlencode($this->options['uwaId']);
        } else {
            $urlOptions['uwaUrl'] = $this->_widget->getUrl();
        }

        // Allowed Scripts Options
        if (isset($options['platform'])) {
            $urlOptions['platform'] = $options['platform'];
        }

        if (isset($options['className'])) {
            $urlOptions['className'] = $options['className'];
        }

        $javascripts[] = $jsBaseUrl . (!empty($urlOptions) ? '?' . http_build_query($urlOptions) : '');

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
            $staticEndPoint = Exposition_Load::getConfig('endpoint', 'static');
            $icon = 'http://' . $staticEndPoint . '/icon.png';
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
        $staticEndPoint = Exposition_Load::getConfig('endpoint', 'static');
        $nvEcoEndPoint = Exposition_Load::getConfig('endpoint', 'nvEco');

        $shareUrl = $nvEcoEndPoint . '/share/?url=' . str_replace('.', '%2E', urlencode($this->_widget->getUrl()));

        $html  = '<div id="moduleStatus" class="moduleStatus">' . "\n";
        $html .= '<a href="' . $shareUrl . '" title="Share this widget" class="share" target="_blank">';
        $html .= '<img src="'. $staticEndPoint .'/share.png" alt="Share this widget"/>';
        $html .= '</a>' . "\n";
        $html .= '<a href="http://dev.netvibes.org/" class="powered" target="_blank">Powered by uwa</a>' . "\n";
        $html .= '</div>';

        return $html;
    }

    /**
     * Retrieves a script containing UWA_* constants
     *
     * @return string
     */
    protected function _getJavascriptConstants()
    {
        // Exposition Server
        $widgetEndPoint = Exposition_Load::getConfig('endpoint', 'widget');
        $jsEndPoint = Exposition_Load::getConfig('endpoint', 'js');
        $cssEndPoint = Exposition_Load::getConfig('endpoint', 'css');
        $proxyEndPoint = Exposition_Load::getConfig('endpoint', 'proxy');
        $staticEndPoint = Exposition_Load::getConfig('endpoint', 'static');

        // Netvibes
        $nvAvatarEndPoint = Exposition_Load::getConfig('endpoint', 'nvAvatar');
        $nvEcoEndPoint = Exposition_Load::getConfig('endpoint', 'nvEco');

        $vars = array(
            "UWA_WIDGET = '" . $widgetEndPoint . "'",
            "UWA_JS = '" . $jsEndPoint . "'",
            "UWA_CSS = '" . $cssEndPoint . "'",
            "UWA_PROXY = '" . $proxyEndPoint . "'",
            "UWA_STATIC = '" . $staticEndPoint . "'",
        );

        $html = '<script type="text/javascript">' . "\n"
              . "var " . implode("\n, ", $vars) . "\n"
              . '</script>';

        return $html;
    }

    /*** ABSTRACT FUNCTIONS ***/

    abstract public function render();
}

