#
# Copyright Netvibes 2006-2009.
# This file is part of Exposition La-Distribution Package.
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
#
# Targets:
#  - doc
#  - syntax                     Check syntax of PHP files
#  - test                       Exec unitTest
#  - php-qa                     Exec PHP Quality reports
#  - php-phpcpd                 Exec PHP Quality Duplicate source report
#  - php-phpcs                  Exec PHP Quality syntax report
#  - php-phploc                 Exec PHP Quality stats report
#  - php-phpunit                Exec PHP unitTest
#  - php-phpunit-report         Exec PHP unitTest with coverage report
#  - php-syntax                 Check syntax of PHP files
#  - php-syntax-commit          Check syntax of non commited PHP file
#  - clean                      Remove the staged files
#
# @todo export zip tar build

# Binary
ZIP = zip
TAR = tar
PHP = php
PHPUNIT = phpunit
PHPCS = phpcs
PHPLOC = phploc
PHPCPD = phpcpd
DOXYGEN = doxygen

# Project ID
PROJECT_NAME = exposition-la-distribution-package
PROJECT_VERSION = preview4

# Path
ROOT = .
PROJECT_DOC_PATH = $(ROOT)/doc
PROJECT_TEST_PATH = $(ROOT)/tests
PROJECT_LOG_PATH = $(PROJECT_DATA_PATH)/log
PROJECT_BUILD_PATH = $(ROOT)/../../build/$(PROJECT_NAME)

# Build
ZIP_NAME = $(PROJECT_NAME)-$(PROJECT_VERSION).zip
TAR_NAME = $(PROJECT_NAME)-$(PROJECT_VERSION).tar.gz


# Files Finder
FIND_PHP_SRC_FULL = find $(ROOT) -type f -iname '*.php' -o -iname '*.phtml'
FIND_PHP_SRC = find $(ROOT) -type f -iname '*.php' -o -iname '*.phtml' \
	! -path '$(ROOT)/.*'

FIND_CLEAN_FILES = find $(ROOT) -type f \
	-iname '*.DS_Store' \
	-o -iname '*~' \
	-o -iname '*.~*' \
	-o -iname '*.bak' \
	-o -iname '*.marks' \
	-o -iname '*.thumb' \
	-o -iname '*Thumbs.db'

# Update Env
all: clean syntax
	@echo "----------------"
	@echo $(PROJECT_NAME) " build complete."
	@echo ""

# Generate the doc
doc:
	@echo "----------------"
	@echo "Generate doxygen doc :"
	@$(DOXYGEN) $(PROJECT_DOC_PATH)/doxygen.cnf > $(PROJECT_LOG_PATH)/doc.log
	@echo "done"
#
# Alias
#
syntax:	php-syntax

#
# PHP
#

# Check syntax of PHP files
php-syntax:
	@echo "----------------"
	@echo "Check PHP syntax on all php files:"
	@list=`$(FIND_PHP_SRC_FULL)`; \
	for i in $$list;do \
		$(PHP) -l $$i | grep -v "No syntax errors";\
	done
	@echo "done"

# Check syntax of non commited PHP files
php-syntax-commit:
	@echo "----------------"
	@echo "Check PHP syntax on all php files updated:"
	@list=`git-diff --name-only | grep '.ph' | tr '\n' ' '`; \
	for i in $$list;do \
		$(PHP) -l $$i | grep -v "No syntax errors";\
	done
	@echo "done"

# Exec PHP Quality reports
php-qa: php-phploc php-phpcs php-phpcpd

# Exec PHP Quality stats report
php-phploc:
	@echo "----------------"
	@echo "Exec PHP Code Stats report:"
	@$(PHPLOC) $(ROOT) > $(PROJECT_LOG_PATH)/php-loc.log
	@echo "done (output: $(PROJECT_LOG_PATH)/php-loc.log)"

# Exec PHP Quality syntax report
php-phpcs:
	@echo "----------------"
	@echo "Exec PHP CodeSniffer report:"
	@$(PHPCS) --standard=PEAR --extensions=php --report=full \
	--ignore=$(ROOT)/lib/ZFDebug/*,$(ROOT)/lib/geshi*,$(ROOT)/public/debug/*,$(ROOT)/lib/Spyc.php,$(ROOT)/lib/SphinxClient.php \
	-n $(ROOT) > $(PROJECT_LOG_PATH)/php-cs.log;
	@echo "done (output: $(PROJECT_LOG_PATH)/php-cs.log)"

# Exec PHP Quality Duplicate source report
php-phpcpd:
	@echo "----------------"
	@echo "Exec PHP Code Duplicate report:"
	@$(PHPCPD) $(ROOT) > $(PROJECT_LOG_PATH)/php-cpd.log
	@echo "done (output: $(PROJECT_LOG_PATH)/php-cpd.log)"

# Remove the staged files
clean:
	@echo "----------------"
	@echo "Cleaning useless files:"
	@list=`$(FIND_CLEAN_FILES)`; \
	for i in $$list;do \
		echo "Removed $$i"; \
		rm -f $$i; \
	done
	@echo "done"

.PHONY: doc clean
