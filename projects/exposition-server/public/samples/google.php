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
<title>Google</title>
<meta name="description" content="" />
<meta name="keywords" content="" />
<meta name="email" content="" />
<meta name="author" content="" />

<meta name="apiVersion" content="1.0" />
<meta name="inline" content="true" />
<meta name="autoRefresh" content="20" />
<meta name="debugMode" content="false" />

<link rel="icon" href="http://cdn.netvibes.com/img/rss.gif" type="image/x-icon" />

    <link rel="stylesheet" type="text/css"
      href="<?php echo MAIN_URL; ?>/css/uwa-standalone.css" />

    <script type="text/javascript"
      src="<?php echo MAIN_URL; ?>/js/c/UWA_Standalone.js?v=preview3"></script>

<widget:preferences>
    <preference name="category" type="hidden" label="Category" />
    <preference name="view" type="list" label="View" defaultValue="">
        <option value="" label="Normal" />
        <option value="Magazine" label="Magazine" />
        <option value="Carousel" label="Slideshow" />
        <option value="Ticker" label="Ticker" />

        <option value="GalleryTwo" label="Classy Slideshow" />
        <option value="Gallery" label="Classy Slideshow (Black)" />
        <option value="Scoop" label="Headline" />
        <option value="Headline" label="Quick Details" />
    </preference>
    <preference name="nbTitles" type="range" label="Number of items to display" defaultValue="3" step="1" min="1" max="25" onchange="updateDisplay" />
    <preference name="details" type="boolean" label="Show more details" defaultValue="true" onchange="updateDisplay" />
    <preference name="showDate" type="boolean" label="Show post date" defaultValue="true" onchange="updateDisplay" />
    <preference name="openOutside" type="boolean" label="Open directly on the site" defaultValue="false" onchange="updateDisplay" />

    <preference name="videoAutoPlay" type="hidden" label="Show the video at startup" defaultValue="false" />
    <preference name="numberTabs" type="hidden" defaultValue="4" />
    <preference name="selectedTab" type="hidden" defaultValue="0" />
    <preference name="title" type="hidden" defaultValue="MultipleFeeds" />
    <preference name="lookForHtmlThumbnail" type="hidden" defaultValue="true" />
    <preference name="provider" type="hidden" defaultValue="google" />
    <preference name="url" type="hidden" defaultValue="" />
    <preference name="lastSearch" type="hidden" defaultValue="__undefined__" />
    <preference name="showTweet" type="boolean" label="Show latest tweet" defaultValue="true" onchange="updateTweet" />

    <preference name="setNbTitles" type="hidden" defaultValue="true" />
</widget:preferences>

<script type="text/javascript">

      var UWA_WIDGET = '<?php echo BASE_URL; ?>/widget',
        UWA_JS = '<?php echo MAIN_URL; ?>/js',
        UWA_CSS = '<?php echo MAIN_URL; ?>/css',
        UWA_PROXY = '<?php echo MAIN_URL; ?>/proxy',
        UWA_STATIC = '<?php echo MAIN_URL; ?>/img';

/*
** MultipleFeeds:
** MultipleFeeds core functions
*/
var MultipleFeeds = {}
widget.getMultipleFeeds = function() {
    return MultipleFeeds;
}

MultipleFeeds.path = "http://"+NV_HOST+"/modules/multipleFeeds/";
MultipleFeeds.offset = 0;
MultipleFeeds.search = "";
MultipleFeeds.highlight = "";
MultipleFeeds.firstLaunch = true;
MultipleFeeds.feeds = [];
MultipleFeeds.histories = [];
MultipleFeeds.lastRequest = false; // contains the last feed request object, so we can cancel it to prevent parallel requests
MultipleFeeds.categoryNames = [_("Category:"), _("Show:"), _("Local content:"), _("Country:")]; // Needed to make category names translatable
MultipleFeeds.autoRefreshState = true;

// Used when a different proxy is specified for each feed in default.js e.g. latimes_entertainment
// Used by widget.getFeed when it is called with no parameters on refresh
MultipleFeeds.currentProxy = null;

MultipleFeeds.getProvider = function() {
    var provider = widget.getValue("provider");
    var category = widget.getValue("category");
    if (provider == 'custom') var url = widget.getValue("url");

    if (widget.readOnly == true &&
        typeof MultipleFeeds.category != "undefined") {
            category = MultipleFeeds.category;
    }

    // Prefetch handling
    if (url) {
        var key = encodeURIComponent(url);
    } else {
        var key = provider + '-' + category;
    }
    if(UWA.Multifeeds && UWA.Multifeeds[key]) {
        var response = UWA.Multifeeds[key];
        MultipleFeeds.buildModule(response);
        // delete prefetchedFeed 15 seconds after using it
        setTimeout(function() { UWA.Multifeeds[key] = null }, 15000);
        return;
    }

    var query = MultipleFeeds.path+"providers/?p="+provider+"&cat="+category;

    if (url) query += "&url="+url;

    UWA.Data.getJson(query, MultipleFeeds.buildModule);
}

