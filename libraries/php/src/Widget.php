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
 * This class represents a widget instance.
 */
class Widget
{
    /**
     * Widget URL.
     *
     * @var string
     */
    protected $_url;

    /**
     * Widget title.
     *
     * @var string
     */
    protected $_title;

    /**
     * Widget metadata.
     *
     * @var array
     */
    protected $_metadata;

    /**
     * Widget preferences.
     *
     * @var array
     */
    protected $_preferences;

    /**
     * Widget inline styles.
     *
     * @var string
     */
    protected $_style;

    /**
     * Widget external stylesheets URL.
     *
     * @var array
     */
    protected $_externalCss;

    /**
     * Widget JavaScript controller.
     *
     * @var string
     */
    protected $_script;

    /**
     * Widget external Javascript libraries URL.
     *
     * @var array
     */
    protected $_externalJs;

    /**
     * Widget XHTML body.
     *
     * @var string
     */
    protected $_body;

    /**
     * Widget icon URL.
     *
     * @var string
     */
    protected $_icon;

    /**
     * Widget rich icon URL.
     *
     * @var string
     */
    protected $_richIcon;


    /**
     * Widget core library.
     *
     * @var unknown_type
     */
    protected $_coreLibrary = 'uwa';

    /**
     * Widget constructor.
     */
    public function __construct($url = '')
    {
        $this->_metadata = array();
        $this->_preferences = array();
        $this->_externalCss = array();
        $this->_externalJs = array();
        $this->_url = $url;
    }

    /**
     * Returns the widget skeleton URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }
     
    /**
     * Set the widget title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = (string) $title;
    }

    /**
     * Returns the widget title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Sets the widget metadata.
     *
     * @param array $metadata
     */
    public function setMetadata(array $metadata)
    {
        $this->_metadata = $metadata;
    }

    /**
     * Returns the widget metadata.
     *
     * @return array metadata
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }
    
    public function getMetas()
    {
        return $this->getMetadata();
    }

    /**
     * Returns the widget metadata in JSON format.
     *
     * @return string JSON metadata
     */
    public function getMetadataJson()
    {
        /**
         * @see Zend_Json
         */
        require_once 'Zend/Json.php';
        return Zend_Json::encode($this->_metadata);
    }

    /**
     * Sets the widget preferences.
     *
     * @param array $preferences
     */
    public function setPreferences(array $preferences)
    {
        $this->_preferences = $preferences;
    }

    /**
     * Adds a widget preference.
     *
     * @param string $name
     * @param Preference $preference
     */
    public function addPreference($name, $preference)
    {
        $this->_preferences[(string) $name] = $preference;
    }

    /**
     * Returns the widget preferences.
     *
     * @return array
     */
    public function getPreferences()
    {
        return $this->_preferences;
    }
     
    public function getPreference($name)
    {
        $preference = null;
        if (isset($this->_preferences[(string) $name])) {
            $preference = $this->_preference[(string) $name];
        }
        return $preference;
    }

    /**
     * Returns the widget preferences in JSON format.
     *
     * @return string JSON preferences
     */
    public function getPreferencesJson()
    {
        /**
         * @see Zend_Json
         */
        require_once 'Zend/Json.php';
        $preferences = array();
        foreach ($this->_preferences as $name => $preference) {
            $preferences[] = $preference->toArray();
        }
        return Zend_Json::encode($preferences);
    }

    /**
     * Sets the widget inline CSS styles.
     *
     * @param string $style
     */
    public function setStyle($style)
    {
        $this->_style = (string) $style;
    }

    /**
     * Returns the widget inline CSS styles.
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->_style;
    }

    /**
     * Sets the widget external stylesheets.
     *
     * @param array $css
     */
    public function setExternalStylesheets(array $css)
    {
        $this->_externalCss = $css;
    }

    /**
     * Returns the widget external stylesheets.
     *
     * @return array
     */
    public function getExternalStylesheets()
    {
        return $this->_externalCss;
    }

    /**
     * Sets the widget JavaScript code.
     *
     * @param string $script
     */
    public function setScript($script)
    {
        $this->_script = $script;
    }

    /**
     * Returns the widget JavaScript code.
     *
     * @return The JavaScript code
     */
    public function getScript()
    {
        return $this->_script;
    }

    /**
     * Compresses and returns the widget JavaScript code.
     *
     * @return The compressed code
     */
    public function getCompressedScript()
    {
        require_once 'jsmin.php';
        return trim(JSMin::minify($this->_script));
    }

    /**
     * Sets the widget external JavaScript libraries.
     *
     * @param array $js
     */
    public function setExternalScripts(array $js)
    {
        $this->_externalJs = $js;
    }

    /**
     * Sets the name of the core library in use.
     *
     * @param string $js
     */
    public function setCoreLibrary($js)
    {
        $this->_coreLibrary = $js;
    }

    /**
     * Returns the name of the core library in use.
     *
     * @return string
     */
    public function getCoreLibrary()
    {
        return $this->_coreLibrary;
    }

    /**
     * Returns the widget external JavaScript libraries.
     *
     * @return array
     */
    public function getExternalScripts()
    {
        return $this->_externalJs;
    }

    /**
     * Sets the widget body markup
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->_body = (string) $body;
    }

    /**
     * Returns the widget body markup
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Sets the widget icon URL.
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->_icon = (string) $icon;
    }

    /**
     * Returns the widget icon URL.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->_icon;
    }

    /**
     * Sets the widget rich icon URL.
     *
     * @param string $richIcon
     */
    public function setRichIcon($richIcon)
    {
        $this->_richIcon = (string) $richIcon;
    }

    /**
     * Returns the widget rich icon URL.
     *
     * @return string
     */
    public function getRichIcon()
    {
        return $this->_richIcon;
    }
}
