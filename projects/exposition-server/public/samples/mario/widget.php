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

//---------------------------------------------------------------------------
// Define usefull paths for current Exposition PHP Server testing.

define('BASE_URL', $_SERVER['HTTP_HOST']);
define('BASE_URL_SCHEME', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://'));
define('MAIN_URL', BASE_URL_SCHEME . BASE_URL);
define('WIDGET_URL', $_SERVER['SCRIPT_URI']);

echo '<?xml version="1.0" encoding="utf-8"?>';

?>
<html xmlns="http://www.w3.org/1999/xhtml"
  xmlns:widget="http://www.netvibes.com/ns/">

  <head>
    <meta name="author" content="Exposition Libraries" />
    <meta name="description" content="Displays Sample CSS and Crontols for UWA" />
    <meta name="apiVersion" content="1.2" />
    <meta name="debugMode" content="true" />

    <link rel="stylesheet" type="text/css"
      href="<?php echo MAIN_URL; ?>/css/uwa-standalone.css" />

    <script type="text/javascript"
      src="<?php echo MAIN_URL; ?>/js/c/UWA_Standalone.js?v=preview3"></script>

    <script type="text/javascript"
      src="<?php echo MAIN_URL; ?>/js/c/UWA_Controls_TabView.js?v=preview3"></script>

    <title>My Sample Widget</title>
    <link rel="icon" type="image/png" href="<?php echo MAIN_URL; ?>/img/icon.png" />

    <script type="text/javascript">

      var UWA_WIDGET = '<?php echo BASE_URL; ?>/widget',
        UWA_JS = '<?php echo MAIN_URL; ?>/js',
        UWA_CSS = '<?php echo MAIN_URL; ?>/css',
        UWA_PROXY = '<?php echo MAIN_URL; ?>/proxy',
        UWA_STATIC = '<?php echo MAIN_URL; ?>/img';

    </script>

    <style>
        #mariokartcontainer img {
            padding : 0 !important;
            margin : 0 !important;
            border : none !important;
        }
    </style>
  </head>
  <body>
    <script type="text/javascript">
      widget.onLoad = function() {

         var MARIO_STATIC = '<?php echo MAIN_URL; ?>/samples/mario/';

        <?php readfile('mariokart.js'); ?>

        MarioKart();

      }
    </script>

    <div style="position:relative; height: 150px;overflow: hidden; margin: 0 10%">
        <div id="mariokartcontainer" style="height:400px;position:relative;">
            Loading...
        </div>
    </div>

    <div id="export-content">

        <h2>Stable</h2>
        <ul>
            <li><a target="_blank" href="http://www.netvibes.com/subscribe.php?module=UWA&amp;moduleUrl=<?php echo urlencode(WIDGET_URL); ?>">Netvibes</a></li>

            <li><a target="_blank" href="http://www.google.com/ig/add?moduleurl=<?php echo urlencode(MAIN_URL); ?>%2Fwidget%2Fgspec%3FuwaUrl%3D<?php echo urlencode(urlencode(WIDGET_URL)); ?>">iGoogle</a></li>
            <li><a target="_blank" href="/widget/dashboard?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Dashboard</a></li>
            <li><a target="_blank" href="/widget/screenlets?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Screenlets</a></li>
            <li><a target="_blank" href="/widget/blogger?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Blogger</a></li>
            <li><a target="_blank" href="http://my.live.com/?s=1&amp;add=<?php echo urlencode(MAIN_URL); ?>%2Fwidget%2Flive%3F<?php echo urlencode(urlencode(WIDGET_URL)); ?>">Live</a></li>
            <li><a target="_blank" href="/widget/opera?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Opera</a></li>

            <li>
                <a target="_blank" href="/widget/frame?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>&amp;id=54bcd7bc3469f1ccb12f1da055ac3986">Iframe</a>
                (
                    <a target="_blank" href="/widget/frame?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>&amp;id=54bcd7bc3469f1ccb12f1da055ac3986&amp;header=1">with header</a>
                    - <a target="_blank" href="/widget/frame?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>&amp;id=54bcd7bc3469f1ccb12f1da055ac3986&amp;header=1&amp;chromeColor=orange">with header color</a>
                    - <a target="_blank" href="/widget/frame?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>&amp;id=54bcd7bc3469f1ccb12f1da055ac3986&amp;header=1&amp;status=0">without status</a>

                )
            </li>
        </ul>

        <h2>Unstable</h2>
        <ul>
            <li><a target="_blank" href="/widget/chrome?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Google Chrome</a> (Work in progress)</li>
            <li><a target="_blank" href="/widget/jil?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Jil/Ophone</a> (Work in progress)</li>

            <li><a target="_blank" href="/widget/vista?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Windows Vista/SEVEN</a> (Work in progress)</li>
        </ul>
    </div>
  </body>
</html>


