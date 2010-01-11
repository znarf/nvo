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
    You can use this class to debug gadget in firefox or other browser...
*/

if (typeof(System) == "undefined") {

	var System = {
	    Gadget: {
		docked: false,
		TransitionType: {
		},
	
		Settings: {
		    read: function() {
		    },
		    write: function() {
		    }
		},
	
		endTransition: function() {
		},
	
		beginTransition: function() {
		}
	    }
	}
}