MultipleFeeds.buildModule = function(data) {
    MultipleFeeds.config = data.config;
    MultipleFeeds.data = data.feeds.slice(0);

    // Force saving the default category
    if (typeof data.forceCategory != "undefined") {
        widget.setValue("category", data.forceCategory);
    }

    // Reorder feeds by id and save the default order
    var data = [];
    MultipleFeeds.defaultOrder = [];
    for (var i = 0; i < MultipleFeeds.data.length; i++) {
        MultipleFeeds.defaultOrder[i] = MultipleFeeds.data[i]['id'];
        data[MultipleFeeds.data[i]['id']] = MultipleFeeds.data[i];
    }
    MultipleFeeds.data = data;

    var body = widget.body;

    // Translate Categories
    if (MultipleFeeds.config.categoryName) {
        MultipleFeeds.config.categoryName = _(MultipleFeeds.config.categoryName);
    }
    if (MultipleFeeds.config.categoryCodes) {
        for (var i = 0; i < MultipleFeeds.config.categoryCodes.length; i++) {
            MultipleFeeds.config.categoryLabels[MultipleFeeds.config.categoryCodes[i]] =
                _(MultipleFeeds.config.categoryLabels[MultipleFeeds.config.categoryCodes[i]]);
        }
    }

    // Sort Categories
    if (MultipleFeeds.config.categoryCodes) {
        MultipleFeeds.config.categoryCodes.sort(function(a, b) {
            var c = MultipleFeeds.config.categoryLabels[a].toLowerCase();
            var d = MultipleFeeds.config.categoryLabels[b].toLowerCase();
            return (c < d ? -1 : 1);
        });
    }

    // Set the Category name
    if (typeof MultipleFeeds.config.categoryName != "undefined") {
        widget.preferences[0].label = MultipleFeeds.config.categoryName;
    }

    // Insert Categories in the preferences
    var provider = widget.getValue("provider");
    var categoryCodes = MultipleFeeds.config.categoryCodes || ["default"];
    if (categoryCodes.length == 1) {
        widget.preferences[0].type = "hidden";
    } else if (typeof widget.preferences[0].options == "undefined" ||
               widget.preferences[0].options.length == 0) {
        widget.preferences[0].options = [];
        for (var i = 0; i < categoryCodes.length; i++) {
            widget.preferences[0].options.push({"value":categoryCodes[i], "label":MultipleFeeds.config.categoryLabels[categoryCodes[i]]});
        }
    }

    // Set / Hide preferences according to the config
    if (typeof MultipleFeeds.config.nbTitles != "undefined") {
        // we only want to set the nbTitles preference to the value from the config once
        // and never again so we don't override the user's choice
        if (widget.getBool('setNbTitles') == true) {
            widget.setValue('nbTitles', parseInt(MultipleFeeds.config.nbTitles,10));
            widget.setValue('setNbTitles', false);
        }
    }
    if (typeof MultipleFeeds.config.details != "undefined") {
        widget.preferences[3].type = "hidden";
        widget.preferences[3].value = MultipleFeeds.config.details;
    }
    if (typeof MultipleFeeds.config.showDate != "undefined") {
        widget.preferences[4].type = "hidden"
        widget.preferences[4].value = MultipleFeeds.config.showDate;
    }
    if (typeof MultipleFeeds.config.openOutside != "undefined") {
        widget.preferences[5].type = "hidden";
        widget.preferences[5].value = MultipleFeeds.config.openOutside;
    }
    if (typeof MultipleFeeds.config.twitterID == "undefined") {
        widget.preferences[14].type = "hidden";
        widget.preferences[14].value = false;
    }

    // Auto Play on eco
    if (widget.isOnEcosystem()) {
        MultipleFeeds.videoAutoPlay = true;
        widget.setValue("videoAutoPlay", true);
    }

    // Auto Play Video Preference
    if (MultipleFeeds.config.showAutoPlay == true /*&& widget.preferences[5].type == "hidden"*/) {
        widget.preferences[6].type = "boolean";
        MultipleFeeds.videoAutoPlay = true;
    }

    // Prenium Widget Header
    if (MultipleFeeds.config.displayHeader == true) {
        widget.body.style.padding = "0px";
        var header = document.createElement("div");
        header.style.margin = "0px";
        header.style.padding = "0px";
        header.style.width = "100%";
        if (typeof MultipleFeeds.config.headerBg != "undefined" && MultipleFeeds.config.headerBg != '') {
            header.style.background = "url("+MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/img/"+MultipleFeeds.config.headerBg+")";
            header.style.backgroundRepeat = "repeat-x";
        }
        if (typeof MultipleFeeds.config.headerBgColor != "undefined") {
            header.style.backgroundColor = MultipleFeeds.config.headerBgColor;
        }
        if (typeof MultipleFeeds.config.headerRight != "undefined" && MultipleFeeds.config.headerRight != '') {
            var headerRight = document.createElement("img");
            headerRight.src = MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/img/"+MultipleFeeds.config.headerRight;
            headerRight.style.cssFloat = headerRight.style.styleFloat = "right";

            if (MultipleFeeds.config.sponsorUrl) {
                headerRight.style.cursor = 'pointer';
                headerRight.onclick = function() {
                    if (widget && widget.openURL) widget.openURL(MultipleFeeds.config.sponsorUrl);
                }
            }

            header.appendChild(headerRight);
        }
        if (typeof MultipleFeeds.config.headerLeft != "undefined" && MultipleFeeds.config.headerLeft != '') {
            var headerLeft = document.createElement("img");
            headerLeft.src = MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/img/"+MultipleFeeds.config.headerLeft;

            if (MultipleFeeds.config.siteUrl) {
            headerLeft.style.cursor = 'pointer';
            headerLeft.onclick = function() {
                if (widget && widget.openURL) widget.openURL(MultipleFeeds.config.siteUrl);
            }
        }
            header.appendChild(headerLeft);
        }
        if (typeof MultipleFeeds.config.headerMiddle != "undefined" && MultipleFeeds.config.headerMiddle != '') {
            var divCenter = document.createElement("div");
            divCenter.style.textAlign = "center";
            divCenter.style.margin = "0px";
            divCenter.style.padding = "0px";
            var headerMiddle = document.createElement("img");

            // //For custom
            var provider = widget.getValue("provider");
            if (provider == 'custom') {
                headerMiddle.src = widget.getValue("url") + '/' +  MultipleFeeds.config.headerMiddle;
            } else {
                headerMiddle.src = MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/img/"+MultipleFeeds.config.headerMiddle;
            }

            if (MultipleFeeds.config.siteUrl) {
            headerMiddle.style.cursor = 'pointer';
            headerMiddle.onclick = function() {
                if (widget && widget.openURL) widget.openURL(MultipleFeeds.config.siteUrl);
            }
        }
            divCenter.appendChild(headerMiddle);
            header.appendChild(divCenter);
        }
        widget.setBody(header);
        body = header;
    } else {
        body.innerHTML = "";
    }

    // Category Selector
    if (MultipleFeeds.config.hasCategorySelector) {
        body.appendChild(MultipleFeeds.createCategorySelector());
    }

    // Video Player
    if (MultipleFeeds.config.hasVideoPlayer == true) {
        MultipleFeeds.playerDiv = widget.createElement("div");
        body.appendChild(MultipleFeeds.playerDiv);
        var playerDim = Element.getDimensions(MultipleFeeds.playerDiv);
        var aspectRatio = MultipleFeeds.config.videoAspectRatio || 1;
        var flashOptions = { width: playerDim.width, height: playerDim.width / aspectRatio,
                             wmode: MultipleFeeds.config.flashNotOpaque ? "" : "opaque",
                             autoResize: true, showFullScreenLink: false };
        if (widget.isInNativeMode()) {
            widget.environment.obj.dataObj.fixId = Math.random()*100000;
            flashOptions.fixModuleId = widget.environment.obj.dataObj.fixId;
        }
        MultipleFeeds.flashPlayer = new UWA.Controls.FlashPlayer(MultipleFeeds.playerDiv, flashOptions);
    }

    // Audio Player
    if (MultipleFeeds.config.hasAudioPlayer == true && AudioPlayer && AudioPlayer.init) {
        MultipleFeeds.audioPlayerDiv = widget.createElement("div");
        MultipleFeeds.audioPlayerDiv.id = 'playermp3Container';
        body.appendChild(MultipleFeeds.audioPlayerDiv);
        AudioPlayer.init();
    }

    MultipleFeeds.miniTabs = new UWA.Controls.TabView({ dataKey: "id", autohideDropdowns: true, softPadding: true });
    MultipleFeeds.length = MultipleFeeds.data.length > widget.getInt("numberTabs") ? widget.getInt("numberTabs") : MultipleFeeds.data.length;
    MultipleFeeds.list = widget.getList(MultipleFeeds.length);

    // Pre selected tab
    var selectedTab = widget.getValue("selectedTab");
    var preSelectedTab = widget.getValue("preSelectedTab");
    if (typeof preSelectedTab != "undefined" && preSelectedTab != null && preSelectedTab != "") {
        widget.setValue("preSelectedTab", "");
        var dataElem = MultipleFeeds.data.detect(function(el) { return el.text.toLowerCase() == preSelectedTab.toLowerCase(); });
        if (typeof dataElem != "undefined") {
            for (var i = 0; typeof MultipleFeeds.list[i] != "undefined"; i++) {
                if (MultipleFeeds.list[i].toString() == dataElem.id.toString()) {
                    var listIndex = i.toString();
                    break ;
                }
            }
            if (typeof listIndex == "undefined") {
                MultipleFeeds.list.pop(); // We remove the last element of the array
                MultipleFeeds.list.splice(0, 0, dataElem.id.toString()); // And we insert the selected tab as the begining of the array
                selectedTab = "0";
            } else {
                selectedTab = listIndex;
            }
        } else {
            selectedTab = "0";
        }
    }

    if (MultipleFeeds.length == 1) {
        MultipleFeeds.miniTabs.addTab(0, MultipleFeeds.data[0]);
        MultipleFeeds.list = [0];
    } else {
        // Adding tabs with dropdowns
        //   MultipleFeeds.length [int]         => tab number preference
        //   MultipleFeeds.list [array]         => tab order preferences
        //   MultipleFeeds.defaultOrder [array] => the tab order configuration of the widget
        //   MultipleFeeds.data [array]         => data[feedid] is the data for the according feed
        var tabsAdded = 0;
        // Round #1, adding items based on the stored order in MultipleFeeds.list
        for (var i = 0; i < MultipleFeeds.list.length && i < MultipleFeeds.defaultOrder.length; i++) {
            var index = MultipleFeeds.list[i];
            // if the index is defined (i'm not sure when it can happen, but happened somehow before)
            // if the feed item is not exists anymore (a recent config change removed it)
            if (typeof index != "undefined" && index !== "" && typeof MultipleFeeds.data[index] != "undefined") {
                var dd = [];
                // Adding the visible tab item
                dd.push(MultipleFeeds.data[index]);
                // Preventing adding the same tabs twice
                MultipleFeeds.data[index].added = true;
                // Adding the dropdown items: all the other items those defined
                for (var j = 0; j < MultipleFeeds.data.length; j++) {
                    if (j != index && typeof MultipleFeeds.data[j] != "undefined") {
                        dd.push(MultipleFeeds.data[j]);
                    }
                }
                // Add the tab to the control
                MultipleFeeds.miniTabs.addTab(tabsAdded++, dd);
            }
        }
        // Round #2, adding items, if there are more tabs (in case user have changed the tab no from 3 to 4,
        // or some of the user chosen feed items have been removed)
        for (var i = 0; tabsAdded < MultipleFeeds.length && i < MultipleFeeds.defaultOrder.length; i++) {
            // Search items those are defined, but not yet added
            for (var j = 0; j < MultipleFeeds.data.length; j++) {
                if (typeof MultipleFeeds.data[j] != "undefined" && typeof MultipleFeeds.data[j].added == "undefined") {
                    var index = MultipleFeeds.data[j].id;
                    // Prepare the dropdown list with some array manipulation
                    var dd = [];
                    // Adding the dropdown items: all the other items those defined
                    for (var k = 0; k < MultipleFeeds.data.length; k++) {
                        if (k != index && typeof MultipleFeeds.data[k] != "undefined") {
                            dd.push(MultipleFeeds.data[k]);
                        }
                    }
                    // Add the index item as the first one
                    dd.unshift(MultipleFeeds.data[index]);
                    // Preventing adding the same tabs twice
                    MultipleFeeds.data[index].added = true;
                    // Add the tab to the control
                    MultipleFeeds.miniTabs.addTab(tabsAdded++, dd);
                    // Add the item to the users' preference list
                    MultipleFeeds.list.push(index);
                    break;
                }
            }
        }
    }
    widget.setList(MultipleFeeds.list);


    // Report 'module.view' on tab change (click only)
    if (widget.isInNativeMode() && MultipleFeeds.miniTabs.tabList.childNodes) {
        for (var i=0, tab; tab = MultipleFeeds.miniTabs.tabList.childNodes[i++];) {
            var sender = $(tab).getElement('span');
            if (sender) {
                sender.addEvent('click', function() {
                    if (!$(this).hasClass('selected')) widget.environment.obj.reportView();
                }.bind(tab));
            }
        }
    }

    // Tab Control Events
    MultipleFeeds.miniTabs.observe("activeTabChange", MultipleFeeds.onActiveTabChanged);
    if (selectedTab > MultipleFeeds.length) selectedTab = 0;
    MultipleFeeds.miniTabs.selectTab(selectedTab.toString());
    if (MultipleFeeds.config.hideTabList) MultipleFeeds.miniTabs.hideTabList();
    MultipleFeeds.miniTabs.appendTo(body);

    // Advertising
    if (MultipleFeeds.config.ad) {
        MultipleFeeds.ad = widget.createElement("div");
        MultipleFeeds.ad.setStyles({'width': '100%', 'height':'60px', 'overflow':'hidden', 'padding': '3px 0', 'text-align':'center'});
        body.appendChild(MultipleFeeds.ad);
        var date = new Date();
        var day = ""+date.getMonth()+date.getDate()+date.getYear();
        var swfobject = new deconcept.SWFObject(
            "http://images.widgetbucks.com/widgets/ippc234x60.swf?uid=" + MultipleFeeds.config.ad.id + "&apiURL=http://api.widgetbucks.com&day=" + day,
            MultipleFeeds.config.ad.id,
            "234",
            "100%"
        );
        swfobject.write(MultipleFeeds.ad);
    }

    MultipleFeeds.buildTweet();

}

