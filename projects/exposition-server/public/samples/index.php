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


// handle test json request
if (isset($_GET['json'])) {

    header("Content-type: application/json");
    echo json_encode(array('date' => date('Y-m-d H:i:s')));
    exit(0);

// handle test ajax request
} else if (isset($_GET['ajax'])) {

    ?>
    <h1>Hello from Ajax</h1>
    <h2>Ajax Request date is <?php echo date('Y-m-d H:i:s'); ?></h2>
    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean vitae rutrum
        justo. Morbi adipiscing consequat facilisis. Proin nunc nunc, condimentum vel
        sagittis sed, tempus eu ligula. Aliquam vitae elit erat. Proin rhoncus tempus
        dignissim. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam a diam
        justo. Etiam blandit ligula id risus sollicitudin aliquam. Morbi in sapien dui.
        Mauris suscipit rutrum dui, nec molestie nulla tincidunt nec. Vivamus nisi dui,
        pulvinar eu molestie a, mattis ut lectus. Class aptent taciti sociosqu ad litora
        torquent per conubia nostra, per inceptos himenaeos. Pellentesque habitant morbi
        tristique senectus et netus et malesuada fames ac turpis egestas. Donec eleifend
        elementum enim fringilla pellentesque. Suspendisse sit amet tincidunt libero.
        Sed sollicitudin risus vel orci semper consequat. Aenean lacus tortor, imperdiet
        id pellentesque eget, pretium vitae ipsum
    </p>

    <p>

        "'
    </p>
    <?php
    exit(0);
}

