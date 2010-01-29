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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
  xmlns:widget="http://www.netvibes.com/ns/">

  <head>
    <meta name="author" content="Soufiane GARCH" />
    <meta name="email" content="soufiane@garch.fr" />
    <meta name="website" content="www.garch.fr" />
    <meta name="version" content="0.2" />
    <meta name="keywords" content="Music, Deezer, Musique, free, music on demand, my music" />
    <meta name="screenshot" content="http://www.garch.fr/widgets/deezer/screenshot.png" />

    <meta name="thumbnail" content="http://www.garch.fr/widgets/deezer/thumbnail.png" />
    <meta name="apiVersion" content="1.2" />
    <meta name="debugMode" content="true" />
    <meta name="description" content="Play your favorite music from deezer playlist" />



    <widget:preferences>
        <preference name="titre" type="text" label="Title" defaultValue="Deezer" />
        <preference name="playlistid" type="text" label="Playlist ID" defaultValue="36329125" />
        <preference name="playlist" type="boolean" label="PlayList View" defaultValue="true"/>
        <preference name="autoplay" type="boolean" label="Auto Play" defaultValue="false"/>

        <preference name="language" type="list" label="Language" onchange="widget.onLoad" defaultValue="fr">
            <option value="en" label="English"/>
            <option value="fr" label="Francais"/>
        </preference>
    </widget:preferences>

    <link rel="stylesheet" type="text/css"
      href="<?php echo MAIN_URL; ?>/css/uwa-standalone.css" />

    <script type="text/javascript"
      src="<?php echo MAIN_URL; ?>/js/c/UWA_Standalone.js?v=preview3"></script>

    <title>Deezer</title>

    <link rel="icon" type="image/png" href="http://files.deezer.com/img/common/favicon.png"/>

    <script type="text/javascript">

        var UWA_WIDGET = '<?php echo BASE_URL; ?>/widget',
          UWA_JS = '<?php echo MAIN_URL; ?>/js',
          UWA_CSS = '<?php echo MAIN_URL; ?>/css',
          UWA_PROXY = '<?php echo MAIN_URL; ?>/proxy',
          UWA_STATIC = '<?php echo MAIN_URL; ?>/img';

        var Fireplace = {};

        widget.onLoad = function() {
            var titre = getTitre();
            var playlistid = getPlaylistID();
            var lang = getLanguage();
            var playlist = getPlayListView();
            var ap = getAutoPlay();

            widget.body.style.padding = "0";

            var contentHtml = '';

            contentHtml += '<div style="margin: 0 auto;text-align:center;">';

            contentHtml += '<object width="100%" height="375">';
            contentHtml += '<param name="movie" value="http://www.deezer.com/embed/player?pid='+ playlistid +'&ap='+ ap +'&ln='+ lang +'&sl='+ playlist +'"></param>';
            contentHtml += '<param name="allowFullScreen" value="true"></param>';
            contentHtml += '<param name="allowscriptaccess" value="always"></param>';
            contentHtml += '<param name="WMODE" value="transparent"></param>'
            contentHtml += '<embed 	src="http://www.deezer.com/embed/player?pid='+ playlistid +'&ap='+ ap +'&ln='+ lang +'&sl='+ playlist +'"';
            contentHtml += 'type="application/x-shockwave-flash" ';
            contentHtml += 'allowfullscreen="true" ';
            contentHtml += 'width="100%" ';
            contentHtml += 'WMODE="transparent"';
            contentHtml += 'height="375">';
            contentHtml += '</embed>';
            contentHtml += '</object>';

            contentHtml += '</div>';

            widget.setBody(contentHtml);
            widget.setTitle(titre);
            widget.onResize();
        }
        widget.onResize = function() {
          var elements = widget.body.getElementsByTagName("object");
          var flash = elements[0];
          if (flash) {
            flash.width = 1;
            flash.width = widget.body.getDimensions().width - 10;
            flash.height = Math.round(flash.width * 3/4);
          }
        }
        function getTitre() {
            var title = "Deezer";
            var tmp = widget.getValue('titre');
            if (tmp == "") {
                tmp = title
            } else {
                widget.setValue('titre', tmp);
            }
            return tmp;
        }
        function getPlaylistID() {
            var id = 36329125;
            var tmp = widget.getValue('playlistid');
            if (tmp == "") {
                tmp = id;
            } else {
                widget.setValue('playlistid', tmp);
            }
            return tmp;
        }
        function getLanguage() {
            var ln = widget.getValue('language');
            if (ln == "") {
                return "fr";
            } else {
                return ln;
            }
        }
        function getPlayListView() {
            if (widget.getValue('playlist') == "true") {
                return 1;
            } else {
                return 0;
            }
        }
        function getAutoPlay() {
            if (widget.getValue('autoplay') == "true") {
                return 1;
            } else {
                return 0;
            }
        }
    </script>

  </head>
  <body>
    <p>Loading...</p>
  </body>
</html>
