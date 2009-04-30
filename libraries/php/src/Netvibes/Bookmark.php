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
 * Netvibes Bookmark.
 */
class Netvibes_Bookmark
{
    /**
     * Bookmark title.
     *
     * @var string
     */
    protected $_title;

    /**
     * Bookmark link.
     *
     * @var string
     */
    protected $_link;

    /**
     * Bookmark tags.
     *
     * @var array
     */
    protected $_tags;

    /**
     * Constructor from a XML element.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct($xml)
    {
        $this->_title = (string) $xml->title;
        $this->_link  = (string) $xml->link;

        $this->_tags = array();
        foreach ($xml->xpath("tags/tag") as $tag) {
            $this->_tags[] = (string) $tag;
        }
    }

    /**
     * Sets the bookmark title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * Returns the bookmark title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Sets the bookmark link.
     *
     * @param string $link
     */
    public function setLink($link)
    {
        $this->_link = $link;
    }

    /**
     * Returns the bookmark link.
     *
     * @return string
     */
    public function getLink()
    {
        return $this->_link;
    }

    /**
     * Sets the bookmark tags.
     *
     * @param array $data
     */
    public function setTags(array $tags)
    {
        $this->_tags = $tags;
    }

    /**
     * Returns the bookmarks tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->_tags;
    }
}
