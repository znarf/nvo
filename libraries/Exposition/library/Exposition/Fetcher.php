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


require_once 'Zend/Http/Client.php';

class Exposition_Fetcher
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
     * Cache lifetime.
     *
     * @var integer
     */
    private $_cachetime = 0;

    /**
     * Constructor.
     *
     * @param string $url URL of the file to fetch
     */
    public function __construct($url, $cache = true)
    {
        if (empty($url)) {
            throw new Exposition_Exception('Unable to fetch empty url');
        }

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
        $proxy = new Exposition_Proxy($this->_url, array(
            'useragent' => self::USER_AGENT,
            'cachetime' => $this->_cachetime,
        ));

        $response = $proxy->getResponse();

        // free proxy instance
        unset($proxy);

        return $response;
    }
}