MultipleFeeds.createCategorySelector = function() {
    var category = widget.getValue("category");
    if (widget.readOnly == true &&
        typeof MultipleFeeds.category != "undefined") {
            category = MultipleFeeds.category;
    }
    var plug = document.createElement("div");
    plug.style.margin = "0px";
    plug.style.padding = "0px";
    plug.style.border = "1px solid #CCC";
    plug.style.height = "48px";

    if (typeof MultipleFeeds.config.categoryBg != "undefined") {
        plug.style.background = "url("+MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/img/"+MultipleFeeds.config.categoryBg+")";
        plug.style.backgroundRepeat = "repeat-x";
    }

    if (typeof MultipleFeeds.config.categoryBgColor != "undefined") {
        plug.style.backgroundColor = MultipleFeeds.config.categoryBgColor;
    }

    if (typeof MultipleFeeds.config.categoryRight != "undefined") {
        var arrowRight = document.createElement("img");
        arrowRight.style.cssFloat = arrowRight.style.styleFloat = "right";
        arrowRight.style.cursor = "pointer";
        arrowRight.src = MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/img/"+MultipleFeeds.config.categoryRight;
        arrowRight.onclick = function() {
            var categoryCodes = MultipleFeeds.config.categoryCodes || ["default"];
            var category = widget.getValue("category");
            if (widget.readOnly == true &&
                typeof MultipleFeeds.category != "undefined") {
                    category = MultipleFeeds.category;
            }
            for (var i = 0; i < categoryCodes.length; i++) {
                if (categoryCodes[i] == category) {
                    i++;
                    if (typeof categoryCodes[i] == "undefined") i = 0;
                    if (widget.readOnly == true) {
                        MultipleFeeds.category = categoryCodes[i];
                    } else {
                        widget.setValue("category", categoryCodes[i]);
                    }
                    widget.onChangeCategory();
                    break ;
                }
            }
        }
        arrowRight.title = _("Next {0}").format(MultipleFeeds.config.categoryName || _("Category"));
        plug.appendChild(arrowRight);
    }

    var categoryPicture = document.createElement("img");
    categoryPicture.style.cssFloat = categoryPicture.style.styleFloat = "right";
    categoryPicture.style.width = "130px";
    categoryPicture.style.height = "48px";
    categoryPicture.src = MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/"+MultipleFeeds.config.categoryPictures[category];
    categoryPicture.onerror = function() { this.src = MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/"+MultipleFeeds.config.defaultPicture; };
    plug.appendChild(categoryPicture);

    if (typeof MultipleFeeds.config.categoryLeft != "undefined") {
        var arrowLeft = document.createElement("img");
        arrowLeft.style.cssFloat = arrowLeft.style.styleFloat = "right";
        arrowLeft.style.cursor = "pointer";
        arrowLeft.src = MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/img/"+MultipleFeeds.config.categoryLeft;
        arrowLeft.onclick = function() {
            var categoryCodes = MultipleFeeds.config.categoryCodes || ["default"];
            var category = widget.getValue("category");
            if (widget.readOnly == true &&
                typeof MultipleFeeds.category != "undefined") {
                    category = MultipleFeeds.category;
            }
            for (var i = 0; i < categoryCodes.length; i++) {
                if (categoryCodes[i] == category) {
                    i--;
                    if (typeof categoryCodes[i] == "undefined") i = categoryCodes.length - 1;
                    if (widget.readOnly == true) {
                        MultipleFeeds.category = categoryCodes[i];
                    } else {
                        widget.setValue("category", categoryCodes[i]);
                    }
                    widget.onChangeCategory();
                    break ;
                }
            }
        }
        arrowLeft.title = _("Previous {0}").format(MultipleFeeds.config.categoryName || _("Category"));
        plug.appendChild(arrowLeft);
    }

    if (typeof MultipleFeeds.config.categoryLogo != "undefined") {
        var logo = document.createElement("img");
        logo.src = MultipleFeeds.path+"providers/"+widget.getValue("provider")+"/img/"+MultipleFeeds.config.categoryLogo;
    if (MultipleFeeds.config.siteUrl) {
        logo.style.cursor = 'pointer';
        logo.onclick = function() {
        if (widget && widget.openURL) widget.openURL(MultipleFeeds.config.siteUrl);
        }
    }
        plug.appendChild(logo);
    }

    return plug;
}

