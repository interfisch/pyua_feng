<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_create_attribute_option.php											*
*  inkludiert von /functions/products/dmc_map_attributes.php 				*
*  Attributwerte anlegen, wenn DropDown Attribute							*
*  Copyright (C) 2013 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
*/

/* WIRD ZUR ZEIT NICHT VERWENDET */
/* WIRD ZUR ZEIT NICHT VERWENDET */
/* WIRD ZUR ZEIT NICHT VERWENDET */

		$Attributebezeichnung = $Merkmale[$Anz_Merkmale];
		$AttributWertBezeichnung = $Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal];
		
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_create_attribute_option Auspraegung ".$AttributWertBezeichnung." anlegen für ".$Attributebezeichnung." ... ");
		// Nur für DropDown (select) Werte erforderlich und Wert noch nicht angelegt
		// if (dmc_sql_select_value( "frontend_input", "eav_attribute", "attribute_code='".$Attributecode."'")) { 
		$optionsid = get_option_id_by_attribute_code_and_option_value($Attributebezeichnung, $AttributWertBezeichnung, $store_id);
		if ($optionsid<>$AttributWertBezeichnung) { 
			$option[$Attributebezeichnung] = $AttributWertBezeichnung; //manufacturer
		//	$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
		//	$setup->addAttributeOption($option); 
		
			if (DEBUGGER>=1) fwrite($dateihandle, " ... erledigt.\n");
		} else {
			if (DEBUGGER>=1) fwrite($dateihandle, " ... nicht erforderlich.\n");
		}
		
?>