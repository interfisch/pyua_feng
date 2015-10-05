<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_delete_first.php														*
*  inkludiert von dmc_write_art.php 										
*  Artikel übergebene Variablen ermitteln									*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/
 
		if ($Aktiv == 'loeschen' || $Aktiv == 'delete' || $Aktiv == '99') {
			$client->call($sessionId, 'product.delete', $Artikel_Artikelnr);
			if (DEBUGGER>=1) fwrite($dateihandle, "Product $Artikel_Artikelnr deleted.\n");		 
			return "deleted";	
		} 
		// Produkt vorab loeschen?
		if (PRODUCT_DELETE_FIRST) {
			//$client->call($sessionId, 'product.delete', $Artikel_Artikelnr);
			//$client->call($sessionId, 'product.delete', $art_id);
			//if (DEBUGGER>=1) fwrite($dateihandle, "Product $Artikel_Artikelnr deleted first.\n");	
			//$art_id = "";
		}
	
?>
	