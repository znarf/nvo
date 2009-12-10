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

UWA.extend(UWA.Environment.prototype, {

  initialize: function() {
    this.google = { 'inline':false, 'iframed':true };
    if (window._IG_Prefs) this.prefs = new _IG_Prefs();
  },

  onRegisterModule: function(module) {

    this.html['body']       = $('moduleContent');
    this.html['status']     = $('moduleStatus');

    for (var key in this.html) {
      this.widget.elements[key] = UWA.extendElement(this.html[key]);
    }

    this.widget.body = this.widget.elements['body'];

    this.setPeriodical('handleResizePeriodical', this.handleResize, 250);
  },

  getData: function(name) {
    if (this.prefs) {
      var value = this.prefs.getString(name);
      if (value == undefined || value == null || value == '' ) return undefined;
      return value;
    }
  },

  setData: function(name, value) {
    if (this.prefs) return this.prefs.set(name, value);
  },

  onUpdateBody: function() {
    this.setDelayed('handleLinks', this.handleLinks, 100);
    this.setDelayed('handleResize', this.handleResize, 100);
  },

  handleLinks: function() {
    var links = this.module.body.getElementsByTagName('a');
    for (var i = 0, lnk; lnk = links[i]; i++) {
      lnk.target = '_blank'; // problem with javascript void(0) links
    }
  },

  handleResize: function() {
    // Calculate total widget height by adding widget body/header/status height
    // note: document.body.offsetHeight is wrong in IE6 as it always return the full windows/iframe height
    var height = parseInt(this.html.body.offsetHeight);
      height += (this.html.header) ? this.html.header.offsetHeight : 0;
      height += (this.html.status) ? this.html.status.offsetHeight : 0;

    if (height > 0 && height != this.prevHeight) {
      var delay = (height > 100 || height > this.prevHeight) ? 0 : 2500;
      this.setDelayed("resizeFrameHeight", function () {
        if (window._IG_AdjustIFrameHeight) {
          _IG_AdjustIFrameHeight(height)
        }
      }, delay, false)
    }

    this.prevHeight = height;
  },

  setTitle: function(title) {
    title = title.stripTags();
    if (window._IG_SetTitle) _IG_SetTitle(title);
  },

  onUpdatePreferences: function() {
    // fix boolean
    for (var i = 0 ; i < widget.preferences.length; i++) {
      var pref = widget.preferences[i];
      if (pref.type == 'boolean' && widget.data[pref.name] == '1') {
        widget.data[pref.name] = 'true';
      }
    }
  }

});

var Environment = new UWA.Environment();

var widget = Environment.getModule();
