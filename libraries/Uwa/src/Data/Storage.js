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
Class: Storage

The Storage class provides abstract methods to access client side resources using localeStorage

Credits:
  Partially based on MooTools, My Object Oriented Javascript Tools.
  Copyright (c) 2006-2007 Valerio Proietti, <http://mad4milk.net>, MIT Style License.
  Partially based on jStore JavaScript, version 1.0
  Copyright (c) 2009 Eric Garside (http://eric.garside.name)
*/

UWA.Data.Storage = function(options) {

  //
  this.EnginesInstances = [];

  // The current engine to use for storage
  this.CurrentEngine = null;

  // Provide global settings for overwriting
  this.defaults = {
    database: 'default',
    engine: null,
    availableEngine: [
      'Gears',
      'Dom',
      'Html5',
      'IE',
      'Flash',
      'Gears',
      'Cookies'
    ]
  },

  // Global settings
  this.options = {};

  // Boolean for ready state handling
  this.isReady = false;

  /* Property: debugMode

  *Boolean*: activates or desactivates the debug mode for the widget.

  The default value is TRUE. When TRUE, messages written with <log> method will appear in the console.
  */
  this.debugMode = false;

  if(this.initialize) this.initialize(options);
}

UWA.Data.Storage.prototype = {

  initialize: function(options) {

      this.setOptions(options);

      // if no engine given, update availables ones and set first found has current
      if (this.options.engine == null) {

          this.detectAvailableEngine();

          // set first available has current
          if (this.options.availableEngine.length > 0) {
              this.setCurrentEngine(this.options.availableEngine[0]);
          } else {
              throw ('No available engine detected.');
          }
      }
  },

  setOptions: function(options) {
      this.options = Object.extend(this.defaults, options || {})
  },

  detectAvailableEngine: function() {

      var availableEngine = [];
      this.options.availableEngine.each(function(engineName, position) {

          if (this.getEngineInstance(engineName).isAvailable()) {
              availableEngine.push(engineName);
          }

      }, this);

      this.options.availableEngine = availableEngine;
  },

  getEngineInstance: function(engineName) {

      if (typeof engineName != "string") {
          throw ('Bad engineName param value <' + engineName + '> for getEngineInstance.');
      }

      if (typeof this.EnginesInstances[engineName] == "undefined") {

          try {
              this.EnginesInstances[engineName] = new UWA.Data.Storage[engineName]();
          } catch (e) {
              throw ('Unable to initialize engine with name <' + engineName + '> cause: ' + e);
          }
      }

      return this.EnginesInstances[engineName];
  },

  // Set the current storage engine and connect it to current database
  setCurrentEngine: function(engineName) {

      if (typeof engineName != "string") {
          throw ('Bad engineName param value <' + engineName + '> for setCurrentEngine.');
      }

      var engineInstance = this.getEngineInstance(engineName);
      engineInstance.connect(this.options.database);

      this.CurrentEngine = engineInstance;
  },

  // Provide a simple interface for storing/getting values
  store: function(key, value) {
    if (!this.CurrentEngine) return false;

    // Executing a get command
    if ( !value ) {
      return this.CurrentEngine.get(key);

    // Executing a set command
    } else {
      return this.CurrentEngine.set(key, value);
    }
  },

  // Provide a simple interface for removing values
  remove: function(key) {

    if (!this.CurrentEngine) {
      return false;
    }

    this.store('lastUpdate', new Date().getTime());

    return this.CurrentEngine.rem(key);
  },

  // Alias access for reading
  get: function(key) {
    return this.store(key);
  },

  // Alias access for setting
  set: function(key, value) {

    this.store('lastUpdate', new Date().getTime());

    return this.store(key, value);
  },

  getLastUpdateDate: function() {

      var lastUpdate = this.get('lastUpdate');

      return new Date().setTime(lastUpdate);
  },

  setDebugMode: function(mode) {
    if (mode === true || mode == 'true') this.debugMode = true; else this.debugMode = false;
  },

  /* Method: log

  Logs environment's messages in the console, if one exists and if the <debugMode> is true.
  It is using <UWA.log> which usually works with Firebug, Safari and Opera.

  Parameters:
    * String message: the message to display in the console.

  Example:
    > widget.log("Environment is loading");
  */
  log: function(message) {
    if (this.debugMode === true) UWA.log(message);
  }
}