// this should be called on Tab Change and on widget init
MultipleFeeds.displayTab = function() {

    // Get the tab's content div
    var content = MultipleFeeds.miniTabs.getTabContent(widget.getValue("selectedTab").toString());
    content.innerHTML = '';

    var data = MultipleFeeds.data[MultipleFeeds.currentId];
    var content = $(MultipleFeeds.miniTabs.getTabContent(widget.getValue("selectedTab").toString()));
    var searchDiv = widget.createElement("div"); widget.searchDiv = searchDiv; content.appendChild(searchDiv);
    var feedDiv = widget.createElement("div"); widget.feedDiv = feedDiv; content.appendChild(feedDiv);

    // If a searchURL is defined
    if (data.searchUrl) {
        var query = decodeURIComponent(widget.getValue("lastSearch"));

        if (query == '__undefined__') {
            if (data.searchValue) {
                query = decodeURIComponent(data.searchValue);
            } else {
                query = '';
            }
        }
        MultipleFeeds.searchForm = new UWA.Controls.SearchForm();
        MultipleFeeds.searchForm.setInitialState('/img/s.gif', query, false);
        MultipleFeeds.searchForm.observe('submit', function(searchText, checked) { widget.doSearch(data.searchUrl, searchText, data.proxy); });
        MultipleFeeds.searchForm.observe('reset', function(searchText, checked) { widget.doSearch(data.searchUrl, '', data.proxy); });
        MultipleFeeds.searchForm.appendTo(searchDiv);
        MultipleFeeds.highlight = query;

        widget.doSearch(data.searchUrl, query, data.proxy);
    }

    // If a feed url is defined
    if (data.url) {

        MultipleFeeds.highlight = '';

        MultipleFeeds.currentProxy = typeof data.proxy != "undefined" ? data.proxy : null;

        // If the feed is not in our cache, let's get it!
        if (typeof MultipleFeeds.feeds[MultipleFeeds.currentId] == "undefined") {
            MultipleFeeds.lastRequestId = MultipleFeeds.currentId;
            widget.getFeed(data.url, data.proxy); // it will call displayFeed
        } else {
            widget.currentFeed = data.url;
            MultipleFeeds.displayFeed();
        }

    }

}

// Displays a feed reader on an initialized tab
MultipleFeeds.displayFeed = function(feed) {

    // Get the current feed
    if (typeof feed != "undefined") {
        // RtL
        if (typeof feed.dir != "undefined" && feed.dir == "rtl") {
            widget.dir = "rtl";
            widget.body.dir = "rtl";
            Element.addClassName(widget.body, "rtl");
            Element.removeClassName(widget.body, "ltr");
            if (widget.elements['title']) {
                Element.addClassName(widget.elements['title'], "rtl");
                Element.removeClassName(widget.elements['title'], "ltr");
            }
        } else {
            widget.dir = "ltr";
            widget.body.dir = "ltr";
            Element.addClassName(widget.body, "ltr");
            Element.removeClassName(widget.body, "rtl");
            if (widget.elements['title']) {
                Element.addClassName(widget.elements['title'], "ltr");
                Element.removeClassName(widget.elements['title'], "rtl");
            }
        }
    } else {
        feed = MultipleFeeds.feeds[MultipleFeeds.currentId];
    }

    // Set the widget icon and title
    var data = MultipleFeeds.data[MultipleFeeds.currentId];
    widget.setIcon(feed.htmlUrl);
    var title = feed.title;
    var category = widget.getValue("category");
    if (widget.readOnly == true &&
        typeof MultipleFeeds.category != "undefined") {
            category = MultipleFeeds.category;
    }
    if (MultipleFeeds.config.useCategoriesAsTitle == true) title = MultipleFeeds.config.categoryLabels[category];
    var url = MultipleFeeds.path + 'redirect.php?url=' + encodeURIComponent(MultipleFeeds.config.moreUrl || feed.htmlUrl) +
              '&provider=' + encodeURIComponent(MultipleFeeds.getIdentifier()) + '&id=' + encodeURIComponent(widget.id);
    if (widget.isInNativeMode()) url += '&campaignId=' + encodeURIComponent(widget.environment.obj.dataObj.campaignId);
    if (widget.environment.vista) {
        widget.setTitle(title);
    } else {
        widget.setTitle('<a href="'+url+'" target="_blank">'+title+'</a>');
    }

    // Handle errors
    if (!feed || feed.error) {
        MultipleFeeds.showError(_("Error"), _("Looks like this feed is not valid or currently not responding.")); return;
    } else
    if (feed.status == 401) {
        MultipleFeeds.showError(_("Unauthorized"), _("You don't have the required authorization to access this feed.")); return;
    } else
    if (!feed.items) {
        if (MultipleFeeds.highlight != ""  || data.searchUrl) {
            MultipleFeeds.showError(feed.title, _("No result for this search.")); return;
        } else {
            MultipleFeeds.showError(_("Error"), _("Looks like this feed is not valid or currently not responding.")); return;
        }
    } else
    if (feed.items.length == 0) {
        MultipleFeeds.showError(feed.title, _("No items in feed.")); return;
    }

    var content = widget.feedDiv;
    var lookForHtmlThumbnailOption = typeof data.lookForHtmlThumbnail == "undefined" ? (typeof MultipleFeeds.config.disableHtmlThumbnail == "undefined" || MultipleFeeds.config.disableHtmlThumbnail == false ? true : false) : data.lookForHtmlThumbnail;
    var showDateOption = typeof data.showDate == "undefined" ? (typeof MultipleFeeds.config.showDate == "undefined" ? widget.getBool("showDate") : MultipleFeeds.config.showDate) : data.showDate;
    var detailsOption = typeof data.details == "undefined" ? (typeof MultipleFeeds.config.details == "undefined" ? widget.getBool("details") : MultipleFeeds.config.details) : data.details;
    var feedSiteUrlDomain = typeof feed.htmlUrl == "undefined" ? "" : feed.htmlUrl;
    if (feedSiteUrlDomain) {
        var tmp = feedSiteUrlDomain.substring(0, feedSiteUrlDomain.indexOf("/", 7));
        if (tmp) feedSiteUrlDomain = tmp;
    }

    var constructor = 'FeedView';
    var view = widget.getValue('view');
    if (view && MultipleFeeds._forceDefaultView != true) {
        constructor += '_' + view;
        widget.preferences[3].type = 'hidden';
        widget.preferences[4].type = 'hidden';
        widget.preferences[6].type = 'hidden';
    } else {
        widget.preferences[3].type = 'boolean';
        widget.preferences[4].type = 'boolean';
        widget.preferences[6].type = 'boolean';
    }

    /*
     * hack: let's fake Exposition parser... TODO: better way to handle this...
     * UWA.Controls.FeedView
     * end hack
     */
    MultipleFeeds.ui = new UWA.Controls[constructor]({
        details: detailsOption,
        showDate: showDateOption,
        lookForHtmlThumbnail: lookForHtmlThumbnailOption,
        removeImagePattern: MultipleFeeds.config.removeImagePattern,
        feedSiteUrlDomain: feedSiteUrlDomain,
        search: MultipleFeeds.search,
        dir: widget.dir,
        allowVideoPlayButton: MultipleFeeds.config.hasVideoPlayer,
        dimensions: widget.body.getDimensions(),
        id: widget.id,
        displayShare: widget.isInNativeMode() && widget.readOnly != true
    });

    var tabContent = UWA.$element(MultipleFeeds.miniTabs.getTabContent(widget.getValue("selectedTab").toString()));
    if (MultipleFeeds.ui.fullSize == true) {
        tabContent.addClassName('fullsize-module');
        tabContent.style.padding = '0px';
    } else {
        tabContent.removeClassName('fullsize-module');
        tabContent.style.padding = '6px 3px 3px';
    }

    if (typeof MultipleFeeds.ui.colorize == 'function') {
        MultipleFeeds.ui.colorize('blank');
    }

    if (typeof MultipleFeeds.ui.setContainer == 'function') {
        MultipleFeeds.ui.setContainer(content);
    }

    if (MultipleFeeds.ui.needPager == false) {
        MultipleFeeds.limit = feed.items.length;
        MultipleFeeds.offset = 0;
        widget.preferences[2].type = 'hidden';
    } else {
        MultipleFeeds.limit = widget.getInt("nbTitles");
        /* We update the maximum value for the preference "nbTitles"
           according to the number of items */
        widget.preferences[2].max = feed.items.length;
        widget.preferences[2].type = 'range';
    }

    MultipleFeeds.ui.observe("onclick", MultipleFeeds.onClick);
    MultipleFeeds.ui.observe("onmiddleclick", MultipleFeeds.onMiddleOrRightClick);
    MultipleFeeds.ui.observe("onrightclick", MultipleFeeds.onMiddleOrRightClick);
    MultipleFeeds.ui.observe("onpodcastplay", MultipleFeeds.onPodcastPlay);
    MultipleFeeds.ui.observe("onvideoplay", MultipleFeeds.onVideoPlay);
    MultipleFeeds.ui.observe("ondownload", MultipleFeeds.onDownload);
    if (widget.isInNativeMode() && widget.readOnly != true) MultipleFeeds.ui.observe("onaddstar", MultipleFeeds.onAddStar);

    if (MultipleFeeds.search != "") {
        limit = feed.items.length;
    } else {
        /* Find the number of item(s) to displpay on this page */
        var limit = feed.items.length - MultipleFeeds.offset;
        limit = limit > MultipleFeeds.limit ? MultipleFeeds.limit : limit;
    }

    /*
     * hack: let's fake Exposition parser... TODO: better way to handle this...
     * UWA.Controls.FeedView
     * end hack
     */

    for (var i = 0; i < limit; i++) {
        var index = MultipleFeeds.offset + i;
        var item = feed.items[index];

        if (typeof item.enclosures == "object" && typeof item.video == "undefined") {
            item.enclosures.each(function(el) { if (el.type == 'application/x-shockwave-flash') { item.video = el.url; } });
        }

        if (item.video &&
            MultipleFeeds.config.hasVideoPlayer == true &&
            MultipleFeeds.videoAutoPlay == true &&
            widget.getBool("videoAutoPlay") &&
            constructor == 'FeedView') {
                MultipleFeeds.flashPlayer.show(item.video, item.flashvars+MultipleFeeds.getSharingLink(), {
                    mediaLinkUrl: item.link, mediaLinkText: _("See on {0}").format(MultipleFeeds.config.providerName)
                });
                MultipleFeeds.customizeVideoToolbar();
                MultipleFeeds.videoAutoPlay = false;
        }

        // item.title = String.highlight(item.title, MultipleFeeds.highlight); alert(item.title);
        // item.content = String.highlight(item.content, MultipleFeeds.highlight);
        MultipleFeeds.ui.addItem(index, item, UWA.Services.FeedHistory.isRead(feed, index));
    }

    if (typeof MultipleFeeds.ui.setContainer == 'function' && typeof MultipleFeeds.ui.finalize == 'function') {
        MultipleFeeds.ui.finalize();
    } else {
        //widget.setBody(FeedReader.FeedView.getContent());
        content.innerHTML = "";
        content.appendChild(MultipleFeeds.ui.getContent());
    }

    // Set the number of unread items
    if (MultipleFeeds.config.hideUnreadCount != true && widget.readOnly != true) {
        var nbUnread = UWA.Services.FeedHistory.getNbNew(feed, feed.items.length);
        widget.setUnreadCount(nbUnread);
    }

    if (MultipleFeeds.search != "") {
         widget.setSearchResultCount(MultipleFeeds.ui.getNumberOfDisplayedItems());
    }

    if (MultipleFeeds.search == "" && MultipleFeeds.ui.needPager == true) {
        var pager = new UWA.Controls.Pager({module: MultipleFeeds,
                                            limit: MultipleFeeds.limit,
                                            offset: MultipleFeeds.offset,
                                            dataArray: feed.items});
        pager.onChange = function(newOffset) {
            this.module.offset = newOffset;
            this.module.displayFeed();
        }
        MultipleFeeds.pagerContent = pager.getContent();
        content.appendChild(MultipleFeeds.pagerContent);
        if (!widget.isInNativeMode() || App.pageCustom.showFeedNav == "1") {
            MultipleFeeds.pagerContent.style.display = "block";
        }
    }

    if (MultipleFeeds.ui.needPager == true &&
        MultipleFeeds.offset + MultipleFeeds.limit >= feed.items.length &&
        (MultipleFeeds.config.moreUrl || feed.htmlUrl)) {
            var seemore = widget.createElement("div", {'class': 'seemore nv-pager', 'style': 'clear: none; text-align: right; padding: 0;'});
            if (MultipleFeeds.offset > 0) {
                seemore.setStyle('margin-top', '-1.3em');
            }
            var seemorelink = widget.createElement("a", {'class': 'next', 'target': '_blank', 'href': MultipleFeeds.config.moreUrl || feed.htmlUrl});
            seemorelink.onclick = function() {
                var url = MultipleFeeds.path + 'redirect.php?url=' + encodeURIComponent(this.href) + '&provider=' + encodeURIComponent(MultipleFeeds.getIdentifier()) + '&id=' + encodeURIComponent(widget.id);
                if (widget.isInNativeMode()) url += '&campaignId=' + encodeURIComponent(widget.environment.obj.dataObj.campaignId);
                widget.openURL(url);
                return false;
            }
            if (typeof feed.title != 'undefined') { seemorelink.title = feed.title; }
            seemorelink.setText(_("more on the site"));
            seemore.appendChild(seemorelink);
            content.appendChild(seemore);
    }

    widget.callback("onUpdateBody");

    // #5977: on dashboard, resize flashplayer after content is rendered in case it overlaps scrollbar
    if (widget.environment.dashboard && MultipleFeeds.flashPlayer) {
        MultipleFeeds.flashPlayer.resize();
    }
}

