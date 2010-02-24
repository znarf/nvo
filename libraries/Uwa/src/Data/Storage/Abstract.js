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

UWA.Data.Storage.Abstract = function(database) {
    if(this.initialize) this.initialize(database);
}

UWA.Data.Storage.Abstract.prototype = {

    initialize: function(database){

        try {
            this.rxJson = new RegExp('^("(\\\\.|[^"\\\\\\n\\r])*?"|[,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t])+?$')
        } catch (e) {
            this.rxJson = /^(true|false|null|\[.*\]|\{.*\}|".*"|\d+|\d+\.\d+)$/
        }

        // Configure the database name
        this.database = database;

        // Cache the data so we can work synchronously
        this.data = {};

        // When set, we're ready to transact data
        this.isReady = false;

        // The maximum limit of the storage engine
        this.limit = -1;

        this.connect(database);
    },

    // This should be overloaded with an actual functionality init
    init: function(){
        throw new Exception('Unable to init UWA.Storage.Abstract');
    },

    // This should be overloaded with an actual functionality presence check
    isAvailable: function(){
        return false;
    },

    // All get/set/rem functions across the engines should add this to the
    // first line of those functions to prevent accessing the engine while unstable.
    interruptAccess: function(){
        if (!this.isReady) throw 'ENGINE_NOT_READY';
    },


    get: function(key){
        this.interruptAccess();
        return this.data[key] || null;
    },

    set: function(key, value){
        this.interruptAccess();
        this.data[key] = value;
        return value;
    },

    rem: function(key){
        this.interruptAccess();
        var beforeDelete = this.data[key];
        this.data[key] = null;
        return beforeDelete;
    },

    // Parse a value as JSON before its stored.
    safeStore: function(value){
        switch (typeof value){
            case 'object':
            case 'function':
                return UWA.Json.compactJSON(value);

            case 'number':
            case 'boolean':
            case 'string':
            case 'xml':
                return value;

            case 'undefined':
            default:
                return '';
        }
    },

    // Restores JSON'd values before returning
    safeResurrect: function(value){
        return this.rxJson.test(value) ? UWA.Json.evalJSON(value) : value;
    }
}
