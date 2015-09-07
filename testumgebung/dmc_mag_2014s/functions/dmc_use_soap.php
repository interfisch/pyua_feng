<?php
/*******************************************************************************************
*                                                       									*
*  dmc_use_soap.php for magento shop														*
*  Copyright (C) 2013 DoubleM-GmbH.de														*
*                                                                                         	*
*  SOAP API Funktionen bereitstellen                                                		*
*                                                                                          	*
*******************************************************************************************/

	// soap authentification
	try {		 
		// Get Soap Connection
			if (DEBUGGER>=1) fwrite($dateihandle,"Get Soap Connection to ".SOAP_CLIENT);	
		    $client = new SoapClient(SOAP_CLIENT);
		    //  api authentification, ->  get session token   
			$session = $client->login($user, $password);	
			$zugriff=true;
    		 if (DEBUGGER>=1) fwrite($dateihandle,"api authentification, ->  get session token\n");			
	} catch (SoapFault $e) {
			// Fehlerabfangroutine, wenn Session zugeteilt aber Access Denied
			// if ($debugger==1) fwrite($dateihandle,"Access denied");
			$sessionID=dmc_get_session_id();
			if ($sessionID<>0) {
				if (DEBUGGER>=1) fwrite($dateihandle,"api authentification failed ->  get session token over dmc_get_session_id\n");
				$zugriff=true;
			} else {
				$session=0;	
				$zugriff=false;
				if (DEBUGGER>=1) fwrite($dateihandle, "user authentification Access denied for ".$user."/".$password." Error=:\n ".$e." \n");
			}
	}
		
?>