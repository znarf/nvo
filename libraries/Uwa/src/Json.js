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
Class: Data

The Data class provides abstract methods to access external resources using Ajax (XMLHttpRequest) requests.

Credits:
  Partially based on MooTools, My Object Oriented Javascript Tools.
  Copyright (c) 2006-2007 Valerio Proietti, <http://mad4milk.net>, MIT Style License.
  Partially based on Prototype JavaScript framework, version 1.6.0 (c) 2005-2007 Sam Stephenson.
  Prototype is freely distributable under the terms of an MIT-style license.
  For details, see the Prototype web site: http://www.prototypejs.org/
*/

if (typeof UWA.Json == "undefined") UWA.Json = {};

UWA.Json = {

  request: function(url, request) {

    var varname = 'json';

    if (request.context && request.context[0]) {
        varname += request.context[0];
    } else {
        varname += Math.round(1000*1000*Math.random());
    }

    UWA.Json.evalJSON('var ' + varname + '= false');

    url += '&object=' + varname ;

    var script = document.createElement('script');
    script.setAttribute('type', 'text/javascript');
    script.src = url;

    var head = document.getElementsByTagName('head')[0];
    var insert = head.appendChild(script);

    if (typeof request.onComplete == "undefined") UWA.log('no callback set');

    var callback = request.onComplete;
    var myCallback = function(c) {
      return function(j) {
          callback(j, c)
      }
    }(request.context);

    var interval = setInterval( ( function() {

      UWA.Json.evalJSON('var json = ' + varname);

      if (json) {

        try {
          myCallback(json);
        } catch(e) {
          UWA.log(e);
        }

        insert.parentNode.removeChild(insert);
        clearInterval(interval);
      }

    }).bind(this), 100);
  },

  // Evals JSON
  evalJSON: function(src) {
    return eval("(" + src + ")");
  },

  // Evals JSON in a way that is *more* secure.
  secureEvalJSON: function(src) {

    var filtered = src;
    filtered = filtered.replace(/\\["\\\/bfnrtu]/g, '@');
    filtered = filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']');
    filtered = filtered.replace(/(?:^|:|,)(?:\s*\[)+/g, '');

    if (/^[\],:{}\s]*$/.test(filtered)) {
        return eval("(" + src + ")");
    } else {
        throw new SyntaxError("Error parsing JSON, source is not valid.");
    }
  },

  compactJSON: function(object) {
    return this.toJSON(object, true);
  },

  // Places quotes around a string, inteligently.
  // If the string contains no control characters, no quote characters, and no
  // backslash characters, then we can safely slap some quotes around it.
  // Otherwise we must also replace the offending characters with safe escape
  // sequences.
  quoteString: function(string) {
    if (escapeable.test(string)) {

      return '"' + string.replace(escapeable, function (a) {

        var c = meta[a];
        if (typeof c === 'string') {
            return c;
        }
        c = a.charCodeAt();
        return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);

      }) + '"';
    }

    return '"' + string + '"';
  },

  toJSON: function(o, compact) {

    var type = typeof(o);

    if (type == "undefined") {
        return "undefined";
    } else if (type == "number" || type == "boolean") {
        return o + "";
    } else if (o === null) {
        return "null";
    }

    // Is it a string?
    if (type == "string") {
        return UWA.Json.quoteString(o);
    }

    // Does it have a .toJSON function?
    if (type == "object" && typeof o.toJSON == "function") {
        return o.toJSON(compact);
    }

    // Is it an array?
    if (type != "function" && typeof(o.length) == "number") {
        var ret = [];
        for (var i = 0; i < o.length; i++) {
            ret.push(UWA.Json.toJSON(o[i], compact) );
        }

        if (compact) {
            return "[" + ret.join(",") + "]";
        } else {
            return "[" + ret.join(", ") + "]";
        }
    }

    // If it's a function, we have to warn somebody!
    if (type == "function") {
        throw new TypeError("Unable to convert object of type 'function' to json.");
    }

    // It's probably an object, then.
    var ret = [];
    for (var k in o) {

        var name;
        type = typeof(k);

        if (type == "number")
            name = '"' + k + '"';
        else if (type == "string")
            name = UWA.Json.quoteString(k);
        else
            continue;  //skip non-string or number keys

        var val = UWA.Json.toJSON(o[k], compact);
        if (typeof(val) != "string") {
            // skip non-serializable values
            continue;
        }

        if (compact) {
            ret.push(name + ":" + val);
        } else {
            ret.push(name + ": " + val);
        }
    }

    return "{" + ret.join(", ") + "}";
  }
};

