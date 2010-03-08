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


require_once 'Exposition/Compiler/Desktop.php';

/**
 * Apple Dashboard Widgets Compiler.
 */
abstract class Exposition_Compiler_Desktop_W3c extends Exposition_Compiler_Desktop
{
    /**
     * Archive Format of the widget
     *
     * @var string
     */
    protected $_archiveFormat = 'zip';

    /**
     * Extension.
     *
     * @var string
     */
    protected $_extension = 'zip';

    /**
     * Mime Type.
     *
     * @var string
     */
    protected $_mimeType = 'application/zip';

    protected function getHtml()
    {
        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8"?>';
        $l[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
            ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $l[] = '<html xmlns="http://www.w3.org/1999/xhtml">';
        $l[] = '<head>';

        $l[] = '<title>' . htmlspecialchars($this->_widget->getTitle()) . '</title>';
        $l[] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        // Add Widget stylesheet
        $stylesheets = $this->getStylesheets();
        foreach ($stylesheets as $stylesheet) {
            $l[] = '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($stylesheet) . '"/>';
        }

        $l[] = '</head>';
        $l[] = '<body>';

        $l[] = $this->_getHtmlBody();

        // Add Widget javascript constants
        $l[] = $this->_getJavascriptConstants();

        // Add Widget javascripts libs
        $javascripts = $this->_getJavascripts(array('platform' => $this->_platform));
        foreach ($javascripts as $script) {
            $l[] = '<script type="text/javascript" src="' . htmlspecialchars($script) . '" charset="utf-8"></script>';
        }

        // Add Widget javascripts
        $l[] = '<script type="text/javascript" src="' . $this->_getWidgetJavascripts() . '"  charset="utf-8"></script>';

        $l[] = '<script type="text/javascript">';
        $l[] = $this->_getScript();
        $l[] = '</script>';

        if (isset($this->options['appendBody'])) {
            $l[] = $this->options['appendBody'];
        }

        $l[] = '</body>';
        $l[] = '</html>';

        return implode("\n", $l);
    }

    protected function _getHtmlBody()
    {
        $l = array();

        $l[] = '<div class="module" id="wrapper">';
        $l[] =   $this->_getHtmlHeader();
        $l[] =   '<div id="contentWrapper">';
        $l[] =     '<div class="moduleContent" id="moduleContent">';
        $l[] =       'Loading....';
        $l[] =     '</div>';
        $l[] =     $this->_getHtmlStatus();
        $l[] =   '</div>';
        $l[] =   '<div class="moduleFooter" id="moduleFooter"></div>';
        $l[] = '</div>';

        return implode("\n", $l);
    }

    protected function _getScript()
    {
        $l = array();
        $l[] = "var id = window.widget ? widget.identifier : Math.round(Math.random() * 1000);";
        $l[] = "if (typeof Environments == 'undefined') var Environments = {};";
        $l[] = "if (typeof Widgets == 'undefined') var Widgets = {};";
        $l[] = "Environments[id] = new UWA.Environment();";
        $l[] = "Widgets[id] = Environments[id].getModule();";
        $l[] = "UWA.script(Widgets[id]);";
        $l[] = "Environments[id].launchModule();";

        return implode("\n", $l);
    }

    /**
     * Get clean widget file name
     *
     * @return string a clean widget name
     */
    public function getFileName()
    {
        $filename = preg_replace('/[^a-z0-9]/i', '', $this->getNormalizedTitle());
        if (!empty($filename)) {
            return $filename . '.' . $this->_extension;
        } else {
            return 'Widget' . '.' . $this->_extension;
        }
    }

    /**
     * Get clean widget title
     *
     * @return string a clean widget name
     */
    public function getNormalizedTitle()
    {
        return $this->_widget->getTitle();
    }

    /**
     * Get widget minetype header value
     *
     * @return string minetype header value
     */
    public function getFileMimeType()
    {
        return $this->_mimeType;
    }

    /*** ABSTRACT FUNCTIONS ***/

    abstract protected function _getManifest();
}

