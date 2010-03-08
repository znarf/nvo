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

UWA.Data.Storage.Abstract = function() {

  // The maximum limit of the storage engine
  this.limit = -1;

  if(this.initialize) this.initialize();
}

UWA.Data.Storage.Abstract.prototype = {

  initialize: function(){

    try {
        this.rxJson = new RegExp('^("(\\\\.|[^"\\\\\\n\\r])*?"|[,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t])+?$')
    } catch (e) {
        this.rxJson = /^(true|false|null|\[.*\]|\{.*\}|".*"|\d+|\d+\.\d+)$/
    }

    // Configure the database name
    this.database = null;

    // Cache the data so we can work synchronously
    this.data = {};

    // Third party script includes
    this.includes = [];

    this.delays = [];

    // When set, we're ready to transact data
    this.isReady = false;
  },

  // This should be overloaded with an actual functionality init
  init: function(){
    throw new ('Unable to init UWA.Storage.Abstract.');
  },

  // This should be overloaded with an actual functionality presence check
  isAvailable: function(){
    return false;
  },

  // All get/set/rem functions across the engines should add this to the
  // first line of those functions to prevent accessing the engine while unstable.
  interruptAccess: function(){
    if (!this.isReady) throw new ('Engine is not ready.');
  },

  // Performs all necessary script includes
  include: function(url){

    var script = document.createElement('script');
    script.setAttribute('type', 'text/javascript');
    script.src = url;

    var head = document.getElementsByTagName('head')[0];
    var insert = head.appendChild(script);
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
  },

  /* Method: setDelayed

  Registers a function as delayed event.

  If 'bind' is not defined, the function will automatically be bound to the current environment object.

  Parameters:
    * String name: the name of the event
    * Function fn: the function to register
    * Integer delay: the delay in milliseconds
    * Object bind: A javascript object to bind the function to.

  Notes:
    internal or advanced use only

  */
  setDelayed: function(name, fn, delay, bind) {
    this.clearDelayed(name);
    if(typeof bind == "undefined" || bind === true) fn = fn.bind(this);
    this.delays[name] = setTimeout(fn, delay);
  },

  /* Method: clearDelayed

  Unregister a delayed event previously registered with <setDelayed>

  Parameters:
    * String name: the name of the event

  Notes:
    internal or advanced use only

  */
  clearDelayed: function(name) {
    if (this.delays[name]) { clearTimeout(this.delays[name]) }
  }
}
