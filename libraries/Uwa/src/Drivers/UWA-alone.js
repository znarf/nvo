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
Script: Driver UWA Alone
*/

// Overwite native Object function
if (typeof Object == "undefined") Object = {};
Object.extend = UWA.extend;

//
// Class Interface
//

UWA.Class = function() {
  return function() {
    if (this.initialize) this.initialize.apply(this, arguments);
  }
}

UWA.Class.create = function() {
  return new Class();
}

Class = UWA.Class;

//
// Element Interface
//

if (typeof UWA.Element == "undefined") UWA.Element = {};

UWA.merge(UWA.Element, {

  getElementById: function(el) {

    if (typeof el == 'string') {
        el = document.getElementById(el);
    }

    return UWA.extendElement(el);
  },

  getElementsByClassName: function(className) {
    var el = document.getElementsByClassName(className, this);
    return UWA.extendElement(el);
  },

  setAttributes: function(properties) {
    UWA.log('warning el.setAttributes : partially implemented');
    for (key in properties) {
      this.setAttribute(key, properties[key]);
    }
    return this;
  },

  getElements: function(selector) {
    UWA.log('warning el.getElements("' + selector + '") : partially implemented');
    // if string without spaces ->
    return this.getElementsByTagName(selector);
    // if string starting with a . ->
    // return this.getElementsByClassName(selector);
  },

  getElement: function(selector) {
    UWA.log('warning el.getElement("' + selector + '") : partially implemented');
    return this.getElements(selector)[0];
  },

  addEvent: function(type, fn) {
    return this.addListener(type, fn);
  },

  addEvents: function(events) {
    for (key in events) {
        this.addEvent(key, events[key]);
    }
    return this;
  },

  removeEvent: function(type, fn) {
    return this.removeListener(type, fn);
  },

  removeEvents: function(events) {
    for (key in events) {
        this.removeEvent(key, events[key]);
    }
    return this;
  }
});

//
// Element builder
//

UWA.merge(UWA, {

   extendElement: function(el) {

    if (el && !el.isUwaExtended) {
      UWA.merge(el, UWA.Element);
      el.isUwaExtended = true;
    }

    return el;
  },

  createElement: function(tagName, options) {

    var el = UWA.extendElement( document.createElement(tagName) );
    if (typeof options == 'string') {
        UWA.log('widget.createElement : elName as 2nd argument is deprecated');
        this.elements[options] = el;
    } else if (typeof options == "object") {
      for (var name in options) {
        var option = options[name];
        switch(name) {
        case 'html':
            el.setHTML(option);
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
});

Element = function(tagName, options) {
  return UWA.createElement(tagName, options);
}

// Shortcut for getElementById AND Element extensions
var $ = UWA.Element.getElementById
UWA.$element = UWA.extendElement;

UWA.merge(Element, {
  hasClassName: function(e, n) {e = UWA.$element(e); if(e) return e.hasClassName(n) },
  addClassName: function(e, n) {e = UWA.$element(e); if(e) return e.addClassName(n) },
  removeClassName: function(e, n) { e = UWA.$element(e); if(e) return e.removeClassName(n) },
  getDimensions: function(e) { e = UWA.$element(e); if(e) return e.getDimensions() },
  hide: function(e) { e = UWA.$element(e); if(e) return e.hide() },
  show: function(e) { e = UWA.$element(e); if(e) return e.show() }
});

//
// Form Interface
//

UWA.Form = {
  collectionToArray: function(collection) {
    resultArray = new Array();
    for (i = 0; i < collection.length; i++) {
      resultArray[resultArray.length] = collection[i];
    }
    return resultArray;
  },

  getElements: function(form) {
    var textareaArray = UWA.Form.collectionToArray($(form).getElementsByTagName('textarea'));
    var inputArray = UWA.Form.collectionToArray($(form).getElementsByTagName('input'));
    var selectArray = UWA.Form.collectionToArray($(form).getElementsByTagName('select'));
    return inputArray.concat(selectArray).concat(textareaArray);
  }
}

//
// Ajax Interface
//

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

//
// Event Interface
//

if (typeof Event == "undefined") Event = {};

UWA.merge(Event, {
  element: function(event) {
    return event.target || event.srcElement;
  },

  findElement: function(event, tagName) {
    var element = Event.element(event);
    while (element.parentNode && (!element.tagName || (element.tagName.toUpperCase() != tagName.toUpperCase())))
      element = element.parentNode;
    return element;
  },

  stop: function(e){
    Event.stopPropagation(e)
    Event.preventDefault(e);
  },

  stopPropagation: function(e){
    if (e.stopPropagation) e.stopPropagation();
    else e.cancelBubble = true;
  },

  preventDefault: function(e){
    if (e.preventDefault) e.preventDefault();
    else e.returnValue = false;
  }
});

//
// Function extensions
//

// @todo remove
function $A(iterable) {
  if (typeof iterable == 'object') {
    var array = [];
    for (var i = 0, l = iterable.length; i < l; i++) array[i] = iterable[i];
    return array;
  }
  return Array.prototype.slice.call(iterable);
};

UWA.merge(Function.prototype, {

  create: function (callback) {

    var __method = this;
     callback = callback || {};

     return function (object) {

       var args = callback.arguments;
       args = (typeof args !== "undefined") ? UWA.Utils.splat(args) : $A(arguments).slice((callback.event) ? 1 : 0);

       if (callback.event) {
         args = [object || window.event].concat(args)
       }

       var event = function () {
         return __method.apply(callback.bind || null, args);
       };

       return event();
    }
  },

  bind: function (callback, args) {
    return this.create({bind: callback, "arguments": args});
  },

  bindAsEventListener: function (callback, args) {
    return this.create({bind: callback, event: true, "arguments": args});
  }
});

//
// Array extensions
//

if (typeof Array.prototype.bindWithEvent != "function") {
  Function.prototype.bindWithEvent = Function.prototype.bindAsEventListener;
}

//
// JSON Interface if require only
//

var JSON = {

  $defined: function(obj) {
    return (obj != undefined);
  },

  $specialChars: {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'},

  $replaceChars: function(chr) {
    return JSON.$specialChars[chr] || '\\u00' + Math.floor(chr.charCodeAt() / 16).toString(16) + (chr.charCodeAt() % 16).toString(16);
  },

  encode: function(obj) {
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

  decode: function(string, secure) {
    if (typeof string != 'string' || !string.length) return null;
    if (secure && !(/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(string.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, ''))) return null;
    return eval('(' + string + ')');
  }
}


