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


require_once 'Parser/Factory.php';
require_once 'Widget.php';

require_once 'Zend/Json.php';

/**
 * Widgets Container.
 *
 * This class maps some widgets descriptors retrieved from the
 * Netvibes REST API with the associated widgets instances created
 * by the UWA widget parser.
 *
 * It also provides a rendering functionality to embed either inline
 * or iframed widgets.
 */
class Container
{
    /**
     * Widgets descriptors.
     *
     * @var array
     */
    protected $_widgetDescriptors = array();

    /**
     * Widgets instances indexed by their URL.
     *
     * @var array
     */
    protected $_widgetInstances = array();

    /**
     * UWA core libraries.
     *
     * @var array
     */
    protected $_coreLibraries = array(
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

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        // Widget template
        $this->_chromeTemplate =
            '<div class="{class}" id="widget-{id}">' . "\n" .
            '  <div class="moduleFrame">' . "\n" .
            '    <div class="moduleHeaderContainer">' . "\n" .
            '      <div class="moduleHeader">' . "\n" .
            '        <span class="title">{title}</span>' . "\n" .
            '      </div>' . "\n" .
            '    </div>' . "\n" .
            '    {content}' . "\n" .
            '  </div>' . "\n" .
            '</div>' . "\n";

        $this->_inlineTemplate = '<div class="moduleContent">{body}</div>' . "\n" .
            '<script type="text/javascript">{js}</script>';

        $this->_iframeTemplate = '<div class="moduleContent" style="padding:0">' .
            '<iframe id="frame_{id}" src="{iframeUrl}" ' .
                'scrolling="no" frameborder="0" style="display:block" width="{width}" height="{height}">' .
            '</iframe>' .
        '</div>';

        // Widgets arrays
        $this->_widgetDescriptors = array();
        $this->_widgetInstances = array();
    }

    /**
     * Returns the correct list of UWA core libraries
     * according to the compressed JavaScript constant value.
     *
     * @return array
     */
    public function getCoreLibraries()
    {
        $libraries = array();
        $useCompressedJs = Zend_Registry::get('useCompressedJs');
        if ($useCompressedJs) {
            $libraries[] = Zend_Registry::get('uwaJsDir') . 'UWA_Core.js';
        } else {
            foreach ($this->_coreLibraries as $library) {
                $libraries[] = Zend_Registry::get('uwaJsDir') . $library;
            }
        }
        return $libraries;
    }

    /**
     * Sets the widgets descriptors.
     *
     * @param array $widgetDescriptors
     */
    public function setWidgets(array $widgetDescriptors)
    {
        $this->_widgetDescriptors = $widgetDescriptors;
    }

    /**
     * Retrieves all the Netvibes Widgets JavaScript files URL.
     *
     * @return array
     */
    public function getScripts()
    {
        $scripts = array();
        foreach ($this->_widgetDescriptors as $widgetDescriptor) {
            $widgetUrl = $widgetDescriptor->getUrl();
            if (isset($widgetUrl) && $this->_isInlinedWidget($widgetDescriptor)) {
                $widget = $this->_buildWidget($widgetDescriptor);
                $scriptUrl = $widgetDescriptor->getScriptUrl();
                $scripts[] = $scriptUrl;
                $scripts = array_merge($scripts, $widget->getExternalScripts());
            }
        }
        return array_unique($scripts);
    }

    /**
     * Retrieves all the Netvibes Widgets Stylesheets URL.
     *
     * @return array
     */
    public function getStylesheets()
    {
        $stylesheets = array();
        foreach ($this->_widgetDescriptors as $widgetDescriptor) {
            $widgetUrl = $widgetDescriptor->getUrl();
            if (isset($widgetUrl) && $this->_isInlinedWidget($widgetDescriptor)) {
                $widget = $this->_buildWidget($widgetDescriptor);
                $stylesheets[] = $widgetDescriptor->getStylesheetUrl();
                $stylesheets = array_merge($stylesheets, $widget->getExternalStylesheets());
            }
        }
        return array_unique($stylesheets);
    }

    /**
     * Renders the widgets for a given tab column number.
     *
     * @param int $column
     */
    public function renderWidgetsChrome($column = null)
    {
        foreach ($this->_widgetDescriptors as $widgetDescriptor) {
            if ((isset($column) && $widgetDescriptor->getCol() == $column) || !isset($column)) {
                echo $this->renderWidgetChrome($widgetDescriptor);
            }
        }
    }

    /**
     * Builds the widget instance from a given Netvibes widget descriptor.
     *
     * @param Netvibes_Widget $widgetDescriptor
     * @return Widget
     */
    private function _buildWidget(Netvibes_Widget $widgetDescriptor)
    {
        $widget = null;
        $widgetUrl = $widgetDescriptor->getUrl();
        if (!empty($widgetUrl)) {
            if (!isset($this->_widgetInstances[$widgetUrl])) {
                // Parse the UWA widget from the given URL
                $parser = Parser_Factory::getParser('uwa', $widgetUrl);
                $this->_widgetInstances[$widgetUrl] = $parser->buildWidget();
            }
            $widget = $this->_widgetInstances[$widgetUrl];
        } else {
            // Create an empty widget
            $widget = new Widget();
            $widget->setBody('<p>This widget cannot be displayed.</p>');
        }
        return $widget;
    }

