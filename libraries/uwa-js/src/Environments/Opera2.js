if (typeof Environments == "undefined") var Environments = {};

if (typeof Widgets == "undefined") var Widgets = {};

UWA.extend(UWA.Environment.prototype, {

  initialize: function() {
    this.opera = {};
  },

  onInit: function() {
    this.html['body']       = $('moduleContent');
    this.html['header']     = $('moduleHeader');
    this.html['title']      = $('moduleTitle');
    this.html['icon']       = $('moduleIcon');
    this.html['edit']       = $('editContent');
    this.html['status']     = $('moduleStatus');
    this.html['editLink']   = $('editLink');
  },

  onRegisterModule: function(module) {

    for (var key in this.html) {
      this.widget.elements[key] = UWA.extendElement(this.html[key]);
    }

    this.widget.body = this.widget.elements['body'];

    if (this.html['editLink']) {
      this.html['editLink'].show();
      this.html['editLink'].onclick = ( function() {
        this.callback('toggleEdit');
        return false;
      } ).bind(this);
    }

  },

  toggleEdit: function() {
    if (this.widget.elements['edit'].style.display == 'none') {
      this.widget.callback('onEdit');
    } else {
      // note that we don't fire 'endEdit' there because we don't want to save form data
      this.widget.elements['edit'].hide();
      this.widget.elements['editLink'].setHTML( _("Edit") );
    }
  },

  getData: function(name) {
    if (this.data[name]) {
      var value = this.data[name];
    } else if (window.widget) {
      var value = window.widget.preferenceForKey(this.createkey(name));
    }
    return value;
  },

  setData: function(name, value) {
    this.data[name] = value;
    if (window.widget) {
      return window.widget.setPreferenceForKey(value, this.createkey(name));
    }
  },

  createkey: function(key) {
    if (window.widget) {
      return window.widget.identifier + "-" + key;
    }
    return key;
  },

  onUpdateBody: function() {
    var content = document.body || this.widget.body;
    var links = content.getElementsByTagName('a');
    for (var i = 0, lnk; lnk = links[i]; i++) {
      if (typeof lnk.onclick != "function") {
        lnk.onclick = function() {
          if (window.widget) {
            window.widget.openURL(this.href);
          } else {
            window.open(this.href);
          }
          return false;
        }
      }
    }
  },

  openURL: function(url) {
    if (window.widget) {
      return window.widget.openURL(url);
    } else {
      return window.open(url);
    } 
  }

} );
