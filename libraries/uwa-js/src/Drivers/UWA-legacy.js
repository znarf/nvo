/*
Script: Driver UWA Legacy
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

if (typeof Array.prototype.bindWithEvent != "function") {
  Function.prototype.bindWithEvent = Function.prototype.bindAsEventListener;
}

if (typeof App == "undefined") App = {};

if (typeof Class == "undefined") Class = {};

Class = function() {
  return function() {
    if (this.initialize) this.initialize.apply(this, arguments);
  }
}

Class.create = function() {
  return new Class();
}

UWA.Class = Class;

if (typeof Object == "undefined") Object = {};

Object.extend = UWA.extend;

Element = function(tagName, options) {
  return UWA.createElement(tagName, options);
}

UWA.merge(UWA.Element, {
  
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

UWA.merge(Element, {
  hasClassName: function(e, n) { e = UWA.$element(e); if(e) return e.hasClassName(n) },
  addClassName: function(e, n) {e = UWA.$element(e); if(e) return e.addClassName(n) },
  removeClassName: function(e, n) { e = UWA.$element(e); if(e) return e.removeClassName(n) },
  getDimensions: function(e) { e = UWA.$element(e); if(e) return e.getDimensions() },
  hide: function(e) { e = UWA.$element(e); if(e) return e.hide() },
  show: function(e) { e = UWA.$element(e); if(e) return e.show() }
});

function $A(iterable) {
  if (typeof iterable == 'object') {
    var array = [];
    for (var i = 0, l = iterable.length; i < l; i++) array[i] = iterable[i];
    return array;
  }
  return Array.prototype.slice.call(iterable);
};

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
