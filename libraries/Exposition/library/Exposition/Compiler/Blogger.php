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
 * Frame Compiler to render a UWA widget within an iframe.
 */
class Exposition_Compiler_Blogger extends Exposition_Compiler
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Frame';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-iframe.css';

    /**
     * Width of the widget.
     *
     * @var string
     */
    protected $_width = 220;

    /**
     * Height of the widget.
     *
     * @var string
     */
    protected $_height = 250;

    /**
     * Main rendering function.
     *
     * @return string
     */
    public function render()
    {
        $widgetEndpoint = Exposition_Load::getConfig('endpoint', 'widget');

        return '<iframe frameborder="0" width="' . $this->_width . '" height="' . $this->_height . '" src="' . $widgetEndpoint . '/frame?uwaUrl=' . urlencode($this->_widget->getUrl()) . '"></iframe>';
    }

    public function getBloggerWidgetUrl()
    {
        $bloggerParams = array(
            'widget.title'      => $this->_widget->getTitle(),
            'widget.content'    => $this->render(),
            'widget.template'   => '&lt;data:content/&gt;',
            'infoUrl'           => $this->_widget->getUrl(),
            'logoUrl'           => $this->_widget->getIcon(),
        );

        return 'http://www.blogger.com/add-widget?' . http_build_query($bloggerParams);
    }
}

