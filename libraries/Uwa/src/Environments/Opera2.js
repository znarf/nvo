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

UWA.Data.useJsonRequest = true;

UWA.extend(UWA.Environment.prototype, {

  initialize: function() {
    this.opera = {};
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

    // Map element with UWA.Element
    for (var key in this.html) {
      this.module.elements[key] = UWA.extendElement(this.html[key]);
    }

    this.module.body = this.module.elements['body']; // shortcut

    // Handle Edit link click
    this.html['editLink'].show();
    this.html['editLink'].addEvent("click", function() {
      this.callback('toggleEdit');
      return false;
    }.bind(this));
  },

  toggleEdit: function() {
    if (this.module.elements['edit'].style.display == 'none') {
      this.module.callback('onEdit');
    } else {
      this.module.callback('endEdit');
    }
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

  /* to document */
  getModuleUrl: function(uwaUrl, platform) {
      var platform = typeof platform == "undefined" ? 'opera' : platform;
      return UWA_WIDGET + '/' + platform + '?uwaUrl=' + encodeURIComponent(uwaUrl);
  },

  openURL: function(url) {
    if (window.widget) {
      return window.widget.openURL(url);
    } else {
      return window.open(url);
    }
  }
});

