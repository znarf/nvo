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


require_once 'Exposition/Parser.php';
require_once 'Exposition/Widget/Preference.php';

/**
 * UWA XHTML Widget Parser.
 */
class Exposition_Parser_Uwa extends Exposition_Parser
{
    /**
     * Metas authorized in UWA widgets
     */
    public $authorizedMetas = array(
        'author', 'email', 'website', 'description', 'keywords', 'version',
        'screenshot', 'thumbnail', 'apiVersion', 'debugMode', 'chromeColor', 'autoRefresh');

    /**
     * Known UWA Controls & Services
     *  index: the string used to instanciate it
     *  value: the library relative path/name
     *
     * NOTE:
     * this components are not officially released as open source yet
     * and therefore should be directly linked on Netvibes servers
     */
    public $knownLibraries = array(
        'UWA.Controls.TabView'     => 'UWA/Controls/TabView.js',
        'UWA.Controls.Pager'       => 'UWA/Controls/Pager.js',
        /*
        'UWA.Controls.ToolTip'     => 'UWA/Controls/ToolTip.js',
        'UWA.Controls.SearchForm'  => 'UWA/Controls/SearchForm.js',
        'UWA.Controls.FlashPlayer' => 'App/Controls/FlashPlayer.js',
        'UWA.Controls.FeedView'    => 'UWA/Controls/FeedView.js',
        'UWA.Services.Search'      => 'UWA/Services/Search.js',
        'UWA.Services.Mail'        => 'UWA/Services/Mail.js',
        'UWA.Services.FeedHistory' => 'UWA/Services/FeedHistory.js',
        'UWA.Controls.MultiPage'   => 'App/Controls/MultiPage.js',
        'UWA.Controls.Timeline'    => 'App/Controls/Timeline.js'
        */
    );

    /**
     * Known UWA Standalone
     *  value: the library relative path/name
     */
    public $ignoredLibraries = array(
        'load.js.php',
        'UWA_Standalone.js',
        'UWA_Standalone_Mootools.js',
    );

    /**
     * Parses the widget title.
     */
    public function parseTitle()
    {
        $this->_widget->setTitle((string) $this->_xml->head->title);

        return $this;
    }

    /**
     * Retrieves the metadata.
     */
    public function parseMetadata()
    {
        $metas = array();
        foreach ($this->_xml->head->meta as $meta) {
            $name = (string) $meta['name'];
            $content = (string) $meta['content'];
            if (!empty($content) && in_array($name, $this->authorizedMetas)) {
                $metas[$name] = $content;
            }
        }

        asort($metas);

        $this->_widget->setMetadata($metas);

        return $this;
    }

    /**
     * Retrieves the widget preferences.
     */
    public function parsePreferences()
    {
        $widget = $this->_xml->head->children('http://www.netvibes.com/ns/');
        if ($widget) {
            foreach ($widget->children() as $preferenceXml) {

                $attributes = $preferenceXml->attributes();
                if (isset($attributes['inline'])) {
                    return; // inline verbotten
                }

                $preference = new Exposition_Widget_Preference((string) $attributes['type']);
                $preference->setName((string) $attributes['name']);
                $preference->setLabel((string) $attributes['label']);
                if (isset($attributes['defaultValue'])) {
                    $preference->setDefaultValue((string) $attributes['defaultValue']);
                }

                if (isset($attributes['onchange'])) {
                    $preference->setOnchangeCallback((string) $attributes['onchange']);
                }

                foreach ($preferenceXml->option as $option) {
                    if (empty($option['label'])) {
                        $preference->addListOption((string) $option['value']);
                    } else {
                        $preference->addListOption((string) $option['value'], (string) $option['label']);
                    }
                }

                if ($preference->getType() == 'range') {
                    $preference->setRangeOptions((string) $attributes['step'], (string) $attributes['min'], (string) $attributes['max']);
                }

                $this->_widget->addPreference($preference->getName(), $preference);
            }
        }

        return $this;
    }

    /**
     * Retrieves the widget style information.
     */
    public function parseStyle()
    {
        $css = '';
        foreach($this->_xml->head->style as $style) {
            $css .= trim((string) $style);
        }

        // Strip comments, double spaces, line breaks
        $css = preg_replace('/\/\*(.*)\*\/|\n|\r|\t/', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);

        // Add line breaks
        $css = str_replace("}", "}\n", $css);

        $this->_widget->setStyle($css);

        return $this;
    }

