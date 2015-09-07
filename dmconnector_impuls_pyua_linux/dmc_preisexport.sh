#!/bin/sh
cd /var/www/pyua/dmconnector_impuls_pyua_linux
HOST='erp.elkline.net'
USER='m4motion'
PASSWD='YsYZd)$l2ZJm'
FILE='export_preisliste.txt'

ftp -n $HOST <<END_SCRIPT
quote USER $USER
quote PASS $PASSWD
cd webshop-pyua/Export/
get $FILE
quit
END_SCRIPT
cp export_preisliste.txt csv/export_preisliste.txt
java -classpath /var/www/pyua/dmconnector_impuls_pyua_linux/libs/dbDrivers/csvjdbc-1.0-20.jar -cp dmConnector.jar dmConnect.operations.BatchStart Preis
exit 0
