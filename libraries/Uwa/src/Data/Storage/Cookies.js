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

/*
Credits:
  Partially based on MooTools, My Object Oriented Javascript Tools.
  Copyright (c) 2006-2007 Valerio Proietti, <http://mad4milk.net>, MIT Style License.
  Partially based on Prototype JavaScript framework, version 1.6.0 (c) 2005-2007 Sam Stephenson.
  Prototype is freely distributable under the terms of an MIT-style license.
  For details, see the Prototype web site: http://www.prototypejs.org/
*/

if (typeof UWA.Data == "undefined") UWA.Data = {};
if (typeof UWA.Data.Storage == "undefined") UWA.Data.Storage = {};

UWA.Data.Storage.Cookies = function() {

  // The type of storage engine
  this.type = 'Cookies';

  // Set the Database limit
  this.limit = 1024 * 4;

  if(this.initialize) this.initialize();
}

UWA.Data.Storage.Cookies.prototype = UWA.merge({

  connect: function(database) {

    // The type of storage engine
    this.database = database;

    this.isReady = true;
  },

  isAvailable: function() {
    return typeof(document.cookie) != "undefined";
  },

  get: function(key) {
    this.interruptAccess();

    var name = 'uwa-' + this.database + '-' + key;
    var index = document.cookie.indexOf(name);

    if ( index != -1) {
      var nStart = (document.cookie.indexOf("=", index) + 1);
      var nEnd = document.cookie.indexOf(";", index);

      if (nEnd == -1) {
        var nEnd = document.cookie.length;
      }

      return unescape(document.cookie.substring(nStart, nEnd));
    }

    return null;
  },

  set: function(key, value) {

    this.interruptAccess();

    var name = 'uwa-' + this.database + '-' + key;
    var expires = 3600 * 60 * 24; // 24 days by default
    var expires_date = new Date( new Date().getTime() + (expires) );
    var cookieData = name + "=" + escape(value) + ';' +
      ((expires) ? "expires=" + expires_date.toGMTString() + ';' : "") +
      "path=" + escape(window.location.pathname) + ';' +
      "domain=" + escape(window.location.hostname);

    document.cookie = cookieData;

    return value;
  },

  rem: function(key) {

    this.interruptAccess();

    var out = this.get(key);
    this.set(key, null);

    return out;
  }
}, UWA.Data.Storage.Abstract.prototype);

