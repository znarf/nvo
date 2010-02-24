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

UWA.Data.Storage.Dom = function(database) {
    if(this.initialize) this.initialize(database);
}

UWA.Data.Storage.Dom.prototype = Object.extend(UWA.Data.Storage.Abstract.prototype, {

    connect: function() {

        // The type of storage engine
        this.type = 'HTML5';

        // Set the Database limit
        this.limit = 1024 * 200;

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

    updateCache: function(){
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
});

