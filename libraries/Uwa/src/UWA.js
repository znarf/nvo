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


/* UWA Globals */

if (typeof UWA_WIDGET == "undefined") {
  var UWA_WIDGET = 'http://nvmodules.netvibes.com/widget';
}
if (typeof UWA_JS == "undefined") {
  var UWA_JS = 'http://nvmodules.netvibes.com/js';
}
if (typeof UWA_CSS == "undefined") {
  var UWA_CSS = 'http://nvmodules.netvibes.com/css';
}
if (typeof UWA_PROXY == "undefined") {
  var UWA_PROXY = 'http://www.netvibes.com';
}
if (typeof UWA_STATIC == "undefined") {
  var UWA_STATIC = 'http://www.netvibes.com/img';
}

/*
Script: Core

UWA Runtime Core.

Credits:
  Partially based on MooTools, My Object Oriented Javascript Tools.
  Copyright (c) 2006-2007 Valerio Proietti, <http://mad4milk.net>, MIT Style License.
*/


if (typeof UWA == "undefined") {
  var UWA = {
    'version': '1.2.4'
    // @todo Uwa need to include into MakeFile from src repository example from mootools:
    //'build': '0d9113241a90b9cd5643b926795852a2026710d4'
  };
}

if (typeof UWA.Widgets == "undefined") UWA.Widgets = {};
if (typeof UWA.Scripts == "undefined") UWA.Scripts = {};
if (typeof UWA.Controls == "undefined") UWA.Controls = {};
if (typeof UWA.Services == "undefined") UWA.Services = {};
if (typeof UWA.Templates == "undefined") UWA.Templates = {};


// @todo Translation of text from English to another language
if (typeof _ == "undefined") {
  var _ = function(s) {
    return s
  };
}

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

Todo:

  Compatibility - To be removed:

  Compatibility mode should be remove after a merge from Netvibes.net
  from Exposition-Libraries repository preview4 branch or added to an into
  /UWA/Evironment namespace, where they can use there constants and a dirty
  mootools/prototype mixed scripts used and into Exposition PHP Library to add
  Compiler for Netvibes into Copiler/Netvibes new namesapace for clean
  implementation ot Netvibes into UWA and Exposition.
*/

if (typeof UWA_NETVIBES_COMPATIBILY == "undefined") {
  var UWA_NETVIBES_COMPATIBILY = true;
}

if (UWA_NETVIBES_COMPATIBILY == true) {

  NV_HOST = 'www.netvibes.com';
  NV_MODULES = 'nvmodules.netvibes.com';
  NV_AVATARS = 'avatars.netvibes.com';
  NV_STATIC = 'http://' + NV_HOST;
  NV_PATH = 'http://' + NV_HOST + '/';

  if (typeof Netvibes == "undefined") var Netvibes = {};
  if (typeof Netvibes.UI == "undefined") Netvibes.UI = {};
  Netvibes.UI._idIncrement = 0;
  UWA.Controls = Netvibes.UI;
  if (Netvibes.DLA) UWA.Controls.SearchForm = Netvibes.DLA.SearchForm;
}
