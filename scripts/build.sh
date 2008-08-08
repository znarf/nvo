cd ../

mkdir build

cd libraries/php/
make
cp build/exposition-php-lib-preview3.zip ../../build/
make clean

cd ../../

cd libraries/uwa-js/
make
cp build/uwa-js-runtime-preview3.zip ../../build/
make clean

cd ../../

cd projects/exposition-server/
make
cp build/exposition-php-server-preview3.zip ../../build/
make clean

cd ../../