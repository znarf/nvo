#
# Exposition PHP Libraries Makefile
#
# Targets:
#  - all: export the PHP libraries, produce a ZIP and a TAR archive.
#  - export: export the PHP libraries.
#  - zip: produce a ZIP archive.
#  - tar: produce a TAR archive.
#  - clean: remove the staged files.
#

SVN = svn
ZIP = zip
TAR = tar

ROOT = ./../..
EXPOSITION_PHP = .
EXPOSITION_PHP_SRC = $(EXPOSITION_PHP)/src
EXPOSITION_PHP_LIB = $(EXPOSITION_PHP)/lib
BUILD = $(EXPOSITION_PHP)/build
COPYING = $(ROOT)/COPYING
COPYING_LESSER = $(ROOT)/COPYING.LESSER

NAME = exposition-php-lib
VERSION = preview3
STAGE = $(BUILD)/$(NAME)
ZIP_NAME = $(NAME)-$(VERSION).zip
TAR_NAME = $(NAME)-$(VERSION).tar.gz

all: export zip tar
	@echo "Exposition PHP Libraries build complete."

export:
	@echo "Exporting files from SVN..."
	@mkdir -p $(BUILD)
	@if [ -d "$(STAGE)" ] ; then \
		echo "Export directory already exists." ; \
	else \
		$(SVN) export $(EXPOSITION_PHP_LIB) $(STAGE) ; \
		$(SVN) export $(EXPOSITION_PHP_SRC) $(STAGE)/Exposition ; \
		cp $(COPYING) $(STAGE) ; \
		cp $(COPYING_LESSER) $(STAGE) ; \
		rm -f $(STAGE)/Makefile ; \
	fi
	@echo "SVN export complete."

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
