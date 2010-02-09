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


UWA.Controls.Pager = function (options) {
    this.initialize(options);
};

UWA.Controls.Pager.prototype = {

    initialize: function (options) {

        this.module = options.module;
        this.limit = parseInt(options.limit, 10);
        this.offset = parseInt(options.offset, 10);
        this.callback = options.callback;
        this.dataArray = options.dataArray;
        this.max = options.max;

        this.text = options.text || {
            prev : _("prev"), next : _("next")
        };

        this.dataLength = (this.dataArray) ? this.dataArray.length : options.dataLength;
        this.loadingData = false;
    },

    getContent: function () {
        var container = UWA.extendElement(document.createElement("div"));
        container.addClassName("nv-pager");

        var subContainer = UWA.extendElement(document.createElement("div"));
        subContainer.inject(container);

        if (this.offset > 0) {
            var prevLink = UWA.extendElement(document.createElement("a"));
            prevLink.addClassName("prev");
            prevLink.href = "javascript:;";
            prevLink.setHTML(this.text.prev);

            prevLink.onclick = function () {
                if (this.loadingData) {
                    return false
                }

                this.onChange(this.offset - this.limit);

                return false

            }.bind(this);

            prevLink.inject(subContainer)
        }

        if ((this.offset < this.dataLength - this.limit) || (typeof this.max != "undefined" && this.dataLength < this.max)) {
            var nextLink = UWA.extendElement(document.createElement("a"));
            nextLink.addClassName("next");
            nextLink.href = "javascript:;";
            nextLink.setHTML(this.text.next);
            nextLink.onclick = function () {

                if (this.loadingData) {
                    return false
                }

                var range = this.offset + this.limit;

                if ((this.dataLength - range < this.limit) &&
                    typeof this.max != "undefined" &&
                    this.dataLength < this.max
                ) {

                    if (this.onNeedMoreData) {
                        this.onNeedMoreData(this.dataLength);
                        subContainer.addClassName("loading");
                        nextLink.addClassName("loading-next");
                        this.loadingData = true;
                        return false;
                    }
                }

                this.onChange(range);

                return false
            }.bind(this);

            nextLink.inject(subContainer)
        }

        var separator = UWA.extendElement(document.createElement("div"));
        separator.setHTML('<p style="padding:0;margin:0;line-height:0;height:0;clear:both"></p>');
        separator.inject(container);

        return container;
    },

    inject: function (element) {
        element.appendChild(this.getContent())
    },

    getDom: function () {
        this.getContent()
    }
};