/* Method: buildTweet

checks if we should display a tweet, removes tweet if preference changed to false
called from MultipleFeeds.buildModule, widget.updateTweet, widget.onRefresh,

Parameters:
*None

Returns:
* Nothing.

*/
MultipleFeeds.buildTweet = function() {

    if (typeof MultipleFeeds.config.twitterID != 'undefined' && widget.getBool('showTweet') == true) {
        if (tweetDiv = widget.body.getElementsByClassName('tweet')[0]){
        } else {
            var tweetDiv = widget.createElement('div', {
                'class': 'tweet',
                // TODO: link to cdn
                'style': 'background: #d7fdfd url(/modules/multipleFeeds/img/twitter_favicon.png) no-repeat scroll 3px 5px;margin: 0 0 2px;padding: 5px 5px 5px 26px;font-size: 1.1em;overflow: hidden;position:relative;'
                });
            widget.body.insertBefore(tweetDiv, widget.body.childNodes[0]);
        }
        tweetDiv.setText('Loading...');
        if (MultipleFeeds.config.twitterID != '' && typeof MultipleFeeds.config.twitterID != 'undefined') {
            MultipleFeeds.getTweet(MultipleFeeds.config.twitterID);
        }
    } else if (tweetDiv = widget.body.getElementsByClassName('tweet')[0]) {
        tweetDiv.remove();
    }

}

/* Method: getTweet

gets the latest tweet of the named user

Parameters:
*String - twitterID : the twitter user ID

Returns:
* Nothing.

*/
MultipleFeeds.getTweet = function(twitterID) {
    widget.log('getTweet()');

    UWA.Data.request('http://www.twitter.com/statuses/user_timeline/' + twitterID + '.json?count=1', {
        method: 'get',
        type: 'json',
        proxy: 'ajax',
        cache: 300, // 5 mins
        onComplete: MultipleFeeds.parseTweet
    });
}

/* Method: parseTweet

checks the json response for errors

Parameters:
*Object - json: data from twitter api

Returns:
* Nothing.

*/
MultipleFeeds.parseTweet = function(json) {
    widget.log('parseTweet()');
    if (typeof json == 'undefined' || json == false || json == '' || json.error || typeof json != 'object') {
        //fail silently
        if (tweetDiv = widget.body.getElementsByClassName('tweet')[0]) {
            tweetDiv.remove();
        }
        return;
    }

    MultipleFeeds.displayTweet(json);
}

/* Method: displayTweet

prints a twitter message

Parameters:
*Object - json : the data from twitter api

Returns:
* Nothing.

*/
MultipleFeeds.displayTweet = function(json) {
    widget.log('displayTweet()');

    //var name = json[0].user.name;
    var screenName = json[0].user.screen_name;

    // clean string
    var text = MultipleFeeds.stripScripts(json[0].text).stripTags();

    // anchorize urls
    text = text.makeClickable();

    // urlize @pseudo
    var pseudoUrl = 'http://twitter.com/$1';
    text = text.replace(/\B@([^\s:=!;\.,\)]+)/ig, '<a href="' + pseudoUrl + '" target="_blank"><strong>@$1</strong></a>');

    // urlize #keyword
    var keywordUrl = 'http://search.twitter.com/search?q=%23$1';
    text = text.replace(/\B#([^\s:=!;\.,\)]+)/ig, '<a href="' + keywordUrl + '" target="_blank"><strong>#$1</strong></a>');

    var tweetDiv = widget.body.getElementsByClassName('tweet')[0];
    var html = '<strong><a href="http://www.twitter.com/' + screenName + '">@' + screenName + '</a></strong>';
    html += ' : ' + text;
    tweetDiv.setHTML(html);

    // for stats
    anchors = tweetDiv.getElementsByTagName('a');
    for (i=0; i < anchors.length; i++) {
        anchors[i].onclick = function() {
            var url = MultipleFeeds.path + 'redirect.php?url=' + encodeURIComponent(this.href) +
                '&provider=' + encodeURIComponent(MultipleFeeds.getIdentifier()) + '&id=' + encodeURIComponent(widget.id);
            if (widget.isInNativeMode()) url += '&campaignId=' + encodeURIComponent(widget.environment.obj.dataObj.campaignId);
            widget.openURL(url);
            return false;
        }
    }
}

