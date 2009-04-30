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
Class: Data

The Data class provides abstract methods to access external resources using Ajax (XMLHttpRequest) requests.

Credits:
  Partially based on MooTools, My Object Oriented Javascript Tools.
  Copyright (c) 2006-2007 Valerio Proietti, <http://mad4milk.net>, MIT Style License.
  Partially based on Prototype JavaScript framework, version 1.6.0 (c) 2005-2007 Sam Stephenson.
  Prototype is freely distributable under the terms of an MIT-style license.
  For details, see the Prototype web site: http://www.prototypejs.org/
*/

if (typeof UWA.proxies == "undefined") {
  
  UWA.proxies = {
    'api'  : NV_PATH + 'proxy/api2Proxy.php',
    'rss'  : NV_PATH + 'proxy/xmlProxy.php',
    'ajax' : NV_PATH + 'proxy/ajaxProxy.php',
    'feed' : NV_PATH + 'proxy/feedProxy.php',
    'xml'  : NV_PATH + 'data/xml/'
  }
  
}


if (typeof UWA.Json == "undefined") UWA.Json = {};

UWA.Json.request = function(url, request) {
  
  var varname = 'json';
  
  if (request.context && request.context[0]) varname += request.context[0];
  else varname += Math.round(1000*1000*Math.random());

  eval(varname + '= false');

  url += '&object=' + varname ;

  var script = document.createElement('script');
  script.setAttribute('type', 'text/javascript');
  script.src = url;
  var head = document.getElementsByTagName('head')[0];
  var insert = head.appendChild(script);

  if (typeof request.onComplete == "undefined") UWA.log('no callback set');

  var callback = request.onComplete;

  var myCallback = function(c){ return function(j) { callback(j, c) } }(request.context);

  var interval = setInterval( ( function() {
    eval('var json = ' + varname);
    if (json) {
      try {
        myCallback(json);
      } catch(e) {
        UWA.log(e);
      }
      insert.parentNode.removeChild(insert);
      clearInterval(interval);
    }
  } ).bind(this), 100);

}


