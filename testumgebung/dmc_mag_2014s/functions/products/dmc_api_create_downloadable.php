<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop												*
*  dmc_api_create_downloadable.php											*
*  inkludiert von dmc_write_art.php 										*
*  Speichert neues downloadable product 									*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
07.05.2014
- neu
*/

					if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_create_downloadable*****\n");

					// existing article
					if ($art_already_exists) {
						// Wenn keine Art_ID vorhanden, dann $newProductId ?
						if ($art_id=='') $art_id=$newProductId;
						// if ($client->call($sessionId, 'product.update', array($art_id, array($newProductData))))	{
						//if ($client->call($sessionId, 'product.update', array($art_id, $newProductData)))	{
							if (DEBUGGER>=1) fwrite($dateihandle, "NO downloadable product ".$Artikel_Bezeichnung." with sku ".$Artikel_Artikelnr." updated\n");$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
						//} else $newProductId = 28021973;	// no update possible						
					} else { // new article
						// neue produkt id -get product id
						$set['set_id']=$attribute_set_id;
						$newProductId = $client->call($sessionId, 'product.create', array('downloadable', $set['set_id'], $Artikel_Artikelnr, $newProductData));

						if (DEBUGGER>=1) fwrite($dateihandle, "downloadable product created with ID: ".$newProductId."\n");						
						// Magento API Bug beheben
						// Prüfen, ob Attribute gesetzt wurden, ggfls setzen
						// super attribute setzen ; set super attribute
						if ($Artikel_Merkmal!="") {
							for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
							{
								 // if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegung ".$Anz_Merkmale." = ".$Auspraegungen[$Anz_Merkmale]."\n");
									 if ($Merkmale[$Anz_Merkmale]!="attribute_set"  && $AuspraegungenID[$Anz_Merkmale]!='280273'
							&& $Merkmale[$Anz_Merkmale]!="base_price_amount" && $Merkmale[$Anz_Merkmale]!="base_price_base_unit"
							&& $Merkmale[$Anz_Merkmale]!="base_price_base_amount" && $Merkmale[$Anz_Merkmale]!="base_price_unit"
							&& is_numeric($MerkmaleID[$Anz_Merkmale]) && is_numeric($AuspraegungenID[$Anz_Merkmale])
								) {
								// if (DEBUGGER>=1) fwrite($dateihandle, "downloadable zuweisen MerkmalID ".$MerkmaleID[$Anz_Merkmale]." = AuspraegungenID".$AuspraegungenID[$Anz_Merkmale]."\n");	
									$table = "catalog_product_entity_int";  
									$columns = "(`entity_type_id` ,`attribute_id` ,`store_id`,`entity_id`,`value`)";
									$values = "(".$attr_type_id.", '".$MerkmaleID[$Anz_Merkmale]."', '0', '".$newProductId."','".$AuspraegungenID[$Anz_Merkmale]."')";		
									// Eventuell alte Zuordnungen löschen
									if (dmc_entry_exits("value_id", "catalog_product_entity_int", " entity_id='".$newProductId."' and attribute_id='".$MerkmaleID[$Anz_Merkmale]."'")) 
										dmc_sql_delete("catalog_product_entity_int", " entity_id='".$newProductId."' and attribute_id='".$MerkmaleID[$Anz_Merkmale]."'");
									dmc_sql_insert($table, $columns, $values);
								} // end if
							} // end for
						} // end if ($Artikel_Merkmal!="")
					} // End if insert
							
			// LINK Ergaenzen
			$title="Download";
			$price=0;
			$downloadUrl=$Download_URL;
			    $resourceDef = array(
                    'title' => $title,
                    'price' => $price,
                    'is_unlimited' => '1',
                    'number_of_downloads' => '0',
                    'is_shareable' => '1',
                    'sample' => array(
                        'type' => 'url',
                        //'file' => array(
                        //    'filename' => 'files/book.pdf',
                        //),
                        'url' => $downloadUrl,
                    ),
                    'type' => 'url',
                    //'file' => array(
                    //    'filename' => 'files/song.mp3',
                    //),
                    'link_url' => $url,
                );

                // build call parameters

                $ApiCallParameters = array(
                    $newProductId,
                    $resourceDef,
                    'link' // [link|sample]
                );

                // make the call
                $result = $client->call(
					$sessionId,
					'product_downloadable_link.add',
					$ApiCallParameters
                );
			
			if (DEBUGGER>=1) fwrite($dateihandle, "downloadable product link added : ".$result."\n");
		/*
			$filesPath = '/kunden/402138_40885/webseiten/blooms-shop/media/downloadable/files';
			// $downloadableProductId = 'downloadable_demo_product';

			$items = array(
				'small' => array(
					'link' => array(
						'title' => 'Versandoptionen',
						'price' => '0',
						'is_unlimited' => '1',
						'number_of_downloads' => '0',
						'is_shareable' => '0',
						'sample' => array(
							'type' => 'file',
							'file' =>
							array(
								'filename' => $SuperAttr,
							),
							'url' => $SuperAttr,
						),
						'type' => 'file',
						'file' =>
						array(
							'filename' => $SuperAttr,
						),
						'link_url' => $SuperAttr,
					),
					'sample' => array(
						'title' => 'Versandoption',
						'type' => 'file',
						'file' => array(
							'filename' => $SuperAttr,
						),
						'sample_url' => $SuperAttr,
						'sort_order' => '1',
					)
				),  
			);

			$result = true;
			foreach ($items as $item) {
				foreach ($item as $key => $value) {
					if ($value['type'] == 'file') {
						$filePath = $filesPath . '/' . $value['file']['filename'];
						$value['file'] = array('name' => str_replace('/', '_', $value['file']['filename']), 'base64_content' => base64_encode(file_get_contents($filePath)), 'type' => $value['type']);
					}
					if ($value['sample']['type'] == 'file') {
						$filePath = $filesPath . '/' . $value['sample']['file']['filename'];
						$value['sample']['file'] = array('name' => str_replace('/', '_', $value['sample']['file']['filename']), 'base64_content' => base64_encode(file_get_contents($filePath)));
					}
					if (DEBUGGER>=1) fwrite($dateihandle, "ADD LINK 109\n");
					if (!$client->call(
						$session,
						'product_downloadable_link.add',
						array($newProductId, $value, $key)
					)
					
					) {
						$result = false;
					}
				}
			}
	*/
?>