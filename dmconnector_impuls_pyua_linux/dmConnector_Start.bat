@ECHO OFF
ECHO.
ECHO dmConnector Statusfenster 
ECHO.
"C:\Program Files (x86)\Java\jre7\bin\java.exe" -classpath .\libs\dbDrivers\csvjdbc-1.0-20.jar -jar dmConnector.jar
PAUSE
CLS
EXIT