/* Method: stripScripts

removes <script> tags and content from strings
used in displayTweet method

Parameters:
*String - str: the string to clean

Returns:
*String: the cleaned string

*/
MultipleFeeds.stripScripts = function(str) {
    return str.replace(/<script[^>]*>[\S\s]*?<\/script>/img, '');
}

MultipleFeeds.onActiveTabChanged = function(name, e) {

    // Switch tabs if selected tab already exist
    if (name == widget.getValue("selectedTab") && MultipleFeeds.firstLaunch == false) {
        delete MultipleFeeds.feeds[parseInt(name)];
        for (var i = 0; i < MultipleFeeds.list.length; i++) {
            if (e.id == MultipleFeeds.list[i]) {
                MultipleFeeds.miniTabs.selectKey(i, MultipleFeeds.list[name], false);
                MultipleFeeds.list[i] = MultipleFeeds.list[name];
                break;
            }
        }
    }
    MultipleFeeds.firstLaunch = false;

    // Update the list of feeds
    MultipleFeeds.currentId = e.id;
    MultipleFeeds.list[parseInt(name)] = e.id;
    widget.setList(MultipleFeeds.list);
    widget.setValue("selectedTab", name);

    MultipleFeeds.offset = 0;
    MultipleFeeds.displayTab();
}

MultipleFeeds.customizeVideoToolbar = function() {
    // Sow a Autoplay/Do not autoplay option in the toolbar
    if (MultipleFeeds.config.showAutoPlay == true) {
        var toolbar = MultipleFeeds.playerDiv.getElementsByTagName('p')[0];
        var autoPlayLink = widget.createElement('a');
        autoPlayLink.href = 'javascript:void(0)';
        if (widget.getBool('videoAutoPlay') == true) autoPlayLink.setText(_("Do not autoplay"));
        else autoPlayLink.setText(_("Autoplay"));
        autoPlayLink.onclick = function() {
            if (widget.getBool('videoAutoPlay') == true) {
                widget.setValue('videoAutoPlay', false);
                this.setText(_("Autoplay"));
            } else {
                widget.setValue('videoAutoPlay', true);
                this.setText(_("Do not autoplay"));
            }
            return false;
        }
        var separator = document.createTextNode(' | ');
        toolbar.insertBefore(separator, toolbar.firstChild);
        toolbar.insertBefore(autoPlayLink, toolbar.firstChild);
    }
    // Colorize links
    if (typeof MultipleFeeds.config.headerTextColor != "undefined") {
        var links = MultipleFeeds.playerDiv.getElementsByTagName('a');
        for (var li=0; li<links.length; li++) {
            links[li].style.color = MultipleFeeds.config.headerTextColor;
        }
    }
}

MultipleFeeds.onClick = function(params) {

    if (typeof obj != "undefined" && obj.previewMode) {
        return true;
    }

    MultipleFeeds.setRead(params.index);

    if (MultipleFeeds.config.forcePlayVideo == true && MultipleFeeds.feeds[MultipleFeeds.currentId].items[params.index].video && MultipleFeeds.config.hasVideoPlayer == true) {
        return MultipleFeeds.onVideoPlay(params);
    }

    var feed = MultipleFeeds.feeds[MultipleFeeds.currentId];

    var openOutsideOption = typeof MultipleFeeds.config.openOutside == "undefined" ? widget.getBool("openOutside") : MultipleFeeds.config.openOutside;
    if (openOutsideOption == true) {
        var link = feed.items[params.index].link;
        var url = MultipleFeeds.path + 'redirect.php?url=' + encodeURIComponent(link) + '&provider=' + encodeURIComponent(MultipleFeeds.getIdentifier()) + '&id=' + encodeURIComponent(widget.id);
        if (widget.isInNativeMode()) url += '&campaignId=' + encodeURIComponent(widget.environment.obj.dataObj.campaignId);
        widget.openURL(url);
        return true;
    }

    if (App.inSubscribePreview) return false;

    if (typeof App.FeedReader != "undefined") {
        if (MultipleFeeds.flashPlayer && typeof MultipleFeeds.flashPlayer != "undefined") {
            MultipleFeeds.flashPlayer.hide();
            widget.callback("onUpdateBody");
        }
        // Open the Netvibes FeedReader
        var Tmp = {};
        Tmp.feed = MultipleFeeds.feeds[MultipleFeeds.currentId];
        var title = Tmp.feed.title;
        var category = widget.getValue("category");
        if (widget.readOnly == true &&
            typeof MultipleFeeds.category != "undefined") {
                category = MultipleFeeds.category;
        }
        if (MultipleFeeds.config.useCategoriesAsTitle == true) title = MultipleFeeds.config.categoryLabels[category];
        var mod = widget.environment.obj;
        var supplement = {'name': mod.dataObj.moduleName, 'id': mod.dataObj.id, 'ident': mod.getIdentifier()};
        if (mod.dataObj.campaignId) supplement.campaignId = mod.dataObj.campaignId;
        App.report('module.clicked', supplement);
        App.FeedReader.display({title: title,
                                moduleLocalData: Tmp,
                                selectedItemIndex: params.index,
                                moduleObj: MultipleFeeds.pub,
                                contentObj: MultipleFeeds.pub,
                                proxy: "proxy/feedProxy.php?url="+encodeURIComponent(MultipleFeeds.data[MultipleFeeds.currentId].url)});
    } else if (typeof Netvibes.UI.EmbedFeedReader != "undefined") {
        new Netvibes.UI.EmbedFeedReader(feed, params.index);
    } else {
        if (widget && widget.openURL) {
            var link = feed.items[params.index].link;
            var url = MultipleFeeds.path + 'redirect.php?url=' + encodeURIComponent(link) + '&provider=' + encodeURIComponent(MultipleFeeds.getIdentifier()) + '&id=' + encodeURIComponent(widget.id);
            if (widget.isInNativeMode()) url += '&campaignId=' + encodeURIComponent(widget.environment.obj.dataObj.campaignId);
            widget.openURL(url);
        }
    }
    return true;
}

MultipleFeeds.onVideoPlay = function(params) {
    MultipleFeeds.setRead(params.index);

    var feed = MultipleFeeds.feeds[MultipleFeeds.currentId];
    if (feed.items[params.index].video && MultipleFeeds.config.hasVideoPlayer == true) {
        // Open the Video Player
        MultipleFeeds.flashPlayer.show(feed.items[params.index].video, feed.items[params.index].flashvars+MultipleFeeds.getSharingLink(), {
            mediaLinkUrl: feed.items[params.index].link,
            mediaLinkText: _("See on {0}").format(MultipleFeeds.config.providerName)
        });
        MultipleFeeds.customizeVideoToolbar();
        widget.callback("onUpdateBody");
    }

    return true;
}

MultipleFeeds.onMiddleOrRightClick = function(params) {
    MultipleFeeds.setRead(params.index);
    if (MultipleFeeds.flashPlayer) {
        MultipleFeeds.flashPlayer.hide();
        widget.callback("onUpdateBody");
    }
}

MultipleFeeds.onPodcastPlay = function(params) {
    var ret;
    if (widget.isInNativeMode()) {
        if (App.inSubscribePreview) {
            alert(_("Sorry, this feature is not available in preview mode."));
            return false;
        }
        ret = false;
    } else {
        ret = true;
    }

    if (typeof AudioPlayer != 'undefined' && AudioPlayer.play) {
        AudioPlayer.play(params.fileLink, params.podName);
        // make it unread only if we could start play
        MultipleFeeds.setRead(params.index);
    }

    return ret;
}

