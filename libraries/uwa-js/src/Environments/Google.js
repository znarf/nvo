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
Class: UWA.Environments.Google
*/

UWA.extend(UWA.Environment.prototype, {

    initialize: function() {
      this.google = { 'inline':false, 'iframed':true };
      if (gadgets && gadgets.Prefs) this.prefs = new gadgets.Prefs();
    },

    onRegisterModule: function(module) {
       this.html['body'] = $('moduleContent');
       this.html['status'] = $('moduleStatus');
       this.module.elements['body'] = UWA.extendElement(this.html['body']);
       this.module.body = this.module.elements['body'];
       this.setPeriodical('handleResizePeriodical', this.handleResize, 250);
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

    handleLinks: function() {
      var links = this.widget.body.getElementsByTagName('a');
      for (var i = 0, lnk; lnk = links[i]; i++) {
        lnk.target = '_blank';
      }
    },

    handleResize: function() {
      // Calculate total widget height by adding widget body/header/status height
      // note: document.body.offsetHeight is wrong in IE6 as it always return the full windows/iframe height
      var height = parseInt(this.html['body'].offsetHeight);
      height += (this.html['header']) ? this.html['header'].offsetHeight : 0;
      height += (this.html['status']) ? this.html['status'].offsetHeight : 0;
      if (height > 0 && height != this.prevHeight) {
        if (gadgets && gadgets.window && gadgets.window.adjustHeight) {
           gadgets.window.adjustHeight(height);
        }
      }
      this.prevHeight = height;
    },

    setTitle: function(title) {
      title = title.stripTags();
      if (gadgets && gadgets.window && gadgets.window.setTitle) {
        gadgets.window.setTitle(title);
      }
    },

    onUpdatePreferences: function() {
      // fix boolean
      for (var i = 0 ; i < widget.preferences.length; i++) {
        var pref = widget.preferences[i];
        if (pref.type == 'boolean' && widget.data[pref.name] == '1') {
          widget.data[pref.name] = 'true';
        }
      }
    }

} );

UWA.Data.request = function(url, request) {

  var callback = request.onComplete;

  if (UWA.proxies[request.proxy]) {
    if (request.proxy == 'feed') {
      url = UWA.proxies[request.proxy] + '?url=' + encodeURIComponent(url) + "&rss=1";
    }
  }

  var method = request.method || 'get';

  var params = {
    'METHOD' : method.toUpperCase()
  }

  if (request.postBody) {
    params.POST_DATA = request.postBody;
    delete request.postBody;
  } else if (request.parameters) {
    params.POST_DATA = request.parameters;
    delete request.parameters;
  }

  switch (request.type) {
    case 'feed':
    case 'json':
      gadgets.io.makeRequest(url, function(response) {
        try {
          eval("var j = " + response.data);
          if (typeof callback == "function") callback(j);
        } catch(e) {
          UWA.log(e);
        }
      }, params);
      break;
    case 'text':
      gadgets.io.makeRequest(url, function(response) {
        if (typeof callback == 'function') {
          callback(response.data);
        }
      }, params);
      break;
    case 'xml':
      params[gadgets.io.RequestParameters.CONTENT_TYPE] = gadgets.io.ContentType.DOM;
      gadgets.io.makeRequest(url, function(response) {
        if (typeof callback == 'function') {
          callback(response.data);
        }
      }, params);
      break;
  }
}

var Environment = new UWA.Environment();

var widget = Environment.getModule();
