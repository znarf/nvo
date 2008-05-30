/*
Class: Environment

The Environment Base class provide base functions to run an UWA Widget Execution Environnement.
It is an abstract class, and must be extended to be useful.
The object must be filled with DOM elements (at least body), in the "<onInit>" or "<onRegisterModule>" callbacks for example.
*/

/*
License:
  Copyright (c) 2005-2008 Netvibes (http://www.netvibes.org/).

  This file is part of Netvibes Widget Platform.

  Netvibes Widget Platform is free software: you can redistribute it and/or modify
  it under the terms of the GNU Lesser General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Netvibes Widget Platform is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public License
  along with Netvibes Widget Platform.  If not, see <http://www.gnu.org/licenses/>.
*/

UWA.Environment = function() {

  /* Property: widget

  *Widget*: current widget registered in this environnement.
  */
  this.module = null;
  this.widget = this.module;

  /* Property: html

  *Object*: HTML Dom elements related to this environnement
  
  Common Elements are 'title', 'edit', 'body' & 'icon'
  */
  this.html = {};

  /* Property: loaded

  *Boolean*: flag to know if the Environnement is loaded.

  Environment is likely ready once it is instanciated and the DOM is ready to be manipulated.
  */
  this.loaded = false;

  /* Property: registered

  *Boolean*: flag to know if a Widget is registered.

  A widget is not registered until the Environment is *loaded*
  */
  this.registered = false;

  /* internal or advanced use only */
  this.callbacks = {};

  /* 'maybe' deprecated - where is it used ? */
  this.query = '';

  /* internal or advanced use only */
  this.data = {};

  /* Property: debugMode

  *Boolean*: activates or desactivates the debug mode for the widget. 

  The default value is TRUE. When TRUE, messages written with <log> method will appear in the console.
  */
  this.debugMode = false;

  /* Property: periodicals

  *Object*: Stores environment's periodical events. 

  The object is initially empty. It is filled by the <setPeriodical> method.
  */
  this.periodicals = {};

  /* Property: delays

  *Object*: Stores environment's delayed events. 

  The object is initially empty. It is filled by the <setDelayed> method.
  */
  this.delays = {};

  /* Property: height

  *Integer*: The current height of the widget in pixel.

  Notes:
    Experimental. Only used in some environments.
  */
  this.height = 200;

  if(this.initialize) this.initialize();

  this.setPeriodical('init', this.init, 100, true);

}

UWA.Environment.prototype = {
  
  /* Method: init

  Initialize the Environment, when DOM is ready

  Parameters:
  * None.
  */ 
  init: function() {
    if (document.body) {
      this.callback('onInit');
      this.clearPeriodical('init');
      this.log('Environnement loaded');
      this.loaded = true;
      return true;
    }
    return false;
  },

  /* Method: getModule

  Returns the widget (= module) currently registered in the Environment. 
  If no widget is registered, the Environment creates one and registers it.

  Parameters:
  * None.

  Returns:
  * Widget: the (maybe newly created) registered widget.
  */ 
  getModule: function() {
    if (this.module) {
      var module = this.module;
    } else {
      var module = new UWA.Module();
      this.registerModule(module);
    }
    return module;
  },

  /* Method: registerModule

  Registers a Widget (module) into the execution Environment. Once done, fire the *onRegisterModule* callback.

  Parameters:
  * Widget : the widget to register in the Environment.

  Returns:
  * Nothing.
  */ 
  registerModule: function(module) {
    this.module = module;
    this.widget = this.module;
    module.environment = this;
    this.setPeriodical('register', function() {
      if (this.loaded) {
        this.callback('onRegisterModule');
        this.registered = true;
        this.log('Module registered');
        this.clearPeriodical('register');
      }
    }, 100, true);
  },

  /* Method: launchModule

  Launch the registered widget by fire the widget.launch method.
  
  If needed, wait until the environment is fully loaded and a widget registered.

  Parameters:
  * None.
  */
  launchModule: function() {
    this.setPeriodical('launch', function() {
      if (this.loaded && this.module && this.registered) {
          this.log('Launching module');
          this.clearPeriodical('launch');
          this.module.launch();
          if (typeof this.module.onLoadComplete != "function") {
            this.callback('onLoadComplete');
          }
      }
    }, 100, true);
  },

  /* deprecated - internal or advanced use only */
  setCallback: function(name, fn) {
    this.callbacks[name] = fn;
  },

  /* Method: callback

  Fire Environment.key then the callback method associated with the given callback name (key).
  Returns false if no callback method is associated with the given key.

  Parameters:
    * String name: the callback name (e.g. "onUpdateTitle");
    * Object args: one optional argument 
    * Object: an object to bind the callback to

  Returns:
    * Nothing, but calls the method associated with the given callback name (key) 
  */
  callback: function(name, args, bind) {
    if (typeof bind == 'undefined') bind = this;
    try {
      if (this[name]) return this[name].apply(bind, [args]);
      if (this.callbacks[name]) return this.callbacks[name].apply(bind, [args]);
    } catch(e) {
      this.log(e);
    }
    return false;
  },

  /* Method: setPeriodical 

  Register a function as periodical event.

  The function will automatically be binded to the current environment object.

  Parameters:
    * String name: the name of the event
    * Function fn: the function to register
    * Integer delay: the execution delay in milliseconds
    * Boolean force: If true, fire the function for the time right now.

  Notes:
    internal or advanced use only

  */
  setPeriodical: function(name, fn, delay, force) {
    this.clearPeriodical(name);
    fn = fn.bind(this);
    this.periodicals[name] = setInterval(fn, delay);
    if (force) fn();
  },

  /* Method: clearPeriodical 

  Unregister a periodical event previously registered with <setPeriodical>

  Parameters:
    * String name: the name of the event

  Notes:
    internal or advanced use only

  */
  clearPeriodical: function(name) {
    if (this.periodicals[name]) { clearInterval(this.periodicals[name]) }
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
  },

  /* to document */
  setIcon: function(url) {
    if(this.module.elements['icon']) this.module.elements['icon'].setHTML('<img width="16" height="16" src="' + url + '" />');
  },

  /* Method: log
  
  Logs environment's messages in the console, if one exists and if the <debugMode> is true.
  It is using <UWA.log> which usually works with Firebug, Safari and Opera.

  Parameters:
    * String message: the message to display in the console.

  Example:
    > widget.log("Environment is loading");
  */
  log: function(string) {
    if (this.debugMode) UWA.log(string);
  }

}
