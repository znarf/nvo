#
# Copyright Netvibes 2006-2009.
# This file is part of UWA JS Runtime.
#
# UWA JS Runtime is free software: you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# UWA JS Runtime is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with UWA JS Runtime.  If not, see <http://www.gnu.org/licenses/>.
#
# Targets:
#  - all: export the SVN files, build the distribution files, produce a ZIP and a TAR archive.
#  - export: export the UWA JavaScript Runtime source files.
#  - dist: build UWA Core, UWA MooTools and UWA Environments for distribution.
#  - core: build UWA Core for distribution.
#  - mootools: build UWA MooTools for distribution.
#  - environments: build UWA Environments for distribution.
#  - zip: produce a ZIP archive.
#  - tar: produce a TAR archive.
#  - clean: remove the staged files.
#

JAVA = java
SVN = svn
ZIP = zip
TAR = tar

ROOT = ./../..
UWA_JS = .
UWA_JS_SRC = $(UWA_JS)/src
UWA_JS_LIB = $(UWA_JS)/lib
UWA_JS_BIN = $(UWA_JS)/bin
BUILD = $(ROOT)/build
COPYING = $(ROOT)/COPYING
COPYING_LESSER = $(ROOT)/COPYING.LESSER

YUI_VERSION = 2.3.5
YUI = java -jar $(UWA_JS_BIN)/yuicompressor-$(YUI_VERSION).jar --charset UTF-8

NAME = uwa-js-runtime
VERSION = preview4
STAGE = $(BUILD)/$(NAME)
ZIP_NAME = $(NAME)-$(VERSION).zip
TAR_NAME = $(NAME)-$(VERSION).tar.gz

BASE_FILES = \
	$(UWA_JS_SRC)/String.js \
	$(UWA_JS_SRC)/Array.js \
	$(UWA_JS_SRC)/Element.js \
	$(UWA_JS_SRC)/Json.js \
	$(UWA_JS_SRC)/Data.js \
	$(UWA_JS_SRC)/Data/Storage.js \
	$(UWA_JS_SRC)/Data/Storage/Abstract.js \
	$(UWA_JS_SRC)/Data/Storage/Dom.js \
	$(UWA_JS_SRC)/Data/Storage/Flash.js \
	$(UWA_JS_SRC)/Data/Storage/Gears.js \
	$(UWA_JS_SRC)/Data/Storage/Html5.js \
	$(UWA_JS_SRC)/Data/Storage/IE.js \
	$(UWA_JS_SRC)/Data/Storage/Cookies.js \
	$(UWA_JS_SRC)/Environment.js\
	$(UWA_JS_SRC)/Widget.js \
	$(UWA_JS_SRC)/Utils.js \
	$(UWA_JS_SRC)/Utils/Client.js \
	$(UWA_JS_SRC)/Controls/PrefsForm.js

CORE_FILES = \
	$(UWA_JS_SRC)/UWA.js \
	$(UWA_JS_SRC)/Drivers/UWA-alone.js \
	$(UWA_JS_SRC)/Drivers/UWA-legacy.js \
	$(BASE_FILES)

MOOTOOLS_FILES = \
	$(UWA_JS_LIB)/mootools-1.2.4-core-nc.js \
	$(UWA_JS_LIB)/mootools-1.2.4.2-more.js \
	$(UWA_JS_SRC)/UWA.js \
	$(UWA_JS_SRC)/Drivers/UWA-mootools.js \
	$(BASE_FILES)

CONTROLS = \
	PrefsForm \
	TabView \
	Pager

ENVIRONMENTS = \
	Frame \
	Frame_Google \
	Standalone \
	Google \
	Dashboard2 \
	Live2 \
	Opera2 \
	Vista \
	Air

UWA_CORE = $(STAGE)/dist/UWA_Core.js
UWA_MOOTOOLS = $(STAGE)/dist/UWA_Mootools.js

FIND_CLEAN_FILES = find $(UWA_JS) -type f \
	-iname '*.DS_Store' \
	-o -iname '*~' \
	-o -iname '*.~*' \
	-o -iname '*.bak' \
	-o -iname '*.marks' \
	-o -iname '*.thumb' \
	-o -iname '*Thumbs.db'

# Update Env

all: dist zip tar
	@echo "UWA JavaScript Runtime build complete."

dist: clean core mootools environments controls

core:
	@echo "Building UWA Core..."
	@mkdir $(STAGE)/src -p
	@mkdir $(STAGE)/dist -p
	@cat $(CORE_FILES) >> $(UWA_CORE).tmp.js
	@$(YUI) $(UWA_CORE).tmp.js -o $(UWA_CORE)
	-@rm -f $(UWA_CORE).tmp.js
	@echo "UWA Core build complete."

mootools:
	@echo "Building UWA MooTools..."
	@cat $(MOOTOOLS_FILES) >> $(UWA_MOOTOOLS).tmp.js
	@$(YUI) $(UWA_MOOTOOLS).tmp.js -o $(UWA_MOOTOOLS)
	-@rm -f $(UWA_MOOTOOLS).tmp.js
	@echo "UWA MooTools build complete."

environments:
	@echo "Building UWA Environments..."
	@for environment in $(ENVIRONMENTS) ; do \
		echo " - $${environment}"; \
		cat $(CORE_FILES) $(UWA_JS_SRC)/Environments/$${environment}.js >> $(STAGE)/src/UWA_$${environment}.js ; \
		$(YUI) $(STAGE)/src/UWA_$${environment}.js -o $(STAGE)/dist/UWA_$${environment}.js ; \
		echo " - $${environment}_Mootools"; \
		cat $(MOOTOOLS_FILES) $(UWA_JS_SRC)/Environments/$${environment}.js >> $(STAGE)/src/UWA_$${environment}_Mootools.js ; \
		$(YUI) $(STAGE)/src/UWA_$${environment}_Mootools.js -o $(STAGE)/dist/UWA_$${environment}_Mootools.js ; \
	done
	-@rm -f $(STAGE)/dist/*.tmp.js
	@echo "UWA Environments build complete."

controls:
	@echo "Building UWA Controls..."
	@for control in $(CONTROLS) ; do \
		echo " - $${control}"; \
		cat $(UWA_JS_SRC)/Controls/$${control}.js >> $(STAGE)/src/UWA_Controls_$${control}.js ; \
		$(YUI) $(UWA_JS_SRC)/Controls/$${control}.js -o $(STAGE)/dist/UWA_Controls_$${control}.js ; \
	done
	@echo "UWA Controls build complete."

zip:
	@echo "Creating the ZIP archive..."
	@(cd $(BUILD) && $(ZIP) -rq $(ZIP_NAME) $(NAME))
	@echo "ZIP archive done."

tar:
	@echo "Creating the TAR archive..."
	@(cd $(BUILD) && $(TAR) -czf $(TAR_NAME) $(NAME))
	@echo "TAR archive done."

clean:
	@echo "Cleaning the build directory..."
	-@rm -rf $(STAGE)
	-@rm -f $(BUILD)/$(NAME)*.zip
	-@rm -f $(BUILD)/$(NAME)*.tar.gz
	@list=`$(FIND_CLEAN_FILES)`; \
	for i in $$list;do \
		rm -f $$i; \
	done
	@echo "Staged files and archives removed."
