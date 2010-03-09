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

if (typeof Environments == "undefined") var Environments = {};

if (typeof Widgets == "undefined") var Widgets = {};

Object.extend(UWA.Environment.prototype, {

  initialize: function () {
      this.vista = {}
  },

  onInit: function () {

    if ((!vistaModule.inFlyout) && System.Gadget.docked) {

      document.getElementById("vistaContent").innerHTML = "";

      var header = document.createElement("div");
      header.setAttribute("id", "moduleDocked");
      header.className = "moduleDocked";
      header.innerHTML = '<div class="ico" id="moduleIcon">&nbsp;</div><div id="moduleTitle" class="title">' + document.title + "</div>";

      var wrapper = document.createElement("div");
      wrapper.setAttribute("id", "wrapper");
      wrapper.className = "docked";
      wrapper.appendChild(header);
      document.getElementById("vistaContent").appendChild(wrapper);

      this.html.body = document.createElement("div");
      this.html.header = header;
      this.html.edit = document.createElement("div");
      this.html.title = document.getElementById("moduleTitle");
      this.html.icon = document.getElementById("moduleIcon")

    } else {

      var header = document.createElement("div");
      header.setAttribute("id", "moduleHeader");
      header.className = "moduleHeader";
      header.innerHTML = '<div class="refresh"><img src="img/refresh.png" onclick="vistaModule.refresh()"></div><div class="ico" id="moduleIcon">&nbsp;</div><div id="moduleTitle" class="title">' + document.title + "</div>";

      var content = document.createElement("div");
      content.setAttribute("id", "moduleContent");
      content.className = "moduleContent";
      if (document.getElementById("moduleContent") != null && document.getElementById("moduleContent").innerHTML != null) {
          content.innerHTML = document.getElementById("moduleContent").innerHTML
      }

      var footer = document.createElement("div");
      footer.setAttribute("id", "moduleFooter");
      footer.className = "moduleFooter";

      var wrapper = document.createElement("div");
      wrapper.setAttribute("id", "wrapper");

      var contentWrapper = document.createElement("div");
      contentWrapper.setAttribute("id", "contentWrapper");

      this.html.body = contentWrapper.appendChild(content);
      this.html.header = wrapper.appendChild(header);
      this.html.edit = document.createElement("div");

      wrapper.appendChild(contentWrapper);
      wrapper.appendChild(footer);
      document.getElementById("vistaContent").innerHTML = "";
      document.getElementById("vistaContent").appendChild(wrapper);

      this.html.icon = document.getElementById("moduleIcon");
      this.html.title = document.getElementById("moduleTitle");
      //this.html.editLink = $("header").getElementsByClassName("edit")[0];
    }
  },

  onRegisterModule: function () {

    // Map element with UWA.Element
    for (var key in this.html) {
        this.module.elements[key] = UWA.$element(this.html[key]);
    }

    this.module.body = this.module.elements['body']; // shortcut

    // Handle edit link
    if (this.html['editLink']) {
      this.html['editLink'].addEvent('click', function() {
        this.callback('toggleEdit');
        return false;
      }.bind(this));
    }

    // Load Module preferences
    var name = document.getElementsByTagName("preference");
    if (name) {
        this.module.setPreferencesXML(name)
    }
  },

  toggleEdit: function() {
    if (this.html['edit'].style.display == 'none') {
      this.module.callback('onEdit');
    } else {
      this.module.callback("endEdit")
    }
  },

  getData: function (name) {
    if (this.data[name]) {
        var value = this.data[name]
    } else {
        var value = System.Gadget.Settings.read("uwa_" + name)
    }

    if (value == "") {
        value = null
    }

    return value;
  },

  setData: function (name, value) {

    this.data[name] = value;

    if (this.module.id) {
        this.setDelayed("saveDatas", this.saveDatas, 1000)
    }

    if (value == null) {
        value = ""
    }

    return System.Gadget.Settings.write("uwa_" + name, value);
  },

  onUpdateBody: function () {
    this.setDelayed("updateSize", vistaModule.refreshSize.bind(vistaModule), 200)
  },

  setIcon: function (name) {

    if (this.module.elements.icon) {
        var url = UWA.proxies['icon'] + "?url=" + encodeURIComponent(this.module.elements.icon);
        this.module.elements.icon.innerHTML = '<img width="16" height="16" src="' + url + '" />';
    }
  }
});

