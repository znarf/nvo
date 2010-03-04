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

UWA.extend(UWA.Environment.prototype, {

  growboxInset: null,

  minWidth: 358,
  minHeight: 600,

  initialize: function() {
    this.dashboard = {};
  },

  onInit: function() {
    this.html['body']       = $('moduleContent');
    this.html['header']     = $('moduleHeader');
    this.html['title']      = $('moduleTitle');
    this.html['icon']       = $('moduleIcon');
    this.html['edit']       = $('editContent');
    this.html['status']     = $('moduleStatus');
    this.html['editLink']   = $('editLink');

  },

  onRegisterModule: function(module) {

    for (var key in this.html) {
      this.widget.elements[key] = UWA.extendElement(this.html[key]);
    }

    this.widget.body = this.widget.elements['body'];

    this.widget.elements['editLink'].empty().show().addClassName('infoButton');
    new AppleInfoButton(this.html['editLink'], $('wrapper'), "black", "white", this.showDashboardPrefs.bind(this));

    this.callback('onUpdatePreferences');


    var resizeButon = UWA.createElement('img', {
        attributes: {
          id: 'resizeButton',
          src: '/System/Library/WidgetResources/resize.png'
        },
        events: {
            mousedown: this.eventResizeButtonDown.bind(this),
        }
      }
    );

    this.html['resizeButon'] = resizeButon;
    this.html['status'].appendChild(resizeButon);

  },

  showDashboardPrefs: function() {

    var editContent = this.widget.elements['edit'];

    this.widget.elements['edit'].empty();

    if (this.hasPreferences()) {
      this.prefsForm = new Netvibes.UI.PrefsForm( { module: this.widget, displayButton: (window.widget ? false : true) } );
      this.form = this.widget.elements['edit'].appendChild(this.prefsForm.getContent());
    }

    if (window.widget) {
      if (this.form) {
        this.form.onsubmit = function() { return false }; // desactive browser default form handling
      }
      var doneButton = UWA.createElement('div');
      doneButton.setAttribute('id','doneButton');
      this.widget.elements['edit'].appendChild(doneButton);
      doneButton = new AppleGlassButton(doneButton, "Done", this.hideDashboardPrefs.bind(this) );
      doneButton.textElement.style.color = '#333';
    }

    var infos = this.widget.getInfos();
    if (infos) {
      this.widget.elements['edit'].addContent(infos);
    }

    if (this.widget.uwaUrl) {
      var upgrade = UWA.createElement('a', {
        href:  UWA_WIDGET + '/dashboard/?uwaUrl=' + encodeURIComponent(this.widget.uwaUrl)
      }).setStyle(
        {'display': 'block', 'padding': '10px', 'text-align': 'right'}
      ).setHTML(
        _('Recompile this widget')
      ).inject(editContent);
      // to handle links with window.widget.openURL
      this.callback('onUpdateBody');
    }

    if (window.widget) {
      window.widget.prepareForTransition("ToBack");
    }

    this.widget.elements['body'].hide();
    this.widget.elements['edit'].show();
    this.widget.elements['editLink'].hide();

    this.widget.callback('onShowEdit', this.widget.elements['edit']);

    if (window.widget) {
      setTimeout("window.widget.performTransition()", 0);
    }

  },

  hideDashboardPrefs: function() {

    if (this.prefsForm) {
      this.prefsForm.saveValues();
    }

    if (window.widget) {
      this.html['editLink'].show();
      // freezes the widget so that you can change it without the user noticing
      window.widget.prepareForTransition("ToFront");
    }

    this.widget.elements['body'].show();
    this.widget.elements['edit'].hide();

    if (this.widget.onRefresh) {
      this.widget.onRefresh();
    } else if (this.widget.onLoad) {
      this.widget.onLoad();
    }

    // and flip the widget over
    if (window.widget) {
      setTimeout("window.widget.performTransition()", 250);
    }

  },

  hasPreferences: function() {
    return this.widget.preferences.some(function(pref){
      return pref.type != 'hidden';
    });
  },

  getData: function(name) {
    if (this.data[name]) {
      var value = this.data[name];
    } else if (window.widget) {
      var value = window.widget.preferenceForKey(this.createkey(name));
    }
    return value;
  },

  setData: function(name, value) {
    this.data[name] = value;
    if (window.widget) {
      return window.widget.setPreferenceForKey(value, this.createkey(name));
    }
  },

  createkey: function(key) {
    if (window.widget) {
      return window.widget.identifier + "-" + key;
    }
    return key;
  },

  onUpdateBody: function() {
    var content = document.body || this.widget.body;
    var links = content.getElementsByTagName('a');
    for (var i = 0, lnk; lnk = links[i]; i++) {
      if (typeof lnk.onclick != "function") {
        lnk.onclick = function() {
          if (window.widget) {
            window.widget.openURL(this.href);
          } else {
            window.open(this.href);
          }
          return false;
        }
      }
    }
  },

  openURL: function(url) {
    if (window.widget) {
      return window.widget.openURL(url);
    } else {
      return window.open(url);
    }
  },

  eventResizeButtonMove: function(event) {

    if (this.growboxInset == -1) {
        return;
    }

    var x = event.x + this.growboxInset.x;
    var y = event.y + this.growboxInset.y;

    // check min sizes
    x = (x <  this.minWidth ? this.minWidth : x);
    y = (y <  this.minHeight ? this.minHeight : y);

    document.getElementById("resizeButton").style.top = (y-12);
    window.resizeTo(x,y);

    event.stopPropagation();
    event.preventDefault();
  },

  eventResizeButtonDown: function(event) {

    document.addEventListener("mousemove", this.eventResizeButtonMove.bind(this), true);
    document.addEventListener("mouseup", this.eventResizeButtonUp.bind(this), true);

    this.growboxInset = {x:(window.innerWidth - event.x), y:(window.innerHeight - event.y)};

    event.stopPropagation();
    event.preventDefault();

    this.callback('onUpdateBody');
    this.callback('onResize');
  },

  eventResizeButtonUp: function(event) {

    this.growboxInset = -1;

    document.removeEventListener("mousemove", this.eventResizeButtonMove.bind(this), true);
    document.removeEventListener("mouseup", this.eventResizeButtonUp.bind(this), true);

    event.stopPropagation();
    event.preventDefault();

    this.callback('onUpdateBody');
    this.callback('onResize');
  }

});

