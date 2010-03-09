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

UWA.Data.useJsonRequest = false;

// Overload UWa log function
UWA.log = function(msg) {
    air.trace(msg);
}

UWA.extend(UWA.Environment.prototype, {

  initialize: function() {
    this.air = {};
  },

  onInit: function() {
    this.html['body']         = $('moduleContent');
    this.html['header']       = $('moduleHeader');
    this.html['title']        = $('moduleTitle');
    this.html['icon']         = $('moduleIcon');
    this.html['edit']         = $('editContent');
    this.html['status']       = $('moduleStatus');
    this.html['closeLink']    = $('closeLink');
    this.html['refreshLink']  = $('refreshLink');
    this.html['editLink']     = $('editLink');
    this.html['minimizeLink'] = $('minimizeLink');

    // for debug only
    air.EncryptedLocalStore.reset();
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

    // Handle move widget
    this.module.elements['header'].addEvent("mousedown", function() {
      this.callback('onMove');
      return false;
    }.bind(this));


    // Handle Refresh link click
    this.html['refreshLink'].show();
    this.html['refreshLink'].addEvent("click", function() {
      this.callback('onRefresh');
      return false;
    }.bind(this));

    // Handle Close link click
    this.html['closeLink'].show();
    this.html['closeLink'].addEvent("click", function() {
      this.callback('onClose');
      return false;
    }.bind(this));

    // Handle collapse link click
    this.html['minimizeLink'].show();
    this.html['minimizeLink'].addEvent("click", function() {
      this.callback('onMinimize');
      return false;
    }.bind(this));

    // Handle Resize
    this.setPeriodical('handleResizePeriodical', this.handleResize, 250);
  },

  toggleEdit: function() {
    if (this.widget.elements['edit'].style.display == 'none') {
      this.widget.callback('onEdit');
    } else {
      // note that we don't fire 'endEdit' there because we don't want to save form data
      this.widget.elements['edit'].hide();
      this.widget.elements['editLink'].setHTML( _("Edit") );
    }
  },

  onUpdateBody: function() {
    this.setDelayed('handleLinks', this.handleLinks, 100);
    this.setDelayed('handleResize', this.handleResize, 100);
  },

  handleResize: function() {
      // Calculate total widget height by adding widget body/header/status height
      // note: document.body.offsetHeight is wrong in IE6 as it always return the full windows/iframe height
      var height = parseInt(this.html['body'].offsetHeight);
      height += (this.html['header']) ? this.html['header'].offsetHeight : 0;
      height += (this.html['status']) ? this.html['status'].offsetHeight : 0;
      height += (this.html['edit']) ? this.html['edit'].offsetHeight : 0;
      if (height > 0 && height != this.prevHeight) {
        window.nativeWindow.height = height+5;
      }
      this.prevHeight = height;
  },

  getData: function(name) {

    this.log('getData:' + name);

    return air.EncryptedLocalStore.getItem(name);
  },

  setData: function(name, value) {

    this.log('setData:' + name + ':' + value);

    var bytes = new air.ByteArray();
    bytes.writeUTFBytes(value);
    air.EncryptedLocalStore.setItem(name, bytes);

    return value;
  },

  createkey: function(key) {
    if (window.widget) {
      return window.widget.identifier + "-" + key;
    }
    return key;
  },

  openURL: function(url) {
    if (window.widget) {
      return window.widget.openURL(url);
    } else {
      return window.open(url);
    }
  },

  onClose: function() {
    window.nativeWindow.close();
  },

  onMinimize: function() {
    window.nativeWindow.minimize();
  },

  onMaximize: function() {

    if (!this.maximized) {
      window.nativeWindow.maximize();
      this.maximized = true;
    } else {
      window.nativeWindow.restore();
    }
  },

  onMove: function() {
    nativeWindow.startMove();
  }
});