echo '<?xml version="1.0" encoding="utf-8"?>';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
  xmlns:widget="http://www.netvibes.com/ns/">

  <head>
    <title>My Sample Widget</title>

    <meta name="author" content="Exposition Libraries" />
    <meta name="description" content="Displays Sample CSS and Crontols for UWA" />
    <meta name="apiVersion" content="1.2" />
    <meta name="debugMode" content="true" />

    <link rel="icon" type="image/png" href="<?php echo MAIN_URL; ?>/img/icon.png" />

    <link rel="stylesheet" type="text/css"
      href="<?php echo MAIN_URL; ?>/css/uwa-standalone.css" />

    <script type="text/javascript">

      var UWA_WIDGET = '<?php echo MAIN_URL; ?>/widget',
        UWA_JS = '<?php echo MAIN_URL; ?>/js',
        UWA_CSS = '<?php echo MAIN_URL; ?>/css',
        UWA_PROXY = '<?php echo MAIN_URL; ?>/proxy',
        UWA_STATIC = '<?php echo MAIN_URL; ?>/img';

    </script>

    <script type="text/javascript"
      src="<?php echo MAIN_URL; ?>/js/c/UWA_Standalone.js?v=preview3"></script>

    <script type="text/javascript"
      src="<?php echo MAIN_URL; ?>/js/c/UWA_Controls_TabView.js?v=preview3"></script>

    <widget:preferences>
        <preference name="my_text" type="text" label="My text pref" defaultValue="" />
        <preference name="my_password" type="password" label="My password pref" defaultValue="" />
        <preference name="my_checkbox" type="boolean" label="My checkbox pref" defaultValue="" />
        <preference name="my_hidden" type="hidden" label="" defaultValue="" />
        <preference name="my_range" type="range" label="My range pref" defaultValue="10" step="5" min="5" max="15" />
    </widget:preferences>

    <style type="text/css">

        div#moduleContent {
              background: #FFF url(<?php echo MAIN_URL; ?>/samples/back.gif) repeat-x ;
        }

        div#moduleContent ul.export-list li img {
            vertical-align:middle;
        }
    </style>

    <script type="text/javascript">

        var TabViewSample = {};

        widget.onLoad = function() {

          // update widget elements
          widget.setTitle('Test Title update');
          widget.setUnreadCount(1);
          widget.setSearchResultCount(2);

          // init widget tabs system
          if (typeof(widget.tabs) == "undefined") {

              widget.tabs = new UWA.Controls.TabView();

              // Create tab items
              widget.tabs.addTab('tab1', {text: "UWA Links", customInfo: "custom"});
              widget.tabs.addTab('tab2', {text: "Grid Data"});
              widget.tabs.addTab('tab3', {text: "E-Mail List"});
              widget.tabs.addTab('tab4', {text: "Rich list "});
              widget.tabs.addTab('tab5', {text: "Thumbs list"});
              widget.tabs.addTab('tab6', {text: "Json"});
              widget.tabs.addTab('tab7', {text: "Ajax"});
              widget.tabs.addTab('tab8', {text: "Prefs"});
              widget.tabs.addTab('tab9', {text: "Export Widget"});

              // Put some content in tabs
              widget.tabs.setContent('tab1', $('hello-content').innerHTML);
              widget.tabs.setContent('tab2', $('griddata-content').innerHTML);
              widget.tabs.setContent('tab3', $('emaillist-content').innerHTML);
              widget.tabs.setContent('tab4', $('richlist-content').innerHTML);
              widget.tabs.setContent('tab5', $('thumbist-content').innerHTML);
              widget.tabs.setContent('tab6', $('json-content').innerHTML);
              widget.tabs.setContent('tab7', $('ajax-content').innerHTML);
              widget.tabs.setContent('tab8', $('prefs-content').innerHTML);
              widget.tabs.setContent('tab9', $('export-content').innerHTML);

              // Handle Some special table
              widget.tabs.onActiveTabChanged = function(name, data) {

                  // Save active tab
                  widget.setValue("activeTab", name);

                  if (name == 'tab6') {

                      var onCompleteJson = function(json) {
                        widget.tabs.setContent(name, 'json date is:' + json.date);
                      }

                      UWA.Data.request('<?php echo MAIN_URL; ?>/samples/index.php?json=true', {
                        method: 'get',
                        type: 'json',
                        proxy: 'ajax',
                        onComplete: onCompleteJson.bind(this)
                      });

                  } else if (name == 'tab7') {

                      var onCompleteAjax = function(html) {
                        widget.tabs.setContent(name, html);
                      }

                      UWA.Data.request('<?php echo MAIN_URL; ?>/samples/index.php?ajax=true', {
                        method: 'get',
                        type: 'html',
                        proxy: 'ajax',
                        onComplete: onCompleteAjax.bind(this)
                      });

                  } else if (name == 'tab8') {

                      var html = '<h1>Preference values</h1>'
                      + '<ul style="padding: 5px;">'
                      + '<li>my_text: ' + widget.getValue('my_text') + "</li>"
                      + '<li>my_text: ' + widget.getValue('pass') + "</li>"
                      + '<li>my_checkbox: ' + widget.getValue('my_checkbox') + "</li>"
                      + '<li>my_range: ' + widget.getValue('my_range') + "</li>"
                      + '</ul>'

                      widget.tabs.setContent(name, html);
                  }
                }

              // Register to activeTabChange event
              widget.tabs.observe('activeTabChange', widget.tabs.onActiveTabChanged);

          }

          // Restore saved active tab or set default
          var activeTab = widget.getValue('activeTab') || 'tab1';
          widget.tabs.selectTab(activeTab);

          // Add tabs to widget body
          widget.setBody(widget.tabs.tabSet);
        }

        // Handle refresh to update tab content
        widget.onRefresh = function() {
            widget.tabs.onActiveTabChanged(widget.getValue('activeTab'))
        }

    </script>
    </head>

    <body>
        <div style="display:none">
            <div id="hello-content" class="tab-content">
                <ul>
                    <li><a href="http://netvibes.org/">UWA Website</a></li>
                    <li><a href="http://dev.netvibes.com/">Developer UWA Website</a></li>
                    <li><a href="http://netvibes.org/specs/uwa/current-work/">specs Universal Widget API (UWA) 1.2 </a></li>

                </ul>
            </div>

            <div id="griddata-content" class="tab-content">

                <table class="nv-datagrid">
                    <thead>
                        <tr>
                            <th>col 0</th>

                            <th>col 1</th>
                            <th>column 2</th>
                        </tr>
                    </thead>

                    <tfoot>
                        <tr>
                            <td>footer 1</td>

                            <td>footer 2</td>
                            <td>footer 3</td>
                        </tr>
                    </tfoot>

                    <tbody>
                        <?php for ($i = 1; $i <= 15; $i++): ?>
                            <tr <?php echo ($i % 2 == false ? 'class="even"' : '') ?>>
                                <td><?php echo $i; ?></td>
                                <td>line <?php echo $i; ?></td>
                                <td>line number <?php echo $i; ?></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>

            </div>

            <div id="emaillist-content" class="tab-content">

                <dl class="nv-emailList">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <dt class="read<?php echo ($i % 2 == false ? ' even' : '') ?>">
                            <a href="#" onclick="return false" title="Read e-mail">
                            <span class="sender">Sender</span> - My e-mail subject #3</a>
                        </dt>

                        <dd>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Phasellus condimentum.</p>
                        </dd>
                    <?php endfor; ?>
                </dl>

            </div>


            <div id="richlist-content">

                <div class="nv-richList">

                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <div class="item<?php echo ($i % 2 == false ? ' even' : '') ?>">
                        <h3><a href="#">Item #1</a></h3>
                        <p>
                            Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                            Phasellus condimentum.
                        </p>
                    </div>
                    <?php endfor; ?>
                </div>

            </div>

            <div id="thumbist-content">

                <div class="nv-thumbnailedList">

                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <div class="item<?php echo ($i % 2 == false ? ' even' : '') ?>">
                        <a href="#"><img src="<?php echo MAIN_URL; ?>/img/uwa-screenshot.png" alt="" class="thumbnail" /></a>

                        <h3><a href="#">Item #1</a></h3>
                        <p>
                        Lorem ipsum dolor sit amet,
                            consectetuer adipiscing elit. Phasellus
                        </p>
                    </div>
                    <?php endfor; ?>
                </div>

            </div>

            <div id="json-content">
                Loading...
            </div>

            <div id="ajax-content">
                Loading...
            </div>

            <div id="prefs-content">
                Loading...
            </div>

            <div id="export-content">

                <h2>Stable - Ready to use</h2>

                <ul class="export-list">

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/netvibes.gif" alt="Netvibes" />
                        <a target="_blank" href="http://www.netvibes.com/subscribe.php?module=UWA&amp;moduleUrl=<?php echo urlencode(WIDGET_URL); ?>">Netvibes</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/google.gif" alt="iGoogle" />
                        <a target="_blank" href="http://www.google.com/ig/add?moduleurl=<?php echo urlencode(MAIN_URL); ?>%2Fwidget%2Fgspec%3FuwaUrl%3D<?php echo urlencode(urlencode(WIDGET_URL)); ?>">iGoogle</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/apple.gif" alt="Dashboard" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/dashboard?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Dashboard</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/blogger.gif" alt="Blogger" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/blogger?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Blogger</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/microsoft.gif" alt="Windows Vista/Seven" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/vista?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Windows Vista/Seven</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/msn.gif" alt="Live" />
                        <a target="_blank" href="http://my.live.com/?s=1&amp;add=<?php echo urlencode(MAIN_URL); ?>%2Fwidget%2Flive%3F<?php echo urlencode(urlencode(WIDGET_URL)); ?>">Live</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/wordpress.gif" alt="Iframe" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/frame?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>&amp;header=1&amp;chromeColor=orange">Iframe</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/uwa.gif" alt="Uwa" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/uwa?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">UWA</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/other.gif" alt="Screenlets" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/screenlets?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Screenlets</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/other.gif" alt="Opera" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/opera?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Opera</a>
                    </li>

                </ul>

                <h2>Beta - Need testing and improvements</h2>
                <ul class="export-list">
                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/chrome.gif" alt="Google Chrome" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/chrome?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Google Chrome</a>
                    </li>
                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/apple.gif" alt="Iphone" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/iphone?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Iphone</a>
                    </li>
                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/other.gif" alt="Adobe Air" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/air?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Adobe Air</a>
                    </li>
                </ul>

                <h2>Alpha - Implementation draft</h2>
                <ul class="export-list">
                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/other.gif" alt="Firefox/Thunderbird" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/firefox?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Firefox/Thunderbird</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/other.gif" alt="Prism" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/prism?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Prism</a>
                    </li>

                    <li>
                        <img src="<?php echo MAIN_URL; ?>/img/icons/other.gif" alt="Jil / Ophone" />
                        <a target="_blank" href="<?php echo MAIN_URL; ?>/widget/jil?uwaUrl=<?php echo urlencode(WIDGET_URL); ?>">Jil/Ophone</a>
                    </li>
                </ul>
            </div>
        </div>
        <p>Loading...</p>
    </body>
</html>

