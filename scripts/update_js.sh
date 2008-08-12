cd ../libraries/uwa-js/
make clean
make

cd ../../

rm -f projects/exposition-server/public/js/*.js
rm -f projects/exposition-server/public/js/c/*.js

cp -R libraries/uwa-js/build/uwa-js-runtime/src/* projects/exposition-server/public/js/
cp -R libraries/uwa-js/build/uwa-js-runtime/dist/* projects/exposition-server/public/js/c/
