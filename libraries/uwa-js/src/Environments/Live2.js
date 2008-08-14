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

/*
Live.com Gadgets documentation can be found there:
  - http://dev.live.com/gadgets/
  - http://dev.live.com/gadgets/sdk/docs/default.htm
  - http://dev.live.com/gadgets/sdk/docs/apiref.htm
*/

// register the gadget namespace
registerNamespace("Netvibes.UWA");

// define the constructor for the Gadget (this must match the name in the manifest XML)
Netvibes.UWA.Live = function(p_elSource, p_args, p_namespace) {

  // always call initializeBase before anything else
  Netvibes.UWA.Live.initializeBase(this, arguments);

  this.body = p_elSource;
  this.gadget = p_args.module;

  // initialize is always called immediately after the object is instantiated
  this.initialize = function(p_objScope) {
      
      // always call the base object's initialize first
      Netvibes.UWA.Live.getBaseMethod(this, "initialize", "Web.Bindings.Base").call(this, p_objScope);
      
      this.environment = Environment = new UWA.Environment();
      this.environment.liveObject = this;
      this.environment.callback('buildSkeleton');

      this.widget = widget = this.environment.getModule();

      if (UWA.script) {
        UWA.script(this.widget);
      } else {
        alert('UWA.script is not available');
      }

      this.environment.launchModule();

  }

  this.dispose = function(p_blnUnload) {
    this.environment = this.widget = null;

    // always call the base object's dispose last
    Netvibes.UWA.Live.getBaseMethod(this, "dispose", "Web.Bindings.Base").call(this, p_blnUnload);

    Netvibes.UWA.Live.registerBaseMethod(this, "dispose");
  }

};

Netvibes.UWA.Live.registerClass("Netvibes.UWA.Live", "Web.Bindings.Base");

UWA.extend(UWA.Environment.prototype, {

  initialize: function() {
    this.live = {};
  },

  buildSkeleton: function() {

    this.liveObject.body.innerHTML = '';

    this.html['edit'] = UWA.createElement('div').addClassName('editContent').hide().inject(this.liveObject.body);

    this.html['body'] = UWA.createElement('div').addClassName('moduleContent').inject(this.liveObject.body);

    this.html['status'] = UWA.createElement('div').addClassName('moduleStatus').inject(this.liveObject.body);
    this.html['status'].setHTML(
      '<a class="share" target="_blank" href="javascript:void(0)">' +
        '<img alt="Share this widget" src="' + NV_STATIC + '/img/share.png"/>' +
      '</a>' +
      '<a class="powered" target="_blank" href="http://www.netvibes.com/">' +
        _("powered by netvibes") +
      '</a>' +
      '<a id="editLink" class="configure" href="javascript:void(0)">' +
        _("Edit") +
      '</a>'
    );

  },

  urlEncode: function(url) {
    return encodeURIComponent(url).replace(/\./g, '%2E');
  },

  onRegisterModule: function() {

    this.html['editLink'] = $('editLink');

    for (var key in this.html) {
      this.module.elements[key] = UWA.extendElement(this.html[key]);
    }

    this.module.body = this.module.elements['body'];

    if (this.html['editLink']) {
      this.html['editLink'].empty();
      this.html['editLink'].onclick = function() {
        Environment.callback('toggleEdit');
        return false;
      };
    }

  },

  getData: function(name) {
    if (this.data[name]) {
      var value = this.data[name];
    } else {
      var value = this.liveObject.gadget.getPreference(name);
    }
    return value;
  },

  setData: function(name, value) {
    this.data[name] = value;
    return this.liveObject.gadget.setPreference(name, value);
  },

  toggleEdit: function() {
    if (this.widget.elements['edit'].style.display == 'none') {
      this.widget.callback('onEdit');
      this.widget.elements['editLink'].empty();
      this.widget.elements['body'].hide();
      this.widget.callback('onUpdateBody');
    } else {
      // note that we don't fire 'endEdit' there because we don't want to save form data
      this.widget.elements['body'].show();
      this.widget.elements['edit'].hide();
      this.widget.callback('onUpdateBody');
    }
  },

  onHideEdit: function() {
    this.widget.elements['editLink'].show().empty();
  },

  onUpdateBody: function() {
    this.handleLinks();
    this.liveObject.gadget.resize();
  },

  onLoadComplete: function() {
    var share = this.html['status'].getElementsByClassName('share')[0];
    share.href = 'http://eco.netvibes.com/share/?url=' + this.urlEncode(this.widget.uwaUrl);
  },

  handleLinks: function() {
    var links = this.module.body.getElementsByTagName('a');
    for( var i = 0, lnk; lnk = links[i]; i++ ) {
      // would be great to find the original purpose of this line
      if(! ( lnk.href && ( lnk.href == ( window.location.href.split( '#' )[0] + '#' ) ) ) ) {
        lnk.target = '_blank';
      }
    }
  }

} );

UWA.Data.request = function(url, request) {

  /* NOTE:
      request.method is not handled
      request.postBody & request.parameters are not handled
      request.cache is not handled
  */

  if (UWA.proxies[request.proxy]) {
    if (request.proxy == 'feed') {
      url = UWA.proxies[request.proxy] + '?url=' + encodeURIComponent(url) + "&rss=1";
    }
  }

  switch (request.type) {
    case 'feed':
    case 'json':
      var callback = function(response) {
        try {
          eval("var j = " + response.responseText);
          if (typeof request.onComplete == "function") request.onComplete(j);
        } catch(e) {
          UWA.log(e);
        }
      }
      break;
    case 'text':
      var callback = function(response) {
        if (typeof request.onComplete == 'function') {
          request.onComplete(response.responseText);
        }
      }
      break;
    case 'xml':
      var callback = function(response) {
        if (typeof request.onComplete == 'function') {
          request.onComplete(response.responseXML);
        }
      }
      break;
  }

  Web.Network.createRequest(Web.Network.Type.XML, url, {proxy:"generic"}, callback).execute();

}