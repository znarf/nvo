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


require_once 'Exposition/Compiler.php';

/**
 * UWA compilation utilities.
 */
class Exposition_Compiler_Iphone  extends Exposition_Compiler_Uwa
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Standalone';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-iphone.css';

    /**
     * Main rendering function.
     *
     * @return string
     */
    public function render()
    {
        $l = array();
        $l[] = '<?xml version="1.0" encoding="utf-8"?>';
        $l[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
            ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $l[] = '<html xmlns="http://www.w3.org/1999/xhtml"'.
            ' xmlns:widget="http://www.netvibes.com/ns/">';

        $l[] = '<head>';
        $l[] = '<title>' . $this->_widget->getTitle() . '</title>';
        $l[] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        // Add Iphone Metas
        $l[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0" />';
        $l[] = '<meta name="apple-mobile-web-app-capable" content="yes" />';

        // Add Widget Metas
        $metas = $this->_widget->getMetadata();
        foreach ($metas as $key => $value) {
            $l[] = '<meta name="' . $key . '" content="' . $value . '" />';
        }

        // Add Widget RichIcon
        $richIcon = $this->_widget->getRichIcon();
        if (!empty($richIcon) && strlen($richIcon) > 0) {
            $l[] = '<link rel="apple-touch-icon" href="' . $richIcon . '" />';
        }

        // Add Widget Icon
        $icon = $this->_widget->getIcon();
        if (!empty($icon) && strlen($icon) > 0) {
            $l[] = '<link rel="icon" type="image/png" href="' . $icon . '" />';
        }

        // Add Widget stylesheet
        $stylesheets = $this->getStylesheets();
        foreach ($stylesheets as $stylesheet) {
            $l[] = '<link rel="stylesheet" type="text/css"'. ' href="' . $stylesheet . '"/>';
        }

        // Add Widget javascript constants
        $l[] =  $this->_getJavascriptConstants();

        // Add Widget javascripts
        $javascripts = $this->_getJavascripts();
        foreach ($javascripts as $javascript) {
            $l[] = '<script type="text/javascript" src="' . $javascript . '"></script>';
        }

        // Add Widget Preferences
        $preferences = $this->_widget->getPreferences();
        if (isset($preferences) && count($preferences) > 0) {
            $l[] = '<widget:preferences>';
            foreach ($preferences as $pref) {
                $l[] = $this->_getPreferenceXml($pref);
            }
            $l[] = '</widget:preferences>';
        }

        // Add Widget Styles
        $style = $this->_widget->getStyle();
        if (isset($style) && strlen($style) > 0) {
            $l[] = '<style type="text/css">'. "\n" . $style . '</style>';
        }

        // Add Widget Javascript
        $script = $this->_widget->getCompressedScript();
        if (isset($script) && strlen($script) > 0) {
            $l[] = '<script type="text/javascript">';
            $l[] = '//<![CDATA[';
            $l[] = $script;
            $l[] = '//]]>';
            $l[] = '</script>';
        }

        $l[] = '</head>';
        $l[] = '<body onload="window.scrollTo(0, 1)">';
        $l[] = $this->_widget->getBody();
        $l[] = '</body>';
        $l[] = '</html>';

        return implode("\n", $l);
    }
}

