<?php
/**
 * Copyright Netvibes 2006-2009.
 * This file is part of Exposition PHP Server.
 *
 * Exposition PHP Server is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exposition PHP Server is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Exposition PHP Server. If not, see <http://www.gnu.org/licenses/>.
 */


require_once 'Zend/Controller/Action.php';

require_once 'Proxy.php';

/**
 * Proxy controller.
 */
class ProxyController extends Zend_Controller_Action
{
    /**
     * Fetched response body.
     *
     * @var string
     */
    private $_body = null;

    /**
     * Username and password for authentication.
     *
     * @var array
     */
    private $_auth = null;

    /**
     * Language for HTTP header.
     *
     * @var string
     */
    private $_lang = 'en-us';

    /**
     * Pre-dispatch routine to prevent default rendering.
     */
    public function preDispatch()
    {
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Handles the feed action.
     */
    public function feedAction()
    {
        $url = $this->getUrl();
        //$url = 'http://' . NV_HOST . '/proxy/feedProxy.php?url=' . urlencode($url);

        $proxyOptions = array(
            'type'      => 'feed',
            'cachetime' => 1200
        );

        // add object option is isset
        if (isset($_GET['object'])) {
            $proxyOptions['object'] = $_GET['object'];
        }

        $proxy = new Proxy($url, $proxyOptions);
        $proxy->sendResponse();
    }

    /**
     * Handles the ajax action.
     */
    public function ajaxAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            //throw new Exception('This proxy can only be used by performing an Ajax call');
        }

        $proxyOptions = array();

        $proxyOptions['type'] = isset($_GET['type']) ? $_GET['type'] : 'text';

        if ($this->getRequest()->isPost()) {
            $proxyOptions['cachetime'] = 0;
        } else if (isset($_GET['cache']) && settype($_GET['cache'], 'integer')) {
            $proxyOptions['cachetime'] = $_GET['cache'];
        } else {
            $proxyOptions['cachetime'] = 60;
        }

        // add object if json
        if (isset($_GET['object'])) {
            $proxyOptions['object'] = $_GET['object'];
        }

        $proxy = new Proxy($this->getUrl(), $proxyOptions);

        $httpOptions = array(
            'method'  => $_SERVER['REQUEST_METHOD'],
            'auth'    => $this->getAuth(),
            'headers' => array(
                'Accept-language' => $this->getLang(),
                'Accept'          => $_SERVER['HTTP_ACCEPT']
            )
        );

        $postParameters = $this->getRequest()->getPost();
        $postBody = $this->getRequest()->getRawBody();

        if (count($postParameters) > 0) {
            $httpOptions['parameterPost'] = $postParameters;
        } else if ( !empty($postBody) ) {
            $httpOptions['rawData'] = $postBody;
        }

        $proxy->setHttpClientOptions($httpOptions);
        $proxy->sendResponse();
    }

    /**
     * Returns the authentication username and password.
     *
     * @return array
     */
    public function getAuth()
    {
        if (isset($_REQUEST['auth'], $_REQUEST['username'], $_REQUEST['password'])) {
            $this->_auth = array('username' => $_REQUEST['username'],
                                 'password' => $_REQUEST['password']);
        }
        return $this->_auth;
    }

    /**
     * Normalizes and returns the 'url' parameter.
     *
     * @return string
     */
    public function getUrl()
    {
        // Fix some invalid URLs
        $url = trim($_GET['url']);
        $url = str_replace(' ', '%20', $url);
        $url = str_replace('|', '%7C', $url);
        return $url;
    }

    /**
     * Returns the lang.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->_lang;
    }
}
