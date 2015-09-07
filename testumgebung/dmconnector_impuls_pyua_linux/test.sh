#!/bin/sh
HOST='erp.elkline.net'
USER='m4motion'
PASSWD='YsYZd)$l2ZJm'
FILE='export_preisliste.txt'
cd bestellungen/
ftp -n $HOST <<END_SCRIPT
quote USER $USER
quote PASS $PASSWD
cd webshop-pyua/Import/archiv/
mput w*.txt
quit
END_SCRIPT
mv *.txt backup/
exit 0
