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
Class: UWA.Environments.Standalone
*/

UWA.extend(UWA.Environment.prototype, {

 initialize: function() {
   this.standalone = {};
 },

 onInit: function() {

   // Creating module header
   var moduleHeader = document.createElement('div');
   moduleHeader.setAttribute('id','moduleHeader');
   moduleHeader.className = 'moduleHeader';

   // Buttons
   // @todo $headerbuttons ?

   moduleHeader.innerHTML =
        '<a id="closeLink" class="close" style="display:none" href="javascript:void(0)">' + _('Close') + '</a>' +
        '<a id="editLink" class="edit" style="display:none" href="javascript:void(0)">' + _('Edit') + '</a>' +
        '<a id="refreshLink" class="refresh" style="display:none" href="javascript:void(0)">' + _('Refresh') + '</a>' +
        '<a id="minimizeLink" class="minimize" style="display:none" href="javascript:void(0)">Minimize</a>' +
        '<a id="moduleIcon" class="ico">' +
        '  <img class="hicon" width="16" height="16" src="http://uwa.service.japanim.fr/img/icon.png"/>' +
        '</a>' +
        '<span id="moduleTitle" class="title">' + document.title + '</span>';

   // Creating module content
   var moduleContent = document.createElement('div');
   moduleContent.setAttribute('id','moduleContent');
   moduleContent.className = 'moduleContent';
   moduleContent.innerHTML = document.body.innerHTML ;

   // Creating edit content
   var editContent = document.createElement('div');
   editContent.setAttribute('id','editContent');
   editContent.style.display = 'none';
   editContent.className = 'editContent optionContent configureContent';

   // Creating widget env into docurment body
   document.body.innerHTML = '';

   var wrapper = document.createElement('div');
   wrapper.setAttribute('id','wrapper');

   this.html['header']  = wrapper.appendChild(moduleHeader);
   this.html['edit']    = wrapper.appendChild(editContent);
   this.html['body']    = wrapper.appendChild(moduleContent);

   document.body.appendChild(wrapper);

   this.html['title']       = document.getElementById('moduleTitle');
   this.html['icon']        = document.getElementById('moduleIcon');
   this.html['editLink']    = document.getElementById('editLink');
   this.html['refreshLink'] = document.getElementById('refreshLink');

 },

 onRegisterModule: function() {

   for (var key in this.html) {
      this.module.elements[key] = UWA.extendElement(this.html[key]);
    }

   this.module.body = this.module.elements['body'];

   // Handle Edit link click
   this.html['editLink'].show();
   this.html['editLink'].addEvent("click", function() {
     this.callback('toggleEdit');
     return false;
   }.bind(this));

   // Handle Refresh link click
   this.html['refreshLink'].show();
   this.html['refreshLink'].addEvent("click", function() {
     this.module.callback('onRefresh');
     return false;
   }.bind(this));

   // Load preferences
   var xmlMetas = document.getElementsByTagName("meta");
   if(xmlMetas && xmlMetas.length) this.module.setMetasXML(xmlMetas);

   var xmlPrefs = document.getElementsByTagName("preference");
   if (xmlPrefs && xmlPrefs.length) this.module.setPreferencesXML(xmlPrefs);

   // Load icon
   var links = document.getElementsByTagName('link');
   for(var i = 0; i < links.length; i++) {
     if(links[i].getAttribute('rel') == 'icon') {
       this.module.metas.icon = links[i].getAttribute('href');
     }
   }

   if(this.module.metas.icon) {
     this.module.setIcon(this.module.metas.icon, true);
   }

   this.setDelayed('launchModule', this.launchModule, 100);
 },

 toggleEdit: function() {
   if (this.module.elements['edit'].style.display == 'none') {
     this.module.callback('onEdit');
   } else {
     this.module.callback('endEdit');
   }
 },

 getData: function(name) {

   this.log('getData:' + name);

   if(typeof(document.cookie) != "undefined") {
     var name = 'uwa-' + name;
     var index = document.cookie.indexOf(name);
     if ( index != -1) {
       var nStart = (document.cookie.indexOf("=", index) + 1);
       var nEnd = document.cookie.indexOf(";", index);
       if (nEnd == -1) {
         var nEnd = document.cookie.length;
       }
       return unescape(document.cookie.substring(nStart, nEnd));
     }
   }
 },

 setData: function(name, value) {

   widget.log('setData:' + name + ':' + value);

   if (typeof(document.cookie) != "undefined") { // Valid cookie ?
     var name = 'uwa-' + name;
     var expires = 3600 * 60 * 24; // 24 days by default
     var expires_date = new Date( new Date().getTime() + (expires) );
     var cookieData = name + "=" + escape(value) + ';' +
       ((expires) ? "expires=" + expires_date.toGMTString() + ';' : "") +
       "path=" + escape(window.location.pathname) + ';' +
       "domain=" + escape(window.location.hostname);

       document.cookie = cookieData;
       return true;
   }
   return false;
 },

 deleteData: function(name) {

   this.log('deleteData:' + name);

   return this.setData(name, null);
 },

 addStar: function() {

   UWA.Client.addStar(location.href, this.module.getTitle());
 },

 setIcon: function(icon) {
   if (this.html['icon']) {
     var url = UWA.proxies['icon'] + '?url=' + encodeURIComponent(icon);
     this.html['icon'].setHTML('<img width="16" height="16" src="' + icon + '" />');
   }
 }
});

var Environment = new UWA.Environment();
var widget = Environment.getModule();

// Force Json Ajax request throw script tag
UWA.Data.useJsonRequest = true;

window.onresize = function() {
  widget.callback('onResize');
}