    /**
     * Renders a widget from its descriptor, either in inline or iframe mode.
     *
     * @param Netvibes_Widget $widgetDescriptor
     */
    public function renderWidgetChrome(Netvibes_Widget $widgetDescriptor)
    {
        $widgetUrl = $widgetDescriptor->getUrl();
        if ($this->_isInlinedWidget($widgetDescriptor) || empty($widgetUrl)) {
            return $this->_renderWidgetChromeInlined($widgetDescriptor);
        } else {
            return $this->_renderWidgetChromeIframed($widgetDescriptor);
        }
    }

    /**
     * Renders a widget in inline mode.
     *
     * @param Netvibes_Widget $widgetDescriptor
     * @return string
     */
    private function _renderWidgetChromeInlined(Netvibes_Widget $widgetDescriptor)
    {
        $id = $widgetDescriptor->getId();
        $script = $widgetDescriptor->getScriptUrl();
        $widget = $this->_buildWidget($widgetDescriptor);

        $template = $this->_renderTemplate($this->_chromeTemplate, array('content' => $this->_inlineTemplate));
        if (!empty($script)) {
            $jsOptions = array(
                'id'         => $id,
                'chromeId'   => "widget-$id",
                'skeletonId' => $widgetDescriptor->getSkeletonId(),
                'data'       => $widgetDescriptor->getData()
            );
            $js = sprintf("UWA.Widgets.push(%s);", Zend_Json::encode($jsOptions));
        }
        $tplOptions = array(
            'id'    => $widgetDescriptor->getId(),
            'title' => $widgetDescriptor->getTitle(),
            'class' => $this->_getWidgetClass($widgetDescriptor),
            'body'  => $widget->getBody(),
            'js'    => isset($js) ? $js : ''
        );
        return $this->_renderTemplate($template, $tplOptions);
    }

    /**
     * Renders a widget in iframe mode.
     *
     * @param Netvibes_Widget $widgetDescriptor
     * @return string
     */
    private function _renderWidgetChromeIframed(Netvibes_Widget $widgetDescriptor)
    {
        $height = $widgetDescriptor->getHeight();
        $template = $this->_renderTemplate($this->_chromeTemplate, array('content' => $this->_iframeTemplate));
        $tplOptions = array(
            'id'        => $widgetDescriptor->getId(),
            'title'     => $widgetDescriptor->getTitle(),
            'class'     => $this->_getWidgetClass($widgetDescriptor),
            'width'     => '100%',
            'height'    => empty($height) ? '200' : $height,
            'iframeUrl' => $this->_getIFrameUrl($widgetDescriptor)
        );
        return $this->_renderTemplate($template, $tplOptions);
    }

    /**
     * Returns the widget CSS class name value.
     *
     * @param Netvibes_Widget $widgetDescriptor
     * @return string
     */
    private function _getWidgetClass(Netvibes_Widget $widgetDescriptor)
    {
        $data = $widgetDescriptor->getData();
        $class = 'uwa module';
        if ($widgetDescriptor->getName() != 'UWA') {
            $class .= ' ' . $widgetDescriptor->getName();
        } else {
            $class .= ' ' . $widgetDescriptor->getSkeletonId();
        }
        $color = $widgetDescriptor->getColor();
        if (isset($color)) {
            $class .= ' ' . $color . '-module';
        }
        return $class;
    }

    /**
     * Replaces the variables within the template and return it.
     *
     * @param  string $html
     * @param  array  $infos
     * @return string
     */
    private function _renderTemplate($html, $infos)
    {
        foreach ($infos as $key => $value) {
            $html = str_replace('{' . $key . '}', $value, $html);
        }
        return $html;
    }

    /**
     * Returns the iframe URL for a widget.
     *
     * @param Netvibes_Widget $widgetDescriptor
     * @return string
     */
    private function _getIFrameUrl(Netvibes_Widget $widgetDescriptor)
    {
        $iframeUrl = $widgetDescriptor->getIframeUrl();
        $parseIframeUrl = parse_url($iframeUrl);
        if (empty($iframeUrl) || $parseIframeUrl['host'] != NV_MODULES) {
            $iframeUrl  = 'http://' . NV_MODULES . '/widget/frame?uwaUrl=' . urlencode($widgetDescriptor->getUrl());
            $iframeUrl .= '&id=' . $widgetDescriptor->getId();
        } else {
            $iframeUrl .= '?id=' . $widgetDescriptor->getId();
        }

        $data = $widgetDescriptor->getData();
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                $iframeUrl .= '&' . $key . '=' . urlencode($value);
            }
        }
        if (isset($this->themeUrl)) {
            $iframeUrl .= '&NVthemeUrl=' . urlencode($this->themeUrl);
        }
        if (isset($this->ifproxyUrl)) {
            $iframeUrl .= '&ifproxyUrl=' . urlencode($this->ifproxyUrl);
        }
        return $iframeUrl;
    }

    /**
     * Whether a Netvibes widget must be inlined or iframed.
     *
     * @param Netvibes_Widget $widgetDescriptor
     * @return boolean
     */
    private function _isInlinedWidget(Netvibes_Widget $widgetDescriptor)
    {
        $origin = $widgetDescriptor->getSkeletonOrigin();
        if (Zend_Registry::get('inlineWidgets')) {
            return ($origin == NV_HOST || $origin == 'www.netvibes.com');
        }
        return false;
    }
}