MultipleFeeds.onDownload = function(params) {
    MultipleFeeds.setRead(params.index);
    if (widget && widget.openURL) widget.openURL(params.mediaUrl);
    return true;
}

MultipleFeeds.onAddStar = function(params) {
    var feed = MultipleFeeds.feeds[MultipleFeeds.currentId];
    var item = feed.items[params.index];
    widget.addStar({title: item.title, url: item.link, date: item.date, summary: item.content.stripTags()});
    return true;
}

MultipleFeeds.setRead = function(index) {
    widget.log("MultipleFeeds.setRead()");

    var feed = MultipleFeeds.feeds[MultipleFeeds.currentId];
    if (UWA.Services.FeedHistory.isRead(feed, index) == false) {
        UWA.Services.FeedHistory.setRead(feed, index);
        MultipleFeeds.ui.setRead(index);

        widget.setValue("history_"+MultipleFeeds.currentId, UWA.Services.FeedHistory.getString(feed));
        if (MultipleFeeds.config.hideUnreadCount != true && widget.readOnly != true) {
            var nbUnread = UWA.Services.FeedHistory.getNbNew(feed, feed.items.length);
            widget.setUnreadCount(nbUnread);
        }
    }
}

MultipleFeeds.showError = function(title, message) {
    widget.writeTitle(title);
    widget.feedDiv.innerHTML = "<p>"+message+"</p>";
}

MultipleFeeds.getSharingLink = function() {
    var category = widget.getValue("category");
    if (widget.readOnly == true &&
        typeof MultipleFeeds.category != "undefined") {
            category = MultipleFeeds.category;
    }
    return "&link=http://"+NV_HOST+
            "/subscribe.php?module=MultipleFeeds%26provider="+
            widget.getValue("provider")+
            "%26category="+category;
}

/*
** Netvibes FeedReader Callbacks
*/
MultipleFeeds.pub = {};
MultipleFeeds.pub.save = function() {
    widget.log("MultipleFeeds.pub.save()");
}
MultipleFeeds.pub.setHistory = function(history) {
    widget.log("MultipleFeeds.pub.setHistory();");

    widget.setValue("history_"+MultipleFeeds.currentId, history);
}
MultipleFeeds.pub.saveHistory = function() {
    widget.log("MultipleFeeds.pub.saveHistory()");
}
MultipleFeeds.pub.setRead = function(index) {
    widget.log("MultipleFeeds.pub.setRead()");

    return MultipleFeeds.ui.setRead(index);
}
MultipleFeeds.pub.setUnRead = function(index) {
    widget.log("FeedReader.pub.setUnRead()");

    return MultipleFeeds.ui.setUnRead(index);
}
MultipleFeeds.pub.setFeedTitle = function() {
    widget.log("MultipleFeeds.pub.setFeedTitle()");

    if (MultipleFeeds.config.hideUnreadCount != true && widget.readOnly != true) {
        var feed = MultipleFeeds.feeds[MultipleFeeds.currentId];
        var nbUnread = UWA.Services.FeedHistory.getNbNew(feed, feed.items.length);
        widget.setUnreadCount(nbUnread);
    }
}
MultipleFeeds.pub.setAllAsRead = function() {
    widget.log("MultipleFeeds.pub.setAllAsRead()");

    if (widget.readOnly == true) return;

    var feed = MultipleFeeds.feeds[MultipleFeeds.currentId];
    var nbItems = feed.items.length;
    var nbDisplayedItems = MultipleFeeds.ui.getNumberOfDisplayedItems();

    for (var i = 0; i < nbItems; i++) {
        if (i >= MultipleFeeds.offset && i < (nbDisplayedItems + MultipleFeeds.offset)) {
            if (UWA.Services.FeedHistory.isRead(feed, i)) {
                MultipleFeeds.ui.setRead(i);
            } else {
                MultipleFeeds.ui.setUnRead(i);
            }
        }
    }
    MultipleFeeds.pub.setFeedTitle();
}
MultipleFeeds.pub.getShowPage = function() {
    widget.log("MultipleFeeds.pub.getShowPage()");

    return parseInt(widget.getValue("showPage"));
}
MultipleFeeds.pub.setShowPage = function(value) {
    widget.log("MultipleFeeds.pub.setShowPage()");

    return parseInt(widget.setValue("showPage", value));
}
MultipleFeeds.pub.setAutoRefreshState = function(state) {
    MultipleFeeds.autoRefreshState = state;
}
MultipleFeeds.pub.openLink = function(index) {
    var link = MultipleFeeds.feeds[MultipleFeeds.currentId].items[index].link;
    link = MultipleFeeds.path + 'redirect.php?url=' + encodeURIComponent(link) +
           '&provider=' + encodeURIComponent(MultipleFeeds.getIdentifier()) +
           '&id=' + encodeURIComponent(widget.id);
    if (widget.isInNativeMode()) link += '&campaignId=' + encodeURIComponent(widget.environment.obj.dataObj.campaignId);
    if (widget && widget.openURL) {
        widget.openURL(link);
        return false;
    }
    return true;
}
MultipleFeeds.pub.itemClicked = function(index) {
    var mod = widget.environment.obj;
    var supplement = {'name': mod.dataObj.moduleName, 'id': mod.dataObj.id, 'ident': mod.getIdentifier()};
    if (mod.dataObj.campaignId) supplement.campaignId = mod.dataObj.campaignId;
    App.report('feed.clicked', supplement);
}
if (widget.readOnly != true) {
    MultipleFeeds.pub.addStar = function(data) {
        widget.log("MultipleFeeds.pub.addStar()");
        widget.addStar(data);
    }
}

MultipleFeeds.getIdentifier = function() {
    var provider = identifier = widget.getValue("provider");
    if (provider == 'custom') {
        var url = widget.getValue("url");
        var matches = url.match(/\/\d+/g);
        var id = matches.map(function(m) {
           return m.substr(1);
        }).join('');

        identifier = 'custom:' + id;
    }
    return identifier;
}

/*
** widget callbacks
*/
widget.onLoad = function() {
    widget.log("widget.onLoad()");

    // Disable Views outside Netvibes && hide inaccurate preference 'open outside'
    if (!widget.isInNativeMode()) {
        widget.preferences[1].type = 'hidden'; // Views
        MultipleFeeds._forceDefaultView = true;

        widget.preferences[5].type = 'hidden'; // open outside
    }

    widget.writeTitle(_("Loading..."));

    if ((typeof widget.getValue("category") == "undefined" || !widget.getValue("category")) &&
        !(widget.readOnly == true && typeof MultipleFeeds.category != "undefined")) {
        widget.setValue("category", widget.locale || "default");
    }

    MultipleFeeds.getProvider();
}

