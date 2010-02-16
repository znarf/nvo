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
Script: Client

Credits:
  Partially based on MooTools, My Object Oriented Javascript Tools.
  Copyright (c) 2006-2007 Valerio Proietti, <http://mad4milk.net>, MIT Style License.

Class: Client
  Some browser properties are attached to the Client object for browser detection.

Engine:
  UWA.Client.Engine.ie - is set to true if the current browser is internet explorer (any)
  UWA.Client.Engine.ie6 - is set to true if the current browser is internet explorer 6
  UWA.Client.Engine.ie7 - is set to true if the current browser is internet explorer 7
  UWA.Client.Engine.gecko - is set to true if the current browser is Mozilla/Gecko
  UWA.Client.Engine.webkit - is set to true if the current browser is Safari/Konqueror
  UWA.Client.Engine.webkit419 - is set to true if the current browser is Safari2 / webkit till version 419
  UWA.Client.Engine.webkit420 - is set to true if the current browser is Safari3 (Webkit SVN Build) / webkit over version 419
  UWA.Client.Engine.opera - is set to true if the current browser is opera
  UWA.Client.Engine.name - is set to the name of the engine

Platform:
  UWA.Client.Platform.mac - is set to true if the platform is mac
  UWA.Client.Platform.windows - is set to true if the platform is windows
  UWA.Client.Platform.linux - is set to true if the platform is linux
  UWA.Client.Platform.other - is set to true if the platform is neither mac, windows or linux
  UWA.Client.Platform.name - is set to the name of the platform

Note:
  Engine detection is entirely object-based.
*/

UWA.Client = {Engine: {'name': 'unknown', 'version': ''}, Platform: {}, 'features': {}};

//features
UWA.Client.features.xhr = !!(window.XMLHttpRequest);
UWA.Client.features.xpath = !!(document.evaluate);

//engine
if (typeof window.opera !== "undefined") UWA.Client.Engine.name = 'opera';
else if (typeof window.ActiveXObject !== "undefined") UWA.Client.Engine = {'name': 'ie', 'version': (UWA.Client.features.xhr) ? 7 : 6};
else if (!navigator.taintEnabled) UWA.Client.Engine = {'name': 'webkit', 'version': (UWA.Client.features.xpath) ? 420 : 419};
else if (document.getBoxObjectFor != null) UWA.Client.Engine.name = 'gecko';
UWA.Client.Engine[UWA.Client.Engine.name] = UWA.Client.Engine[UWA.Client.Engine.name + UWA.Client.Engine.version] = true;

//platform
var platform = navigator.platform.match(/(mac)|(win)|(linux)|(nix)/i) || ['Other'];
UWA.Client.Platform.name = platform[0].toLowerCase();
UWA.Client.Platform[UWA.Client.Platform.name] = true;

// Retro compatibility with old Browser object
if (typeof Browser == "undefined") var Browser = {};
if(UWA.Client.Engine.ie) Browser.isIE = true; else Browser.isIE = false;
if(UWA.Client.Engine.opera) Browser.isOpera = true; else Browser.isOpera = false;
