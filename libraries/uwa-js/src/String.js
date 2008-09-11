/*
Script: String

Extensions to the native JavaScript String class.

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

UWA.merge(String.prototype, {

  /* Method: stripTags 

  Strips a string of any HTML tag.

  Original documentation:
    <http://www.prototypejs.org/api/string/stripTags>

  */
  stripTags: function() {
    return this.replace(/<\/?[^>]+>/gi, '');
  },

  /* Method: truncate 

  Truncates a string to the given length and appends a suffix to it (indicating that it is only an excerpt).

  Original documentation:
    <http://www.prototypejs.org/api/string/truncate>

  Notes:
    needed for backward compatibility with a third-party UWA implementation

  */
  truncate: function(length, truncation) {
    length = length || 30;
    truncation = truncation === undefined ? '...' : truncation;
    return this.length > length ?
      this.slice(0, length - truncation.length) + truncation : String(this);
  },

  /* Method: escapeRegExp 

  Returns string with escaped regular expression characters

  Original documentation:
    <http://docs.mootools.net/Native/String.js#String.escapeRegExp>

  Notes:
    needed for compatibility with Vibes native widget

  */
  escapeRegExp: function() {
    return this.replace(/([.*+?^${}()|[\]\/\\])/g, '\\$1');
  },

  /* Method: trim 

  Trims the leading and trailing spaces off a string.

  Original documentation:
    <http://docs.mootools.net/Native/String.js#String.trim>

  Notes:
    needed for compatibility with a third-party UWA implementation

  */
  trim: function(){
    return this.replace(/^\s+|\s+$/g, '');
  },

  /* Method: isEmail

  Not documented

  Notes:
    needed for compatibility with a third-party UWA implementation

  */
  isEmail: function() {
    var regexp = /^([a-zA-Z0-9_.\-+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regexp.test(this);
  },

  /* Method: s

  Not documented
    
  Notes:
    needed for compatibility with various Netvibes native widget

  */
  s: function() {
    var str = this;
    if (arguments.length < 1) return str;
    var re = /([^%]*)%s(.*)/;
    var a = [], numSubstitutions = -1;
    while (a = re.exec(str)) {
      var leftpart = a[1], rightPart = a[2];
      if (++numSubstitutions >= arguments.length) {
        break;
      }
      str = leftpart + arguments[numSubstitutions] + rightPart;
    }
    return str;
  },
  
  /* Method: format

  Not documented
    
  Notes:
    needed for compatibility with various Netvibes native widget (new format)

  */
  format : function() {
    var args = arguments;
    return this.replace(/\{(\d+)\}/g, function(m, i){
      return args[i];
    });
  },
    
  /* Method: parseRelativeTime

  Not documented

  Notes:
    needed for Timeline Control, from /js/App/Core/String.js

  */
  parseRelativeTime: function(raw, offset) {
    if (typeof offset != 'number') offset = 0;

    var matches = (raw && raw.match(/^(\d\d\d\d)\-(\d\d)\-(\d\d) (\d\d):(\d\d):(\d\d)$/));
    if (!matches) return false;

    var date = new Date(matches[1], matches[2] - 1, matches[3], matches[4], matches[5], matches[6]);
    var relative_to = new Date();
    var delta = parseInt((relative_to.getTime() - date.getTime()) / 1000); // Compute the diff
    delta = delta + (relative_to.getTimezoneOffset() * 60 + 3600 * offset); // Add timezone offset (+1 because we are not in GMT)
    if (delta < 60) {
      return _("less than a minute ago");
    } else if(delta < 120) {
      return _("about a minute ago");
    } else if(delta < (45*60)) {
      return _("{0} minutes ago").format( Math.round(delta/60) );
    } else if(delta < (90*60)) {
      return _("about an hour ago");
    } else if(delta < (24*60*60)) {
      return _("about {0} hours ago").format( Math.round(delta/3600) );
    } else if(delta < (48*60*60)) {
      return _("yesterday");
    } else {
      return _("{0} days ago").format( Math.round(delta/86400) );
    }
  },

  /* Method: contains
  
  Not documented

  Notes:
    used in el.hasClassName implementation in Element.js
    
  */
  contains: function(string, separator) {
    return (separator) ? (separator + this + separator).indexOf(separator + string + separator) > -1 : this.indexOf(string) > -1;
  },

  /* Method: camelCase
  
  Not documented

  Notes:
    used in el.setStyle implementation in Element.js
    
  */
  camelCase: function() {
    return this.replace(/-\D/g, function(match) {
      return match.charAt(1).toUpperCase();
    });
  },

  /* Method: makeClickable
  
  Not documented

  Notes:
    needed for compatibility with various Netvibes native widget
    
  */
  makeClickable: function() {
    var htmlCode = this;
    
    htmlCode = htmlCode.replace(/([\w]+:\/\/|www\.)[\w\d-_]+\.[\w\d-_:%&\?\/.=]+/gi, function(m, match1) {
      var url = m;
      var text = m;
      var trail = '';
      
      var trailingChar = /(.*)([\.\!\?\:\=]$)/.exec(m);
      if (trailingChar){
        url   = trailingChar[1];
        text  = url;
        trail = trailingChar[2];
      }
      
      if (match1 == 'www.'){
        url = 'http://' + url;
      }
      
      return text.link(url) + trail;
    });
    
    // add link to mail address
    htmlCode = htmlCode.replace(/([\w\.\+-]+@[\w\.-]+)/g, '<a href="mailto:$1" target="_blank">$1</a>');

    return htmlCode;
  },

/**
 * @private Not Documented.
 * Note: needed for compatibility with various Netvibes native widget.
 *
 * @return {String} The converted string.
 */
  unescapeHTML: function() {
    var div = document.createElement('div');
    div.innerHTML = this.stripTags();
    return div.childNodes[0] ? div.childNodes[0].nodeValue : '';
  }
    
});

// Timeline control use String.parseRelativeTime(string)
String.parseRelativeTime = String.prototype.parseRelativeTime;

// Avoid breaking an old example
if (typeof String.highlight == 'undefined') { String.highlight = function(s) { return s } }
