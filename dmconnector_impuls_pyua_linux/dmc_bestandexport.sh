#!/bin/sh
cd /var/www/pyua/dmconnector_impuls_pyua_linux
HOST='erp.elkline.net'
USER='m4motion'
PASSWD='YsYZd)$l2ZJm'
FILE='Export_lager.txt'

ftp -n $HOST <<END_SCRIPT
quote USER $USER
quote PASS $PASSWD
cd webshop-pyua/export_lager/
get $FILE
quit
END_SCRIPT
cp Export_lager.txt csv/Export_lager.txt
java -classpath /var/www/pyua/dmconnector_impuls_pyua_linux/libs/dbDrivers/csvjdbc-1.0-20.jar -cp dmConnector.jar dmConnect.operations.BatchStart Bestand
rm csv/Export_lager.txt
exit 0
