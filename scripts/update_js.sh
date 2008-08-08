cd ../libraries/uwa-js/
make clean
make

cd ../../

rm projects/exposition-server/public/js/*.js
rm -R projects/exposition-server/public/js/c

cp -R libraries/uwa-js/build/uwa-js-runtime/dist projects/exposition-server/public/js/c
cp -R libraries/uwa-js/build/uwa-js-runtime/src/* projects/exposition-server/public/js/
