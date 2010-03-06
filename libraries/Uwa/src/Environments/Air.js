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

    for (var key in this.html) {
      this.widget.elements[key] = UWA.extendElement(this.html[key]);
    }

    this.widget.body = this.widget.elements['body'];

    this.html['editLink'].show();
    this.html['editLink'].addEvent("click", function() {
      this.callback('toggleEdit');
      return false;
    }.bind(this));

    this.module.elements['header'].addEvent("mousedown", function() {
      this.callback('onMove');
      return false;
    }.bind(this));

    /*
    var refreshLink = UWA.$element( this.module.elements['header'].getElementsByClassName('refresh')[0] );
   refreshLink.addEvent("click", function() {
     widget.callback('onRefresh');
     return false;
   });

   var closeLink = UWA.$element( this.module.elements['header'].getElementsByClassName('close')[0] );
   closeLink.addEvent("click", function() {
     widget.callback('onClose');
     return false;
   });

   var closeLink = UWA.$element( this.module.elements['header'].getElementsByClassName('minimize')[0] );
   closeLink.addEvent("click", function() {
     widget.callback('onMinimize');
     return false;
   });

   var closeLink = UWA.$element( this.module.elements['header'].getElementsByClassName('maximize')[0] );
   closeLink.addEvent("click", function() {
     widget.callback('onMaximize');
     return false;
   });

   var refreshLink = UWA.$element( this.module.elements['header'].getElementsByClassName('refresh')[0] );
   refreshLink.addEvent("click", function() {
     widget.callback('onRefresh');
     return false;
   });

   var refreshLink = UWA.$element( this.module.elements['header'].getElementsByClassName('refresh')[0] );
   refreshLink.addEvent("click", function() {
     widget.callback('onRefresh');
     return false;
   });
    */

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

  getData: function(name) {

    this.log('getData:' + name);

    if(typeof(document.cookie) != "undefined") {
      var name = 'uwa-' + name;
      var index = document.cookie.indexOf(name);
      if ( index != -1) {
        var nStart = (document.cookie.indexOf("=", index) + 1);
        var nEnd = document.cookie.indexOf(";", index);
        if (nEnd == -1) {
          var nEnd = document.cookie.length;
        }
        return unescape(document.cookie.substring(nStart, nEnd));
      }
    }
  },

  setData: function(name, value) {

   this.log('setData:' + name + ':' + value);

   if (typeof(document.cookie) != "undefined") { // Valid cookie ?
     var name = 'uwa-' + name;
     var expires = 3600 * 60 * 24; // 24 days by default
     var expires_date = new Date( new Date().getTime() + (expires) );
     var cookieData = name + "=" + escape(value) + ';' +
       ((expires) ? "expires=" + expires_date.toGMTString() + ';' : "") +
       "path=" + escape(window.location.pathname) + ';' +
       "domain=" + escape(window.location.hostname);

       document.cookie = cookieData;
       return true;
     }
     return false;
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
