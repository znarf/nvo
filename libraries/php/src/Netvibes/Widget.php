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
 * Netvibes Widget descriptor.
 */
class Netvibes_Widget
{
    /**
     * Widget identifier.
     *
     * @var int
     */
    protected $_id;

    /**
     * Widget column.
     *
     * @var int
     */
    protected $_col;

    /**
     * Widget row.
     *
     * @var int
     */
    protected $_row;

    /**
     * Widget name.
     *
     * @var string
     */
    protected $_name;

    /**
     * Widget title.
     *
     * @var string
     */
    protected $_title;

    /**
     * Widget data.
     *
     * @var array
     */
    protected $_data;

    /**
     * Widget height.
     *
     * @var int
     */
    protected $_height;
    
    /**
     * Widget color.
     *
     * @var int
     */
    protected $_color;

    /**
     * Widget skeleton id.
     *
     * @var string
     */
    protected $_skeletonId;

    /**
     * Widget skeleton origin.
     *
     * @var string
     */
    protected $_skeletonOrigin;

    /**
     * Widget links.
     *
     * @var string
     */
    protected $_links;

    /**
     * Constructor from a XML element.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct($xml)
    {
        $this->_id     = (int) $xml['id'];
        $this->_col    = (int) $xml['col'];
        $this->_row    = (int) $xml['row'];
        $this->_name   = (string) $xml['name'];
        $this->_title  = (string) $xml->title;
        $this->_url    = (string) $xml->url;
        $this->_height = (int) $xml->height;
        $this->_color  = (string) $xml->color;
        
        if (isset($xml->skeleton)) {
            $this->_skeletonId     = (string) $xml->skeleton['id'];
            $this->_skeletonOrigin = (string) $xml->skeleton['origin'];
            $this->_links = array();
            foreach ($xml->skeleton->link as $link) {
                $this->_links[(string) $link['rel']] = (string) $link['href'];
            }
        }
        
        $this->_data = array();
        foreach ($xml->data as $data) {
            $this->_data[(string) $data['name']] = (string) $data;
        }
    }

    /**
     * Sets the widget identifier.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = (int) $id;
    }
     
    /**
     * Returns the widget identifier.
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the widget column number.
     *
     * @param int $col
     */
    public function setCol($col)
    {
        $this->_col = (int) $col;
    }
     
    /**
     * Returns the widget column number.
     *
     * @return int
     */
    public function getCol()
    {
        return $this->_col;
    }

    /**
     * Sets the widget row number.
     *
     * @param int $row
     */
    public function setRow($row)
    {
        $this->_row = (int) $row;
    }
     
    /**
     * Returns the widget row number.
     *
     * @return int
     */
    public function getRow()
    {
        return $this->_row;
    }

    /**
     * Sets the widget name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = (string) $name;
    }
     
    /**
     * Returns the widget name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the widget links.
     *
     * @return string
     */
    public function getLinks()
    {
        return $this->_links;
    }

    /**
     * Returns the widget uwa url.
     *
     * @return string
     */
    public function getUrl()
    {
        return (isset($this->_links['uwa']) ? $this->_links['uwa'] : '');
    }

    /**
     * Returns the widget iframe url.
     *
     * @return string
     */
    public function getIframeUrl()
    {
        return (isset($this->_links['iframe']) ? $this->_links['iframe'] : '');
    }

    /**
     * Returns the widget script url.
     *
     * @return string
     */
    public function getScriptUrl()
    {
        return (isset($this->_links['script']) ? $this->_links['script'] : '');
    }

    /**
     * Returns the widget stylesheet url.
     *
     * @return string
     */
    public function getStylesheetUrl()
    {
        return (isset($this->_links['stylesheet']) ? $this->_links['stylesheet'] : '');
    }

    /**
     * Retrieves the widget skeleton identifier.
     *
     * @return string
     */
    public function getSkeletonId()
    {
        return $this->_skeletonId;
    }

    /**
     * Retrieves the widget skeleton origin.
     *
     * @return string
     */
    public function getSkeletonOrigin()
    {
        return $this->_skeletonOrigin;
    }

    /**
     * Sets the widget height.
     *
     * @param string $height
     */
    public function setHeight($height)
    {
        $this->_height = (string) $height;
    }
     
    /**
     * Returns the widget height.
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->_height;
    }
    
    /**
     * Returns the widget color.
     *
     * @return string
     */
    public function getColor()
    {
        return $this->_color;
    }
    
    /**
     * Sets the tab title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
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
     * Sets the widget data.
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Returns the widget data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}
