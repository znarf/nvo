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

UWA.Data.Storage.IE = function() {

  // The type of storage engine
  this.type = 'IE';

  // Set the Database limit
  this.limit = 64 * 1024;

  if(this.initialize) this.initialize();
}

UWA.Data.Storage.IE.prototype = UWA.merge({

  connect: function(database) {

    // The type of storage engine
    this.database = database;

    // Create a hidden div to store attributes in
    this.db = $('<div style="display:none;behavior:url(\'#default#userData\')" id="uwa-data-storage-' + this.database + '"></div>')
                .appendTo(document.body).get(0);

    this.isReady = true;
  },

  isAvailable: function() {
    return !!window.ActiveXObject;
  },

  get: function(key) {
    this.interruptAccess();
    this.db.load(this.database);
    return this.safeResurrect( this.db.getAttribute(key) );
  },

  set: function(key, value) {
    this.interruptAccess();
	this.db.setAttribute(key, this.safeStore(value));
	this.db.save(this.database);
	return value;
  },

  rem: function(key) {
    this.interruptAccess();
    var beforeDelete = this.get(key);
    this.db.removeAttribute(key);
    this.db.save(this.database);
    return beforeDelete;
  }
}, UWA.Data.Storage.Abstract.prototype);

