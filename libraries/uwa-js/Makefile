SRC_DIR = src
BUILD_DIR = build

YUI_VERSION = 2.3.5

PREFIX = .
BIN_DIR = ${PREFIX}/bin
LIB_DIR = ${PREFIX}/lib
DIST_DIR = ${PREFIX}/dist

BASE_FILES = ${SRC_DIR}/String.js\
	${SRC_DIR}/Array.js\
	${SRC_DIR}/Element.js\
	${SRC_DIR}/Data.js\
	${SRC_DIR}/Environment.js\
	${SRC_DIR}/Widget.js\
	${SRC_DIR}/Utils.js\
	${SRC_DIR}/Utils/Client.js

CORE_FILES = ${SRC_DIR}/UWA.js\
	${SRC_DIR}/Drivers/UWA-alone.js\
	${SRC_DIR}/Drivers/UWA-legacy.js\
	${BASE_FILES}

MOOTOOLS_FILES = ${LIB_DIR}/mootools.js\
	${SRC_DIR}/UWA.js\
	${SRC_DIR}/Drivers/UWA-mootools.js\
	${BASE_FILES}

ENVIRONMENTS = Frame\
	Standalone\
	Google

UWA_CORE = ${DIST_DIR}/UWA_Core.js
UWA_MOOTOOLS = ${DIST_DIR}/UWA_Mootools.js

JAR = java -jar ${BIN_DIR}/yuicompressor-${YUI_VERSION}.jar --charset UTF-8

all: core mootools environments
	@@echo "UWA build complete."

core:
	@@echo "Building UWA Core"

	@@cat ${CORE_FILES} >> ${UWA_CORE}.tmp.js
	
	@@${JAR} ${UWA_CORE}.tmp.js -o ${UWA_CORE}

	@@echo ${UWA_CORE} "Built"
	
mootools:
	@@echo "Building UWA Mootools"
	
	@@cat ${MOOTOOLS_FILES} >> ${UWA_MOOTOOLS}.tmp.js

	@@${JAR} ${UWA_MOOTOOLS}.tmp.js -o ${UWA_MOOTOOLS}

	@@echo ${UWA_MOOTOOLS} "Built"

environments:
	@@echo "Building UWA Environments"
	
	@@for environment in ${ENVIRONMENTS} ; do \
		cat ${UWA_CORE}.tmp.js ${SRC_DIR}/Environments/$${environment}.js >> ${UWA_CORE}.$${environment}.js.tmp.js ; \
		cat ${UWA_MOOTOOLS}.tmp.js ${SRC_DIR}/Environments/$${environment}.js >> ${UWA_MOOTOOLS}.$${environment}.js.tmp.js ; \
		${JAR} ${UWA_CORE}.$${environment}.js.tmp.js -o ${DIST_DIR}/UWA_$${environment}.js ; \
		${JAR} ${UWA_MOOTOOLS}.$$environment.js.tmp.js -o ${DIST_DIR}/UWA_$${environment}_Mootools.js ; \
		rm ${UWA_CORE}.$${environment}.js.tmp.js ; \
		rm ${UWA_MOOTOOLS}.$${environment}.js.tmp.js ; \
	done

clean:
	@@echo "Emptying Distribution directory:" ${DIST_DIR}
	@@rm -rf ${DIST_DIR}/*
