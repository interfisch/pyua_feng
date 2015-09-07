<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop												*
*  dmc_api_create_grouped.php												*
*  inkludiert von dmc_write_art.php 										*
*  Speichert neues grouped product 											*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/
						fwrite($dateihandle, "dmc_api_create_grouped - create new grouped product article with sku ".$Artikel_Artikelnr."\n");
						$set['set_id']=$attribute_set_id;
						$newProductId = $client->call($sessionId, 'product.create', array('grouped', $set['set_id'], $Artikel_Artikelnr, $newProductData));	
	
?>