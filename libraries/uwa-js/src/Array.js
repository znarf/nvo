/*
Script: Array

Extensions to the native JavaScript Array class.

Credits:
  Partially based on MooTools, My Object Oriented Javascript Tools.
  Copyright (c) 2006-2007 Valerio Proietti, <http://mad4milk.net>, MIT Style License.
  Partially based on Prototype JavaScript framework, version 1.6.0 (c) 2005-2007 Sam Stephenson.
  Prototype is freely distributable under the terms of an MIT-style license.
  For details, see the Prototype web site: http://www.prototypejs.org/
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

UWA.merge(Array.prototype, {

  /* Method: forEach 

  Executes a provided function once per array element.

  Notes:
    Javascript 1.6 method

  See also:
    <http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:forEach>
  */
  forEach: function(fn, bind){
    for (var i = 0, j = this.length; i < j; i++) fn.call(bind, this[i], i, this);
  },

  /* Method: filter 

  Creates a new array with all elements that pass the test implemented by the provided function.

  Notes:
    Javascript 1.6 method

  See also:
    <http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:filter>
  */
  filter: function(fn, bind){
    var results = [];
    for (var i = 0, j = this.length; i < j; i++){
      if (fn.call(bind, this[i], i, this)) results.push(this[i]);
    }
    return results;
  },

  /* Method: map 

  Creates a new array with the results of calling a provided function on every element in this array.

  Notes:
    Javascript 1.6 method

  See also:
    <http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:map>
  */
  map: function(fn, bind){
    var results = [];
    for (var i = 0, j = this.length; i < j; i++) results[i] = fn.call(bind, this[i], i, this);
    return results;
  },

  /* Method: every 

  Tests whether all elements in the array pass the test implemented by the provided function.

  Notes:
    Javascript 1.6 method

  See also:
    <http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:every>
  */
  every: function(fn, bind){
    for (var i = 0, j = this.length; i < j; i++){
      if (!fn.call(bind, this[i], i, this)) return false;
    }
    return true;
  },

  /* Method: some 

  Tests whether some element in the array passes the test implemented by the provided function.

  Notes:
    Javascript 1.6 method

  See also:
    <http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:some>
  */
  some: function(fn, bind){
    for (var i = 0, j = this.length; i < j; i++){
      if (fn.call(bind, this[i], i, this)) return true;
    }
    return false;
  },

  /* Method: indexOf 

  Returns the first index at which a given element can be found in the array, or -1 if it is not present.

  Notes:
    Javascript 1.6 method

  See also:
    <http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Global_Objects:Array:indexOf>
  */
  indexOf: function(item, from){
    var len = this.length;
    for (var i = (from < 0) ? Math.max(0, len + from) : from || 0; i < len; i++){
      if (this[i] === item) return i;
    }
    return -1;
  }

});

UWA.merge(Array.prototype, {

  /* Method: normalize

  Not documented

  Notes:
   - needed for compatibility with a third-party UWA implementation

  */
  normalize: function(sum) {
    var x = 0;
    var ratio = sum / this.inject(0, function(a, n) { return a + n; } );
    for (var i = 0; i < this.length - 1; i++) x += (this[i] *= ratio);
    this[this.length - 1] = sum - x;
  },

  /* Method: equals

  Test wether the array equals to the one passed as parameter.

  Notes:
   - needed for compatibility with a third-party UWA implementation

  */
  equals: function(compare) {
    if (!compare) {
      return false;
    }
    var len = this.length;
    if (len != compare.length) {
      return false;
    }
    for (var i = 0; i < len; i++) {
      if (this[i] != compare[i]) {
        return false;
      }
    }
    return true;
  },

  /* Method: detect

  Not documented

  Notes:
   - needed for compatibility with the TabView Control
   - needed for compatibility with the multiplefeeds native widget

  */
  detect: function(iterator) {
    var result;
    this.each(function(value, index) {
      if (iterator(value, index)) {
        result = value;
        return result;
      }
    });
    return result;
  }

});

if (typeof Array.prototype.each != "function") {
  Array.prototype.each = Array.prototype.forEach;
}
