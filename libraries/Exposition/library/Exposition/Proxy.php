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
class Exposition_Proxy
{
    /**
     * HTTP Request User Agent.
     */
    const USER_AGENT = 'Exposition PHP Lib Proxy';

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
     * Proxy user agent.
     *
     * @var string
     */
    private $_useragent = self::USER_AGENT;

    /**
     * Constructor.
     *
     * @param string $url
     * @param array  $options
     */
    public function __construct($url, $options = array())
    {
        if (empty($url)) {
            throw new Exposition_Proxy_Exception('No URL has been set');
        }

        $this->_url = $url;
        $this->_key = 'proxy_' . sha1($this->_url);

        foreach ($options as $name => $value) {
            if ($name == 'type') {
                $this->_type = $value;
            } else if ($name == 'cachetime') {
                $this->_cachetime = $value;
            } else  if ($name == 'object') {
                $this->_object =  preg_replace('/[^a-z0-9]/i', '', $value);
            } else  if ($name == 'useragent') {
                $this->_useragent =  $value;
            }
        }

        $options = array(
            'useragent'    => $this->_useragent,
            'timeout'      => '60',
            'maxredirects' => 5,
        );

        $this->_client = new Zend_Http_Client($this->_url, $options);
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

        // get from cache
        if ($cache) {
            $body = $cache->load($this->_key);
        }

        // get from live source
        if (empty($body)) {

            try {
                $this->_response = $this->_client->request();

                // Service unavailable
                if ($this->_response->getStatus() == 503) {
                    throw new Exposition_Proxy_Exception(sprintf(
                        'Unable to get content of url %s HTTP/1.1 %s cause: %s',
                        $this->_client->getUri(),
                        $this->_response->getStatus(),
                        $this->_response->getMessage()
                    ));
                }

                // get body has var
                $body = $this->_response->getBody();

                // save body into cache
                if ($cache && $this->isCachable() && mb_strlen($body) < 200000) {
                    $cache->save($body, $this->_key);
                }

            } catch (Exception $e) {

                throw new Exposition_Proxy_Exception(sprintf(
                    'Unable to get content of url %s cause: %s',
                    $this->_client->getUri(),
                    $e->getMessage()
                ));
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

        $response = $this->getResponse($body, $this->_type, $this->_object);

        echo $response;
    }

    /**
     * Send Proxy headers
     */
    public function sendHeader()
    {
        // use default minType if not valide
        if (!isset($this->_mimeTypes[$this->_type])) {
            $this->_type = self::DEFAULT_MINE_TYPE;
        }

        $mimeType = $this->_mimeTypes[$this->_type];
        header('Content-Type:' . $mimeType);

        return $this;
    }

    /**
     * Return String as Javascript Object in function of type of proxy
     *
     * @param string $string
     * @param string $type
     * @return string javascript var code
     */
    public function getResponse($string = null, $type = null, $object = null)
    {
        // use current body
        if (is_null($string)) {
            $string = $this->getBody();
        }

        // use current type
        if (is_null($type)) {
            $type = $this->_type;
        }

        // use default minType if not valide
        if (!isset($this->_mimeTypes[$type])) {
            $this->_type = self::DEFAULT_MINE_TYPE;
        }

        // set response json type default value
        $isJsonResponse = false;

        // parse source
        switch ($type) {
            case 'feed':
            case 'rss':
            case 'atom':
                $isJsonResponse = true;
                $response = self::feedToJson($string);
                break;

            case 'json':
                $isJsonResponse = true;
                $response = $string;
                break;

            case 'xml':
            case 'html':
            default:
                $isJsonResponse = false;
                $response = $string;
                break;
        }

        // return simple response type
        if (is_null($object)) {

            return $response;

        // return json response type
        } else {

            // force json encode on non json formated response
            if ($isJsonResponse === false) {
                $response =  Zend_Json::encode($response);
            }

            return $object . '=' . $response . ';';
        }
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
        // initilize cache if null
        if (is_null($this->_cache)) {

            $proxyCache = Exposition_Load::getConfig('proxy', 'cache');

            // no cache config or no cache time
            if (empty($proxyCache) || $proxyCache['enable'] === false || $this->_cachetime == 0) {

                $this->_cache = false;

            // else use config
            } else {

                // build frontend cache options
                $frontendOptions = array();
                if (isset($proxyCache['frontend']) && is_array($proxyCache['frontend'])) {
                    $frontendOptions = $proxyCache['frontend'];
                }

                // build backend cache options
                $backendOptions = array();
                if (isset($proxyCache['backend']) && is_array($proxyCache['backend'])) {
                    $backendOptions = $proxyCache['backend'];
                }

                // create cache instance
                $this->_cache = Zend_Cache::factory(
                    'Core',
                    $proxyCache['adapter'],
                    $frontendOptions,
                    $backendOptions
                );

                // set _cachetime has cache lifetime
                $this->_cache->getBackend()->setDirectives(array(
                    'lifetime' => $this->_cachetime,
                ));
            }
        }

        return $this->_cache;
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
                throw new Exposition_Proxy_Exception(sprintf('Missing feed format to Json callback function "%s" in class Exposition_"%s".', $feedToJsonCallback, __CLASS__));
            }

            $jsonOutput = self::$feedToJsonCallback($feed);

        } catch (Proxy_Exception $e) {

            // @todo error response 500/404 or objectb error ?
            $jsonOutput = array();
        }

        return $jsonOutput;
    }

    /**
     * Build Json response from atom feed
     */
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
                'link' 			=> $entry->link(),
                'content' 		=> $entry->content(),
                'date'			=> date('M d, Y H:i:s', strtotime($entry->updated())) . ' GMT',
            );


            $arrayOutput->items[] = $item;
        }

        return Zend_Json::encode($arrayOutput);
    }

    /**
     * Build Json response from rss feed
     */
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

            if (mb_strlen($entry->enclosure['type']) > 0) {
                $item->enclosures[] = (object) array(
                    'type'  => $entry->enclosure['type'],
                    'url'   => $entry->enclosure['url'],
                );
            }

            $arrayOutput->items[] = $item;
        }

        return Zend_Json::encode($arrayOutput);
    }
}

