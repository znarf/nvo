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


require_once 'Zend/Http/Client.php';

class Fetcher
{
    /**
     * HTTP Request User Agent.
     */
    const USER_AGENT = 'Netvibes Exposition Fetcher';

    /**
     * File URL.
     *
     * @var string
     */
    protected $_url;

    /**
     * File content.
     *
     * @var string
     */
    protected $_content;

    /**
     * Whether to use the cache.
     *
     * @var boolean
     */
    protected $_useCache;

    /**
     * Constructor.
     *
     * @param string $url URL of the file to fetch
     */
    public function __construct($url, $cache = true)
    {
        $this->_url = $url;
        $this->_useCache = $cache;
    }

    /**
     * Retrieves the file content.
     *
     * @return string The file content
     */
    public function fetchContent()
    {
        if (empty($this->_content)) {
            $pu = parse_url($this->_url);
            if (empty($pu['host'])) {
                $this->_content = file_get_contents($this->_url);
            } else {
                $this->_content = $this->_getFileHttp();
            }
        }
        return $this->_content;
    }

    /**
     * Retrieves the file content by performing a HTTP Request.
     *
     * @return string The file content
     * @throws Exception
     */
    private function _getFileHttp()
    {
        $result = '';
        $cacheId = md5($this->_url);
        $registry = Zend_Registry::getInstance();
        if (isset($registry['cache'])) {
            $cache = $registry['cache'];
        }
        if ($this->_useCache && isset($cache) && $cache->test($cacheId)) {
            // Extract data from the cache
            $result = (string) $cache->load($cacheId);
        } else {
            // Retrieve the file and cache its content for further use
            $client = new Zend_Http_Client($this->_url, array('useragent' => self::USER_AGENT));
            $response = $client->request();
            if ($response->getStatus() == 200) {
                $result = $response->getBody();
                if (isset($cache)) {
                    $cache->save($result, $cacheId);
                }
            } else {
                throw new Exception($response->getStatus() . ": " . $response->getMessage() . " " . $this->_url);
            }
        }
        return $result;
    }
}
