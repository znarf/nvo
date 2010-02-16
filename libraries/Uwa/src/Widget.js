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
Class: Widget

The Widget class provides abstract methods to create and manipulate UWA widgets.
The Widget object is typically instanciated as the *widget* var in a widget execution scope.
*/


UWA.Widget = function() {

  /* Section: Properties */

  /* Property: id
    *String* - Unique identifier of the widget.
    The value depends on the execution environment: the Environment registration handler sets this property.
  */
  this.id = '';

  /*
  Property: environment
    *Object* - Reference to the execution environnement.
    The Environment registration handler sets this property. Instance of the Environment class.
  */
  this.environment = null;

  /*
  Property: title
    *String* - widget's title.
    The title of the widget. It is set by the <setTitle> method.
  */
  this.title = '';

  /*
  Property: body
    *Object* - widget's body.
    The main HTML element of the widget.
    Value is null until the <widget> is fully registered in the Environment.
    Should not be used before <launch> or <onLoad> are fired.
  */
  this.body = null;

  /*
  Property: data
    *Object* - Stores widget's data.
    This property can be modified by the <setValue> method, and accessed by the <getValue> method.
  */
  this.data = {};

  /* deprecated - internal or advanced use only */
  this.callbacks = {};

  /*
  Property: preferences
    *Array*: Stores widget's preferences.
    The array is initially empty. It is initialised by the <setPreferences> method.
  */
  this.preferences = [];

  /*
  Property: metas
    *Object* - Stores widget's metas.
    The object is initially empty. It is initialised by the <setMetas> method.
  */
  this.metas = {};

  /*
  Property: debugMode
    *Boolean* - activates or desactivates the debug mode for the widget.
    The default value is TRUE. When TRUE, messages written with <log> method will appear in the console.
  */
  this.debugMode = false;

  /*
  Property: periodicals
    *Object* - Stores widget's periodical events.
    The object is initially empty. It is filled by the <setPeriodical> method.
  */
  this.periodicals = {};

  /*
  Property: searchResultCount
    *Integer* - the search result count when the widget is onSearch.
    This property is set by the <setSearchResultCount> method.
  */
  this.searchResultCount = 0;

  /*
  Property: unreadCount
    *Integer* - the count of unread items in the widget.
    The unread count is set by the <setUnreadCount> method.
  */
  this.unreadCount = 0;

  /* deprecated - internal or advanced use only */
  this.prefsForm = null;

  /* deprecated - internal or advanced use only */
  this.elements = {};

  /* deprecated */
  this.inline = false;

  /* internal or advanced use only */
  this.apiVersion = '1.2';

  /*
  Property: lang
    *String* - The preferred language as defined by the Environment.
  */
  this.lang = 'en_US';

  /*
  Property: locale
    *String* - The preferred locale as defined by the Environment.
  */
  this.locale = 'us';

  /*
  Property: dir
    *String* - The preferred direction as defined by the Environment.
  */
  this.dir = 'ltr';

  /* internal or advanced use only */
  this.isNew = false;

  /*
  Property: readOnly
    *Boolean* - Default to false. True if the widget is currently read only for the viewer.
  */
  this.readOnly = false;

  /* new - internal or advanced use only */
  this.theme = null;

  /* new - internal or advanced use only */
  this.userId = null;

  if(this.initialize) this.initialize();

}

