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

if (typeof Environments == "undefined") var Environments = {};

if (typeof Widgets == "undefined") var Widgets = {};

Object.extend(UWA.Environment.prototype,
{
    initialize: function (url){
        /*
        Usage:
        <script type="text/javascript" src="http://www.netvibes.com/js/UWA/load.js.php?env=BlogWidget2"></script>
        <script type="text/javascript">
        var BW = new UWA.Environment({moduleUrl:'http://uwa.service.japanim.fr/samples/index.php'});
        BW.setConfiguration({'title':'', 'height':145});
        </script>
        */
    },

    onInit: function ()
    {

    },

    setConfiguration: function (options)
    {

    },

    onRegisterModule: function ()
    {

    },

    getData: function (name)
    {

    },

    setData: function (name, value)
    {

    },

    onUpdateBody: function ()
    {

    },

    setIcon: function (name)
    {
        if (this.module.elements.icon)
        {
            var url = UWA.proxies['icon'] + "?url=" + encodeURIComponent(this.module.elements.icon);
            this.module.elements.icon.innerHTML = '<img width="16" height="16" src="' + url + '" />';
        }
    }
});

