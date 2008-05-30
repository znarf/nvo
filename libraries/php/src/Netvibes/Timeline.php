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


require_once 'Netvibes/Activity.php';

/**
 * Netvibes Timeline representing a set of activities.
 */
class Netvibes_Timeline implements SeekableIterator
{
    /**
     * Activities.
     *
     * @var array
     */
    protected $_activities;

    /**
     * Total results available.
     *
     * @var int
     */
    protected $_totalResultsAvailable;

    /**
     * Total results returned.
     *
     * @var int
     */
    protected $_totalResultsReturned;

    /**
     * Current index for the Iterator.
     *
     * @var int
     */
    private $_currentIndex = 0;

    /**
     * Constructor from a XML element.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct($xml)
    {
        $this->_activities = array();
        foreach ($xml->timeline->item as $item) {
            $this->_activities[] = new Netvibes_Activity($item);
        }

        $this->_totalResultsAvailable = (int) $xml->numfound;
        $this->_totalResultsReturned = count($this->_activities);
    }

    /**
     * Retuns the activities array.
     *
     * @return array
     */
    public function getActivities()
    {
        return $this->_activities;
    }

    /**
     * Returns the total number of available results.
     *
     * @return int
     */
    public function getTotalResultsAvailable()
    {
        return $this->_totalResultsAvailable;
    }

    /**
     * Returns the total number of returned results.
     *
     * @return int
     */
    public function getTotalResultsReturned()
    {
        return $this->_totalResultsReturned;
    }

    /**
     * Implements SeekableIterator::key().
     *
     * @return int
     */
    public function key()
    {
        return $this->_currentIndex;
    }

    /**
     * Implements SeekableIterator::next().
     */
    public function next()
    {
        $this->_currentIndex += 1;
    }

    /**
     * Implements SeekableIterator::rewind().
     *
     * @return boolean
     */
    public function rewind()
    {
        $this->_currentIndex = 0;
        return true;
    }

    /**
     * Implements SeekableIterator::seek().
     *
     * @param  int $index
     * @throws OutOfBoundsException
     */
    public function seek($index)
    {
        $indexInt = (int) $index;
        if ($indexInt >= 0 && $indexInt < $this->_totalResultsReturned) {
            $this->_currentIndex = $indexInt;
        } else {
            throw new OutOfBoundsException("Illegal index '$index'");
        }
    }

    /**
     * Implements SeekableIterator::valid().
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_activities !== null && $this->_currentIndex < $this->_totalResultsReturned;
    }

    /**
     * Implements SeekableIterator::current().
     *
     * @return Netvibes_Activity
     */
    public function current()
    {
        return $this->_activities[$this->_currentIndex];
    }
}
