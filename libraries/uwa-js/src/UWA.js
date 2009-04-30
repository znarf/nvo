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

/* NV Globals */

if (typeof window.NV_HOST == "undefined") {
  NV_HOST = 'www.netvibes.com';
}
if (typeof window.NV_MODULES == "undefined") {
  NV_MODULES = 'nvmodules.netvibes.com';
}
if (typeof window.NV_AVATARS == "undefined") {
  NV_AVATARS = 'avatars.netvibes.com';
}
if (typeof window.NV_STATIC == "undefined") {
  NV_STATIC = 'http://' + NV_HOST;
}
if (typeof window.NV_PATH == "undefined") {
  NV_PATH = 'http://' + NV_HOST + '/';
}

if (typeof UWA == "undefined") var UWA = {};
if (typeof UWA.Widgets == "undefined") UWA.Widgets = {};
if (typeof UWA.Scripts == "undefined") UWA.Scripts = {};
if (typeof UWA.Controls == "undefined") UWA.Controls = {};
if (typeof UWA.Services == "undefined") UWA.Services = {};
if (typeof UWA.Templates == "undefined") UWA.Templates = {};

UWA.version = '1.2';

// Compatibility - To be removed
if (typeof Netvibes == "undefined") var Netvibes = {};
if (typeof Netvibes.UI == "undefined") Netvibes.UI = {};
Netvibes.UI._idIncrement = 0;
UWA.Controls = Netvibes.UI;
if (Netvibes.DLA) UWA.Controls.SearchForm = Netvibes.DLA.SearchForm;

if (typeof _ == "undefined") {
  _ = function(s) {
    return s
  };
}

/*
Script: Core

UWA Runtime Core.

Credits:
  Partially based on MooTools, My Object Oriented Javascript Tools.
  Copyright (c) 2006-2007 Valerio Proietti, <http://mad4milk.net>, MIT Style License.
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

/* Method: extend 

Copies all the properties from the second passed object to the first passed Object.

See also:
  <http://docs.mootools.net/Core/Core.js#$extend>

Example:
  TODO
*/
UWA.extend = function(original, extended) {
    /*
    for (var property in arguments[1]) arguments[0][property] = arguments[1][property];
    return arguments[0];
    */
    for (var key in (extended || {})) original[key] = extended[key];
    return original;
}

/* Method: merge 

Copies the properties from the second passed object to the first passed Object if it not exists already.

*/
UWA.merge = function() {
    for (var property in arguments[1]) {
      if(typeof arguments[0][property] == "undefined")
        arguments[0][property] = arguments[1][property];
    }
    return arguments[0];
}

/* Method: log 

Log a message to a console, if one available.

Example:
  > widget.log("Widget is loading ...")
*/
UWA.log = function(message) {
    if (window.console && typeof(console.log) == "function") console.log(message); // firebug, safari
    else if (window.opera && typeof(opera.postError) == "function") opera.postError(message);
    // else if (window.widget) window.alert(message); // dashboard
    // else window.alert(message); // IE
}
