/*
Class: UWA.Environments.Frame
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

UWA.merge(window, {
  'addListener': UWA.Element.addListener
});

UWA.merge(document, {
  'addListener': UWA.Element.addListener
});

UWA.extend(UWA.Environment.prototype, {
  
  initialize: function() {
    this.generic = { 'inline':false, 'iframed':true };
    this.initCommunication();
  },
  
  onRegisterModule: function(module) {
    
    this.html['body']       = $('moduleContent');
    this.html['header']     = $('moduleHeader');
    this.html['title']      = $('moduleTitle');
    this.html['edit']       = $('editContent');
    this.html['status']     = $('moduleStatus');
    this.html['editLink']   = $('editLink');
    
    for (var key in this.html) {
      this.widget.elements[key] = UWA.$element(this.html[key]);
    }
    
    this.widget.body = this.widget.elements['body']; // shortcut
    
    if (this.html['editLink']) {
      this.html['editLink'].onclick = function() {
        Environment.callback('toggleEdit');
        return false;
      };
    }
    
    this.setPeriodical('handleResizePeriodical', this.handleResize, 250);
  },
  
  toggleEdit: function() {
    if (widget.elements['edit'].style.display == 'none') {
      widget.callback('onEdit');
    } else {
      // note that we don't fire 'endEdit' there because we don't want to save form data
      widget.elements['edit'].hide();
      widget.elements['editLink'].setHTML( _("Edit") );
    }
  },
    
  getData: function(name) {
    if (this.data[name]) return this.data[name];
    return undefined;
  },
  
  setData: function(name, value) {
    if (this.data[name] != value) {
      this.data[name] = value;
      this.sendRemote('setValue', name, value);
    }
  },
  
  deleteData: function(name) {
    delete this.data[name];
    this.sendRemote('deleteValue', name);
  },
  
  setUnreadCount: function(count) {
    this.sendRemote('setUnreadCount', false, count);
  },
  
  setTitle: function(title) {
    this.sendRemote('setTitle', false, title);
  },
  
  setIcon: function(icon) {
    this.sendRemote('setIcon', false, icon)
  },
  
  addStar: function(data) {
    this.sendRemote('addStar', false, UWA.Json.encode(data));
  },
  
  setSearchResultCount: function(count) {
    this.sendRemote('setSearchResultCount', false, count);
  },
  
  handleResize: function() {
    if(typeof this.widget.body == "undefined") {
      this.widget.log('widget.body is not defined : widget #' + this.widget.id + '');
      this.widget.log(this.widget.body);
      return;
    }
    
    // Calculate total widget height by adding widget body/header/status/edit height
    // note: document.body.offsetHeight is wrong in IE6 as it always return the full windows/iframe height
    var height = parseInt(this.html['body'].offsetHeight);
    var html = this.html;
    ['header', 'status', 'edit'].forEach(function(name){
        height += (html[name]) ? html[name].offsetHeight : 0;
    })

    if(height > 0 && height != this.prevHeight) {
      this.sendRemote('resizeHeight', false, height);
    }
    this.prevHeight = height;
  },
  
  handleLinks: function() {
    var links = this.widget.body.getElementsByTagName('a');
    for (var i = 0, lnk; lnk = links[i]; i++) {
      lnk.target = '_blank'; // problem with javascript void(0) links
    }
  },
  
  onUpdateBody: function() {
    this.setDelayed('handleLinks', this.handleLinks, 100);
  },
  
  onUpdatePreferences: function() {
    if (this.widget.elements['editLink']) {
      var editable = this.widget.preferences.some(function(pref){
        return pref.type != 'hidden';
      });
      if (editable) {
        this.widget.elements['editLink'].show();
      } else {
        this.widget.elements['editLink'].hide();
      }
    }
  },
  
  setIcon: function(url) {
    if(this.widget.elements['icon']) {
      var iconUrl = 'http://' + NV_HOST + NV_PATH + 'proxy/favIcon.php?url=' + encodeURIComponent(url);
      this.widget.elements['icon'].innerHTML = '<img width="16" height="16" src="' + iconUrl + '" />';
    }
  },
  
  initCommunication: function() {
    // Choose the best cross-domain messaging mechanism
    this.communicationType = 'proxy';
    if (typeof document.postMessage === 'function') {
      this.communicationType = 'documentPostMessage';
    } else if (typeof window.postMessage === 'function') {
      this.communicationType = 'windowPostMessage';
    }
    if (this.communicationType == 'documentPostMessage' || this.communicationType == 'windowPostMessage') {
      var handler = function(e) {
        var message = UWA.Json.decode(e.data);
        UWA.log('Received message in widget #' + this.widget.id + ': ' + message.action);
        this.publicInterface(message.action, message.name, message.value);
      };
      document.addListener('message', handler.bind(this), false);
      window.addListener('message', handler.bind(this), false);
    }
  },

  publicInterface: function(action, name, value) {
    if (action) {
      switch (action) {
        case 'onResize':
          this.widget.callback('onResize');
          this.widget.callback('onUpdateBody');
          break;
        case 'onRefresh':
          if(this.widget.onRefresh) this.widget.callback('onRefresh');
          else if(this.widget.onLoad) this.widget.callback('onLoad');
          break;
        case 'onUpdateTheme':
          this.updateTheme(value);
          break;
        case 'launchModule':
          this.launchModule();
          break;
        case 'setValue':
          this.widget.data[name] = value;
          break;
        case 'deleteValue':
          delete this.data[name];
          delete this.widget.data[name];
          break;
        case 'onSearch':
        case 'onResetSearch':
        case 'onResetUnreadCount':
        case 'onKeyboardAction':
          this.widget.callback(action, value);
          break;
      }
    }
  },
  
  sendRemote: function(action, name, value) {
    if (this.widget.id == '') {
      UWA.log(action);
      UWA.log('no widget id defined yet.');
      return false;
    }
    // Encode the message into JSON
    var message = UWA.Json.encode({'id': this.widget.id, 'action': action, 'name': name, 'value': value});
    // Implementations of the different communication mechanisms
    switch (this.communicationType) {
      // HTML 5 PostMessage (Firefox 3 - Opera 9 - WebKit)
      case 'documentPostMessage':
      case 'windowPostMessage':
        var target = null;
        if (this.communicationType == 'documentPostMessage') {
          target = parent.document;
        } else if (this.communicationType == 'windowPostMessage') {
          target = parent.window;
        }
        if (target && typeof target.postMessage === 'function') {
          try {
            target.postMessage(message, '*');
          } catch (e) {
            UWA.log('Exception while trying to use ' + this.communicationType);
            if (this.ifproxyUrl) {
              this.sendRemoteUsingProxy(this.ifproxyUrl, 'parent', message);
            }
          }
        } else {
          UWA.log('postMessage interface not available')
        }
        break;
      // Proxy mechanism (iframe insertion with URL hash message)
      case 'proxy':
        if (this.ifproxyUrl) {
          this.sendRemoteUsingProxy(this.ifproxyUrl, 'parent', message);
        } else {
          UWA.log('No iframe proxy URL defined');
        }
        break;
      default:
        UWA.log('Unimplemented communication mechanism');
        break;
      }
      return;
  },
  
  // this method was created because removeListener needs a function
  // as second parameter in IE, it's used in sendRemoteUsingProxy method
  iframeDiscard: function(iframe){
    iframe.removeListener('load', this.iframeDiscard);
    setTimeout(function() {
      document.body.removeChild(iframe);
    }, 500);
  },
  
  sendRemoteUsingProxy: function (proxy, target, message) {
    // Create a new hidden iframe
    var iframe = this.widget.createElement('iframe');
    iframe.setStyle({
      'position'   : 'absolute',
      'visibility' : 'hidden',
      'width'  : '0',
      'height' : '0',
      'top'    : '0',
      'left'   : '0'
    });

    // Manage the onload event and prepare iframe discarding
    iframe.addListener('load', this.iframeDiscard.bind(this, iframe));

    // Compose the message and use it as a hash identifier in the proxy
    var message = 'target='   + escape(target) +
                  '&message=' + escape(encodeURIComponent(message)) +
                  '&origin='  + escape(encodeURIComponent(document.domain)) +
                  '&uri='     + escape(encodeURIComponent(location.href));

    iframe.src = proxy + '#' + message;

    // Append the iframe to the document
    document.body.appendChild(iframe);

    // Public interface notice
    this.publicInterface(message.action, message.name, message.value); 
  } 
});

var Environment = new UWA.Environment();

var widget = Environment.getModule();
