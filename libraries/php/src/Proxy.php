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


require_once 'Zend/Json.php';
require_once 'Zend/Http/Client.php';

/**
 * Proxy to handle Ajax calls.
 */
class Proxy
{
    /**
     * HTTP Request User Agent.
     */
    const USER_AGENT = 'Netvibes Exposition Proxy';

    /**
     * Response body.
     *
     * @var string
     */
    public $body = null;

    /**
     * Target URL.
     *
     * @var string
     */
    private $_url = null;

    /**
     * HTTP Client.
     *
     * @var Zend_Http_Client
     */
    private $_client = null;

    /**
     * HTTP Response.
     *
     * @var Zend_Http_Response
     */
    private $_response = null;

    /**
     * HTTP method.
     *
     * @var string
     */
    private $_method = 'GET';

    /**
     * Reponse type.
     *
     * @var string
     */
    private $_type = 'text';

    /**
     * JavaScript callback function.
     *
     * @var array
     */
    private $_callback = null;

    /**
     * Mime types to be used in responses headers.
     *
     * @var array
     */
    private $_mimeTypes = array(
        'xml'  => 'text/xml',
        'html' => 'text/html',
        'feed' => 'application/json',
        'json' => 'application/json',
        'text' => 'text/plain'
    );

    /**
     * Cache key.
     *
     * @var string
     */
    private $_key = null;

    /**
     * Cache object.
     *
     * @var Zend_Cache
     */
    private $_cache = null;

    /**
     * Cache lifetime.
     *
     * @var integer
     */
    private $_cachetime = 0;

    /**
     * Constructor.
     *
     * @param string $url
     * @param array  $options
     */
    public function __construct($url, $options = array())
    {
        if (empty($url)) {
            throw new Exception('No URL has been set');
        }

        $this->_url = $url;
        $this->_key = 'ajax_' . md5($this->_url);

        foreach ($options as $name => $value) {
            if ($name == 'type') {
                $this->_type = $value;
            } else if ($name == 'cachetime') {
                $this->_cachetime = $value;
            }
        }

        $options = array(
            'useragent'    => self::USER_AGENT,
            'timeout'      => '60',
            'maxredirects' => 5
        );
        $this->_client = new Zend_Http_Client($this->_url, $options);

        $registry = Zend_Registry::getInstance();
        $this->_cache = $registry['cache'];
    }

    /**
     * Sets the HTTP client options.
     *
     * @param array $options
     */
    public function setHttpClientOptions($options)
    {
        foreach ($options as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if ($setter == 'setAuth') {
                $this->_client->$setter($value['username'], $value['password'], Zend_Http_Client::AUTH_BASIC);
            } else {
                $this->_client->$setter($value);
            }
        }
    }

    /**
     * Checks if the result is cachable.
     *
     * @return True if the result is cachable, otherwise false.
     */
    public function isCachable()
    {
        return ($this->_method == 'GET' && $this->_response->getStatus() == 200);
    }

    /**
     * Retrieves the response body.
     *
     * @return The response body
     */
    public function getBody()
    {
        $cache = $this->getCache();
        if ($cache) {
            $body = $cache->load($this->_key . '_body');
        }
        if (empty($body)) {
            try {
                $this->_response = $this->_client->request();
            } catch (Zend_Http_Client_Exception $e) {
                error_log("Http exception via AjaxProxy on " . $this->_url);
                return null;
            }
            if ($this->_response->getStatus() == 503) { // Service unavailable
                header('HTTP/1.1 ' . $this->_response->getStatus() . ' ' . $this->_response->getMessage());
                exit;
            }
            $body = $this->_response->getBody();
            if ($cache && $this->isCachable() && strlen($body) < 200000) {
                $cache->save($body, $this->_key . '_body');
            }
        }
        return $body;
    }

    /**
     * Sends the Ajax reponse.
     */
    public function sendResponse()
    {
        $body = $this->getBody();
        // experimental, encode utf8 automatically
        if (!$this->isUtf8Encoded($body)) {
            $body = utf8_encode($body);
        }

        $mimeType = $this->_mimeTypes[$this->_type];
        header("Content-Type: $mimeType");
        echo $body;
    }

    /**
     * Detects if a string is UTF-8 encoded.
     * @see http://us2.php.net/mb_detect_encoding
     *
     * @param string $string
     * @return boolean True is the string is UTF-8 encoded, otherwise false.
     */
    public static function isUtf8Encoded($string)
    {
        $match = preg_match('%(?:
            [\xC2-\xDF][\x80-\xBF]              # non-overlong 2-byte
            |\xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |\xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            |\xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            |[\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |\xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )+%xs', $string);
        return ($match > 0);
    }

    /**
     * Retrieves the cache instance.
     *
     * @return Zend_Cache
     */
    public function getCache()
    {
        return $this->_cache;
    }
}
