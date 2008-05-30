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


require_once 'Netvibes/Widget.php';
require_once 'Netvibes/Bookmark.php';

/**
 * Netvibes Tab descriptor.
 */
class Netvibes_Tab
{
    /**
     * Tab identifier.
     *
     * @var int
     */
    protected $_id;

    /**
     * Tab title.
     *
     * @var string
     */
    protected $_title;

    /**
     * Tab columns number.
     *
     * @var int
     */
    protected $_cols;

    /**
     * Tab icon.
     *
     * @var string
     */
    protected $_icon;

    /**
     * Tab widgets descriptors.
     *
     * @var array
     */
    protected $_widgets;

    /**
     * Tab bookmarks.
     *
     * @var array
     */
    protected $_bookmarks;

    /**
     * Contructor from a XML element.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct($xml)
    {
        $this->_id    = (string) $xml['id'];
        $this->_title = (string) $xml->title;
        $this->_cols  = (int) $xml['cols'];

        $icon = $xml->xpath("link[@rel='icon']");
        if (count($icon)) {
            $this->_icon = (string) $icon[0]['href'];
        }

        $this->_widgets = array();
        foreach ($xml->widget as $widget) {
            $this->_widgets[] = new Netvibes_Widget($widget);
        }
    }

    /**
     * Sets the tab identifier.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = (int) $id;
    }
     
    /**
     * Returns the tab identifier.
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the tab title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = (string) $title;
    }
     
    /**
     * Returns the tab title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }
     
    /**
     * Sets the tab columns number.
     *
     * @param int $cols
     */
    public function setCols($cols)
    {
        $this->_cols = (int) $cols;
    }
     
    /**
     * Returns the tab columns number.
     *
     * @return int
     */
    public function getCols()
    {
        return $this->_cols;
    }

    /**
     * Sets the tab icon.
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->_icon = $icon;
    }

    /**
     * Returns the tab icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->_icon;
    }

    /**
     * Sets the tab widgets descriptors.
     *
     * @param array $widgets
     */
    public function setWidgets(array $widgets)
    {
        $this->_widgets = $widgets;
    }

    /**
     * Returns the tab widgets descriptors.
     *
     * @return array
     */
    public function getWidgets()
    {
        return $this->_widgets;
    }

    /**
     * Retrieves the widget list from a given tab and a column number.
     *
     * @param int $column The column
     * @return array Widgets
     */
    public function getWidgetsByColumn($column)
    {
        $widgets = array();
        foreach ($this->_widgets as $widget) {
            if ($widget->getCol() == $column) {
                $widgets[] = $widget;
            }
        }
        usort($widgets, array('Tab', 'compareWidgetRows'));
        return $widgets;
    }

    /**
     * Retrieves a widget by giving its identifier.
     *
     * @param int $widgetId The widget ID
     * @return array Widget properties
     */
    public function getWidgetById($widgetId)
    {
        foreach ($this->_widgets as $widget) {
            if ($widget->getId() == $widgetId) {
                return $widget;
            }
        }
        return null;
    }

    /**
     * Sets the bookmarks.
     *
     * @param array $bookmarks
     */
    public function setBookmarks(array $bookmarks)
    {
        $this->_bookmarks = $bookmarks;
    }

    /**
     * Returns the bookmarks.
     *
     * @return array
     */
    public function getBookmarks()
    {
        return $this->_bookmarks;
    }

    /**
     * Compares the rows of two widgets for sorting purpose.
     *
     * @param Netvibes_Widget $widget1
     * @param Netvibes_Widget $widget2
     * @return int
     */
    public static function compareWidgetRows($widget1, $widget2)
    {
        return ($widget1->getRow() < $widget2->getRow()) ? -1 : 1;
    }
}
