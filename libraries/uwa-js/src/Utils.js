/*
Library: UWA Utils
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

UWA.Utils = {
  
  /* only used in /modules/uwa/uwa2.js */
  buildUrl: function (moduleUrl, linkHref) {
        var first_split = moduleUrl.split("://");
        var scheme =  first_split[0]
        var without_resource = first_split[1];
        var second_split = without_resource.split("/");
        var domain = second_split[0];
        var path = '';
        for (i=1;i<second_split.length-1;i++) {
            path += '/' + second_split[i];
        }
        if (linkHref.split("://").length > 1) { // complete
            return false;
        } else if (linkHref.substring(0, 1) == '/') { // absolute
            return scheme +'://' + domain + linkHref;
        } else { // relative
            return scheme +'://' + domain + path + '/' + linkHref;
        }
    },

  setTooltip: function(element, text, width) {
    if (window.App && App.toolTip)
      new App.toolTip(element, text, width, "left");
    return false;
  },
  
  /* used in /js/UWA/Widget.js & /modules/uwa/uwa2.js */
  setCss: function(id, content, namespace) {
    
    if (typeof namespace == 'undefined') {
      var namespace = ( id && id != '' ? '#m_' + id : '');
    }
    
    var cssId = 'css_' + id;
    
    if (!$(cssId)) {
      var css = document.createElement("style");
      css.setAttribute('id', cssId);
      css.setAttribute('type','text/css');
      var head = document.getElementsByTagName('head').item(0);
      head.appendChild(css);
    } 
    
    content = "\n" + content + "\n"; // fix a problem with the final regexp
    content = content.replace(/,/g, ",\n"); // fix a problem with the final regexp
    content = content.replace(/#moduleContent/g, ''); // remove namespace (Netvibes - not documented)
    content = content.replace(/#container/g, ''); // remove namespace (WZD)
    content = content.replace(/\n\s*([a-zA-z0-9\.\-, :#]*)\s*([{|,])/g, "\n" + namespace + " $1$2");
    
    if ($(cssId).styleSheet){ // IE
      $(cssId).styleSheet.cssText = content;
    } else { // w3c
      $(cssId).appendChild( document.createTextNode(content) );
    }
    
  }
  
}
