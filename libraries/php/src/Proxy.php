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
     * Default Mine Type.
     *
     * @var string
     */
    const DEFAULT_MINE_TYPE = 'text';

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
     * JavaScript object variable name.
     *
     * @var string
     */
    private $_object= null;

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
            } else  if ($name == 'object') {
                $this->_object =  preg_replace('/[^a-z0-9]/i', '', $value);
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
        if (!self::isUtf8Encoded($body)) {
            $body = utf8_encode($body);
        }

        // use default minType if not valide
        if (!isset($this->_mimeTypes[$this->_type])) {
            $this->_type = self::DEFAULT_MINE_TYPE;
        }

        $mimeType = $this->_mimeTypes[$this->_type];
        header("Content-Type: $mimeType");

        if ($this->_object) {
            echo self::getJsonResponse($body, $this->_type, $this->_object);
        } else if ($this->_type == 'feed') {
            echo self::feedToJson($body);
        } else {
            echo $body;
        }
    }

    /**
     * Return String as Javascript Object in function of type of proxy
     *
     * @param string $string
     * @param string $type
     * @return string javascript var code
     */
    public static function getJsonResponse($string, $type, $objectName)
    {
		switch ($type) {
            case 'feed':
				$objectCleanValue = self::feedToJson($string);
                break;

			case 'json':
                $objectCleanValue = $string;
                break;

            case 'xml':
            case 'html':
            default:
				$objectCleanValue = self::stringToJson($string);
                break;
        }

		// @todo security requirement ? try catch js ?
        return $objectName . '=' . $objectCleanValue . ';';
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

    /**
     * Format string as Javascript Object value
     *
     * @param string $string simple string
     * @return string json var value part
     */
	public static function stringToJson($string)
	{
		return '"' . str_replace("\n", '\n', addslashes($string)) . '"';
	}

    /**
     * Format Feed as Javascript Object value
     *
     * @param string $xmlStringContents XML feed
     * @return string json var value part
     */
    public static function feedToJson($xmlStringContents)
    {
        try {

            $feed = Zend_Feed::importString($xmlStringContents);
            $feedType = strtolower(str_replace('Zend_Feed_', '', get_class($feed)));
            $feedToJsonCallback = $feedType . 'FeedToJson';

			if (!method_exists(__CLASS__, $feedToJsonCallback)) {
				throw new Proxy_Exception(sprintf('Missing feed format to Json callback function "%s" in class "%s".', $feedToJsonCallback, __CLASS__));
			}

            $jsonOutput = self::$feedToJsonCallback($feed);

        } catch (Proxy_Exception $e) {

			// @todo error response 500/404 or objectb error ?

        }

		return $jsonOutput;
    }

    protected static function atomFeedToJson(Zend_Feed_Abstract $feed)
    {
        $arrayOutput = (object) array(
            'type' 			=> 'atom',
            'version' 		=> 'atom10',
            'nvFeed' 		=> '1',
            'htmlUrl' 		=> $feed->link('alternate'),
            'title' 		=> $feed->title(),
            'content' 		=> $feed->description(),
            'items' 		=> array(),
            'date' 			=> $feed->update(),
            'author'        => $feed->author->name() . ' (' . $feed->author->email() . ')',
            'author_detail' => (object) array(
                'name'  => $feed->author->name(),
                'email' => $feed->author->email(),
            ),
            'icon'          => $feed->logo(),
            'last-parsed' 	=> time(),
        );

        foreach ($feed as $entryId => $entry) {

            $item = (object) array(
                'id'            => $entryId,
                'id_old'        => $entryId,
                'title' 		=> $entry->title(),
                'link' 			=> $entry->link['href'],
                'content' 		=> $entry->content(),
                'date'			=> 'Jul 17, 2009 16:02:35 GMT',
                'date'			=> date('M d, Y H:i:s', strtotime($entry->updated())) . ' GMT',
            );


            $arrayOutput->items[] = $item;
        }

        return Zend_Json::encode($arrayOutput);
    }

    protected static function rssFeedToJson(Zend_Feed_Abstract $feed)
    {
        $arrayOutput = (object) array(
            'type' 			=> 'rss',
            'version' 		=> 'rss20',
            'nvFeed' 		=> '1',
            'htmlUrl' 		=> $feed->link(),
            'title' 		=> $feed->title(),
            'lang' 			=> $feed->language(),
            'content' 		=> $feed->description(),
            'items' 		=> array(),
            'date' 			=> $feed->pubDate(),
            'last-parsed' 	=> '',
        );

        foreach ($feed as $entryId => $entry) {

            $item = (object) array(
                'id'            => $entryId,
                'id_old'        => $entryId,
                'title' 		=> $entry->title(),
                'link' 			=> $entry->link['href'],
                'content' 		=> $entry->description(),
                'date'			=> $entry->pubDate(),
                'enclosures' 	=> array(),
            );

            $arrayOutput->items[] = $item;
        }

        return Zend_Json::encode($arrayOutput);
    }
}
