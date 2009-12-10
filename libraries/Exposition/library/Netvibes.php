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

require_once 'Netvibes/Page.php';

require_once 'Zend/Rest/Client.php';

/**
 * Netvibes API REST Client.
 */
class Netvibes
{
    /**
     * Netvibes REST server base URL.
     *
     * @var string
     */
    public $baseUrl;

    /**
     * Netvibes REST server base path.
     *
     * @var string
     */
    public $basePath;

    /**
     * Netvibes API Key
     *
     * @var string
     */
    public $apiKey;

    /**
     * Zend_Service_Rest instance.
     *
     * @var Zend_Service_Rest
     */
    protected $_rest;

    /**
     * Cache object.
     *
     * @var Zend_Cache
     */
    private $_cache = null;

    /**
     * Constructor.
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = 'http://' . NV_REST;
        $this->basePath = defined('NV_REST_PATH') ? NV_REST_PATH : '';
        $this->_rest = new Zend_Rest_Client($this->baseUrl);

        $registry = Zend_Registry::getInstance();
        if (isset($registry) && isset($registry['cache'])) {
            $this->_cache = $registry['cache'];
        }
    }

    /**
     * Returns a reference to the REST client.
     *
     * @return Zend_Rest_Client
     */
    public function getRestClient()
    {
        return $this->_rest;
    }

    /**
     * Retrieves a full universe with tabs and widgets.
     *
     * @param string $netvibesId
     * @param string $format
     * @return Netvibes_Universe
     */
    public function getUniverse($netvibesId, $format = 'xml')
    {
        $path = '/universe/' . $netvibesId;
        $xml = $this->restGetRequest($path, $format);
        return new Netvibes_Page($xml);
    }

    /**
     * Retrieves all the tabs from a universe.
     *
     * @param string $netvibesId
     * @param string $format
     * @return array
     */
    public function getUniverseAllTabs($netvibesId, $format = 'xml')
    {
        $path = '/universe/' . $netvibesId . '/tabs';
        $xml = $this->restGetRequest($path, $format);
        $tabs = array();
        foreach ($xml->tabs->tab as $tab) {
            $tabs[] = new Netvibes_Tab($tab);
        }
        return $tabs;
    }

    /**
     * Retrieves one tab and the associated widgets from a universe.
     *
     * @param string $netvibesId
     * @param string $tabId
     * @param string $format
     * @return Netvibes_Tab
     */
    public function getUniverseOneTab($netvibesId, $tabId, $format = 'xml')
    {
        $path = '/universe/' . $netvibesId . '/tab/' . $tabId;
        $xml = $this->restGetRequest($path, $format);
        return new Netvibes_Tab($xml);
    }


    /**
     * Retrieves a widget from a universe.
     *
     * @param string $netvibesId
     * @param string $widgetId
     * @param string $format
     * @return Netvibes_Widget
     */
    public function getUniverseWidget($netvibesId, $widgetId, $format = 'xml')
    {
        $path = '/universe/' . $netvibesId . '/widget';
        $xml = $this->restGetRequest($path, $format);
        return new Netvibes_Widget($xml);
    }

    /**
     * Retrieves the public timeline for a specified user.
     *
     * @param string $netvibesId
     * @param string $format
     */
    public function getUserTimeline($netvibesId, $format = 'xml')
    {
        $path = '/timeline/user/' . $netvibesId;
        $xml = $this->restGetRequest($path, $format);
        return new Netvibes_Timeline($xml);
    }

    /**
     * Searches within the public timeline.
     *
     * @param string $query
     * @param string $format
     */
    public function getTimelineResults($query, $format = 'xml')
    {
        $path = '/timeline/search?query=' . $query;
        $xml = $this->restGetRequest($path, $format, false);
        return new Netvibes_Timeline($xml);

    }

    /**
     * Retrieves the followers for a specific user.
     *
     * @param string $netvibesId
     * @param string $format
     */
    public function getFollowers($netvibesId, $format = 'xml')
    {
        $path = '/universe/' . $netvibesId . '/followers';
        return $this->restGetRequest($path, $format);
    }

    /**
     * Performs a get request to a REST endpoint.
     *
     * @param string $path The REST request path
     * @param string $format The response format
     * @param boolean $cache Either to use the cache or not
     */
    protected function restGetRequest($path, $format, $cache = true)
    {
        $restClient = $this->getRestClient();
        $restClient->getHttpClient()->resetParameters();

        $options = array();
        $options['api_key'] = $this->apiKey;
        $options['format'] = $format;

        $body = '';
        $key = preg_replace('/[^a-zA-Z0-9_]/i', '_', substr($path, 1)) . '_' . $format;
        if ($cache && isset($this->_cache) && $this->_cache->test($key)) {
            $body = $this->_cache->load($key);
        } else {
            $response = $restClient->restGet($this->basePath . $path, $options);
            $body = $response->getBody();
            if ($cache && isset($this->_cache)) {
                $this->_cache->save($body, $key);
            }
        }
        try {
            $xml = @new SimpleXMLElement($body);
        } catch (Netvibes_Exception $e) {
            throw new Netvibes_Exception('Problem while parsing XML.');
        }
        return $xml;
    }
}
