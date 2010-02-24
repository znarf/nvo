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
        this.type = 'DOM';

        // Set the Database limit
        this.limit = 5 * 1024 * 1024;

        if (this.isAvailableLocal()) {
            this.db = !window.globalStorage ? window.localStorage : window.globalStorage[location.hostname];
        } else if (this.isAvailableSession()) {
            this.db = sessionStorage;
        }

        this.isReady = true;
    },

    isAvailable: function() {
        return this.isAvailableSession() || this.isAvailableLocal();
    },

    isAvailableSession: function() {
        return !!window.sessionStorage;
    },

    isAvailableLocal: function() {
        return !!(window.localStorage || window.globalStorage);
    },

    get: function(key) {
        this.interruptAccess();
        var out = this.db.getItem(this.database + '-' + key);

        // Gecko's getItem returns {value: 'the value'}, WebKit returns 'the value'
        return this.safeResurrect((out && out.value ? out.value : out));
    },

    set: function(key, value) {
        this.interruptAccess();
        this.db.setItem(this.database + '-' + key,this.safeStore(value));
        return value;
    },

    rem: function(key) {
        this.interruptAccess();
        var out = this.get(this.database + '-' + key);
        this.db.removeItem(this.database + '-' + key);
        return out
    }
});

