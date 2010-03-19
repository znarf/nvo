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


require_once 'Exposition/Fetcher.php';
require_once 'Exposition/Widget.php';

require_once 'Zend/Cache.php';
require_once 'Zend/Registry.php';

/**
 * Parser main class.
 */
abstract class Exposition_Parser
{
    /**
     * The widget url.
     *
     * @var string
     */
    protected $_url;

    /**
     * The widget to build.
     *
     * @var Widget
     */
    protected $_widget;

    /**
     * Widget file fetcher.
     *
     * @var Fetcher
     */
    protected $_fetcher;

    /**
     * Widget file content.
     *
     * @var string
     */
    protected $_content;

    /**
     * Widget as XML.
     *
     * @var SimpleXMLElement
     */
    protected $_xml;

    /**
     * Widget Parser constructor.
     *
     * @param string  $url The URL to parse
     * @param boolean $cache Whether to use the cache
     */
    public function __construct($url, $cache = true)
    {
        $this->_url = trim($url);

        if (empty($this->_url)) {
            throw new Exposition_Parser_Exception('Unable to fetch empty Url.');
        }

        $this->_widget = new Exposition_Widget($this->_url);
        $this->_fetcher = new Exposition_Fetcher($this->_url, $cache);
    }

    /**
     * Widget builder.
     *
     * @return Widget The widget instance built from the skeleton parsing
     */
    public function buildWidget()
    {
        try {
            $this->getContent();
            $this->handleContent();
            $parse = $this->parseContentAsXml(array('throwException' => false));
            if ($parse === false) {
                $this->recoverXml();
                $this->parseContentAsXml();
            }
            $this->parseWidget();
        } catch (Zend_Http_Exception $e) {
            $this->_handleHttpErrors($e);
        } catch (Parser_Exception $e) {
            $this->_handleXmlErrors($e);
        }
        return $this->_widget;
    }

    /**
     * Widget parser
     */
    public function parseWidget()
    {
        $this->parseTitle();
        $this->parseMetadata();
        $this->parsePreferences();
        $this->parseStyle();
        $this->parseExternalStylesheets();
        $this->parseScript();
        $this->parseCoreLibrary();
        $this->parseExternalScripts();
        $this->parseBody();
        $this->parseIcon();
        $this->parseRichIcon();
    }

    /**
     * Retrieve raw content.
     *
     * @return string The widget content
     */
    public function getContent()
    {
        if (empty($this->_content)) {
            $this->_content = $this->_fetcher->fetchContent();
        }
        return $this->_content;
    }

    /**
     * Retrieves the file content as XML.
     *
     * @return SimpleXMLElement The file as XML
     */
    public function parseContentAsXml(array $options = array())
    {
        // SimpleXml send PHP notices with invalid XML
        // we try to handle this errors elsewhere
        try {
            $this->_xml = @new SimpleXMLElement($this->_content);
        } catch (Exception $e) {
            if (empty($options) || !isset($options['throwException']) || $options['throwException'] === true) {
                throw new Exposition_Parser_Exception($e);
            }
            return false;
        }
        return true;
    }

    /**
     * Retrieves the XML errors.
     *
     * @return array XML Errors
     */
    private function _getXmlErrors()
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'utf-8');
        $document->loadXML($this->_content);
        $errors = libxml_get_errors();

        if (empty($errors)) {
            return array();
        }

        $lines = explode("\n", $this->_content);

        foreach ($errors as $error) {
            $this->_errors[] = array(
            "message" => trim($error->message),
            "line" => $error->line,
            "code" => $lines[($error->line) - 1]
            );
        }
        return $this->_errors;
    }

    /**
     * Fill the title & body of the widget object
     * with XML debugging informations
     */
    private function _handleXmlErrors($exception)
    {
        $body = '<ul>';
        $i = 0;
        foreach ($this->_getXmlErrors() as $error) {
            $body .= "\n" . '<li><b>' . $error['message'] . '</b> at line '. $error['line'] . ': ' .
            '<code>' . htmlentities($error['code']) . '</code></li>' . "\n";
            $i++;
            if ($i == 3) {
                break;
            }
        }
        $body .= '</ul>';
        $this->_widget->setTitle('XML Error');
        $this->_widget->setBody($body);
    }

    /**
     * Fill the title & body of the widget object
     * with HTTP debugging informations
     */
    private function _handleHttpErrors($exception)
    {
        $this->_widget->setTitle('HTTP Error');
        $this->_widget->setBody(
            '<p>' . $exception->getMessage() . '</p>' .
            '<p>' . 'with url: ' . $this->_url . '</p>');
    }

    /**
     * Content handler that does nothing but can be redefined in subclasses.
     */
    protected function handleContent() {}

    /**
     * Content handler that does nothing but can be redefined in subclasses.
     */
    protected function recoverXml() {}

    /*** ABSTRACT FUNCTIONS ***/

    abstract public function parseTitle();

    abstract public function parseMetadata();

    abstract public function parseIcon();

    abstract public function parseRichIcon();

    abstract public function parsePreferences();

    abstract public function parseStyle();

    abstract public function parseExternalStylesheets();

    abstract public function parseScript();

    abstract public function parseExternalScripts();

    abstract public function parseBody();
}