UWA.Data = {
  
  useJsonRequest: false,

  /* Section: methods */

  /* Method: getFeed

  Gets the content of a feed, in a JSON format.

  Parameters:
    * String url: the URL of the feed data source.
    * Function callback: the callback method that will be triggered when the request is succesful. 
      This method *must have one parameter* to receive the feed (JSON format) returned by the request.

  Returns:
    * Nothing, but if the request is successful, the callback method is fired and receives the feed as parameter.
  
  Example:
    (start code)
    UWA.Data.getFeed('http://feeds.feedburner.com/NetvibesDevBlog', myModule.display);
    myModule.display = function(aFeed) {
      // your display code 
    }
    (end code)

  Notes:
    In this example, the callback method is named "display", and is used to display the feed content, which is contained in the aFeed variable.
  */
  getFeed: function(url, callback) {
    if(UWA.Feeds && UWA.Feeds[url]) {
      callback(UWA.Feeds[url]);
      setTimeout(function() { UWA.Feeds[url] = null }, 15000);
      return;
    }
    if (typeof UWA.feedCallbackType == "undefined") UWA.feedCallbackType = "json";
    return this.request(url, { method : 'GET', proxy: 'feed', type: UWA.feedCallbackType, onComplete: callback } );
  },


  /* Method: getXml

  This method is used to get the content of an external XML data source. 
  It can be used to retrieve the content of a feed in XML format.

  Parameters:
    * String url: the URL of the XML data source,
    * Function callback: the callback method that will be fired when the request is succesful. 
    This method *must have one parameter* to receive the XML content returned by the request.

  Returns:
    * Nothing, but if the request is successful, the callback method is fired and receives the XML content as parameter.
  
  Example:
    (start code)
    UWA.Data.getXml('http://example.com/content.xml', myModule.parse);
    myModule.parse = function(xml) {
      // your parsing code
    }
  (end)

  Notes:
    In this example, the callback method is named "parse", and is used to parse the XML tree, which is contained in the "xml" variable.
  */
  getXml: function(url, callback) {
    return this.request(url, { method : 'GET', type: 'xml', onComplete: callback } );
  },
  

  /* Method: getText

  This method is used to get the content of an external data source. 
  It can be used to retrieve any kind of content, as long as it is made of text.

  Parameters:
    * String url: the URL of the data source,
    * Function callback: the callback method that will be fired when the request is succesful. 
    This method *must have one parameter* to receive the text content returned by the request.

  Returns:
    * Nothing, but if the request is successful, the callback method is fired and receives the XML content as parameter.
  
  Example:
    (start code)
    UWA.Data.getText('http://example.com/content.txt', myModule.parse);
    myModule.parse = function(text) {
      // your parsing code
    }

  Notes:
    In this example, the callback method is named "parse", and is used to display the feed content, which is contained in the "text" variable.
  (end)
  */
  getText: function(url, callback) {
    return this.request(url, { method : 'GET', type: 'text', onComplete: callback } );
  },
  

  /* Method: getJson

  This method is used to get the content of an external JSON data source. 
  It can be used to retrieve any kind of JSON data.

  Parameters:
    * String url: the URL of the data source,
    * Function callback: the callback method that will be fired when the request is succesful. 
    This method *must have one parameter* to receive the JSON content returned by the request.

  Returns:
    * Nothing, but if the request is successful, the callback method is fired and receives the JSON content as parameter.
  
  Example:
    (start code)
    UWA.Data.getJson('http://example.com/json.php', myModule.parse);
    myModule.parse = function(text) {
      // your parsing code
    }
  (end)
  */
  getJson: function(url, callback) {
    return this.request(url, { method : 'GET', type: 'json', onComplete: callback } );
  },


  /* Method: getModule

  This method is used to get the content of an external widget, in XML format.

  Parameters:
    * String url: the URL of the feed data source,
    * Function callback: the callback method that will be fired when the request is succesful. 
  This method *must have one parameter* to receive the module content returned by the request.

  Returns:
    * Nothing, but if the request is successful, the callback method is fired and receives the widget's content as parameter.
  */  
  getModule: function(url, callback, id) {
    return this.request(url, { method : 'GET', type: 'xml', proxy: 'api', onComplete: callback } );
  },


  /* Method: request

  This method is used to get the content of an external data source. 
  It can be used to retrieve or set any kind of data: text-based, XML, JSON or a feed.
  The other Ajax methods (getText(), getXml(), getJson(), getFeed()) are all shortcut methods to specific uses of request().
  This method is also the only way to perform HTTP POST request, as well as authenticated requests

  Parameters:
    * String url: the URL of the data source.
    * Object request: a JavaScript object containing setting/value pairs. 
      This object can take a handful of settings, the only required one being 'onComplete', 
      because you need to always set a callback method that will receive the Ajax response.
      That method *must have one parameter* to receive the JSON content returned by the request.

  Sample request object:
     > { method : 'get', proxy: 'ajax', type: 'xml', onComplete: callback }

  Available methods:
      (code)
      ^ Setting       ^ Options                    ^ Default option 
        method          get, post (in lowercase!)    post
        proxy           ajax, feed                   ajax
        type            json, xml, text, html        text
        cache           seconds of server caching)   undefined
        onComplete      (choose your own method)     undefined
        parameters      (your POST parameters)       undefined
        postBody        (in case you need to set it) undefined
        authentication  (the auth object. See doc)   undefined
      (end)
      
  Returns:
    * Nothing, but if the request is successful, the callback method is fired and receives the content as parameter.
  
  Example:
    (start code)
    UWA.Data.request(
    'http://example.org/api.php', 
    { 
    method: 'get', 
    proxy: 'ajax', 
    type: 'xml', 
    cache: 3600,
    onComplete: myModule.parse 
    });
    myModule.parse = function(response) {
      // your parsing code
    }
    (end)
  */
  request: function(url, request) {
    
    if (typeof request == 'undefined') request = {};
    
    if (typeof request.method == 'undefined') request.method = 'GET';
    
    // Always set this header
    if (typeof request.headers  == 'undefined') request.headers = {};
    
    request.headers['X-Requested-Method'] = request.method;
    
    if (request.method == 'DELETE' || request.method == 'PUT') {
      // Support for Opera and Safari
      request.method = 'POST';
    }
    
    if (typeof request.proxy == 'undefined') {
      if (typeof request.authentication == 'object' || location.hostname == '' ||
            (url.substr(0, 4) == "http" && url.indexOf("http://" + location.hostname) == -1) ) {
                request.proxy = 'ajax';
      }
    }
    
    if (typeof request.type == 'undefined') request.type = 'text';
    
    if (UWA.proxies[request.proxy]) {
      url = UWA.proxies[request.proxy] + '?url=' + encodeURIComponent(url);
      if (request.proxy == "feed" && request.shortFeed != false) url += "&rss=1";
    } else if (request.proxy) {
      UWA.log('no proxy URL set for ' + request.proxy);
    }
    
    var auth = request.authentication;
    
    if (typeof auth == 'object') {
      if (auth.type) url += '&auth=' + auth.type;
      if (auth.gp) url += '&gp=' + auth.gp;
      if (auth.moduleId) url += '&moduleId=' + auth.moduleId;
      if (auth.username) url += '&username=' + encodeURIComponent(auth.username);
      if (auth.password) url += '&password=' + encodeURIComponent(auth.password);
    }
      
    if (request.type && request.proxy) {
      url += '&type=' + request.type;
    }
    
    if (typeof request.cache != 'undefined') {
      url += '&cache=' + request.cache;
    }
    
    if(UWA.Client.Engine.ie) {
        url += '&rnd='+ Math.random();
    }
    
    var callbacks = {
      'xml'  : 'onCompleteXML',
      'feed' : 'onCompleteFeed',
      'json' : 'onCompleteJson',
      'text' : 'onCompleteText',
      'html' : 'onCompleteText'
    }
    
    switch(request.type) {
      
      case 'xml':
        var callback = request.onComplete;
        request.onComplete = function() {
          UWA.Ajax.onCompleteXML(arguments, callback);
        }
        return UWA.Ajax.Request(url, request);
        
      default:
        // disable JSON request if no proxy defined
        if(typeof request.proxy == 'undefined' || request.proxy == null) {
          this.useJsonRequest = false;
        }
        if (this.useJsonRequest && typeof request.authentication == "undefined") {
          return UWA.Json.request(url, request);
        } else {
          var callback = request.onComplete;
          var context = request.context;
          if (typeof UWA.Ajax[callbacks[request.type]] == 'undefined') request.type = 'text';
          request.onComplete = function() {
            UWA.Ajax[callbacks[request.type]](arguments, callback, context);
          }
          return UWA.Ajax.Request(url, request);
        }
        
    }
    
  }

};
