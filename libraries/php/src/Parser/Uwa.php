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


require_once 'Parser.php';
require_once 'Widget/Preference.php';

/**
 * UWA XHTML Widget Parser.
 */
class Parser_Uwa extends Parser
{
    /**
     * Parses the widget title.
     */
    public function parseTitle()
    {
        $this->_widget->setTitle((string) $this->_xml->head->title);
    }

    /**
     * Retrieves the metadata.
     */
    public function parseMetadata()
    {
        $metas = array();
        $authorized = array(
            'author', 'website', 'description', 'keywords', 'version',
            'screenshot', 'thumbnail', 'apiVersion', 'debugMode', 'autoRefresh');
        foreach ($this->_xml->head->meta as $meta) {
            $name = (string) $meta['name'];
            $content = (string) $meta['content'];
            if (!empty($content) && in_array($name, $authorized)) {
                $metas[$name] = $content;
            }
        }
        asort($metas);
        $this->_widget->setMetadata($metas);
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
                $preference = new Widget_Preference((string) $attributes['type']);
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
    }

    /**
     * Retrieves the list of external CSS stylesheets URI,
     * excluding the UWA and Mini-API ones.
     */
    public function parseExternalStylesheets()
    {
        // UWA CSS Stylesheets
        $ignore = array(
            "/themes/uwa/style.css",
            "/api/0.3/style.css",
            "/api/0.2/style.css");

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
        $ignore = array(
                "/js/UWA/load.js.php",
                "/js/c/UWA_Standalone.js",
                "/js/c/UWA_Standalone_Mootools.js");
        $libraries = array();
        foreach ($this->_xml->head->script as $script) {
            $src = (string) $script['src'];
            if (!empty($src)) {
                $url = parse_url($src);
                if( !empty($url['host']) && !in_array($url['path'], $ignore) ) {
                    $libraries[] = $src;
                }
            }
        }
        $libraries = array_unique(array_merge($libraries, $this->getDetectedLibraries()));
        $this->_widget->setExternalScripts($libraries);
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
            'getElements'            => 'uwa-mootools',
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
        $detect = array(
            'UWA.Controls.Pager'       => 'UWA/Controls/Pager.js',
            'UWA.Controls.TabView'     => 'UWA/Controls/TabView.js',
            'UWA.Controls.ToolTip'     => 'UWA/Controls/ToolTip.js',
            'UWA.Controls.SearchForm'  => 'UWA/Controls/SearchForm.js',
            'UWA.Controls.FlashPlayer' => 'App/Controls/FlashPlayer.js',
            'UWA.Controls.FeedView'    => 'UWA/Controls/FeedView.js',
            'UWA.Services.Search'      => 'UWA/Services/Search.js',
            'UWA.Services.Mail'        => 'UWA/Services/Mail.js',
            'UWA.Services.FeedHistory' => 'UWA/Services/FeedHistory.js',
            'UWA.Controls.MultiPage'   => 'App/Controls/MultiPage.js',
            'UWA.Controls.Timeline'    => 'App/Controls/Timeline.js'
        );
        foreach ($detect as $string => $src) {
            if (stripos($script, $string)) {
                $useCompressedJs = Zend_Registry::get('useCompressedJs');
                if ($useCompressedJs) {
                    preg_match("/^(UWA|App)\/([^\/]+)\/([^\/]+).js$/", $src, $matches);
                    $type = $matches[2];
                    $name = $matches[3];
                    $libraries[] = 'http://' . NV_STATIC . '/js/c/UWA_' . $type . '_' . $name . '.js';
                } else {
                    $libraries[] = 'http://' . NV_STATIC . '/js/' . $src;
                }
            }
        }
        return $libraries;
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
    }

    /**
     * Normalizes a HTML document to try to make its markup XML valid.
     * Removes the BOM (Byte Order Mark) and encapsulates title, styles
     * and JavaScript code in a CDATA section for XML well-formedness.
     */
    protected function handleContent()
    {
        $body = trim($this->_content);

        // Remove the BOM
        $body = str_replace("\xEF\xBB\xBF", "", $body);

        // Make the widget valid XML by adding CDATA sections
        $body = preg_replace('/(<(style|script) type[^>]*?>)\s*(<!\[CDATA\[)?/si', '\\1<![CDATA[', $body);
        $body = preg_replace('/(\]\]>)?\s*(<\/(style|script)>)/si', ']]>\\2', $body);

        // Disable proxy replacement
        $comment = "\n// Proxy declaration for standalone mode disabled.\nvar isProxyDisabled = true;\n// UWA.proxies.";
        $body = str_replace('UWA.proxies.', $comment, $body);

        $this->_content = $body;
    }
}