    /**
     * Retrieves the list of external CSS stylesheets URI,
     * excluding the UWA and Mini-API ones.
     */
    public function parseExternalStylesheets()
    {
        // UWA CSS Stylesheets
        $ignore = array(
            "/css/uwa-standalone.css",
            "/themes/uwa/style.css",
            "/api/0.3/style.css",
            "/api/0.2/style.css"
        );

        $stylesheets = array();
        foreach ($this->_xml->head->link as $link) {
            if (isset($link['type']) && (string) $link['type'] == 'text/css') {
                $href = (string)$link['href'];
                $url = parse_url($href);
                if( !empty($url['host']) && !in_array($url['path'], $ignore) ) {
                    $stylesheets[] = $href;
                }
            }
        }

        $this->_widget->setExternalStylesheets($stylesheets);

        return $this;
    }

    /**
     * Retrieves the internal JavaScript code.
     */
    public function parseScript()
    {
        $js = '';
        foreach ($this->_xml->head->script as $script) {
            if (empty($script['src'])) {
                $js .= (string) $script;
            }
        }

        $this->_widget->setScript($js);

        return $this;
    }

    /**
     * Retrieves the list of external JavaScript libraries URI,
     * excluding the UWA environment emulation files.
     */
    public function parseExternalScripts()
    {
        if (empty($this->_xml)) {
            return array();
        }

        $libraries = array();
        $knownLibraries = array_map(array(get_class($this), '_getNormalScriptPath'), $this->knownLibraries);
        $knownLibrariesCompressed = array_map(array(get_class($this), '_getCompressedScriptPath'), $this->knownLibraries);


        foreach ($this->_xml->head->script as $script) {

           if (
               // exclude ignored library
               self::_matchScriptPathFromScriptsNames($script, $this->ignoredLibraries)

               // exclude known Libraries with normal and compressed name
               && self::_matchScriptPathFromScriptsNames($script, $knownLibraries)
               && self::_matchScriptPathFromScriptsNames($script, $knownLibrariesCompressed)
           ) {
                $libraries[] = (string) $script['src'];
           }
        }

        $libraries = array_unique(array_merge($libraries, $this->getDetectedLibraries()));
        $this->_widget->setExternalScripts($libraries);

        return $this;
    }

