/*
Script: Driver UWA MooTools
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

UWA.Class = Class;

UWA.Form = {
  getElements: function(form) {
    return $(form).getElements('input, textarea, select');
  }
}

UWA.Ajax = {
  getRequest: function(url, options) {
    options.url = url;
    if (options.postBody) {
      options.data = options.postBody;
      options.urlEncoded = false;
      delete options.postBody;
    } else if (options.parameters) {
      options.data = options.parameters;
      delete options.parameters;
    }
    if (options.method) {
        options.method = options.method.toLowerCase();
    }
    if (options.requestHeaders) {
        options.headers = options.requestHeaders;
        delete options.requestHeaders;
    }
    if (options.headers) {
      options.headers['X-Requested-With'] = 'XMLHttpRequest';
    }
    return new Request(options);
  },
  Request: function(url, options) {
    var request = this.getRequest(url, options);
    return request.send();
  },
  onCompleteXML: function(arg, callback, context) {
    if (typeof callback == "function") callback(arg[1])
  },
  onCompleteText: function(arg, callback, context) {
    if (typeof callback == "function") callback(arg[0])
  },
  onCompleteFeed: function(arg, callback, context) {
    var response = {responseText: arg[0], responseXML: arg[1]};
    var feed = UWA.Utils.parseFeed(response);
    if (typeof callback == "function") callback(feed)
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
  
UWA.createElement = function(tagName, options){
  return UWA.$element( new Element(tagName, options) );
}

UWA.extendElement = function(el){
  if (el) {
    el = $(el);
    if (!el.isUwaExtended) {
      UWA.merge(el, UWA.Element);
      el.setStyle = UWA.Element.setStyle; // conflict between mootools setStyle and UWA setStyle
      el.isUwaExtended = true;
    }
    return el;
  }
}

UWA.$element = UWA.extendElement;

if (typeof UWA.Element == "undefined") UWA.Element = {};

UWA.extend(UWA.Element, {
  
  setAttributes: function(properties) {
     return this.setProperties(properties);
  }
  
});

if (typeof UWA.Json == "undefined") UWA.Json = {};

UWA.Json.encode = JSON.encode;
UWA.Json.decode = function(string) { return JSON.decode(string, true) }

// Legacy

UWA.merge(Object, {
  extend: UWA.extend
});

UWA.Class = UWA.extend(Class, {
  create: function() {
    return function() {
      this.initialize.apply(this, arguments);
    }
  }
});

UWA.merge(Element, {
  hasClassName: function(el, className) { return $(el).hasClassName(className) },
  addClassName: function(el, className) { return $(el).addClassName(className) },
  removeClassName: function(el, className) { return $(el).removeClassName(className) },
  getDimensions: function(el) { return $(el).getDimensions() },
  hide: function(el) { return $(el).hide() },
  show: function(el) { return $(el).show() }
});

UWA.merge(Event, {
  element: function(event) {
    return event.target;
  },
  findElement: function(event, tagName) {
    var element = event.target || new Event(event).target;
    while (element.parentNode && (!element.tagName || (element.tagName.toUpperCase() != tagName.toUpperCase())))
      element = element.parentNode;
    return element;
  }
});

Function.implement({
  bindAsEventListener: function(bind, args){
    return this.create({'bind': bind, 'event': true, 'arguments': args});
  }
});
