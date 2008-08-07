#
# UWA JavaScript Runtime Makefile
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
UWA_JS = $(ROOT)/libraries/uwa-js
UWA_JS_SRC = $(UWA_JS)/src
UWA_JS_LIB = $(UWA_JS)/lib
UWA_JS_BIN = $(UWA_JS)/bin
BUILD = $(UWA_JS)/build
COPYING = $(ROOT)/COPYING
COPYING_LESSER = $(ROOT)/COPYING.LESSER

YUI_VERSION = 2.3.5
YUI = java -jar $(UWA_JS_BIN)/yuicompressor-$(YUI_VERSION).jar --charset UTF-8

NAME = uwa-js-runtime
VERSION = preview3
STAGE = $(BUILD)/$(NAME)
ZIP_NAME = $(NAME)-$(VERSION).zip
TAR_NAME = $(NAME)-$(VERSION).tar.gz

BASE_FILES = \
	$(UWA_JS_SRC)/String.js \
	$(UWA_JS_SRC)/Array.js \
	$(UWA_JS_SRC)/Element.js \
	$(UWA_JS_SRC)/Data.js \
	$(UWA_JS_SRC)/Environment.js\
	$(UWA_JS_SRC)/Widget.js \
	$(UWA_JS_SRC)/Utils.js \
	$(UWA_JS_SRC)/Utils/Client.js

CORE_FILES = \
	$(UWA_JS_SRC)/UWA.js \
	$(UWA_JS_SRC)/Drivers/UWA-alone.js \
	$(UWA_JS_SRC)/Drivers/UWA-legacy.js \
	$(BASE_FILES)

MOOTOOLS_FILES = \
	$(UWA_JS_LIB)/mootools.js \
	$(UWA_JS_SRC)/UWA.js \
	$(UWA_JS_SRC)/Drivers/UWA-mootools.js \
	$(BASE_FILES)

CONTROLS = \
	PrefsForm

ENVIRONMENTS = \
	Frame \
	Standalone \
	Google

UWA_CORE = $(STAGE)/dist/UWA_Core.js
UWA_MOOTOOLS = $(STAGE)/dist/UWA_Mootools.js

all: export dist zip tar
	@echo "UWA JavaScript Runtime build complete."

export:
	@echo "Exporting files from SVN..."
	@mkdir -p $(BUILD)
	@if [ -d "$(STAGE)" ] ; then \
		echo "Export directory already exists." ; \
	else \
		mkdir -p $(STAGE)/dist ; \
		$(SVN) export $(UWA_JS_SRC) $(STAGE)/src ; \
		$(SVN) export $(UWA_JS_LIB) $(STAGE)/lib ; \
		cp $(COPYING) $(STAGE) ; \
		cp $(COPYING_LESSER) $(STAGE) ; \
	fi
	@echo "SVN export complete."

dist: core mootools environments controls

core:
	@echo "Building UWA Core..."
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
		cat $(CORE_FILES) $(UWA_JS_SRC)/Environments/$${environment}.js >> $(STAGE)/dist/UWA_$${environment}.tmp.js ; \
		cat $(MOOTOOLS_FILES) $(UWA_JS_SRC)/Environments/$${environment}.js >> $(STAGE)/dist/UWA_$${environment}_Mootools.tmp.js ; \
		$(YUI) $(STAGE)/dist/UWA_$${environment}.tmp.js -o $(STAGE)/dist/UWA_$${environment}.js ; \
		$(YUI) $(STAGE)/dist/UWA_$${environment}_Mootools.tmp.js -o $(STAGE)/dist/UWA_$${environment}_Mootools.js ; \
	done
	-@rm -f $(STAGE)/dist/*.tmp.js
	@echo "UWA Environments build complete."

controls:
	@echo "Building UWA Controls..."
	@for control in $(CONTROLS) ; do \
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
	@echo "Staged files and archives removed."