UWA.Widget.prototype = {

  /* Section: Methods */

  /* Group: Content management */

  /*
  Method: setTitle

  Sets the title of the Widget.

  Parameters:
    * String title: The title of the widget. Can contain HTML code.
    * String extended: An extra string. Internal use only.

  Example:
    > widget.setTitle('Netvibes Blog');
    or
    > widget.setTitle('<a href="http://blog.netvibes.com/">Netvibes Blog</a>');

  Notes:
    Implementation can differ between environments.
  */
  setTitle: function(title, extended) {
    this.title = title;
    if (this.elements['title']) {
      if (extended) {
         extended = ' ' + extended + '';
       } else {
         extended = '';
       }
      this.elements['title'].setHTML(title + extended);
    }
    if (this.environment && this.environment.setTitle) this.environment.setTitle(title);
  },

  /*
  Method: getTitle

  Get the title of the Widget.

  Parameters:
   * Nothing

   Returns:
   * String : the title of the widget. HTML tags are stripped.
  */
  getTitle: function() {
    if (this.environment && this.environment.getTitle) return this.environment.getTitle();
    return this.title.stripTags(); // stripTags = prototype.js
  },

  /*
  Method: setBody

  Sets the body of the Widget. Erases the previous body.

  Use the String setContent function.

  Parameters:
    * Object content: The body of the widget.

  Returns:
    * Nothing, but fire the "onUpdateBody" callback.

  Example:
    > var div = widget.createElement('div');
    > div.addClassName('container');
    > div.setHTML("<p>Hello World</p>");
    > widget.setBody(div);
    or
    > widget.setBody("<p>Hello World</p>");
  */
  setBody: function(content) {
    this.body.setContent(content);
    this.callback('onUpdateBody');
  },

  /*
  Method: addBody

  Adds contents to the existing body of the Widget.

  Use the String addContent function.

  Parameters:
    * Object content: The content to add in the body of the widget.

  Returns:
    * Nothing, but calls the methods associated with the "onUpdateBody" callback.

  Example:
    > var div = widget.createElement('div');
    > div.addClassName('footer');
    > div.setText("Powered by Netvibes UWA.");
    > widget.addBody(div);
    or
    > widget.addBody("<p>Powered by Netvibes UWA.</p>");
  */
  addBody: function(content) {
    this.body.addContent(content);
    this.callback('onUpdateBody');
  },

  /*
  Method: setIcon

  Sets the icon for the Widget.

  Parameters:
    * String url: the url of the icon. The URL should include the protocol (http://)
    * Boolean search: If true, try to autodiscover the icon for the given url. Internal use only.

  Returns:
    * Nothing.

  Example:
    > widget.setIcon("http://www.netvibes.com/favicon.ico");
  */
  setIcon: function(url, search) {
    if (this.environment.setIcon) {
      this.environment.setIcon(url, search);
    } else if(this.elements['icon']) {
      url = UWA.proxies['icon'] + "?url=" + encodeURIComponent(url);
      this.elements['icon'].setHTML('<img width="16" height="16" src="' + url + '" />');
    }
  },

  /* deprecated */
  setElementId: function(element, id) {
    UWA.log('widget.setElementId is deprecated');
    this.$(element).setAttribute('id', 'm_' + this.id + '_' + id);
  },

  /*
  Method: createElement

  Creates a new element according to the provided "tagName".

   - if options is not defined, works like document.createElement(tagName)
   - if options is defined, works like JS frameworks DOM builders (mootools/prototype) - new Element(tagName, options)

  Parameters:
    * String tagName: the HTML tag name of the element to create.
    * Object options: will be set on the newly-created element using Element#setAttributes.

  Returns:
    * Element: The created element.

  Example:
    > var div = widget.createElement('div');
    or
    > var input = widget.createElement('input', {'type': 'submit', 'value': "Update");
  */
  createElement: function(tagName, options) {
    if (typeof options == 'string') {
      UWA.log('widget.createElement : elName as 2nd argument is deprecated');
      options = {}; // createElement options NEED an objet
    }
    return UWA.createElement(tagName, options);
  },

  /* deprecated */
  $: function(el) {
    UWA.log('widget.$ is deprecated');
    if (typeof el == 'string' && this.elements[el]) {
      el = this.elements[el];
    }
    return UWA.$element(el);
  },

  /*

  Group: Preferences management

  This methods are mostly for internal use or advanced scripting.
  Behavior can differ between differents execution environments.

  */

  /*
  Method: initPreferences

  Initializes preferences of the widget. The method gets values from the environnement.
  If values do not exist in the environment, it sets them to their default values.
  This method is likely internaly fired by the <launch> method of the Widget.

  Parameters:
    * None.
  */
  initPreferences: function() {
    for (var i = 0; i < this.preferences.length; i++) {
      var pref = this.preferences[i];
      if (typeof pref.name == "undefined") { // no name = preference ignored
         continue;
      }
      if (pref.defaultvalue) {
        pref.defaultValue = pref.defaultvalue; // fix after xml parsing
      }
      this.data[pref.name] = this.getValue(pref.name);
      if (this.data[pref.name] == null && pref.defaultValue) {
        this.data[pref.name] = pref.defaultValue;
      }
    }
  },

  /*
  Method: getPreference

  Get a preference with its name.

  Parameters:
    * String name : the name of the preference

  Returns:
    * Object : a preference in its JSON serialization

  Example:
    If you have this preference defined in XML
    > <preference name="limit" type="range" label="Number of items to display" defaultValue="5" step="1" min="1" max="25" />
    You can get its javascript representation with the following code
    > widget.getPreference("limit")
  */
  getPreference: function(name) {
    for(var i = 0; i < this.preferences.length; i++) {
      if(this.preferences[i].name == name) return this.preferences[i];
    }
    return null;
  },

  /*
  Method: setPreferences

  Sets preferences of the widget. Replaces previous preferences.

  Parameters:
    * Array schema: an Array of preferences in their JSON serialization

  Returns:
    * Nothing.

  Example:
    > widget.setPreferences([
    >  {"name":"paging","type":"boolean","label":"Enable pagination","defaultValue":"false"},
    >  {"name":"offset","type":"hidden","defaultValue":"0"}
    > ]);
  */
  setPreferences: function(schema) {
    if (typeof schema == 'object') {
        this.preferences = schema;
        for (var i = 0, l = this.preferences.length; i < l; i++) {
            var name = this.preferences[i].name;
            var defaultValue = this.preferences[i].defaultValue;
            if (defaultValue && !this.getValue(name)) {
                this.setValue(name, defaultValue);
            }
        }
    }
    this.callback('onUpdatePreferences');
  },

  /*
  Method: mergePreferences

  Add preferences to the widget if preferences of the same name are not already defined.

  Parameters:
    * Array schema: an Array of preferences in their JSON serialization

  Returns:
    * Nothing.
  */
  mergePreferences: function(prefs) {
    for (var i = 0; i < prefs.length; i++) {
      if (this.getPreference(prefs[i].name) == null) this.addPreference(prefs[i]);
    }
  },

  /*
  Method: addPreference

  Adds a single preference to the existing preferences of the widget.

  Parameters:
    * Object : a preference in its JSON serialization
  */
  addPreference: function(preference) {
    this.preferences.push(preference);
  },

  /* internal use only - not documented */
  setPreferencesXML: function(prefs) {
    this.preferences = []; // this.setPreferences( [] ); // empty preferences array
    for(var i = 0; i < prefs.length; i++) {
      var preference = {};
      for(var j = 0; j < prefs[i].attributes.length; j++) {
        var name = prefs[i].attributes[j]['nodeName'];
        var value = prefs[i].attributes[j]['nodeValue'];
        preference[name] = value;
      }
      if (preference.type == 'list') {
        var options = prefs[i].getElementsByTagName("option");
        preference.options = [];
        for(var j = 0; j < options.length; j++) {
          var option = {};
          if ( options[j].attributes[0]['value'] ) option[options[j].attributes[0]['name']] = options[j].attributes[0]['value'];
          if ( options[j].attributes[1]['value'] ) option[options[j].attributes[1]['name']] = options[j].attributes[1]['value'];
          preference.options.push(option)
        }
      }
      this.addPreference(preference);
    }
    this.callback('onUpdatePreferences');
  },

  /* to document */
  onEdit: function() {
    if (this.prefsForm) {
      var form = this.prefsForm;
    } else {
      var prefsForm = new UWA.Controls.PrefsForm( { module: this } );
      var form = prefsForm.getContent();
    }
    this.elements['edit'].setContent(form);
    var infos = this.getInfos();
    if(infos) this.elements['edit'].addContent(infos);
    // Fire "ShowEdit" notification with HTMLDivElement "edit" as argument
    this.callback('onShowEdit', this.elements['edit']);
    this.elements['edit'].show();
    if(this.elements['editLink']) this.elements['editLink'].setHTML( _("Close Edit") );
  },
  onCloseEdit: function ()
  {
    this.callback("onHideEdit")
  },
  /* internal or advanced use only - not documented */
  getInfos: function() {
    var content = "";
    if(this.metas['author']) {
      if(this.metas['website']) {
        var content = 'Widget by <strong><a href="' + this.metas['website'] + '" rel="author">' + this.metas['author'] + '</a></strong>';
      } else {
        var content = 'Widget by <strong>' + this.metas['author'] + '</strong>';
      }
      if(this.metas['version']) {
        content += ' - version <strong>' + this.metas['version'] + '</strong>';
      }
    }
    return this.createElement('p').setStyle({'padding': '10px', 'textAlign': 'right'}).setHTML(content);
  },

  /* to document */
  endEdit: function() {
    this.elements['body'].show();
    this.elements['edit'].hide();
    if (this.elements['editLink']) {
      this.elements['editLink'].show().setHTML( _("Edit") );
    }
    if (this.onRefresh) {
      this.onRefresh();
    } else if (this.onLoad) {
      this.onLoad();
    }
    this.callback('onHideEdit');
  },

  /* Group: Data storage */

  /*
  Method: getValue

  Gets the value of the given preference.

  Parameters:
    * String - name: the name of the preference we want the value of.

  Returns:
    * String : the current value of the preference

  Example:
    > var url = widget.getValue("feedUrl");
  */
  getValue: function(name) {
    if (typeof this.data[name] != "undefined") {
      return this.data[name];
    }
    if (this.environment && this.environment.getData) {
      var value = this.environment.getData(name);
      if (value == 'null') {
        value = null;
      }
      this.data[name] = value;
      return value;
    }
    return null;
  },

  /*
  Method: getInt

  Gets the Integer value of the given preference.

  It is particularly advised to use getInt when a preference is of type range.

  Parameters:
    * String name: the name of the preference we want the value of.

  Returns:
    * Number : the current value of the preference, converted as integer.
  */
  getInt: function(name) {
    var value = this.getValue(name);
    if (value == 'true' || value == true) {
      value = 1;
    }
    value = parseInt(value, 10);
    return isNaN(value) ? 0 : value;
  },

  /*
  Method: getBool

  Gets the Boolean value of the given preference.

  It is particularly advised to use getBool when a preference is of type boolean.

  Parameters:
    * String name: the name of the preference we want the value of.

  Returns:
    * Boolean : the current value of the preference, converted as boolean.
  */
  getBool: function(name) {
    return this.getInt(name) ? true : false;
  },

  /*
  Method: setValue

  Sets the value of the given preference.

  Parameters:
    * String name: the name of the preference we want to set.
    * String value: the value of the preference

  Returns:
    * Object: the value of the preference we set.

  Example:
    > widget.setValue("nbItems", "5");
  */
  setValue: function(name, value) {
    if (this.data[name] == value) {
      return value;
    }
    this.data[name] = value;
    var pref = this.getPreference(name);
    if (this.environment && this.environment.setData) {
      this.environment.setData(name, value);
    }
    return value;
  },

  /* new - to document */
  deleteValue: function(name) {
    delete this.data[name];
    if (this.environment && this.environment.deleteData) {
      return this.environment.deleteData(name);
    }
  },

  /* internal or advanced use only - not documented */
  saveValues: function(callback) {
    if (this.environment && this.environment.saveDatas && this.readOnly == false) {
      this.environment.saveDatas(callback);
    } else {
      callback();
    }
  },

  /* Group: Others */

  /*
  Method: log

  Logs widget's messages in the console, if one exists and if the "<debugMode>" is true.
  It is using <UWA.log> which usually works with Firebug, Safari and Opera.

  Parameters:
    * String message: the message to display in the console.

  Example:
    > widget.log("Widget is loading");
  */
  log: function(message) {
    if (this.debugMode === true) UWA.log(message);
  },

  /*
  Method: setPeriodical

  Register a function as periodical event.

  The function will automatically be binded to the current widget object.

  Parameters:
    * String name: the name of the event
    * Function fn: the function to register
    * Integer delay: the execution delay in milliseconds
    * Boolean force: If true, fire the function for the time right now.

  Notes:
    internal or advanced use only

  */
  setPeriodical: function(name, fn, delay, force) {
    this.clearPeriodical(name);
    this.periodicals[name] = setInterval(fn.bind(this), delay);
    if (force) fn();
  },

  /*
  Method: clearPeriodical

  Unregister a periodical event previously registered with <setPeriodical>

  Parameters:
    * String name: the name of the event

  Notes:
    internal or advanced use only

  */
  clearPeriodical: function(name) {
    if (this.periodicals[name]) { clearInterval(this.periodicals[name]) }
  },

  /*
  Method: callback

  Executes the callback method associated with the given callback name (key).
  Returns false if no callback method is associated with the given key.

  Parameters:
    * String name: the callback name (e.g. "onUpdateTitle");
    * Object args: one optional argument
    * Object: an object to bind the callback to

  Returns:
    * Nothing, but calls the method associated with the given callback name (key)
  */
  callback: function(name, args, bind) {

    this.log('widget.callback:' + name);

    if (typeof bind == 'undefined') bind = this;

    try {

      if (this[name]) this[name].apply(bind, [args]);
      if (this.callbacks[name]) this.callbacks[name].apply(bind, [args]);

    } catch(e) {

      this.log('Error:' + e);
    }

    if(this.environment && this.environment.callback) this.environment.callback(name);
  },

  /* deprecated - internal or advanced use only */
  setCallback: function(name, fn) {
    this.callbacks[name] = fn;
  },

  /*
  Method: setMetas

  Set the metas of the widget.

  Parameters:
    * Object metas: metas in a key:value form

  Notes:
    internal or advanced use only

  */
  setMetas: function(metas) {
    this.metas = metas;
    if(this.metas.debugMode) this.setDebugMode(this.metas.debugMode);
    if(this.metas.autoRefresh) this.setAutoRefresh(this.metas.autoRefresh);
  },

  /* to document */
  setDebugMode: function(mode) {
    if (mode === true || mode == 'true') this.debugMode = true; else this.debugMode = false;
  },

  /* deprecated */
  setInline: function(mode) {
    UWA.log('widget.setInline is deprecated');
    if (mode) this.inline = true; else this.inline = false;
  },

  /*
  Method: setAutoRefresh

  Sets the auto-refresh interval for the widget.
  The widget must have a "onRefresh" method to work properly.

  Parameters:
    * Integer - delay: the refresh delay, in *minutes*.

  Returns:
    * Nothing.

  Example:
    > widget.setAutoRefresh(20); // Set the auto-refresh interval to 20 minutes
  */
  setAutoRefresh: function(delay) {
    var rndUpdateTime = Math.round(10*1000*Math.random());
    delay = parseInt(delay);
    if (this.onRefresh && delay && delay > 0) {
      delay = delay * 1000 * 60; // from minutes to milliseconds
      this.setPeriodical('autoRefresh', this.onRefresh, delay + rndUpdateTime);
    }
  },

  /* internal use only - not documented */
  setMetasXML: function(metas) {
    var metasArray = [];
    for(var i = 0; i < metas.length; i++) {
      if(metas[i].name) var name = metas[i].name;
      else var name = metas[i].attributes[0]['nodeValue'];
      if(metas[i].content) var value = metas[i].content;
      else var value = metas[i].attributes[1]['nodeValue'];
      if(value == 'false') value = false; else if(value == 'true') value = true; // booleanise
      metasArray[name] = value;
    }
    this.setMetas(metasArray);
  },

  /*
  Method: setStyle

  Set the stylesheet of the widget with the given CSS rules.

  Notes:
    Internal or advanced use only
  */
  setStyle: function(style) {
    if (typeof style == 'string') {
      UWA.Utils.setCss(this.id, style);
    }
  },

  /* deprecated */
  setCSS: function(css) {
    UWA.log('widget.setCSS is deprecated. Use widget.setStyle instead.');
    UWA.Utils.setCss(this.id, css);
  },

  /* experimental - internal use only - not documented */
  setTemplate: function(module) {
    UWA.log('setTemplate:' + module.name);
    var tpl = module.name;
    var klass = new UWA.Templates[tpl](this);
    klass.createFromJSON(module);
  },

  /* experimental - internal use only - not documented */
  setFeeds: function(feeds) {
    if (typeof UWA.Feeds == "undefined") UWA.Feeds = {};
    for (key in feeds) UWA.Feeds[key] = feeds[key];
  },

  /*
  Method: setSearchResultCount

  Sets the search result count.

  Parameters:
    * Integer - count: the count of results for the current search terms.

  Returns:
    * Nothing, but updates the title with the result count, if greater or equal to zero.
  */
  setSearchResultCount: function(count) {
    this.searchResultCount = count;
    if (this.environment.setSearchResultCount) this.environment.setSearchResultCount(count);
  },

  /*
  Method: setUnreadCount

  Sets the count of unread items.

  Parameters:
    * Integer - count: the count of unread items.

  Returns:
    * Nothing, but updates the title with the unread count, if greater or equal to zero.
  */
  setUnreadCount: function(count) {
    this.unreadCount = count;
    if (this.environment && this.environment.setUnreadCount) this.environment.setUnreadCount(count);
  },

  /*
  Method: openURL

  Open an URL. Behavior differ between execution environments.
    - open the page in an iframe on the same screen
    - open the page in a new window/tab
    - open the page in a new browser window (desktop widgets)

  Parameters:
    * String url: the url to open in a new window
  */
  openURL: function(url) {
    if (this.environment && this.environment.openURL) this.environment.openURL(url);
    else window.open(url);
  },

  /* experimental - to be documented */
  getHistory: function() {
    if (this.environment && this.environment.getHistory) return this.environment.getHistory();
    else return this.getValue('history');
  },
  setHistory: function(history) {
    if (this.environment && this.environment.setHistory) this.environment.setHistory(history);
    else this.setValue('history', history);
  },
  saveHistory: function() {
    if (this.environment && this.environment.saveHistory) this.environment.saveHistory();
  },
  addStar: function(data) {
    if (this.environment && this.environment.addStar) this.environment.addStar(data);
  },

  /*
  Method: launch

  Launch the widget : call <initPreferences> then fire widget.onLoad.

  Notes:
    Internal or advanced use only
  */
  launch: function() {
    this.initPreferences();
    this.callback('onLoad');
  }

};

// old name
UWA.Module = UWA.Widget;
