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

UWA.Controls.TabView = Class.create();
UWA.Controls.TabView.prototype =
{
  setOptions : function (options)
  {
    this.options =
    {
      autohideDropdowns : true, classTabSet : "nv-tabSet", classTabList : "nv-tabList", classTabContent : "nv-tabContent",
      softPadding : false, orientation : "top", dataKey : "text", extendedAction : false, allowReload : false
    };

    Object.extend(this.options, options || {})
  },

  initialize : function (options)
  {
    this.setOptions(options);
    this.dataItems = {};
    this.selectedTab = null;
    this.selectedIndex = this.options.selectedIndex || 0;
  },

  _createTabSet : function ()
  {
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

  _createTabItem : function (J, A, K)
  {
    if (typeof K == "undefined") {
      K = {}
    }

    var G = document.createElement("a");
    G.href = "javascript:void(0)";
    G.target = "_blank";
    G.style.whiteSpace = "nowrap";

    G.onclick = function ()
    {
      return false;
    };

    if (A.length)
    {

      if (A[0].image) {
        var I = document.createElement("img");
        I.src = A[0].image;
        G.appendChild(I)
      } else {
        if (A[0].picto)
        {
          var C = document.createElement("img");
          C.src = A[0].picto;
          C.style.marginRight = "4px";
          C.style.marginBottom = "-2px";
          G.appendChild(C)
        } else {
          if (A[0].icon)
          {
            var F = document.createElement("img");
            F.src = A[0].icon;
            F.style.marginRight = "4px";
            F.style.marginBottom = "-2px";
            G.appendChild(F)
          }
        }
        var B = document.createElement("span");
        B.appendChild(document.createTextNode(K.staticText || A[0].text));
        G.appendChild(B)
      }
      J.setAttribute("key", A[0][this.options.dataKey]);
      var D = document.createElement("span");
      D.className = "dropdown";
      do {
        var E = "dropdownTab-" + (++Netvibes.UI._idIncrement)
      }
      while ($(E));
      D.setAttribute("id", E);
      var H = document.createElement("img");
      H.src = "http://" + NV_HOST + "/img/s.gif";
      H.width = 14;
      H.height = 14;
      H.style.verticalAlign = "middle";
      H.className = "placeHolder";
      D.appendChild(H);
      G.appendChild(D);
      D.onmousedown = this.eventDropDown.bindAsEventListener(this)
    } else {
      if (A.image) {
        var I = document.createElement("img");
        I.src = A.image;
        G.appendChild(I)
      } else {
        if (A.picto)
        {
          var C = document.createElement("img");
          C.src = A.picto;
          C.style.marginRight = "4px";
          C.style.marginBottom = "-2px";
          G.appendChild(C)
        } else {
          if (A.icon)
          {
            var F = document.createElement("img");
            F.src = A.icon;
            F.style.marginRight = "4px";
            F.style.marginBottom = "-2px";
            G.appendChild(F)
          }
        }
        if (typeof A.text == "string") {
          G.appendChild(document.createTextNode(A.text))
        }
        else {
          G.appendChild(A.text)
        }
      }
    }
    return (G);
  },

  appendTo : function (A)
  {
    if (!this.tabSet) {
      this._createTabSet()
    }
    if (!this.selectedTab && this.tabList.hasChildNodes()) {
      this.selectTab(0, false)
    }
    $(A).appendChild(this.tabSet);
    if (typeof widget == "object" && typeof widget.callback == "function")
    {
      widget.callback("onUpdateBody")
    }
  },

  addTab : function (D, C, B)
  {
    if (!this.tabSet) {
      this._createTabSet()
    }
    if (typeof B == "undefined") {
      B = {}
    }
    var A = document.createElement("li");
    A.className = "tab " + D;
    A.setAttribute("name", D);
    if (C.disabled) {
      Element.addClassName(A, "disabled")
    } else {
      A.onclick = this.eventTabClicked.bindAsEventListener(this);
      if (B.staticText) {
        A.setAttribute("static", "static")
      }
    }
    A.appendChild(this._createTabItem(A, C, B));
    if (this.selectedTab == null) {}
    this.tabList.appendChild(A);
    this.createTabContent(D);
    this.dataItems[D] = C;
    return A;
  },

  removeTab : function (tab)
  {
    var element = this.getTab(tab);
    Element.remove(element)
  },

  setTab : function (C, B, A)
  {
    var D = this.getTab(C);
    this.dataItems[C] = UWA.merge(B, this.dataItems[C]);
    D.setHTML("");
    D.appendChild(this._createTabItem(D, this.dataItems[C], A))
  },

  addExternalLink : function (B, A)
  {
    var C = this.getTab(B);
    C.firstChild.setAttribute("href", A)
  },

  eventTabClicked : function (B)
  {
    if (Event.element(B).className == "placeHolder") {
      return false
    }
    var A = Event.findElement(B, "LI");
    if (!Element.hasClassName(A, "disabled")) {
      this.selectTab(A)
    }
    return false;
  },

  eventExtendedActionClicked : function (A)
  {
    this.hidePopupMenu();
    this._notify("extendedActionClicked");
    return false;
  },

  enableTab : function (B, A)
  {
    var C = this.getTab(B);
    if (A) {
      Element.removeClassName(C, "disabled")
    }
    else {
      Element.addClassName(C, "disabled")
    }
  },

  selectTab : function (E, I)
  {
    if (typeof E == "number" || typeof E == "string") {
      E = this.getTab(E)
    }

    var A = E.getAttribute("name");
    if (this.selectedTab && (this.selectedTab.getAttribute("name") == A) && I == undefined && !this.options.allowReload) {
      return
    }

    var G = this.tabList.getElementsByTagName("li");

    for (var D = 0, H; H = G[D]; D++)
    {
      Element.removeClassName(H, "selected");

      if (this.popupMenu) {
        this.hidePopupMenu()
      }

      if (this.options.autohideDropdowns)
      {
        var C = $(H).getElementsByClassName("dropdown");
        $A(C).each(function (J)
        {
          Element.hide(J)
        })
      }
    }
    Element.addClassName(E, "selected");

    if (this.options.autohideDropdowns)
    {
      var C = $(E).getElementsByClassName("dropdown");
      $A(C).each(function (J)
      {
        Element.show(J)
      })
    }

    for (var D = 0, F; F = this.contentArray[D]; D++)
    {
      if (Browser.isSafari && Browser.version < 3 && F.getElementsByTagName("iframe").length == 1 && F.getElementsByTagName("iframe")[0].style.width == "100%")
      {
        if (A == F.getAttribute("name")) {
          F.style.visibility = "visible";
          F.style.position = "static"
        } else {
          var B = F.getElementsByTagName("iframe")[0];
          F.style.width = B.contentWindow.innerWidth + "px";
          F.style.visibility = "hidden";
          F.style.position = "absolute";
          F.style.left = "0px";
          F.style.top = "0px";
        }
      } else {
        if (A == F.getAttribute("name")) {
          Element.show(F)
        } else {
          Element.hide(F)
        }
      }
    }
    this.selectedTab = E;
    this.selectedIndex = E.getAttribute("index");
    if (I === false) {
      return
    }
    this._notify("activeTabChange")
  },

  hide : function ()
  {
    Element.hide(this.tabSet)
  },

  show : function ()
  {
    Element.show(this.tabSet)
  },

  hideTabList : function ()
  {
    Element.hide(this.tabList)
  },

  showTabList : function ()
  {
    Element.show(this.tabList)
  },

  reload : function ()
  {
    this._notify("activeTabChange")
  },

  eventDropDown : function (B)
  {
    var A = Event.findElement(B, "LI");
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
    var C = $(A).getElementsByClassName("dropdown")[0];
    if (this.popupMenu.style.display != "none" && this.popupMenu.getAttribute("dropdownId") == C.id) {
      this.hidePopupMenu();
      return
    }
    this._showPopupMenu(A);
    this.popupMenu.setAttribute("dropdownId", C.id);
    Event.stop(B);
    return false;
  },

  getTabContent : function (B)
  {
    var D = (typeof B == "number") ? "index" : "name";
    for (var C = 0, A = this.contentArray.length; C < A; C++) {
      if (this.contentArray[C].getAttribute(D) == B) {
        return this.contentArray[C];
      }
    }
  },

  setContent : function (A, C)
  {
    var B = this.getTabContent(A);
    if (B) {
      if (typeof C == "string") {
        B.innerHTML = C
      }
      else {
        B.innerHTML = "";
        B.appendChild(C)
      }
    }
    if (typeof widget == "object" && typeof widget.callback == "function")
    {
      widget.callback("onUpdateBody")
    }
  },

  getTab : function (B)
  {
    if (typeof B == "string" || typeof B == "number")
    {
      var A = this.tabList.getElementsByTagName("li");
      for (var C = 0, D; D = A[C]; C++) {
        if (typeof B == "number" && B == C) {
          return D
        }
        if (B == D.getAttribute("name")) {
          return D;
        }
      }
    }
    return B;
  },

  observe : function (B, A)
  {
    if (!this.observers) {
      this.observers = []
    }
    this.observers.push([B, A]);
  },

  _notify : function (D)
  {
    if (!this.observers) {
      return
    }
    var C = this.selectedTab;
    var B = this.dataItems[C.getAttribute("name")];
    if (B.length)
    {
      var F = this.options.dataKey;
      for (var E = 0, A = B.length; E < A; E++) {
        if (B[E][F] == C.getAttribute("key")) {
          B = B[E];
          break
        }
      }
    }
    this.observers.each(function (G)
    {
      if (G[0] == D && typeof (G[1]) == "function")
      {
        G[1](C.getAttribute("name"), B)
      }
    })
  },

  _showPopupMenu : function (R)
  {
    this.tabItem = R;
    var D = this._getElementCumulativeOffset(R);
    this.popupMenu.innerHTML = "";
    try
    {
      var A = R.getAttribute("name");
      var C = this.dataItems[A];
      var T = R.getAttribute("key");
      for (var M = 0, P; P = C[M]; M++)
      {
        if (P[this.options.dataKey] == T && R.getAttribute("static") != "static") {
          continue
        }
        var H = document.createElement("li");
        var S = document.createElement("a");
        if (P.picto)
        {
          var I = document.createElement("img");
          I.src = P.picto;
          I.style.marginRight = "4px";
          I.style.marginBottom = "-2px";
          S.appendChild(I)
        } else {
          if (P.icon)
          {
            var N = document.createElement("img");
            N.src = P.icon;
            N.style.marginRight = "4px";
            N.style.marginBottom = "-2px";
            S.appendChild(N)
          }
        }
        S.href = (P.htmlUrl || "javascript:void(0)");
        S.setAttribute("context", A);
        S.appendChild(document.createTextNode(P.text));
        S.setAttribute("key", P[this.options.dataKey]);
        if (!this.options.extendedAction && (C.length - 1) == M) {
          Element.addClassName(S, "last")
        }
        S.onclick = this.eventPopupMenuClicked.bindAsEventListener(this);
        H.appendChild(S);
        this.popupMenu.appendChild(H)
      }
      if (this.options.extendedAction)
      {
        var H = document.createElement("li");
        var S = document.createElement("a");
        S.href = "javascript:void(0)";
        Element.addClassName(S, "action");
        S.setAttribute("context", A);
        S.appendChild(document.createTextNode(this.options.extendedAction));
        S.onclick = this.eventExtendedActionClicked.bindAsEventListener(this);
        H.appendChild(S);
        this.popupMenu.appendChild(H)
      }
    }
    catch (O) {}
    var F = Element.getDimensions(R);
    var Q = (typeof App != "undefined" && App.userCustom && (App.userCustom.themeTitle == "Coriander")) ? 0 : 1;
    if (Browser.isSafari || Browser.isOpera) {
      Q = 0
    }
    this.popupMenu.style.left = (D[0] + Q) + "px";
    this.popupMenu.style.top = (D[1] + Q + F.height) + "px";
    Element.show(this.popupMenu);
    this.popupMenu.style.width = "auto";
    var B = Element.getDimensions(this.popupMenu).width;
    if (B < F.width) {
      var Q = 12;
      if (Browser.isIE) {
        Q = 11
      }
      this.popupMenu.style.width = F.width - Q + "px"
    }
    var L = $(this.tabList).getElementsByClassName("dropped");
    $A(L).each(function (U)
    {
      Element.removeClassName(U, "dropped")
    });
    Element.addClassName(R, "dropped");
    if (typeof widget == "object" && typeof widget.callback == "function")
    {
      var P;
      var K;
      P = widget.body;
      var E = 0;
      while (P && P != document.body) {
        E += P.offsetTop;
        P = P.offsetParent
      }
      P = this.popupMenu;
      var J = 0;
      while (P && P != document.body) {
        J += P.offsetTop;
        P = P.offsetParent
      }
      popupDim = Element.getDimensions(this.popupMenu);
      bodyDim = widget.body.getDimensions();
      var G = J - E + popupDim.height;
      if (bodyDim.height < G) {
        widget.body.style.height = G + "px"
      }
      widget.callback("onUpdateBody");
    }
  },

  eventPopupMenuClicked : function (F)
  {
    var D = Event.findElement(F, "A");
    var E = D.getAttribute("context");
    var H = this.getTab(E);
    if (H.getAttribute("static") != "static")
    {
      var G = this.options.dataKey;
      var B;
      for (var C = 0, A = this.dataItems[E].length; C < A; C++) {
        if (this.dataItems[E][C][G] == D.getAttribute("key")) {
          B = this.dataItems[E][C];
          break
        }
      }
      H.getElementsByTagName("span")[0].innerHTML = B.text;
      if (B.picto) {
        H.getElementsByTagName("img")[0].src = B.picto
      }
      else {
        if (B.icon) {
          H.getElementsByTagName("img")[0].src = B.icon;
        }
      }
    }
    H.setAttribute("key", D.getAttribute("key"));
    this._notify("activeTabChange");
    this.selectTab(H);
    this.hidePopupMenu();
    Event.stop(F);
    return false;
  },

  selectKey : function (D, F, A)
  {
    var H = this.getTab(D);
    var C = null;
    var G = this.options.dataKey;
    for (var E = 0, B = this.dataItems[D].length; E < B; E++) {
      if (this.dataItems[D][E][G] == F) {
        C = this.dataItems[D][E];
        break
      }
    }
    if (C)
    {
      H.getElementsByTagName("span")[0].innerHTML = C.text;
      H.setAttribute("key", F);
      if (C.icon) {
        H.getElementsByTagName("img")[0].src = C.icon
      }
      if (A == undefined || A) {
        this.selectTab(H)
      }
    }
  },

  hidePopupMenu : function (A)
  {
    if (!this.popupMenu || (A && Event.element(A).tagName == "A")) {
      return false
    }
    Element.hide(this.popupMenu);
    var B = $(this.tabList).getElementsByClassName("dropped");
    $A(B).each(function (C)
    {
      Element.removeClassName(C, "dropped")
    });
    if (typeof widget == "object" && typeof widget.callback == "function")
    {
      widget.body.style.height = "";
      widget.callback("onUpdateBody")
    }
  },

  createTabContent : function (B, A)
  {
    var C = document.createElement("div");
    if (this.options.softPadding)
    {
      if (Browser.isIE) {
        C.style.padding = "3px 3px 3px 3px"
      }
      else {
        C.style.padding = "6px 3px 3px 3px";
      }
    }
    C.className = this.options.classTabContent + " " + B;
    C.setAttribute("name", B);
    C.innerHTML = _("Loading...");
    this.tabSet.appendChild(C);
    if (!this.contentArray) {
      this.contentArray = []
    }
    this.contentArray.push(C);
    C.setAttribute("tabIndex", this.contentArray.length - 1)
  },

  destroy : function ()
  {
    $(document.body).removeEvent("mousedown", this.bindedHidePopupMenu)
  },

  _getElementCumulativeOffset : function (B)
  {
    var A = 0, C = 0;
    do {
      A += B.offsetTop || 0;
      C += B.offsetLeft || 0;
      B = B.offsetParent
    }
    while (B);
    return [C, A];
  }
};
