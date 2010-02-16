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

UWA.Controls.TabView = function (options) {
    this.initialize(options);
}

UWA.Controls.TabView.prototype =
{
    initialize: function (options) {

        this.setOptions(options);
        this.dataItems = {};
        this.selectedTab = null;
        this.selectedIndex = this.options.selectedIndex || 0;
    },

    setOptions: function (options) {

        this.options = {
            autohideDropdowns: true,
            classTabSet: "nv-tabSet",
            classTabList: "nv-tabList",
            classTabContent: "nv-tabContent",
            softPadding: false,
            orientation: "top",
            dataKey: "text",
            extendedAction: false,
            allowReload: false
        };

        Object.extend(this.options, options || {})
    },

    _createTabSet: function () {

        this.tabSet = document.createElement("div");
        this.tabSet.className = this.options.classTabSet;
        this.tabList = document.createElement("ul");
        this.tabList.className = this.options.classTabList + " autoclear";
        this.tabList.style.padding = "0";

        if (/^(top|bottom|left|right)$/.test(this.options.orientation)) {
            Element.addClassName(this.tabList, this.options.orientation)
        }

        this.tabSet.appendChild(this.tabList)
    },

    _createTabItem: function (k, b, l) {
        if (typeof l == "undefined") {
            l = {}
        }
        var h = document.createElement("a");
        h.href = "javascript:void(0)";
        h.title = b.tooltip || "";
        h.target = "_blank";
        h.style.whiteSpace = "nowrap";
        h.onclick = function ()
        {
            return false;
        };
        if (b.length)
        {
            if (b[0].image) {
                var j = document.createElement("img");
                j.src = b[0].image;
                h.appendChild(j)
            }
            else
            {
                if (b[0].picto)
                {
                    var d = document.createElement("img");
                    d.src = b[0].picto;
                    d.style.marginRight = "4px";
                    d.style.marginBottom = "-2px";
                    h.appendChild(d)
                }
                else
                {
                    if (b[0].icon)
                    {
                        var g = document.createElement("img");
                        g.src = b[0].icon;
                        g.style.marginRight = "4px";
                        g.style.marginBottom = "-2px";
                        h.appendChild(g)
                    }
                }
                var c = document.createElement("span");
                c.appendChild(document.createTextNode(l.staticText || b[0].text));
                h.appendChild(c)
            }
            k.setAttribute("key", b[0][this.options.dataKey]);
            var e = document.createElement("span");
            e.className = "dropdown";
            do {
                var f = "dropdownTab-" + (++Netvibes.UI._idIncrement)
            }
            while ($(f));
            e.setAttribute("id", f);
            var i = document.createElement("img");
            i.src = "http://" + NV_HOST + "/img/s.gif";
            i.width = 14;
            i.height = 14;
            i.style.verticalAlign = "middle";
            i.className = "placeHolder";
            e.appendChild(i);
            h.appendChild(e);
            e.onmousedown = this.eventDropDown.bindAsEventListener(this)
        }
        else
        {
            if (b.image) {
                var j = document.createElement("img");
                j.src = b.image;
                h.appendChild(j)
            }
            else
            {
                if (b.picto)
                {
                    var d = document.createElement("img");
                    d.src = b.picto;
                    d.style.marginRight = "4px";
                    d.style.marginBottom = "-2px";
                    h.appendChild(d)
                }
                else
                {
                    if (b.icon)
                    {
                        var g = document.createElement("img");
                        g.src = b.icon;
                        g.style.marginRight = "4px";
                        g.style.marginBottom = "-2px";
                        h.appendChild(g)
                    }
                }
                if (typeof b.text == "string") {
                    h.appendChild(document.createTextNode(b.text))
                }
                else {
                    if (typeof b.text != "undefined") {
                        h.appendChild(b.text)
                    }
                }
            }
        }
        return (h);
    },

    appendTo: function (a) {

        if (!this.tabSet) {
            this._createTabSet()
        }
        if (!this.selectedTab && this.tabList.hasChildNodes()) {
            this.selectTab(0, false)
        }
        $(a).appendChild(this.tabSet);
        if (typeof widget == "object" && typeof widget.callback == "function")
        {
            widget.callback("onUpdateBody")
        }
    },

    addTab: function (d, c, b) {

        if (!this.tabSet) {
            this._createTabSet()
        }
        if (typeof b == "undefined") {
            b = {}
        }
        var a = document.createElement("li");
        a.className = "tab " + d;
        a.setAttribute("name", d);
        if (c.disabled) {
            Element.addClassName(a, "disabled")
        }
        else
        {
            a.onclick = this.eventTabClicked.bindAsEventListener(this);
            if (b.staticText) {
                a.setAttribute("static", "static")
            }
        }
        a.appendChild(this._createTabItem(a, c, b));
        if (this.selectedTab == null) {}
        this.tabList.appendChild(a);
        this.createTabContent(d);
        this.dataItems[d] = c;
        return a;
    },

    removeTab: function (a) {
        var b = this.getTab(a);
        Element.remove(b)
    },

    setTab: function (c, b, a) {
        var d = this.getTab(c);
        this.dataItems[c] = UWA.merge(b, this.dataItems[c]);
        d.setHTML("");
        d.appendChild(this._createTabItem(d, this.dataItems[c], a))
    },

    addExternalLink: function (b, a) {
        var c = this.getTab(b);
        c.firstChild.setAttribute("href", a)
    },

    eventTabClicked: function (b) {
        if (Event.element(b).className == "placeHolder") {
            return false
        }
        var a = Event.findElement(b, "LI");
        if (!Element.hasClassName(a, "disabled")) {
            this.selectTab(a)
        }
        return false;
    },

    eventExtendedActionClicked: function (a) {
        this.hidePopupMenu();
        this._notify("extendedActionClicked");
        return false;
    },

    enableTab: function (b, a) {
        var c = this.getTab(b);
        if (a) {
            Element.removeClassName(c, "disabled")
        }
        else {
            Element.addClassName(c, "disabled")
        }
    },

    selectTab: function (e, j) {
        if (typeof e == "number" || typeof e == "string") {
            e = this.getTab(e)
        }
        var a = e.getAttribute("name");
        if (this.selectedTab && (this.selectedTab.getAttribute("name") == a) && j == undefined && !this.options.allowReload) {
            return
        }
        var g = this.tabList.getElementsByTagName("li");
        for (var d = 0, h; h = g[d]; d++)
        {
            Element.removeClassName(h, "selected");
            if (this.popupMenu) {
                this.hidePopupMenu()
            }
            if (this.options.autohideDropdowns)
            {
                var c = $(h).getElementsByClassName("dropdown");
                $A(c).each(function (i)
                {
                    Element.hide(i)
                })
            }
        }
        Element.addClassName(e, "selected");
        if (this.options.autohideDropdowns)
        {
            var c = $(e).getElementsByClassName("dropdown");
            $A(c).each(function (i)
            {
                Element.show(i)
            })
        }
        for (var d = 0, f; f = this.contentArray[d]; d++)
        {
            if (Browser.isSafari && Browser.version < 3 && f.getElementsByTagName("iframe").length == 1 && f.getElementsByTagName("iframe")[0].style.width == "100%")
            {
                if (a == f.getAttribute("name")) {
                    f.style.visibility = "visible";
                    f.style.position = "static"
                }
                else
                {
                    var b = f.getElementsByTagName("iframe")[0];
                    f.style.width = b.contentWindow.innerWidth + "px";
                    f.style.visibility = "hidden";
                    f.style.position = "absolute";
                    f.style.left = "0px";
                    f.style.top = "0px";
                }
            }
            else {
                if (a == f.getAttribute("name")) {
                    Element.show(f)
                }
                else {
                    Element.hide(f)
                }
            }
        }
        this.selectedTab = e;
        this.selectedIndex = e.getAttribute("index");
        if (j === false) {
            return
        }
        this._notify("activeTabChange")
    },

    hide: function () {
        Element.hide(this.tabSet)
    },

    show: function () {
        Element.show(this.tabSet)
    },

    hideTabList: function () {
        Element.hide(this.tabList)
    },

    showTabList: function () {
        Element.show(this.tabList)
    },

    reload: function () {
        this._notify("activeTabChange")
    },

    eventDropDown: function (b)
    {
        var a = Event.findElement(b, "LI");
        this.popupMenu = $("minitabsOptions");
        if (!this.popupMenu)
        {
            this.popupMenu = document.createElement("ul");
            this.popupMenu.setAttribute("id", "minitabsOptions");
            this.popupMenu.className = "popupMenu";
            this.popupMenu.style.position = "absolute";
            document.getElementsByTagName("body").item(0).appendChild(this.popupMenu);
            Element.hide(this.popupMenu);
            this.bindedHidePopupMenu = this.hidePopupMenu.bindAsEventListener(this);
            $(document.body).addEvent("mousedown", this.bindedHidePopupMenu)
        }
        var c = $(a).getElementsByClassName("dropdown")[0];
        if (this.popupMenu.style.display != "none" && this.popupMenu.getAttribute("dropdownId") == c.id) {
            this.hidePopupMenu();
            return
        }
        this._showPopupMenu(a);
        this.popupMenu.setAttribute("dropdownId", c.id);
        Event.stop(b);
        return false;
    },

    getTabContent: function (b)
    {
        var d = (typeof b == "number") ? "index" : "name";
        for (var c = 0, a = this.contentArray.length; c < a; c++) {
            if (this.contentArray[c].getAttribute(d) == b) {
                return this.contentArray[c];
            }
        }
    },

    setContent: function (a, c) {

        var b = this.getTabContent(a);
        if (b) {
            if (typeof c == "string") {
                b.innerHTML = c
            }
            else {
                b.innerHTML = "";
                b.appendChild(c)
            }
        }
        if (typeof widget == "object" && typeof widget.callback == "function")
        {
            widget.callback("onUpdateBody")
        }
    },

    getTab: function (b) {
        if (typeof b == "string" || typeof b == "number")
        {
            var a = this.tabList.getElementsByTagName("li");
            for (var c = 0, d; d = a[c]; c++) {
                if (typeof b == "number" && b == c) {
                    return d
                }
                if (b == d.getAttribute("name")) {
                    return d;
                }
            }
        }
        return b;
    },

    observe: function (b, a) {
        if (!this.observers) {
            this.observers = []
        }
        this.observers.push([b, a]);
    },

    _notify: function (d) {
        if (!this.observers) {
            return
        }
        var c = this.selectedTab;
        var b = this.dataItems[c.getAttribute("name")];
        if (b.length)
        {
            var f = this.options.dataKey;
            for (var e = 0, a = b.length; e < a; e++) {
                if (b[e][f] == c.getAttribute("key")) {
                    b = b[e];
                    break
                }
            }
        }
        this.observers.each(function (g)
        {
            if (g[0] == d && typeof (g[1]) == "function")
            {
                g[1](c.getAttribute("name"), b)
            }
        })
    },

    _showPopupMenu: function (u) {
        var f = this._getElementCumulativeOffset(u);
        this.popupMenu.innerHTML = "";
        try
        {
            var b = u.getAttribute("name");
            var d = this.dataItems[b];
            var w = u.getAttribute("key");
            for (var p = 0, s; s = d[p]; p++)
            {
                if (s[this.options.dataKey] == w && u.getAttribute("static") != "static") {
                    continue
                }
                var k = document.createElement("li");
                var v = document.createElement("a");
                if (s.picto)
                {
                    var l = document.createElement("img");
                    l.src = s.picto;
                    l.style.marginRight = "4px";
                    l.style.marginBottom = "-2px";
                    v.appendChild(l)
                }
                else
                {
                    if (s.icon)
                    {
                        var q = document.createElement("img");
                        q.src = s.icon;
                        q.style.marginRight = "4px";
                        q.style.marginBottom = "-2px";
                        v.appendChild(q)
                    }
                }
                v.href = (s.htmlUrl || "javascript:void(0)");
                v.setAttribute("context", b);
                v.appendChild(document.createTextNode(s.text));
                v.setAttribute("key", s[this.options.dataKey]);
                if (!this.options.extendedAction && (d.length - 1) == p) {
                    Element.addClassName(v, "last")
                }
                v.onclick = this.eventPopupMenuClicked.bindAsEventListener(this);
                k.appendChild(v);
                this.popupMenu.appendChild(k)
            }
            if (this.options.extendedAction)
            {
                var k = document.createElement("li");
                var v = document.createElement("a");
                v.href = "javascript:void(0)";
                Element.addClassName(v, "action");
                v.setAttribute("context", b);
                v.appendChild(document.createTextNode(this.options.extendedAction));
                v.onclick = this.eventExtendedActionClicked.bindAsEventListener(this);
                k.appendChild(v);
                this.popupMenu.appendChild(k)
            }
        }
        catch (r) {}
        var h = Element.getDimensions(u);
        var t = (typeof App != "undefined" && App.pageCustom && (App.pageCustom.themeTitle == "Coriander")) ? 0 : 1;
        if (Browser.isSafari || Browser.isOpera) {
            t = 0
        }
        this.popupMenu.style.left = (f[0] + t) + "px";
        this.popupMenu.style.top = (f[1] + t + h.height) + "px";
        Element.show(this.popupMenu);
        this.popupMenu.style.width = "auto";
        var c = Element.getDimensions(this.popupMenu).width;
        if (c < h.width) {
            var t = 12;
            if (Browser.isIE) {
                t = 11
            }
            this.popupMenu.style.width = h.width - t + "px"
        }
        var o = $(this.tabList).getElementsByClassName("dropped");
        $A(o).each(function (a)
        {
            Element.removeClassName(a, "dropped")
        });
        Element.addClassName(u, "dropped");
        if (typeof widget == "object" && typeof widget.callback == "function")
        {
            var s;
            var n;
            s = widget.body;
            var g = 0;
            while (s && s != document.body) {
                g += s.offsetTop;
                s = s.offsetParent
            }
            s = this.popupMenu;
            var m = 0;
            while (s && s != document.body) {
                m += s.offsetTop;
                s = s.offsetParent
            }
            popupDim = Element.getDimensions(this.popupMenu);
            bodyDim = widget.body.getDimensions();
            var j = m - g + popupDim.height;
            if (bodyDim.height < j) {
                widget.body.style.height = j + "px"
            }
            widget.callback("onUpdateBody");
        }
    },

    eventPopupMenuClicked: function (g) {
        var d = Event.findElement(g, "A");
        var f = d.getAttribute("context");
        var j = this.getTab(f);
        if (j.getAttribute("static") != "static")
        {
            var h = this.options.dataKey;
            var b;
            for (var c = 0, a = this.dataItems[f].length; c < a; c++) {
                if (this.dataItems[f][c][h] == d.getAttribute("key")) {
                    b = this.dataItems[f][c];
                    break
                }
            }
            j.getElementsByTagName("span")[0].innerHTML = b.text;
            if (b.picto) {
                j.getElementsByTagName("img")[0].src = b.picto
            }
            else {
                if (b.icon) {
                    j.getElementsByTagName("img")[0].src = b.icon;
                }
            }
        }
        j.setAttribute("key", d.getAttribute("key"));
        this._notify("activeTabChange");
        this.selectTab(j);
        this.hidePopupMenu();
        Event.stop(g);
        return false;
    },

    selectKey: function (d, f, a) {
        var h = this.getTab(d);
        var c = null;
        var g = this.options.dataKey;
        for (var e = 0, b = this.dataItems[d].length; e < b; e++) {
            if (this.dataItems[d][e][g] == f) {
                c = this.dataItems[d][e];
                break
            }
        }
        if (c)
        {
            h.getElementsByTagName("span")[0].innerHTML = c.text;
            h.setAttribute("key", f);
            if (c.icon) {
                h.getElementsByTagName("img")[0].src = c.icon
            }
            if (a == undefined || a) {
                this.selectTab(h)
            }
        }
    },

    hidePopupMenu: function (a) {

        if (!this.popupMenu || (a && Event.element(a).tagName == "A")) {
            return false
        }

        Element.hide(this.popupMenu);
        var b = $(this.tabList).getElementsByClassName("dropped");
        $A(b).each(function (c) {
            Element.removeClassName(c, "dropped")
        });

        if (typeof widget == "object" && typeof widget.callback == "function") {
            widget.body.style.height = '';
            widget.callback("onUpdateBody")
        }
    },

    createTabContent: function (b, a) {

        var c = document.createElement("div");
        if (this.options.softPadding)
        {
            if (Browser.isIE) {
                c.style.padding = "3px 3px 3px 3px"
            }
            else {
                c.style.padding = "6px 3px 3px 3px";
            }
        }
        c.className = this.options.classTabContent + " " + b;
        c.setAttribute("name", b);
        c.innerHTML = _("Loading...");
        this.tabSet.appendChild(c);
        if (!this.contentArray) {
            this.contentArray = []
        }
        this.contentArray.push(c);
        c.setAttribute("tabIndex", this.contentArray.length - 1)
    },

    destroy: function () {
        $(document.body).removeEvent("mousedown", this.bindedHidePopupMenu)
    },

    _getElementCumulativeOffset: function (b) {
        var a = 0, c = 0;
        do {
            a += b.offsetTop || 0;
            c += b.offsetLeft || 0;
            b = b.offsetParent
        }
        while (b);
        return [c, a];
    }
};
