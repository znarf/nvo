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

var map = {}
var hash = location.hash.substr(1);
var pairs = hash.split('&');
for (var i = 0; i < pairs.length; i++) {
    var pair = pairs[i].split('=');
    if (pair.length == 2 && pair[0].length > 0) {
        map[pair[0]] = unescape(pair[1]);
    }
}

if (typeof map.target !== 'undefined' && typeof map.message !== 'undefined' &&
    typeof map.origin !== 'undefined' && typeof map.uri !== 'undefined') {

    if (map.target != 'parent') {
        throw new Error('The communication system works with the parent window.');
    }
    
    if (parent.parent.UWA.MessageHandler) {
        var message = null;
        if (typeof map.message === 'string' && map.message.length) {
            parent.parent.UWA.MessageHandler.dispatch(map.message, map.origin, 'ifproxy');
        }
    }
}

