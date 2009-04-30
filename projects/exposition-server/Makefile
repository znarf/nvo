#
# Copyright Netvibes 2006-2009.
# This file is part of Exposition PHP Server.
# 
# Exposition PHP Server is free software: you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# Exposition PHP Server is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
# 
# You should have received a copy of the GNU Lesser General Public License
# along with Exposition PHP Server.  If not, see <http://www.gnu.org/licenses/>.
#
# Targets:
#  - all: export the application and the needed libraries, produce a ZIP and a TAR archive.
#  - export: export the application and the needed libraries.
#  - zip: produce a ZIP archive.
#  - tar: produce a TAR archive.
#  - clean: remove the staged files.
#

SVN = svn
ZIP = zip
TAR = tar

ROOT = ./../..
EXPOSITION_PHP_SRC = $(ROOT)/libraries/php/src
EXPOSITION_PHP_LIB = $(ROOT)/libraries/php/lib
EXPOSITION_PHP_SERVER = $(ROOT)/projects/exposition-server
BUILD = $(EXPOSITION_PHP_SERVER)/build
COPYING = $(ROOT)/COPYING
COPYING_LESSER = $(ROOT)/COPYING.LESSER

NAME = exposition-php-server
VERSION = preview3
STAGE = $(BUILD)/$(NAME)
ZIP_NAME = $(NAME)-$(VERSION).zip
TAR_NAME = $(NAME)-$(VERSION).tar.gz

all: export zip tar
	@echo "Exposition PHP Server build complete."

export:
	@echo "Exporting files from SVN..."
	@mkdir -p $(BUILD)
	@if [ -d "$(STAGE)" ] ; then \
		echo "Export directory already exists." ; \
	else \
		$(SVN) export $(EXPOSITION_PHP_SERVER) $(STAGE) ; \
		$(SVN) export $(EXPOSITION_PHP_LIB) $(STAGE)/lib ; \
		$(SVN) export $(EXPOSITION_PHP_SRC) $(STAGE)/lib/Exposition ; \
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
