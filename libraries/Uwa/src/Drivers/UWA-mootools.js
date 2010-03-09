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
Script: Driver UWA MooTools
*/

// Overwite native Object function
Object.extend = UWA.extend;
Object.clone = $merge;
Object.toQueryString = Hash.toQueryString;

//
// Class Interface
//

UWA.Class = UWA.extend(Class, {
  create: function() {
    return function() {
      this.initialize.apply(this, arguments);
    }
  }
});

//
// Element Interface
//

if (typeof UWA.Element == "undefined") UWA.Element = {};

// Element Interface

Element.implement({

  hide: function () {
    this.style.display = "none";
    return this;
  },

  show: function () {
    this.style.display = "";
    return this;
  },

  getValue: function () {
    return this.get("value");
  },

  setHTML: function () {
    return this.set("html", arguments);
  },

  getHTML: function () {
    return this.get("html");
  },

  getTag: function () {
    return this.get("tag");
  }
});

UWA.merge(UWA.Element, {

  getElementById: function(el) {

    if (typeof el == 'string') {
        el = $(el);
    }

    return UWA.extendElement(el);
  },

  setAttributes: function(properties) {
    return this.setProperties(properties);
  },

  getElementsByClassName: function (className) {
    return this.getElements("." + className);
  },

  getValue: function () {
    return this.get("value");
  },

  setHTML: function () {
    return this.set("html", arguments);
  },

  getHTML: function () {
    return this.get("html");
  },

  getTag: function () {
    return this.get("tag");
  }
});

Native.implement([Element, Document], {
  getElementsByClassName: function (className) {
    return this.getElements("." + className);
  }
});

//
// Element builder
//

UWA.merge(UWA, {

   extendElement: function(el) {

    el = $(el);
    if (!el.isUwaExtended) {
      UWA.merge(el, UWA.Element);
      el.setStyle = UWA.Element.setStyle; // conflict between mootools setStyle and UWA setStyle
      el.isUwaExtended = true;
    }

    return el;
  },

  createElement: function(tagName, options) {
      return UWA.$element(new Element(tagName, options));
  }
});

// Shortcut for getElementById AND Element extensions
UWA.$element = UWA.extendElement;

UWA.merge(Element, {
  hasClassName: function(el, className) { return $(el).hasClass(className) },
  addClassName: function(el, className) { return $(el).addClass(className) },
  removeClassName: function(el, className) { return $(el).removeClass(className) },
  getDimensions: function(el) { return $(el).getDimensions() },
  hide: function(el) { return el.style.display = "none"; },
  show: function(el) { return el.style.display = ""; },
  getElementsByClassName: function (el, className) { return el.getElements("." + className); }
});

//
// Form Interface
//

UWA.Form = {
  getElements: function(form) {
    return $(form).getElements('input, textarea, select');
  }
}

//
// Ajax Interface
//

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
    return event.target || new Event(event).target;
  },

  findElement: function(event, tagName) {
    var element = event.target || new Event(event).target;
    while (element.parentNode && (!element.tagName || (element.tagName.toUpperCase() != tagName.toUpperCase())))
      element = element.parentNode;
    return element;
  }
});

//
// Function extensions
//

Function.implement({
  bindAsEventListener: function(bind, args){
    return this.create({'bind': bind, 'event': true, 'arguments': args});
  }
});

//
// JSON Interface if require only
//

if (typeof UWA.Json == "undefined") UWA.Json = {};

UWA.Json.encode = JSON.encode;
UWA.Json.decode = function(string) { return JSON.decode(string, true) }

//
// Add some alias for Mootools compatibility
//

Cookie.set = Cookie.write;
Cookie.get = Cookie.read;
Cookie.remove = Cookie.dispose;

Array.alias({
  erase: "remove"
});

Element.alias({
  dispose: "remove"
});

