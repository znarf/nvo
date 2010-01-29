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

echo '<?xml version="1.0" encoding="utf-8"?>';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
  xmlns:widget="http://www.netvibes.com/ns/">

  <head>
    <meta name="author" content="Exposition Libraries" />
    <meta name="description" content="Displays latest post from a feed" />
    <meta name="apiVersion" content="1.2" />
    <meta name="debugMode" content="true" />

    <link rel="stylesheet" type="text/css"
      href="/css/uwa-standalone.css" />

    <script type="text/javascript"
      src="/js/c/UWA_Standalone.js?v=preview3"></script>

    <title>Latest blog post</title>
    <link rel="icon" type="image/png" href="<?php echo MAIN_URL; ?>/img/icon.png" />

    <widget:preferences>
      <preference type="text" name="theFeed" label="RSS/Atom feed to use"
        defaultValue="http://blog.netvibes.com/rss.php" />
    </widget:preferences>

    <script type="text/javascript">

      var UWA_WIDGET = '<?php echo BASE_URL; ?>/widget',
        UWA_JS = '<?php echo MAIN_URL; ?>/js',
        UWA_CSS = '<?php echo MAIN_URL; ?>/css',
        UWA_PROXY = '<?php echo MAIN_URL; ?>/proxy',
        UWA_STATIC = '<?php echo MAIN_URL; ?>/img'

      var DisplayLatestBlogPost = {};
      DisplayLatestBlogPost.display = function(feed) {
        var latestPost = feed.items[0];

        widget.setTitle('<a href="'+feed.htmlUrl+'">'+feed.title+'</a>');

        var contentHtml = '';
        contentHtml += '<h2><a href="' + latestPost.link + '">' + latestPost.title + '</a></h2>';
        contentHtml += 'Posted on ' + latestPost.date + '<br />';
        contentHtml += latestPost.content;
        contentHtml += '<em>Read all the previous posts by ' +
                    + '<a href="' + feed.htmlUrl + '">visiting the blog</a>!</em>'
        widget.setBody(contentHtml);
      }

      widget.onLoad = function() {
        UWA.Data.getFeed( widget.getValue('theFeed'), DisplayLatestBlogPost.display );
      }
    </script>

  </head>
  <body>
    <p>Loading...</p>
  </body>
</html>

