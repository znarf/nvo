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
 * Live Compiler to render widget as a Live.com XML manifest.
 */
class Exposition_Compiler_Live extends Exposition_Compiler
{
    /**
     * Javascript UWA environment.
     *
     * @var string
     */
    protected $_environment = 'Live2';

    /**
     * Stylesheet.
     *
     * @var string
     */
    protected $_stylesheet = 'uwa-iframe.css';

    /**
     * Platform Name.
     *
     * @var string
     */
    protected $_platform = 'live';

    /**
     * Main rendering function.
     *
     * @return string
     */
    public function render()
    {
        $metas = $this->_widget->getMetas();
        $richIcon = $this->_widget->getRichIcon();
        $description = isset($metas['description']) ? $metas['description'] : '';

        $l = array();

        $l[] = '<?xml version="1.0" encoding="utf-8"?>';
        $l[] = '<rss version="2.0" xmlns:binding="http://www.live.com">';
        $l[] = '<channel>';
        $l[] = '<title>' . htmlspecialchars( $this->_widget->getTitle() ) . '</title>';
        $l[] = '<description>' . htmlspecialchars($description) . '</description>';

        $l[] = '<language>en-us</language>';
        $l[] = '<binding:type>Netvibes.UWA.Live</binding:type>';

        $javascripts = $this->_getJavascripts( array('platform' => $this->_platform) );

        foreach ($javascripts as $javascript) {
            $l[] = '<item>';
            $l[] = '<link>' . htmlspecialchars($javascript) . '</link>';
            $l[] = '</item>';
        }

        foreach ($this->_getStylesheets() as $stylesheet) {
            $l[] = '<item>';
            $l[] = '<link binding:type="css">' . htmlspecialchars($stylesheet) . '</link>';
            $l[] = '</item>';
        }

        if (isset($richIcon)) {
            $l[] = '<item>';
            $l[] = '<icon>' . $richIcon . '</icon>';
            $l[] = '</item>';
        }

        $l[] = '</channel>';
        $l[] = '</rss>';

        return implode("\n", $l);
    }

}