widget.getFeed = function(url, proxy, disableDisplay) {
    // if disableDisplay is true, we won't load displayFeed (used from the FeedReader component)
    widget.log("widget.getFeed()");

    var callback = function(feed) {
        // just clear the lastRequest object
        if (MultipleFeeds.lastRequest && MultipleFeeds.lastRequest.cancel) { MultipleFeeds.lastRequest.cancel(); }

        // Rewrite URLs if it's defined in the config
        if (typeof MultipleFeeds.config.linkRewrite == "object" && MultipleFeeds.config.linkRewrite.from && MultipleFeeds.config.linkRewrite.to && feed.items) {
            var r = new RegExp(MultipleFeeds.config.linkRewrite.from);
            for (var i = 0; i < feed.items.length; i++) {
                feed.items[i].link = feed.items[i].link.replace(r, MultipleFeeds.config.linkRewrite.to);
            }
        }
        MultipleFeeds.feeds[MultipleFeeds.lastRequestId] = feed;

        // Build the history for the current feed
        MultipleFeeds.histories[MultipleFeeds.lastRequestId] = widget.getValue("history_"+MultipleFeeds.lastRequestId);
        UWA.Services.FeedHistory.build(widget.readOnly ? "" : MultipleFeeds.histories[MultipleFeeds.lastRequestId], feed);

        if (!disableDisplay) { MultipleFeeds.displayFeed(feed); }
    }

    if (typeof url == 'undefined') { url = widget.currentFeed; }
    if (typeof url == 'undefined') return;
    if (MultipleFeeds.lastRequest && MultipleFeeds.lastRequest.cancel) {
        MultipleFeeds.lastRequest.cancel();
    }
    widget.currentFeed = url;

    // Prefetch handling
    if(UWA.Feeds && UWA.Feeds[url]) {
        var response = UWA.Feeds[url];
        callback(response);
        // delete prefetchedFeed 15 seconds after using it
        setTimeout(function() { UWA.Feeds[url] = null }, 15000);
        return;
    }

    if (typeof proxy == "undefined") {
        if(typeof MultipleFeeds.config.proxy != "undefined") {
            proxy = MultipleFeeds.currentProxy != null ? MultipleFeeds.currentProxy : MultipleFeeds.config.proxy;
        } else {
            proxy = 'feed';
        }
    }

    // if we're trying to use no proxy as the content available locally,
    // but we're not on Netvibes, then we have to change to AJAX proxy to
    // get the data remotely
    if (proxy === null && !widget.isInNativeMode()) { proxy = 'ajax'; }

    // if the URL was relative to the current host, but we're using a proxy,
    // let's make the URL absolute
    if (proxy !== null && url.substring(0,1) == '/') { url = 'http://' + NV_HOST + url; }

    if (!disableDisplay) {
        var feedDiv = widget.feedDiv;
        feedDiv.innerHTML = _("Loading...");
    }

    var parameters = { method : 'GET',
                       proxy: proxy,
                       shortFeed: false,
                       type: 'feed',
                       onComplete: callback,
                       onFailure: callback };

    MultipleFeeds.lastRequest = UWA.Data.request(url, parameters);
}

// this should be called on search - in a tab
widget.doSearch = function(url, query, proxy) {
    query = encodeURIComponent(query);
    MultipleFeeds.offset = 0;
    widget.setValue("lastSearch", query);
    if (query == '') {
        MultipleFeeds.showError(MultipleFeeds.config.title, _('No search entry yet'));
    } else {
        widget.setValue("history_"+MultipleFeeds.currentId, '');
        MultipleFeeds.lastRequestId = MultipleFeeds.currentId;
        widget.getFeed(url.replace(/%s/, query), proxy);
    }

}

widget.updateDisplay = function() {
    if (MultipleFeeds.limit != widget.getValue("nbTitles")) {
        var feed = MultipleFeeds.feeds[MultipleFeeds.currentId];
        var page = MultipleFeeds.offset / MultipleFeeds.limit;
        var newOffset = page * widget.getValue("nbTitles");
        while (newOffset >= feed.items.length) {
            newOffset -= widget.getValue("nbTitles");
        }
        MultipleFeeds.offset = newOffset;
    }
    MultipleFeeds.displayFeed();
}

widget.updateTweet = function() {
    MultipleFeeds.buildTweet();
}

widget.writeTitle = function(title) {
    widget.log("widget.writeTitle()");

    if (widget.elements['title']) widget.elements['title'].setHTML(title);
}

widget.setFeedTitle = function() {
    // Used to update the position of the unread count
    if (widget.elements['title']) {
        var unread = widget.elements['title'].getElementsByClassName('unread')[0];
        widget.elements['title'].removeChild(unread);
    }
    MultipleFeeds.pub.setFeedTitle();
}

widget.onSearch = function(query) {
    widget.log("widget.onSearch()");

    MultipleFeeds.search = query;
    MultipleFeeds.displayFeed();
}

widget.onResetSearch = function() {
    widget.log("widget.onResetSearch()");

    MultipleFeeds.search = "";
    MultipleFeeds.displayFeed();
}

widget.onRefresh = function() {
    if (MultipleFeeds.autoRefreshState != true) return;
    if (widget.getValue('view') != '' && MultipleFeeds.flashPlayer) {
        MultipleFeeds.flashPlayer.hide();
    }

    // #5977: on dashboard, force flash player to be displayed on refresh
    if (widget.environment.dashboard && widget.getBool('videoAutoPlay') == true && MultipleFeeds.flashPlayer) {
        MultipleFeeds.videoAutoPlay = true;
    }

    MultipleFeeds.feeds = [];
    MultipleFeeds.lastRequestId = MultipleFeeds.currentId;
    widget.getFeed();
    MultipleFeeds.buildTweet();
}

widget.onChangeCategory = function() {
    widget.setValue("selectedTab", "0");
    MultipleFeeds.firstLaunch = true;
    MultipleFeeds.feeds = [];
    widget.setValue("list", "");
    widget.onLoad();
}

widget.onResize = function() {
    var content = MultipleFeeds.miniTabs.getTabContent(widget.getValue("selectedTab").toString());
    var dim = Element.getDimensions(content);

    if (MultipleFeeds.flashPlayer) {
        var aspectRatio = MultipleFeeds.config.videoAspectRatio || 1;
        var h = dim.width / aspectRatio;
        MultipleFeeds.flashPlayer.resize(dim.width, h);
        widget.callback("onUpdateBody");
    }

    if (typeof MultipleFeeds.ui != 'undefined' && typeof MultipleFeeds.ui.resize == 'function') {
        if (!MultipleFeeds.ui.resize(dim)) {
            MultipleFeeds.displayFeed();
        }
    }
}

widget.onResetUnreadCount = function() {
    if (widget.readOnly == true) return;
    var feed = MultipleFeeds.feeds[MultipleFeeds.currentId];
    if (!feed) return;

    var nbItems = feed.items.length;
    var nbDisplayedItems = MultipleFeeds.ui.getNumberOfDisplayedItems();

    for (var i = 0; i < nbItems; i++) {
        UWA.Services.FeedHistory.setRead(feed, i);
        if (i >= MultipleFeeds.offset && i < (nbDisplayedItems + MultipleFeeds.offset)) {
            MultipleFeeds.ui.setRead(i);
        }
    }
    widget.setUnreadCount(0);
    widget.setValue("history_"+MultipleFeeds.currentId, UWA.Services.FeedHistory.getString(feed));
}

widget.getList = function(length) {
    var category = widget.getValue("category");
    if (widget.readOnly == true &&
        typeof MultipleFeeds.category != "undefined") {
            category = MultipleFeeds.category;
    }
    var list = widget.getValue("list_"+category);
    if (typeof list == "undefined" || list == "" || list == null) {
        list = [];
        for (var i = 0; i < length; i++) {
            list.push(MultipleFeeds.defaultOrder[i]);
        }
        return list;
    }
    // Ugly temporary workaround for ugly bug in Vista
    if(typeof list != "string") {
      widget.setValue("list_"+category, "");
      return widget.getList(length);
    }
    var ret = list.split(",");
    // If the list does not match the data, we reset the list
    for (var i = 0; i < ret.length; i++) {
        var id;
        for (var o=0, l=MultipleFeeds.data.length; o<l; o++){
            if (MultipleFeeds.data[o] && MultipleFeeds.data[o]['id'] == ret[i]) {
            id = ret[i];
            break;
            }
        }
        if (typeof id == "undefined") {
             widget.setValue("list_"+category, "");
             return widget.getList(length);
        }
    }
    return ret;
}

widget.setList = function(list) {
    var category = widget.getValue("category");
    if (widget.readOnly == true &&
        typeof MultipleFeeds.category != "undefined") {
            category = MultipleFeeds.category;
    }
    widget.setValue("list_"+category, list.join(","));
}

widget.toggleNavigation = function(display) {
    if (display) MultipleFeeds.pagerContent.style.display = "block";
    else MultipleFeeds.pagerContent.style.display = "none";
}

widget.isInNativeMode = function() {
    return (typeof widget.environment.netvibes != "undefined" && widget.environment.netvibes.inline);
}

widget.isOnEcosystem = function() {
    return widget.environment.commUrl == "http://eco.netvibes.com/uwa.html";
}

widget.clickItem = function(index) {
    MultipleFeeds.onClick({'index': index});
}

widget.shareItem = function(index) {
    MultipleFeeds.onAddStar({'index': index});
}

// "provider" backend is not compatible with Json request without ajax
UWA.Data.useJsonRequest = false;

widget.setValue("provider", "google");


</script>

</head>
<body>
    <p>Loading ...</p>
</body>
</html>

