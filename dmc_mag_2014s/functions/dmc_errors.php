<?php
/*******************************************************************************************
*                                                                                          									*
*  dmc_error for magento shop											*
*  Copyright (C) 2011 DoubleM-GmbH.de											*
*                                                                                          									*
*******************************************************************************************/

	function dmc_write_error($module, $action, $error_line, $message, $log_error, $email_error, $dateihandle)
	{	
		// mappings und loggen
		global $dateihandleError;
		$error_hint = ""; 
		if (DEBUGGER>=1) fwrite($dateihandle,"** dmc_write_error -> Details in Error LOG ***\n");
		if (DEBUGGER>=1) fwrite($dateihandleError,"** ".date("d.m.Y")." ".date("H:i")." dmc_write_error in module ".$module.", action=$action, line=$error_line,**\n");
		if (strpos($message, "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry") !== false) {
			$error_hint =	"\n**** ROBIN's HINT* ***Check if Symmetrics_SetMet is installed an deactivate it by opening "."
							app/etc/modules/Symmetrics_SetMeta.xml and changing <active>true</active> to <active>false</active>.\n";
		}
		if (strpos($message, 'The value of attribute "Artikelnummer" must be unique') !== false ||
			strpos($message, 'The value of attribute "SKU" must be unique') !== false) {
			$error_hint =	"\n**** ROBIN's HINT* *** Product already exists.";
		}
		if (strpos($message, '_attribute_set') !== false) {
			$error_hint =	"\n**** ROBIN's HINT* *** Check for correct spelling incl case sensitive Default <> default!!!\n";
		}
		
		if (DEBUGGER>=1) fwrite($dateihandleError,"** Message: ".$message."\n");
		if (DEBUGGER>=1) fwrite($dateihandle,"** Message: ".$message."\n");
		if (DEBUGGER>=1 && $log_error && $error_hint != "") fwrite($dateihandleError,"** DMC-Hint: $error_hint\n");
	}
		
?>