#!/bin/sh
cd /var/www/pyua/dmconnector_impuls_pyua_linux
HOST='erp.elkline.net'
USER='m4motion'
PASSWD='YsYZd)$l2ZJm'
FILE='Export_artikel.txt'

ftp -n $HOST <<END_SCRIPT
quote USER $USER
quote PASS $PASSWD
cd webshop-pyua/Export/
get $FILE
quit
END_SCRIPT
cp Export_artikel.txt csv/Export_artikel.txt
java -classpath /var/www/pyua/dmconnector_impuls_pyua_linux/libs/dbDrivers/csvjdbc-1.0-20.jar -cp dmConnector.jar dmConnect.operations.BatchStart Artikel
exit 0