    /**
     *
     */
    protected static function _matchScriptPathFromScriptsNames(SimpleXMLElement $script, $scriptsNames)
    {
        $scriptSrc = (string) $script['src'];

        // exclude empty script src
        if (empty($scriptSrc)) {
            return false;
        }

        $scriptUrlParts = parse_url($scriptSrc);

        // exclude external try to load internal
        if (empty($scriptUrlParts['host'])) {
            return false;
        }

        foreach ($scriptsNames as $scriptName => $scriptPath) {
            if (strstr($scriptUrlParts['path'], $scriptPath) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Detects the JavaScript framework, for instance Mootools.
     */
    public function parseCoreLibrary()
    {
        foreach ($this->_xml->head->script as $script) {
            $src = (string) $script['src'];
            if (!empty($src)) { // it's an external script
                if (stripos($src, 'UWA_Standalone_Mootools.js') !== false) {
                    $this->_widget->setCoreLibrary('uwa-mootools');
                    return ;
                } else if (stripos($src, 'UWA_Standalone.js') !== false) {
                    $this->_widget->setCoreLibrary('uwa');
                    return ;
                }
            }
        }

        $script = $this->_widget->getScript();
        $libraries = array();
        $detect = array(
            'getElements('           => 'uwa-mootools',
            'new Hash'               => 'uwa-mootools',
            'Hash.'                  => 'uwa-mootools',
            'new Element'            => 'uwa-mootools',
            'removeEvents'           => 'uwa-mootools',
            '.clone()'               => 'uwa-mootools',
            'new PeriodicalExecuter' => 'uwa-mootools',
            'UWA.Controls.Timeline'  => 'uwa-mootools'
        );

        foreach ($detect as $string => $name) {
            if (stripos($script, $string)) {
                $this->_widget->setCoreLibrary($name);
                return;
            }
        }

        return $this;
    }

    /**
     * Detects the specific UWA Controls.
     *
     * @return array
     */
    public function getDetectedLibraries()
    {
        $script = $this->_widget->getScript();
        $libraries = array();

        $useCompressedJs = Exposition_Load::getConfig('js', 'compressed');
        $useVersionJs = Exposition_Load::getConfig('js', 'version');
        $jsEndPoint = Exposition_Load::getConfig('endpoint', 'js');

        foreach ($this->knownLibraries as $string => $src) {

            if (stripos($script, $string)) {

                if ($useCompressedJs) {
                    $library = $jsEndPoint . self::_getCompressedScriptPath($src);
                } else {
                    $library = $jsEndPoint . self::_getNormalScriptPath($src);
                }

                $libraries[] = $library . '?v=' . $useVersionJs;

            }
        }

        return $libraries;
    }

    protected static function _getCompressedScriptPath($scriptPath)
    {
        $matches = array();
        preg_match("/^(UWA|App)\/([^\/]+)\/([^\/]+).js$/", $scriptPath, $matches);
        $type = $matches[2];
        $name = $matches[3];

        return '/c/UWA_' . $type . '_' . $name . '.js';
    }

    protected static function _getNormalScriptPath($scriptPath)
    {
    	$matches = array();
        preg_match("/^(UWA|App)\/([^\/]+)\/([^\/]+).js$/", $scriptPath, $matches);
        $type = $matches[2];
        $name = $matches[3];

        return '/lib/UWA/' . $type . '/' . $name . '.js';
    }

    /**
     * Retrieves the widget body.
     */
    public function parseBody()
    {
        if (count($this->_xml->body->children()) == 0) {
            $this->_widget->setBody((string) $this->_xml->body);
        } else {
            $html = '';
            foreach ($this->_xml->body->children() as $child) {
                $string = $child->asXML();
                $string = str_replace('<![CDATA[', '', $string);
                $string = str_replace(']]>', '', $string);
                $html .= $string;
            }

            $this->_widget->setBody($html);
        }

        return $this;
    }

    /**
     * Retrieves the widget icon URL.
     */
    public function parseIcon()
    {
        foreach ($this->_xml->head->link as $link) {
            if (isset($link['rel']) && $link['rel'] == 'icon' && isset($link['href'])) {
                $this->_widget->setIcon((string) $link['href']);
                return;
            }
        }

        // set default Icon
        $staticEndPoint = Exposition_Load::getConfig('endpoint', 'static');
        $this->_widget->setIcon($staticEndPoint . '/icon.png');

        return $this;
    }

    /**
     * Retrieves the widget rich icon URL.
     */
    public function parseRichIcon()
    {
        foreach ($this->_xml->head->link as $link) {
            if (isset($link['rel']) && $link['rel'] == 'rich-icon' && isset($link['href'])) {
                $this->_widget->setRichIcon((string) $link['href']);
                return;
            }
        }

        // set default RichIcon
        $staticEndPoint = Exposition_Load::getConfig('endpoint', 'static');
        $this->_widget->setRichIcon($staticEndPoint . '/richIcon.png');

        return $this;
    }

    /**
     * Apply some fixes to the content.
     */
    protected function handleContent()
    {
        $body = trim($this->_content);

        // Remove the BOM
        $body = str_replace("\xEF\xBB\xBF", "", $body);

        // Make the widget valid XML by adding CDATA sections (skipped if we find at least one CDATA section in it)
        if (strpos($body, "<![CDATA[") === false){
               $body = preg_replace('/(<(style|script)[^>]*?>)[^<]+?/si', '\\1<![CDATA[', $body);
               $body = preg_replace('/\s*[^>](<\/(style|script)>)/si', "\n]]>\\1", $body);
        }

        // Disable proxy replacement
        $comment = "\n// Proxy declaration for standalone mode disabled.\nvar isProxyDisabled = true;\n// UWA.proxies.";
        $body = str_replace('UWA.proxies.', $comment, $body);

        $this->_content = $body;
    }

    /**
     * Try to make XML well formed.
     */
    protected function recoverXml()
    {
        $body = $this->_content;

        // replace all '&' not followed by 'amp;' by '&amp;' in body
        $matches = array();
        $bodyTag = preg_match("/<body>(.*)<\/body>/Uis", $body, $matches);
        if (isset($matches[1])) {
            $bodyTag = $matches[1];
            $modifiedBodyTag = preg_replace("/&(?!amp;)/i", "&amp;", $bodyTag);
            $body = str_replace($bodyTag, $modifiedBodyTag, $body);
        }

        // replace htmlentities in the preferences tag if necessary
        $matches = array();
        $prefTag = preg_match("/<widget:preferences>(.*)<\/widget:preferences>/Uis", $body, $matches);
        if (isset($matches[1])) {
            $prefTag = $matches[1];
            $modifiedPrefTag = html_entity_decode($prefTag, ENT_NOQUOTES, 'UTF-8');
            $body = str_replace($prefTag, $modifiedPrefTag, $body);
        }

        $this->_content = $body;
    }
}
