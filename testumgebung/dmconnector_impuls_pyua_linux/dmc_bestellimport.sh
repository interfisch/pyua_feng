#!/bin/sh
cd /var/www/pyua/dmconnector_impuls_pyua_linux
java -classpath /var/www/pyua/dmconnector_impuls_pyua_linux/libs/dbDrivers/csvjdbc-1.0-20.jar -cp dmConnector.jar dmConnect.operations.BatchStart Bestellimport
HOST='erp.elkline.net'
USER='m4motion'
PASSWD='YsYZd)$l2ZJm'
FILE='export_preisliste.txt'
cd bestellungen/
ftp -n $HOST <<END_SCRIPT
quote USER $USER
quote PASS $PASSWD
prompt
cd webshop-pyua/Import/
mput w*.txt
mput c*.txt
quit
END_SCRIPT
mv *.txt backup/
exit 0
