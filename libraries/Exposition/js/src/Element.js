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
Script: Element

Document Object Model extensions.

Credits:
  Partially based on MooTools, My Object Oriented Javascript Tools.
  Copyright (c) 2006-2007 Valerio Proietti, <http://mad4milk.net>, MIT Style License.
  Partially based on Prototype JavaScript framework, version 1.6.0 (c) 2005-2007 Sam Stephenson.
  Prototype is freely distributable under the terms of an MIT-style license.
  For details, see the Prototype web site: http://www.prototypejs.org/
*/

if (typeof UWA.Element == "undefined") UWA.Element = {};

UWA.merge(UWA.Element, {

  /* Group: Content manipulation methods */

  /* Method: addContent

  Status:
    Documented in UWA 1.0 - to be deprecated

  */
  addContent: function(content) {
    if (typeof content == 'string') {
      // UWA.log("addContent should be soon deprecated. Use alternative syntaxes.");
      var node = document.createElement("div");
      node.innerHTML = content;
      return this.appendChild(node);
    }
    return this.appendChild(content);
  },

  /* Method: setText

  Sets the inner text of the Element.

  From MooTools

  Status:
    Documented in UWA 1.0

  */
  setText: function(text) {
    this[(typeof this.innerText != 'undefined') ? 'innerText' : 'textContent'] = text;
    return this;
  },

  /* Method: appendText

  Adds a new text node at the end of the element's existing content

  Status:
    Documented in UWA 1.0

  */
  appendText: function(text) {
    var node = document.createTextNode(text);
    return this.appendChild(node);
  },

  /* Method: setHTML

  Sets the innerHTML of the Element.

  In MooTools

  Status:
    Documented in UWA 1.0

  */
  setHTML: function(html) {
    this.innerHTML = html;
    return this;
  },

  setContent: function(content) {
    if (typeof content == 'string') {
      this.setHTML(content);
    } else if (typeof content == 'object') {
      this.innerHTML = '';
      this.appendChild(content);
    }
    return this;
  },

  /* Group: Class manipulation methods */

  /* Method: hasClassName

  Checks whether element has the given CSS className.

  - From Prototype, code derived from MooTools

  Status:
    Documented in UWA 1.0

  */

  hasClassName: function(className) {
    return this.className.contains(className, ' ');
  },

  /* Method: addClassName

  Adds a CSS class to element.

  - From Prototype, code derived from MooTools

  Status:
    Documented in UWA 1.0

  */

  addClassName: function(className) {
    if (!this.hasClassName(className)) this.className = (this.className + ' ' + className);
    return this;
  },

  /* Method: removeClassName

  Removes element's CSS className and returns element.

  - From Prototype, code derived from MooTools

  Status:
    Documented in UWA 1.0

  */

  removeClassName: function(className) {
    this.className = this.className.replace(new RegExp('(^|\\s)' + className + '(?:\\s|$)'), '$1');
    return this;
  },

  /* Group: DOM manipulation methods */


  /* Method: getParent

  return a reference to the element's parent node

  - In Mootools

  Status:
    Documented in UWA 1.0

  */
  getParent: function() {
    return UWA.$element(this.parentNode);
  },

  /* Method: getChildren

  return a collection of the element's child nodes

  - In Mootools

  Status:
    Documented in UWA 1.0

  */
  getChildren: function() {
    return this.childNodes;
  },

  /* Method: empty

  Empty an element of all its children.

  - From MooTools

  Status:
    Documented in UWA 1.0

  */

  empty: function() {
    this.innerHTML = '';
    return this;
  },

  /* Method: hide

  Hides and returns element.

  - From Prototype

  Status:
    Documented in UWA 1.0

  */

  hide: function() {
   return this.setStyle('display', 'none');
  },

  /* Method: show

  Displays and returns element.

  - From Prototype

  Status:
    Documented in UWA 1.0

  */

  show: function() {
    return this.setStyle('display', '');
  },

  /* Method: toggle

  Toggles the visibility of element.

  - From Prototype

  Status:
    Documented in UWA 1.0 - to deprecate

  */

  toggle: function() {
    this.style.display == 'none' ? this.setStyle('display', '') : this.setStyle('display', 'none');
    return this;
  },

  /* Method: remove

  Completely removes element from the document and returns it.

  - From Prototype, code from MooTools (dispose)

  Status:
    Documented in UWA 1.0

  */

  remove: function() {
    return this.parentNode.removeChild(this);
  },

  /* Method: getDimensions

  Finds the computed width and height of element and returns them as key/value pairs of an object.

  - From Prototype

  Status:
    Documented in UWA 1.0

  */

  getDimensions: function() {
    return { width: this.offsetWidth, height: this.offsetHeight };
  },

  /* Method: setStyle

  Modifies element's CSS style properties. Styles are passed as either a hash or a name/value pair.

  Status:
    Documented in UWA 1.0

  */

  setStyle: function(style) {
    if (typeof style == 'string') {
      style = style.camelCase();
      this.style[style] = arguments[1];
    } else if (typeof style == 'object') {
      return this.setStyles(style);
    }
    return this;
  },

  setStyles: function(styles) {
    var elementStyle = this.style;
    for (var property in styles) {
      if (property == 'opacity') {
        this.setOpacity(styles[property]);
      } else {
        elementStyle[(property == 'float' || property == 'cssFloat') ?
          (elementStyle.styleFloat === undefined ? 'cssFloat' : 'styleFloat') :
            property] = styles[property];
        }
    }
    return this;
  },

  setOpacity: function(value) {
    this.style.opacity = (value == 1 || value === '') ? '' :
      (value < 0.00001) ? 0 : value;
    return this;
  },

  /* Method: inject

  Insert the Element inside the passed element

  - From MooTools
  - also in Prototype : insert

  Status:
    Introduced in UWA 1.2 (ginger)

  */

  inject: function(el, where) {
    if (typeof where != 'undefined') {
      UWA.log('warning: el.inject. 2nd argument not supported. ' + where);
    }
    return el.appendChild(this);
  },

  /* Group: Events manipulation methods */

  addListener: function(type, fn) {
    if (this.addEventListener) this.addEventListener(type, fn, false);
    else this.attachEvent('on' + type, fn);
    return this;
  },

  removeListener: function(type, fn){
    if (this.removeEventListener) this.removeEventListener(type, fn, false);
    else this.detachEvent('on' + type, fn);
    return this;
  }

});


if (window.HTMLElement) {
  UWA.merge(window.HTMLElement.prototype, UWA.Element);
}
