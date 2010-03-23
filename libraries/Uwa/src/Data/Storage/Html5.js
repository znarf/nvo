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

UWA.Data.Storage.Html5 = function() {

  // The type of storage engine
  this.type = 'HTML5';

  // Set the Database limit
  this.limit = 1024 * 200;

  if(this.initialize) this.initialize();
}

UWA.Data.Storage.Html5.prototype = UWA.merge({

  connect: function(database) {
    // The type of storage engine
    this.database = database;

    // Create our database connection
    var db = this.db = openDatabase('uwa-data-storage-' + this.database, '1.0', this.database, this.limit);
    if (!db) throw 'JSTORE_ENGINE_HTML5_NODB';
    db.transaction(function(db){
        db.executeSql( 'CREATE TABLE IF NOT EXISTS data (k TEXT UNIQUE NOT NULL PRIMARY KEY, v TEXT NOT NULL)' );
    });

    // Cache the data from the table
    this.updateCache();

    this.isReady = true;
  },

  updateCache: function() {
    var self = this;
    // Read the database into our cache object
    this.db.transaction(function(db){
        db.executeSql( 'SELECT k,v FROM data', [], function(db, result) {
            var rows = result.rows, i = 0, row;
            for (; i < rows.length; ++i){
                row = rows.item(i);
                self.data[row.k] = self.safeResurrect(row.v);
            }
        });
    });
  },

  isAvailable: function() {
    return !!window.openDatabase
  },

  get: function(key) {
    this.interruptAccess();
    //@todo
  },

  set: function(key, value) {
    this.interruptAccess();
    // Update the database
    this.db.transaction(function(db){
        db.executeSql( 'INSERT OR REPLACE INTO data(k, v) VALUES (?, ?)', [key,this.safeStore(value)]);
    });
    return this._super(key, value);
  },

  rem: function(key) {
    this.interruptAccess();
    // Update the database
    this.db.transaction(function(db){
        db.executeSql( 'DELETE FROM data WHERE k = ?', [key] )
    })
    return this._super(key);
  }
}, UWA.Data.Storage.Abstract.prototype);

