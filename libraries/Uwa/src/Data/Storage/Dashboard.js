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
  Partially based on jStore JavaScript, version 1.0
  Copyright (c) 2009 Eric Garside (http://eric.garside.name)
*/

if (typeof UWA.Data == "undefined") UWA.Data = {};
if (typeof UWA.Data.Storage == "undefined") UWA.Data.Storage = {};

UWA.Data.Storage.Dashboard = function() {

  // The type of storage engine
  this.type = 'Dashboard';

  // Set the Database limit
  this.limit = 64 * 1024;

  if(this.initialize) this.initialize();
}

UWA.Data.Storage.Dashboard.prototype = UWA.merge({

  connect: function(database) {

    // The type of storage engine
    this.database = database;

    this.isReady = true;
  },

  isAvailable: function() {
      return !!window.widget && !!UWA.Client.Platform.mac;
  },

  get: function(key) {
    this.interruptAccess();

    return window.widget.preferenceForKey(this.createkey(name));
  },

  set: function(key, value) {
    this.interruptAccess();

    return window.widget.setPreferenceForKey(value, this.createkey(name));
  },

  createkey: function(key) {
    if (window.widget) {
      return window.widget.identifier + '-' + this.database + '-' + key;
    }
    return key;
  },

  rem: function(key) {
    this.interruptAccess();
    var beforeDelete = this.get(key);

    this.set(key, null);

    return beforeDelete;
  }
}, UWA.Data.Storage.Abstract.prototype);

