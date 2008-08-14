if (typeof Environments == "undefined") var Environments = {};

if (typeof Widgets == "undefined") var Widgets = {};

UWA.extend(UWA.Environment.prototype, {

  initialize: function() {
    this.dashboard = {};
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

    this.widget.elements['editLink'].empty().show().addClassName('infoButton');
    new AppleInfoButton(this.html['editLink'], $('wrapper'), "black", "white", this.showDashboardPrefs.bind(this));

    this.callback('onUpdatePreferences');

  },

  showDashboardPrefs: function() {

    var editContent = this.widget.elements['edit'];

    this.widget.elements['edit'].empty();

    if (this.hasPreferences()) {
      this.prefsForm = new Netvibes.UI.PrefsForm( { module: this.widget, displayButton: (window.widget ? false : true) } );
      this.form = this.widget.elements['edit'].appendChild(this.prefsForm.getContent());
    }

    if (window.widget) {
      if (this.form) {
        this.form.onsubmit = function() { return false }; // desactive browser default form handling
      }
      var doneButton = UWA.createElement('div');
      doneButton.setAttribute('id','doneButton');
      this.widget.elements['edit'].appendChild(doneButton);
      doneButton = new AppleGlassButton(doneButton, "Done", this.hideDashboardPrefs.bind(this) );
      doneButton.textElement.style.color = '#333';
    }

    var infos = this.widget.getInfos();
    if (infos) {
      this.widget.elements['edit'].addContent(infos);
    }

    if (this.widget.uwaUrl) {
      var upgrade = UWA.createElement('a', {
        href: 'http://' + NV_MODULES + '/widget/dashboard/?uwaUrl=' + encodeURIComponent(this.widget.uwaUrl)
      }).setStyle(
        {'display': 'block', 'padding': '10px', 'text-align': 'right'}
      ).setHTML(
        'Recompile this widget'
      ).inject(editContent);
      // to handle links with window.widget.openURL
      this.callback('onUpdateBody');
    }

    if (window.widget) {
      window.widget.prepareForTransition("ToBack");
    }

    this.widget.elements['body'].hide();
    this.widget.elements['edit'].show();
    this.widget.elements['editLink'].hide();

    if (window.widget) {
      setTimeout("window.widget.performTransition()", 0);
    }

  },

  hideDashboardPrefs: function() {

    if (this.prefsForm) {
      this.prefsForm.saveValues();
    } 

    if (window.widget) {
      this.html['editLink'].show();
      // freezes the widget so that you can change it without the user noticing
      window.widget.prepareForTransition("ToFront");   
    }

    this.widget.elements['body'].show();
    this.widget.elements['edit'].hide();

    if (this.widget.onRefresh) {
      this.widget.onRefresh();
    } else if (this.widget.onLoad) {
      this.widget.onLoad();
    } 

    // and flip the widget over
    if (window.widget) {
      setTimeout("window.widget.performTransition()", 250);
    }

  },

  hasPreferences: function() {
    return this.widget.preferences.some(function(pref){
      return pref.type != 'hidden';
    });
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

UWA.log = function(string) {
  if (window.alert) {
    window.alert(string)
  }
}
