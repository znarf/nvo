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

  // creating module header
  var moduleHeader = document.createElement('div');
  moduleHeader.setAttribute('id','moduleHeader');
  moduleHeader.className = 'moduleHeader';
  moduleHeader.innerHTML = 
      '<div class="edit"><a href="#">Edit</a></div>' +
      '<div class="refresh"><img src="http://www.netvibes.com/api/0.3/refresh.gif" /></div>' +
      '<div class="ico" id="moduleIcon" style="padding-left:3px"></div>' +
      '<div id="moduleTitle" class="title">' + document.title + '</div>';
  
  // creating module content
  var moduleContent = document.createElement('div');
  moduleContent.setAttribute('id','moduleContent');
  moduleContent.className = 'moduleContent';
  moduleContent.innerHTML = document.body.innerHTML ;
  
  // creating edit content
  var editContent = document.createElement('div');
  editContent.setAttribute('id','editContent');
  editContent.style.display = 'none';
  editContent.className = 'editContent optionContent configureContent';
  
  document.body.innerHTML = '';
  
  var wrapper = document.createElement('div');
  wrapper.setAttribute('id','wrapper');

  this.html['header'] = wrapper.appendChild(moduleHeader);
  this.html['edit'] = wrapper.appendChild(editContent);
  this.html['body'] = wrapper.appendChild(moduleContent);

  document.body.appendChild(wrapper);
  
  this.html['title'] = document.getElementById('moduleTitle');
  this.html['icon'] = document.getElementById('moduleIcon');
  
  var addto = document.createElement('div');
  addto.setAttribute('id','addto');
  
  if( typeof UWA.widgetTrueURL == 'undefined' ) UWA.widgetTrueURL = document.location.href;

  addto.innerHTML =  '<a style="border:0" title="Add this module to Netvibes" href="' + NV_PATH + 'subscribe.php?module=UWA&amp;moduleUrl=' + encodeURIComponent(UWA.widgetTrueURL) + '"><img alt="Add to Netvibes" src="' + NV_PATH + 'img/uwa-netvibes.png" /></a>';

  addto.innerHTML +=  '<br /> <br /><a style="border:0" title="Add this module to Google Homepage" href="http://www.google.com/ig/add?moduleurl=' + encodeURIComponent(NV_PATH + 'api/uwa/compile/google.php?moduleUrl=' +  encodeURIComponent(UWA.widgetTrueURL) ) + '"><img alt="Add to Google Homepage" src="' + NV_PATH + 'img/uwa-google.png" /></a>';

  document.body.appendChild(addto);
    
},

onRegisterModule: function() {
  
  for (var key in this.html) {
    this.module.elements[key] = UWA.$element(this.html[key]);
  }
  
  // new syntax
  this.module.body = this.module.elements['body'];
  
  var editLink = UWA.$element( this.module.elements['header'].getElementsByClassName('edit')[0] );
  editLink.addEvent("click", function() {
    Environment.callback('toggleEdit');
    return false;
  });
  
  var refreshLink = UWA.$element( this.module.elements['header'].getElementsByClassName('refresh')[0] );
  refreshLink.addEvent("click", function() {
    widget.callback('onRefresh');
    return false;
  });
  
  var xmlMetas = document.getElementsByTagName("meta");
  if(xmlMetas && xmlMetas.length) this.module.setMetasXML(xmlMetas);
  
  var xmlPrefs = document.getElementsByTagName("preference");
  if (xmlPrefs && xmlPrefs.length) this.module.setPreferencesXML(xmlPrefs);

  var links = document.getElementsByTagName('link');
  for(var i = 0; i < links.length; i++) {
    if(links[i].getAttribute('rel') == 'icon') {
      this.module.metas.icon = links[i].getAttribute('href');
    }
  }
  if(this.module.metas.icon) {
    this.module.setIcon(this.module.metas.icon, true);
  }
  
  this.setDelayed('launchModule', this.launchModule, 100)
     
},

toggleEdit: function() {
  if (widget.elements['edit'].style.display == 'none') {
    widget.callback('onEdit');
  } else {
    widget.elements['edit'].hide();
  }
},

showEdit: function() {
  if(this.module.onEdit) this.module.onEdit();
},

getData: function(name) {
  widget.log('getData:' + name);
  if(window.Cookie) return Cookie.get('uwa-' + name);
},

setData: function(name, value) {
  widget.log('setData:' + name + ':' + value);
  if(window.Cookie) return Cookie.set('uwa-' + name, value);
},

deleteData: function(name) {
  widget.log('deleteData:' + name);
  if(window.Cookie) return Cookie.remove('uwa-' + name);
},

addStar: function() {
  alert("Starring not available in Standalone mode.")
}

});

var Environment = new UWA.Environment();

var widget = Environment.getModule();

UWA.Data.useJsonRequest = true;

UWA.proxies.feed = UWA.proxies.ajax;

function _(s) {
  return s
}

window.onresize = function() {
  widget.callback('onResize');
}
