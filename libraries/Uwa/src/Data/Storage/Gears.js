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

UWA.Data.Storage.Gears = function() {

   // The type of storage engine
    this.type = 'Google Gears';

    // Set the Database limit
    this.limit = 5 * 1024 * 1024;




    if(this.initialize) this.initialize();
}

UWA.Data.Storage.Gears.prototype = UWA.merge({

    connect: function(database) {

        // The type of storage engine
        this.database = database;

        // Add required third-party scripts
        this.include('http://code.google.com/apis/gears/gears_init.js');

        console.info('wait');

        while(!(window.google && window.google.gears)) {
            console.info('.');
        }

        console.info('ok');

         // Create our database connection
        var db = this.db = google.gears.factory.create('beta.database');
        db.open( 'uwa-' + this.database );
        db.execute( 'CREATE TABLE IF NOT EXISTS data (k TEXT UNIQUE NOT NULL PRIMARY KEY, v TEXT NOT NULL)' );

        // Cache the data from the table
        this.updateCache();

        this.isReady = true;
    },

    isAvailable: function() {
        return true;
        return !!(window.google && window.google.gears);
    },

    updateCache: function(){
        // Read the database into our cache object
        var result = this.db.execute( 'SELECT k,v FROM data' );
        while (result.isValidRow()){
            this.data[result.field(0)] = this.safeResurrect( result.field(1) );
            result.next();
        } result.close();
    },

    set: function(key, value){
        this.interruptAccess();

        // Update the database
        var db = this.db;
        db.execute( 'BEGIN' );
        db.execute( 'INSERT OR REPLACE INTO data(k, v) VALUES (?, ?)', [key,this.safeStore(value)] );
        db.execute( 'COMMIT' );

        return value;
    },
    rem: function(key){
        this.interruptAccess();

        var out = this.get(key);

        // Update the database
        var db = this.db;
        db.execute( 'BEGIN' );
        db.execute( 'DELETE FROM data WHERE k = ?', [key] );
        db.execute( 'COMMIT' );

        return out;
    }
}, UWA.Data.Storage.Abstract.prototype);

