<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop												*
*  dmc_array_create_bundle.php												*
*  inkludiert von dmc_write_art.php 										*
*  Array fuer neues bundle product setzen									*
*  Copyright (C) 2014 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
08.04.2014
- neu
*/
	//create a new bundle product
	if (DEBUGGER>=50) fwrite($dateihandle, "array_create_bundle store -> ".$store_id." \n");

	$Artikel_Status=4;
	
	$mageFilename = '../app/Mage.php';
	require_once $mageFilename;
	umask(0);
	//Mage::app();
	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

	$website_id=array('1',Mage::app()->getStore(true)->getWebsite()->getId());
		
	try {
		$attribute_set_id = 9;
		// SCHRITT 1  -  Artikel als BUNDLE anlegen
		$product = Mage::getModel('catalog/product');
		 
		$product->setSku( "B_".$Artikel_Artikelnr );
		$product->setStoreId('default');
		$product->setTypeId('bundle');
		// $product->setManufacturer(20)
		$product->setName($Artikel_Bezeichnung);
		$product->setDescription($Artikel_Text);
		$product->setShortDescription($Artikel_Kurztext);
		$product->setPrice($Artikel_Preis);
		$product->setAttributeSetId($attribute_set_id); 
		// $product->setCategoryIds("20,24"); 				// -> neue Funktion seit April 2013 : dmc_attach_cat_ids in dmc_api_create_simple
		$product->setWeight($Artikel_Gewicht);
		$product->setTaxClassId($Artikel_Steuersatz); 		// (non)taxable goods
		$product->setVisibility($Artikel_Status); // catalog, search
		$product->setStatus($Aktiv); 
		// assign product to the default website
		$product->setWebsiteIds($website_id);
		$product->save();
		/*	'delivery_time'=>$Artikel_Lieferstatus,			 
							'tier_price' => $Kundengruppenpreise,
							// 'manufacturer' => $Hersteller_ID,
							'qty'=> $Artikel_Menge, 
							'meta_title' => $Artikel_MetaTitle,
							'meta_description' => $Artikel_MetaDescription,
							'meta_keywords' => $Artikel_MetaKeywords,
							'is_in_stock'=>1,
							'has_options' =>0 
									$newProductData['generate_meta']=1;
					*/
		$newProductId = $product->getId();
		if (DEBUGGER>=50) fwrite($dateihandle, "array_create_bundle -> bundle created with id = ".$newProductId." \n");
		
		// SCHRITT 2  -  Artikel als SIMPLE anlegen
		$product = Mage::getModel('catalog/product');		 
		$product->setSku( $Artikel_Artikelnr );
		$product->setStoreId('default');
		$product->setTypeId('simple');
		// $product->setManufacturer(20)
		$product->setName($Artikel_Bezeichnung);
		$product->setDescription($Artikel_Text);
		$product->setShortDescription($Artikel_Kurztext);
		$product->setPrice($Artikel_Preis);
		$product->setAttributeSetId($attribute_set_id); 
		// $product->setCategoryIds("20,24"); 				// -> neue Funktion seit April 2013 : dmc_attach_cat_ids in dmc_api_create_simple
		$product->setWeight($Artikel_Gewicht);
		$product->setTaxClassId($Artikel_Steuersatz); 		// (non)taxable goods
		$product->setVisibility($Artikel_Status); // catalog, search
		$product->setStatus($Aktiv); 
		// assign product to the default website
		$product->setWebsiteIds($website_id);
		$product->save();
		/*	'delivery_time'=>$Artikel_Lieferstatus,			 
							'tier_price' => $Kundengruppenpreise,
							// 'manufacturer' => $Hersteller_ID,
							'qty'=> $Artikel_Menge, 
							'meta_title' => $Artikel_MetaTitle,
							'meta_description' => $Artikel_MetaDescription,
							'meta_keywords' => $Artikel_MetaKeywords,
							'is_in_stock'=>1,
							'has_options' =>0 
									$newProductData['generate_meta']=1;
					*/
		$newSimpleProductId = $product->getId();
		if (DEBUGGER>=50) fwrite($dateihandle, "array_create_bundle -> simple created with id = ".$newSimpleProductId." \n");
		
		// SCHRITT 3 - Hauptartikel dem neuen Bundle zuweisen
		$selections = array();
        $selections2 = array();         
		// Hauptartikel
		$items[] = array(
                'title' => 'Ihr Abo-Magazin',
                'option_id' => '',
                'delete' => '',
                'type' => 'radio',
                'required' => 1,
                'position' => 0);
				
	    $selectionRawData[] = array(
                'selection_id' => '',
                'option_id' => '',
                'product_id' => $newSimpleProductId,					// ID Hauptartikel "simple
                'delete' => '',
                'selection_price_value' => $Artikel_Preis,
                'selection_price_type' => 0,
                'selection_qty' => 1,
                'selection_can_change_qty' => 0,
                'position' => 1);
        $selections[] = $selectionRawData;			

		// Schritt 4 - Bundle Produkte zuordnen
		$items[] = array(
                'title' => 'W&auml;hlen Sie eine Pr&auml;mien aus:',
                'option_id' => '',
                'delete' => '',
                'type' => 'dropdown',
                'required' => 1,
                'position' => 0);
		
		// Auswahlartikel - zu "bundlende" Artikel
		// Ermittlung der IDs der "Praemien-Artikel", deren Artikelnummern im Feld $Artikel_Variante_Von liegen muessen
		$bundles_sku = explode ( '@', $Artikel_Variante_Von);
		// Wenn letzes Zeichen ein @, dann entfernen
		if (substr($Artikel_Variante_Von)=="@")
			$Artikel_Variante_Von=substr($Artikel_Variante_Von,0,-1);
	
		for ( $i = 0; $i < count ( $bundles_sku ); $i++ ) {
			$bundle_product_id = dmc_get_id_by_artno($bundles_sku[$i]);
			if (DEBUGGER>=50) fwrite($dateihandle, "array_create_bundle -> bundle ".$i." to attach = ".$bundle_product_id." \n");
	
			if ($bundle_product_id != "")
				$selectionRawDataPraemie[] = array(
					'selection_id' => '',
					'option_id' => '',
					'product_id' => $bundle_product_id,							// ID Paemienartikel $i
					'delete' => '',
					'selection_price_value' => '0',
					'selection_price_type' => 0,
					'selection_qty' => 1,
					'selection_can_change_qty' => 0,
					'position' => 0);
		} // end for 
		
		$selections[] = $selectionRawDataPraemie;

        $productId = $newProductId;							// Buendelartikel/Abo Artikel
	    $product    = Mage::getModel('catalog/product')
        ->setStoreId(0);
	    if ($productId) {
             $product->load($productId);
        }
	    Mage::register('product', $product);
        Mage::register('current_product', $product);
        $product->setCanSaveConfigurableAttributes(false);
        $product->setCanSaveCustomOptions(true);

        $product->setBundleOptionsData($items);
        $product->setBundleSelectionsData($selections);
        $product->setCanSaveCustomOptions(true);
        $product->setCanSaveBundleSelections(true);
		if (DEBUGGER>=50) fwrite($dateihandle, "162 array_create_bundle -> attach all to bundle: ".$productId." \n");
        $product->save();
		if (DEBUGGER>=50) fwrite($dateihandle, "164 array_create_bundle -> attach all to bundle: ".$productId." \n");
	}
	catch (Mage_Core_Exception $e) {
		if (DEBUGGER>=1) fwrite($dateihandle, "Core-FEHLER array_create_bundle -> bundel nicht angelegt: = ".$e->getMessage()." \n");
	}
	catch (Exception $e) {
		if (DEBUGGER>=1) fwrite($dateihandle, "Core-FEHLER array_create_bundle -> bundel nicht angelegt: = ".$e." \n");
	}					
	
?>