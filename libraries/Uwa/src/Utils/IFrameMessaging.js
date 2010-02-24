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

if (typeof UWA == 'undefined') UWA = {};

UWA.iFrameMessaging = function(){}

UWA.iFrameMessaging.prototype = {
    _options : {},

    init: function(options){
        var _this = this;

        if (typeof options!='object' || !options.eventHandler) {
            return;
        }

        this._options  = options;

        if (typeof document.postMessage === 'function' || typeof window.postMessage === 'function') {
            window.addEventListener('message',  function(msg){
                var origin = msg.origin;
                if (origin){ // Common case
                    origin = origin.split('//')[1];
                } else { // Opera case
                    origin = msg.domain;
                }

                _this.dispatch(msg.data, origin, 'postMessage');
            }, false);
        }
    },

    dispatch: function(msg, msgOrigin, msgCommType){
        var options = this._options;
        msgOrigin = unescape(msgOrigin);
        if (typeof options.trustedOrigin == 'undefined' || msgOrigin==options.trustedOrigin){
            var msg = this.decodeJson(msg);
            if (msg) {
                msg.commType = msgCommType;
                options.eventHandler(msg);
            };
        } else {
             throw new Error('Origin ' + msgOrigin + ' is not trusted.');
        }
    },

    decodeJson: function(json){
        var ret = false;
        if ((/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(unescape(json).replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, ''))) {
            ret = eval('(' + unescape(json) + ')');
        }
        return ret;
    }
}