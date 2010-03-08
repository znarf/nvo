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

UWA.Data.Storage.Flash = function() {

  // The type of storage engine
  this.type = 'Flash';

  // Set the Database limit
  this.limit = 5 * 1024 * 1024;

  this.flashProxyPath = '';

  if(this.initialize) this.initialize();
}

UWA.Data.Storage.Flash.prototype = UWA.merge({

  connect: function(database) {

    // set current database
    this.database = database;

    var name = 'uwa-data-storage-flash-' + this.database;

    this.db = !window.globalStorage ? window.localStorage : window.globalStorage[location.hostname];

    // To make Flash Storage work on IE, we have to load up an iFrame
    // which contains an HTML page that embeds the object using an
    // object tag wrapping an embed tag. Of course, this is unnecessary for
    // all browsers except for IE, which, to my knowledge, is the only browser
    // in existance where you need to complicate your code to fix bugs. Goddamnit. :(
    /*
    $(document.body).append('<iframe style="height:1px;width:1px;position:absolute;left:0;top:0;margin-left:-100px;" ' +
            'id="jStoreFlashFrame" src="' + this.flashProxyPath + '"></iframe>');
            */

    this.isReady = true;
  },

  isAvailable: function() {
    return !!(this.hasFlash('8.0.0'));
  },

  get: function(key) {
    this.interruptAccess();
    var out = this.db.f_get_cookie(key);
    return out == 'null' ? null : this.safeResurrect(out);
  },

  set: function(key, value) {
    this.interruptAccess();
    this.db.f_set_cookie(key, this.safeStore(value));
    return value;
  },

  rem: function(key) {
    this.interruptAccess();
    var beforeDelete = this.get(key);
    this.db.f_delete_cookie(key);
    return beforeDelete;
  },

   hasFlash: function(version) {
    var pv = this.flashVersion().match(/\d+/g),
    rv = version.match(/\d+/g);

    for(var i = 0; i < 3; i++) {
      pv[i] = parseInt(pv[i] || 0);
      rv[i] = parseInt(rv[i] || 0);
      // player is less than required
      if(pv[i] < rv[i]) return false;
      // player is greater than required
      if(pv[i] > rv[i]) return true;
    }

    // major version, minor version and revision match exactly
    return true;
   },

   flashVersion: function() {

     // ie
	 try {
	 	try {
	      // avoid fp6 minor version lookup issues
	      // see: http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
	      var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
	      try { axo.AllowScriptAccess = 'always';	}
	      catch(e) { return '6,0,0'; }

	 	} catch(e) {

        }

	    return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];
	 // other browsers
	 } catch(e) {

	 	try {
          if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin){
	 		return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
	      }
	 	} catch(e) {}
	 }

	 return '0,0,0';
   }
}, UWA.Data.Storage.Abstract.prototype);

