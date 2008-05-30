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


require_once 'Fetcher.php';
require_once 'Widget.php';

require_once 'Zend/Cache.php';
require_once 'Zend/Registry.php';

/**
 * Parser main class.
 */
abstract class Parser
{
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
        $this->_widget = new Widget($url);
        $this->_fetcher = new Fetcher($url, $cache);
    }

    /**
     * Widget builder.
     * 
     * @return Widget The widget instance built from the skeleton parsing
     */
    public function buildWidget()
    {
        $this->_content = $this->_fetcher->fetchContent();
        $this->handleContent();
        $this->parseContentAsXml();
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
        return $this->_widget;
    }

    /**
     * Retrieves the file content as XML.
     *
     * @return SimpleXMLElement The file as XML
     */
    public function parseContentAsXml()
    {
        $this->_xml = @new SimpleXMLElement($this->_content);
        if (!isset($this->_xml)) {
            $this->_errors = $this->_getXmlErrors();
        }
        // Display XML errors if XML is empty
        if (empty($this->_xml)) {
            $message = '<ul>';
            foreach ($this->_errors as $error) {
                $message .= "\n" . '<li><b>' . $error['message'] . '</b> at line '. $error['line'] . ': ' .
                '<code>' . htmlentities($error['code']) . '</code></li>' . "\n";
            }
            $message .= '</ul>';
            return $message;
        }
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
            return null;
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
     * Content handler that does nothing but can be redefined in subclasses.
     */
    protected function handleContent() {}

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
