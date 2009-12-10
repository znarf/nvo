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


require_once 'Netvibes/Tab.php';

/**
 * Netvibes Page.
 */
class Netvibes_Page
{
    /**
     * Page title.
     *
     * @var string
     */
    protected $_title;

    /**
     * Page settings, for instance the theme information.
     *
     * @var array
     */
    protected $_settings;

    /**
     * Page tabs.
     *
     * @var array
     */
    protected $_tabs;

    /**
     * Constructor from a XML element.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct($xml)
    {
        $this->_title = (string) $xml->title;

        $this->_settings = array();
        $xmlPrefs = $xml->xpath("settings/setting");
        if (isset($xmlPrefs)) {
            foreach ($xmlPrefs as $setting) {
                $this->_settings[(string) $setting['name']] = (string) $setting;
            }
        }

        $this->_tabs = array();
        foreach ($xml->tab as $tab) {
            $this->_tabs[] = new Netvibes_Tab($tab);
        }
    }

    /**
     * Sets the page title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * Returns the page title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Sets the page settings.
     *
     * @param array $settings
     */
    public function setSettings(array $settings)
    {
        $this->_settings = $settings;
    }

    /**
     * Returns the page settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->_settings;
    }

    /**
     * Sets the tabs.
     *
     * @param array $tabs
     */
    public function setTabs(array $tabs)
    {
        $this->_tabs = $tabs;
    }

    /**
     * Returns the tabs.
     *
     * @return array
     */
    public function getTabs()
    {
        return $this->_tabs;
    }

    /**
     * Retrieves a tab by giving its identifier.
     *
     * @param int $tabId The tab ID
     * @return Netvibes_Tab Tab descriptor if it exists, otherwise null
     */
    public function getTab($tabId)
    {
        foreach ($this->_tabs as $tab) {
            if ($tab->getId() == $tabId) {
                return $tab;
            }
        }
        return null;
    }
}
