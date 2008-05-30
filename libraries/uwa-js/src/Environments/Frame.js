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
    this.html['body'] = $('moduleContent');
    this.module.elements['body'] = UWA.$element(this.html['body']);
    this.module.body = this.module.elements['body']; // shortcut
    this.setPeriodical('handleResizePeriodical', this.handleResize, 250);
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
    if(typeof this.module.body == "undefined") {
      this.widget.log('widget.body is not defined : widget #' + this.widget.id + '');
      this.widget.log(this.widget.body);
      return;
    }
    var height = parseInt(this.module.body.getDimensions().height);
    if(height > 0 && height != this.prevHeight) {
      this.sendRemote('resizeHeight', false, height);
    }
    this.prevHeight = height;
  },
  
  initCommunication: function() {
    // Choose the best cross-domain messaging mechanism
    if (typeof document.postMessage === 'function') {
      this.communicationType = 'documentPostMessage';
    } else if (typeof window.postMessage === 'function' && !UWA.Client.Engine.gecko) {
      this.communicationType = 'windowPostMessage';
    } else {
      this.communicationType = 'proxy';
    }
    //UWA.log('Communication type: ' + this.communicationType);
    if (this.communicationType == 'documentPostMessage' || this.communicationType == 'windowPostMessage') {
      var handler = function(e) {
        var message = UWA.Json.decode(e.data);
        UWA.log('Received message in widget #' + this.module.id + ': ' + message.action);
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
    if (this.module.id == '') {
      UWA.log(action);
      UWA.log('too fast. no widget id defined.');
      return false;
    }
    // Encode the message into JSON
    var message = UWA.Json.encode({'id': this.module.id, 'action': action, 'name': name, 'value': value});
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
            var origin = location.protocol + '//' + location.host;
            target.postMessage(message, origin);
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
    iframe.addListener('load', function() {
      this.removeListener('load');
      setTimeout(function() {
        document.body.removeChild(iframe);
      }, 500);
    });

    // Compose the message and use it as a hash identifier in the proxy
    var message = 'target='   + escape(target) +
                  '&message=' + escape(encodeURIComponent(message)) +
                  '&domain='  + escape(document.domain) +
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
