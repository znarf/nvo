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
 * Netvibes Activity.
 */
class Netvibes_Activity
{
    /**
     * Activity Id.
     *
     * @var int
     */
    protected $_id;

    /**
     * Activity Type.
     *
     * @var int
     */
    protected $_type;

    /**
     * User Id.
     *
     * @var int
     */
    protected $_userId;

    /**
     * Username (Netvibes Id).
     *
     * @var string
     */
    protected $_userName;

    /**
     * User full name.
     *
     * @var string
     */
    protected $_userFullName;

    /**
     * Title.
     *
     * @var string
     */
    protected $_title;

    /**
     * Comment.
     *
     * @var string
     */
    protected $_comment;

    /**
     * Status, either public or private.
     *
     * @var string
     */
    protected $_status;

    /**
     * Creation date.
     *
     * @var Zend_Date
     */
    protected $_createdOn;

    /**
     * Update date.
     *
     * @var Zend_Date
     */
    protected $_updatedOn;

    /**
     * Data describing the activity.
     *
     * @var array
     */
    protected $_data;

    /**
     * Constructor from a XML element.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct($xml)
    {
        $this->_id           = (int) $xml->id;
        $this->_type         = (int) $xml->type;
        $this->_userId       = (int) $xml->userId;
        $this->_userName     = (string) $xml->userName;
        $this->_userFullName = (string) $xml->userFullName;
        $this->_title        = (string) $xml->data->title;
        $this->_comment      = (string) $xml->comment;
        $this->_status       = (string) $xml->status;
        $this->_createdOn    = new Zend_Date((string) $xml->createdOn);
        $this->_updatedOn    = new Zend_Date((string) $xml->updatedOn);

        $this->_data = array();
        if ($xml->data->children()) {
            foreach ($xml->data->children() as $key => $value) {
                $this->_data[(string) $key] = (string) $value;
            }
        } else {
            $this->_data[] = (string) $xml->data;
        }
    }

    /**
     * Returns the activity id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the activity type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Returns the user id.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * Returns the user name.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->_userName;
    }

    /**
     * Returns the user full name.
     *
     * @return string
     */
    public function getUserFullName()
    {
        return $this->_userFullName;
    }

    /**
     * Returns the activity title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Returns the activity comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->_comment;
    }

    /**
     * Returns the activity status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Returns the activity creation date.
     *
     * @return Zend_Date
     */
    public function getCreatedOn()
    {
        return $this->_createdOn;
    }

    /**
     * Returns the activity update date.
     *
     * @return Zend_Date
     */
    public function getUpdatedOn()
    {
        return $this->_updatedOn;
    }

    /**
     * Returns the activity data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}
