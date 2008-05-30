/*
Class: UWA.Environments.Google
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

UWA.extend(UWA.Environment.prototype, {
  
    init: function() {
      if (document.body && $('moduleContent')) {
        this.callback('onInit');
        this.clearPeriodical('init');
        this.log('Environnement loaded');
        this.loaded = true;
        return true;
      }
      return false;
    },
    
    initialize: function() {
      this.google = { 'inline':false, 'iframed':true };
      if (window._IG_Prefs) this.prefs = new _IG_Prefs();
    },
    
    onRegisterModule: function(module) {
       this.html['body'] = $('moduleContent');
       this.module.elements['body'] = UWA.$element(this.html['body']);
       this.module.body = this.module.elements['body']; // shortcut
     },

    getData: function(name) {
      if (this.prefs) {
        var value = this.prefs.getString(name);
        if (value == undefined || value == null || value == '' ) return undefined;
        return value;
      }
    },

    setData: function(name, value) {
      if (this.prefs) return this.prefs.set(name, value);
    },

    onUpdateBody: function() {
      this.setDelayed('handleLinks', this.handleLinks, 100);
      this.setDelayed('handleResize', this.handleResize, 100);
    },

    handleResize: function() {
      if (window._IG_AdjustIFrameHeight) _IG_AdjustIFrameHeight();
    },

    setTitle: function(title) {
      return false;
      title = title.stripTags();
      if (window._IG_SetTitle) _IG_SetTitle(title);
    }

} );

UWA.Data.request = function(url, request) {
  
  var callback = request.onComplete;
  
  if (UWA.proxies[request.proxy]) {
    if (request.proxy == 'feed') {
      url = UWA.proxies[request.proxy] + '?url=' + encodeURIComponent(url) + "&rss=1";
    }
  }
  
  var params = {
    'METHOD' : request.method.toUpperCase()
  }
  
  if (request.postBody) {
    params.POST_DATA = request.postBody;
    delete request.postBody;
  } else if (request.parameters) {
    params.POST_DATA = request.parameters;
    delete request.parameters;
  }
  
  switch(request.type) {
    case 'feed':
    case 'json':
      _IG_FetchContent(url, function(responseText) {
        try {
          eval("var j = " + responseText);
          if (typeof callback == "function") callback(j);
        } catch(e) {
          UWA.log(e);
        }
      }, params);
      break;
    case 'text':
      _IG_FetchContent(url, function(responseText) {
        if (typeof callback == 'function') {
          callback(responseText);
        }
      }, params);
      break;
    case 'xml':
      _IG_FetchXmlContent(url, function(response) {
        if (typeof callback == 'function') {
          callback(response);
        }
      }, params);
      break;
  }
}

var Environment = new UWA.Environment();

var widget = Environment.getModule();
