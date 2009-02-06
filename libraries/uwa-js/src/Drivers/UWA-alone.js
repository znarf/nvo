/*
Script: Driver UWA Alone
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

// Shortcut for getElementById AND extend element
$ = function(el) {
  if (typeof el == 'string') {
    el = document.getElementById(el);
  }
  if (el && !el.isUwaExtended) {
    UWA.merge(el, UWA.Element);
    el.isUwaExtended = true;
  }
  return el;
}

// getElementsByClassName for browsers that don't support it yet
UWA.merge(document, {
  getElementsByClassName: function(className, elm) {
    var node = elm || document;
    var children = node.getElementsByTagName('*');
    var elements = new Array();
    for (var i=0; i<children.length; i++) {
      var child = children[i];
      var classNames = child.className.split(' ');
      for (var j = 0; j < classNames.length; j++) {
        if (classNames[j] == className) {
          elements.push(child);
          break;
        }
      }
    }
    return elements;
  }
});

// Extend an Element with UWA element extensions
UWA.extendElement = function(el) {
  return $(el);
}

UWA.$element = UWA.extendElement;

// Element builder
UWA.createElement = function(tagName, options){
  var el = UWA.extendElement( document.createElement(tagName) ); 
  if (typeof options == 'string') {
    UWA.log('widget.createElement : elName as 2nd argument is deprecated');
    this.elements[options] = el;
  } else if (typeof options == "object") {
    for (var name in options) { 
     var option = options[name]; 
     switch(name) { 
       case 'styles': 
         el.setStyle(option); 
         break;
       case 'attributes':
         el.setAttributes(option);
         break;
       case 'id':
         el.id = option;
         break;
       case 'class':
         el.className = option;
         break;
       case 'events':
         el.addEvents(option);
         break; 
       default:
         el.setAttribute(name, option); 
     } 
   } 
 } 
 return el;
}

UWA.Form = {
  collectionToArray : function(collection) {
    resultArray = new Array();
    for (i = 0; i < collection.length; i++) {
      resultArray[resultArray.length] = collection[i];
    }
    return resultArray;
  },
    
  getElements: function(form) {
    var inputArray = UWA.Form.collectionToArray($(form).getElementsByTagName('input'));
    var selectArray = UWA.Form.collectionToArray($(form).getElementsByTagName('select'));
    return inputArray.concat(selectArray);
  }
}

// Ajax Interface
UWA.Ajax = {
  getRequest: function(url, options) {
    options.url = url;
    var method = options.method ? options.method.toLowerCase() : 'get';
    if (options.requestHeaders) {
        options.headers = options.requestHeaders;
        delete options.requestHeaders;
    }
    if (options.headers) {
      options.headers['X-Requested-With'] = 'XMLHttpRequest';
    }
    if (window.XMLHttpRequest) {
      var client = new XMLHttpRequest();
    } else if (window.ActiveXObject){
      var client = new ActiveXObject("Msxml2.XMLHTTP");
    }
    client.onreadystatechange = function() {
      if (client.readyState == 4) {
        options.onComplete(client.responseText, client.responseXML);
      }
    }
    client.open(method, url, true);
    for (var key in options.headers) {
      client.setRequestHeader(key, options.headers[key]);
    }
    return client;
  },
  Request: function(url, options) {
    var request = this.getRequest(url, options);
    if (options.postBody) {
      options.data = options.postBody;
      delete options.postBody;
    } else if (options.parameters) {
      options.data = options.parameters;
      delete options.parameters;
      request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
    }
    return request.send(options.data || null);
  },
  onCompleteXML: function(arg, callback, context) {
    if (typeof callback == "function") callback(arg[1])
  },
  onCompleteText: function(arg, callback, context) {
    if (typeof callback == "function") callback(arg[0])
  },
  onCompleteFeed: function(arg, callback, context) {
    var response = {responseText: arg[0], responseXML: arg[1]};
    if (typeof UWA.Utils.parseFeed == 'function') { 
      response = UWA.Utils.parseFeed(response);
    }
    else {
     eval('response = ' + response.responseText);
    }
    if (typeof callback == "function") callback(response)
  },
  onCompleteJson: function(arg, callback, context) {
    try {
      eval("var j = " + arg[0]);
      if (typeof callback == "function") callback(j, context);
    } catch(e) {
      UWA.log(e);
    }
  }
}

// Element extensions
if (typeof UWA.Element == "undefined") UWA.Element = {};

UWA.merge(UWA.Element, {
  
  getElementById: function(id) {
    return document.getElementById(id);
  },
  
  getElementsByClassName: function(className) {
    return document.getElementsByClassName(className, this);
  }
  
});

// Function extensions
UWA.merge(Function.prototype, {

  bind: function() {
    if (arguments.length < 2 && arguments[0] === undefined) return this;
    var __method = this, args = $A(arguments), object = args.shift();
    return function() {
      return __method.apply(object, args.concat($A(arguments)));
    }
  },
  
  bindAsEventListener: function() {
    var __method = this, args = $A(arguments), object = args.shift();
    return function(event) {
      return __method.apply(object, [event || window.event].concat(args));
    }
  }

});

// JSON Interface
var JSON = {

  $defined: function(obj) {
    return (obj != undefined);
  },

  encode: function(obj){
    switch (typeof obj){
      case 'string':
        return '"' + obj.replace(/[\x00-\x1f\\"]/g, JSON.$replaceChars) + '"';
      case 'array':
        return '[' + String(obj.map(JSON.encode).filter(JSON.$defined)) + ']';
      case 'object':
        var string = [];
        for(var key in obj) {
          var json = JSON.encode(obj[key]);
          if (json) string.push(JSON.encode(key) + ':' + json);
        }
        return '{' + String(string) + '}';
      case 'number': case 'boolean': return String(obj);
      case false: return 'null';
    }
    return null;
  },

  $specialChars: {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'},

  $replaceChars: function(chr){
    return JSON.$specialChars[chr] || '\\u00' + Math.floor(chr.charCodeAt() / 16).toString(16) + (chr.charCodeAt() % 16).toString(16);
  },

  decode: function(string, secure){
    if (typeof string != 'string' || !string.length) return null;
    if (secure && !(/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(string.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, ''))) return null;
    return eval('(' + string + ')');
  }

}

if (typeof UWA.Json == "undefined") UWA.Json = {};

UWA.Json.encode = JSON.encode;
UWA.Json.decode = function(string) { return JSON.decode(string, true) }
