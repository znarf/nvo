/*
License:
    Copyright Netvibes 2006-2009.
    This file is part of UWA JS Runtime.

    UWA JS Runtime is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    UWA JS Runtime is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with UWA JS Runtime. If not, see <http://www.gnu.org/licenses/>.
*/

var UWA_Overlay = {

    debug: true,

    // element
    sidebar_box: null,
    splitter: null,

    init: function () {

        // init element
        UWA_Overlay.sidebar_box = window.top.document.getElementById("mywidget-sidebar-box");
        UWA_Overlay.splitter = window.top.document.getElementById("mywidget-splitter");

        // set display or not
        if(nsPreferences.getBoolPref("extensions.uwa.is_closed") == true) {
            UWA_Overlay.closeSidebar();
        } else {
            UWA_Overlay.showSidebar();
        }
    },

    uninit: function () {

    },

    firstinit: function ()
    {
        var version = nsPreferences.copyUnicharPref("extensions.uwa.version");

        // the latter is for development only
        if(version == null || version < "0.1")
        {
            // when user turns uwa off, don't show it after firefox restart
            nsPreferences.setBoolPref("extensions.uwa.is_closed", false);

            // the width of uwa window
            nsPreferences.setIntPref("extensions.uwa.width", 330);

            // uwa version
            nsPreferences.setUnicharPref("extensions.uwa.version", "0.1");

            // theme name
            nsPreferences.setUnicharPref("extensions.uwa.theme", "garlic");
        }
    },

    closeSidebar: function ()
    {
        nsPreferences.setBoolPref("extensions.uwa.is_closed", true);

        UWA_Overlay.sidebar_box.setAttribute("hidden", "true");
        UWA_Overlay.splitter.setAttribute("hidden", "true");
    },

    showSidebar: function ()
    {
        nsPreferences.setBoolPref("extensions.uwa.is_closed", false);

        UWA_Overlay.sidebar_box.setAttribute("hidden", "false");
        UWA_Overlay.splitter.setAttribute("hidden", "false");

        var sibebar_width = nsPreferences.getIntPref("extensions.uwa.width");

        UWA_Overlay.sidebar_box.setAttribute("width", sibebar_width.toString());
    },

    toogleSidebar: function ()
    {
        // set display or not
        if(nsPreferences.getBoolPref("extensions.uwa.is_closed") == true) {
            UWA_Overlay.showSidebar();
        } else {
            UWA_Overlay.closeSidebar();
        }
    },

    onVSplitterRelease: function ()
    {
        var width = DM_DailyPlayer.sidebar_box.getAttribute("width");
        nsPreferences.setIntPref("extensions.uwa.width", parseInt(width));
    },

    onHSplitterRelease: function ()
    {
        var height = DM_DailyPlayer.video_window.getAttribute("height");
        nsPreferences.setIntPref("extensions.uwa.height", parseInt(height));
    }
}
