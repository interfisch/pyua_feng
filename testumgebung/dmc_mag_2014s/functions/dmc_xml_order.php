<?php
/*******************************************************************************************
*                                                                                          									*
*  dmc_xml_order  for magento shop											*
*  Copyright (C) 2010 DoubleM-GmbH.de											*
*                                                                                          									*
*******************************************************************************************/

/* 17.12.2010  <ATTRIBUTE_ID> ergaenzt */
/*11012011 DELIVERY_FEE_GROS ergaenzt */
/* Allgemeine Produktoptionen ermittelten  und als Produkt uebergeben */
// 02.10.2012 - Uebergabe von bezahlt Kennzeichen <ORDER_IS_PAID>, Store ID <ORDER_STORE_ID>, Kundengruppe <CUSTOMER_GROUP_ID> sowie <PAYMENT_TRANSACTION_ID>

	if (DEBUGGER>=1) fwrite($dateihandle,"** dmc_xml_order **\n");
					// mage zb zum entschluesseln einbinden
					require_once '../app/Mage.php';
					umask(0);
					Mage::app();
								
					//  Auslandskunden
					$iso_code=$order_infos[shipping_address][country_id];
					// Ausland/Netto initialisieren
					$eg_ausland=0;
					$ausland=0;
					$nettokunde=0;
						// Prüfen, ob  Ausland
					if ($iso_code!='DE' && $iso_code!='D') { //Deutschland
						$ausland=1;
						$nettokunde=1;
					}
				
						// Prüfen, ob EG Ausland
					if (($iso_code=='AT')  //Österreich
						|| ($iso_code=='B') //  Belgien
					 	|| ($iso_code=='BG') //Bulgarien
					 	|| ($iso_code=='CZ') //Tschechische Republik
					 	|| ($iso_code=='CY') //Cypern
					 	|| ($iso_code=='DK') //Dänemark
					 	|| ($iso_code=='E') //  Spanien
					 	|| ($iso_code=='EL') //Griechenland
					 	|| ($iso_code=='F') //  Frankreich
					 	|| ($iso_code=='FI') // Finnland
						|| ($iso_code=='HU') //Ungarn
					 	|| ($iso_code=='UK') //Vereinigtes Königreich
					 	|| ($iso_code=='IRL') //  Irland
					 	|| ($iso_code=='I') // //Italien
					 	|| ($iso_code=='L') //  Luxemburg
					 	|| ($iso_code=='LT') //Litauen
					 	|| ($iso_code=='LV') //Lettland
					 	|| ($iso_code=='MT') //  Malta
					 	|| ($iso_code=='NL') //Niederlande
					 	|| ($iso_code=='P') // Portugal
					 	|| ($iso_code=='PL') //Polen
					 	|| ($iso_code=='RO') //  Rumänien
					 	|| ($iso_code=='RS') //  Serbien
					 	|| ($iso_code=='SE') //  Schweden
					 	|| ($iso_code=='SI') //Slowenien
					 	|| ($iso_code=='SK'))  // Slowakische Republik
						{
							$eg_ausland=1;
							// Prüfen, um UmStIdentNr gesetzt
							//if ($cust_ustid!='') 
						} // end if EG Ausland
					
			// Order ID in Datei speichern, wenn ID höher als letzte
			if ($order_list[$i]['created_at']>=$last_order) {
				// Letzte (höchste) OrderID speichern
				$last_order=$order_list[$i]['created_at'];
				// 1 sekunde addieren
				if (substr($last_order, -1)<>9) $last_order = substr($last_order, 0,-1).(substr($last_order, -1)+1);  
				else $last_order = substr($last_order, 0,-2).(substr($last_order, -2)+1);	
				if ($noupdate_order_date!=1 && USE_ORDER_ID) {
					$dateihandleOrderID = fopen("./order_id.txt","w");
					fwrite($dateihandleOrderID, $last_order);
					fclose($dateihandleOrderID);
				}
			} // end if
		
			// Überprüfen, ob Lieferanschrift abweichend
			if ($order_infos[shipping_address][firstname] == $order_infos[billing_address][firstname]
				&& $order_infos[shipping_address][street] == $order_infos[billing_address][street]) 
					$Address_type = 'same';
			else
					$Address_type = 'different';

			// Pruefen, ob bezahlt
			if ($order_list[$i]['total_paid'] == 'NULL'  
				|| $order_list[$i]['total_paid'] == '' 
				|| $order_list[$i]['total_paid'] == '0') 
				$ispaid=0;
			else 
				$ispaid=-1;
		
			// Überprüfen, ob Gutschein vorhanden
			if ($order_infos[discount_amount] != '0.0000'
				|| $order_infos[discount_amount]>0) {
					$gutschein = true;
					$gutschein_artnr = $order_infos[coupon_code];
					$gutschein_wert = $order_infos[discount_amount]*-1;
					$gutschein_netto = ($gutschein_wert/1.19);
					$gutschein_mwst =$gutschein_wert-$gutschein_netto;
			} else
					$gutschein = false;
			
			// Magento API Street = Magento Adress1 + \n + Adress2
			$strasse= umlaute_order_export($order_infos[shipping_address][street]);
			$strasse=str_replace("\n","XXX",$strasse);
			list ($strasse1, $strasse2) = explode ('XXX', $strasse);
			$strasse_re= umlaute_order_export($order_infos[billing_address][street]);
			$strasse_re=str_replace("\n","XXX",$strasse_re);
			list ($strasse_re1, $strasse_re2) = explode ('XXX', $strasse_re);
			
			$schema2="";
			$versand_temp="";
			
			// fwrite($dateihandle, "Kundennummer=".$order_infos[customer_id].".\n"); 
			// if (DEBUGGER>=1) fwrite($dateihandle,"\n\n ** Kundennummer=".$order_infos[customer_id].".\n");
			$schema .= 	'<ORDER version="1.0" type="standard">' . "\n".
						'<ORDER_HEADER>' . "\n".
							'<CONTROL_INFO>' . "\n".					  
								'<GENERATOR_INFO>' . "dmconnector".'</GENERATOR_INFO>' . "\n".
								'<GENERATOR_VERSION>' . "magento-".$version_datum.'</GENERATOR_VERSION>' . "\n".
								// todo 	aktuelles datum
								'<GENERATION_DATE>' . $order_infos[created_at] .'</GENERATION_DATE>' . "\n".	
								'<ORDER_NUMBERS>' . (($rcm*10)+($i+1)) .' of '.$BestellAnzahl .'</ORDER_NUMBERS>' . "\n".
								'<key>PLAN_KEY_EB_' . $order_list[$i][increment_id].'</key>' . "\n".												
							'</CONTROL_INFO>' . "\n".
						'<ORDER_INFO>' . "\n".	  
							'<ORDER_ID>' . $order_infos[order_id].'</ORDER_ID>' . "\n".
							'<ORDER_CID>' .$order_list[$i][increment_id].'</ORDER_CID>' . "\n".
							'<ORDER_IP>' . $order_infos[remote_ip].'</ORDER_IP>' . "\n".
							'<ORDER_DATE_KW>' .  date("W",time()) . '</ORDER_DATE_KW>' . "\n" .
	                    	'<ORDER_DATE>' . $order_infos[updated_at].'</ORDER_DATE>' . "\n".
							'<ORDER_IS_PAID>' . $ispaid.'</ORDER_IS_PAID>' . "\n".
							'<ORDER_STORE_ID>' . $order_list[$i]['store_id'] .'</ORDER_STORE_ID>' . "\n".
							'<ORDER_STORE_NAME>' .$order_infos['store_name'] .'</ORDER_STORE_NAME>' . "\n".
							'<ORDER_STATUS>' .$order_infos[status].'</ORDER_STATUS>' . "\n";
							
							if ($order_infos[customer_id] != "")
								$schema .= '<CUSTOMER_CID>' . dmc_get_incr_id_by_cust_id($order_infos[customer_id]).'</CUSTOMER_CID>' . "\n";
							else 
								$schema .= '<CUSTOMER_CID>0</CUSTOMER_CID>' . "\n";
							$schema .= '<CUSTOMER_FOREIGN>' . $ausland . '</CUSTOMER_FOREIGN>' . "\n" .
							'<CUSTOMER_EU>' . $eg_ausland . '</CUSTOMER_EU>' . "\n" .
							'<CUSTOMER_NET>' . $nettokunde . '</CUSTOMER_NET>' . "\n" .
                    		'<CUSTOMER_GROUP_ID>' . $order_list[$i]['customer_group_id'] . '</CUSTOMER_GROUP_ID>' . "\n" .
                    		'<INVOICE_ID>' .$invoice_id.'</INVOICE_ID>' . "\n".
							'<INVOICE_CID>' .$invoice_no.'</INVOICE_CID>' . "\n".
							'<INVOICE_DATE>' . $invoice_date.'</INVOICE_DATE>' . "\n";
							
							if ($order_infos[customer_id] != "")
								$schema .= '<CUSTOMER_ADDRESS_CID>' . dmc_get_incr_id_by_cust_id($order_infos[customer_id]).'</CUSTOMER_ADDRESS_CID>' . "\n";
							else 
								$schema .= '<CUSTOMER_ADDRESS_CID>0</CUSTOMER_ADDRESS_CID>' . "\n";
								
							$schema .= '<ORDER_PARTIES>' . "\n".
								'<BUYER_PARTY>' . "\n".
									'<PARTY>' . "\n".
									'<PARTY_ID type="buyer_specific">'.$order_infos[customer_id].'</PARTY_ID>'."\n".
									'<ADDRESS>' . "\n".
										'<ADDRESS_TYPE>'.$Address_type.'</ADDRESS_TYPE>'. "\n".
										'<ADDRESS_ID>' . $order_infos[shipping_address][address_id].'</ADDRESS_ID>' . "\n".
										'<ADDRESS_ALT_CID>' . (300000+$order_infos[shipping_address][address_id]) .'</ADDRESS_ALT_CID>' . "\n".
										'<ADDRESS_CID>' . (200000000+$order_infos[shipping_address][address_id]) .'</ADDRESS_CID>' . "\n".
										'<SUFFIX>' . substr(umlaute_order_export($order_infos[shipping_address][suffix]),0,2000).'</SUFFIX>' . "\n".
										'<PREFIX>' .substr(umlaute_order_export($order_infos[shipping_address][prefix]),0,2000).'</PREFIX>' . "\n".
										'<TITLE>' . substr(umlaute_order_export($order_infos[shipping_address][title]),0,2000).'</TITLE>' . "\n".
										'<GENDER>' . substr(umlaute_order_export($order_infos[shipping_address][gender]),0,1000).'</GENDER>' . "\n".
										'<NAME>' . umlaute_order_export(substr($order_infos[shipping_address][firstname],0,3000)).'</NAME>' . "\n".
										'<NAME2>' . (umlaute_order_export(substr($order_infos[shipping_address][lastname],0,3000))).'</NAME2>' . "\n".
										'<NAME3>' . umlaute_order_export(substr($order_infos[shipping_address][company],0,3000)).'</NAME3>' . "\n".
										'<STREET>' . substr($strasse1,0,3000).'</STREET>' . "\n".
										'<STREET2>' . substr($strasse2,0,3000).'</STREET2>' . "\n".
										'<ZIP>' . $order_infos[shipping_address][postcode].'</ZIP>' . "\n".
										'<CITY>' . substr(umlaute_order_export($order_infos[shipping_address][city]),0,3000).'</CITY>' . "\n".
										'<COUNTRY>' . substr($order_infos[shipping_address][country_id],0,1000).'</COUNTRY>' . "\n".
										'<VAT_ID>' . '' .'</VAT_ID>' . "\n".
										'<PHONE>' . umlaute_order_export($order_infos[shipping_address][telephone]).'</PHONE>' . "\n".
										'<PHONE2></PHONE2>' . "\n".
										'<FAX>' . umlaute_order_export($order_infos[shipping_address][fax]).'</FAX>' . "\n".
										'<EMAIL>' . umlaute_order_export($order_infos[customer_email]).'</EMAIL>' . "\n".
									'</ADDRESS>' . "\n".
									'</PARTY>' . "\n".
								'</BUYER_PARTY>' . "\n".
								'<INVOICE_PARTY>' . "\n".
									'<PARTY>' . "\n".
										'<ADDRESS>' . "\n".
										'<ADDRESS_ID>' . $order_infos[billing_address][address_id] .'</ADDRESS_ID>' . "\n".
										'<ADDRESS_ALT_CID>' . (300000+$order_infos[billing_address][address_id]) .'</ADDRESS_ALT_CID>' . "\n".
										'<ADDRESS_CID>' .(200000000+$order_infos[billing_address][address_id]) .'</ADDRESS_CID>' . "\n".
										'<SUFFIX>' . substr(umlaute_order_export($order_infos[billing_address][suffix]),0,2000).'</SUFFIX>' . "\n".
										'<PREFIX>' . substr(umlaute_order_export($order_infos[billing_address][prefix]),0,2000).'</PREFIX>' . "\n".
										'<TITLE>' . substr(umlaute_order_export($order_infos[billing_address][title]),0,2000).'</TITLE>' . "\n".
										'<GENDER>' . substr(umlaute_order_export($order_infos[billing_address][gender]),0,1000).'</GENDER>' . "\n".
										'<NAME>' . umlaute_order_export(substr($order_infos[billing_address][firstname],0,3000)).'</NAME>' . "\n".
										'<NAME2>' . (umlaute_order_export(substr($order_infos[billing_address][lastname],0,3000))).'</NAME2>' . "\n".
										'<NAME3>' . umlaute_order_export(substr($order_infos[billing_address][company],0,3000)).'</NAME3>' . "\n".
										'<STREET>' . substr($strasse_re1,0,3000).'</STREET>' . "\n".
										'<STREET2>' . substr($strasse_re2,0,3000).'</STREET2>' . "\n".
										'<ZIP>' . $order_infos[billing_address][postcode].'</ZIP>' . "\n".
										'<CITY>' . substr(umlaute_order_export($order_infos[billing_address][city]),0,3000).'</CITY>' . "\n".
										'<COUNTRY>' . substr($order_infos[billing_address][country_id],0,1000).'</COUNTRY>' . "\n".
										'<VAT_ID>' . '' .'</VAT_ID>' . "\n".
										'<PHONE>' . umlaute_order_export($order_infos[billing_address][telephone]).'</PHONE>' . "\n".
										'<PHONE2></PHONE2>' . "\n".
										'<FAX>' . umlaute_order_export($order_infos[billing_address][fax]).'</FAX>' . "\n".
										'<EMAIL>' . umlaute_order_export($order_infos[customer_email]).'</EMAIL>' . "\n".
									'</ADDRESS>' . "\n".
									'</PARTY>' . "\n".
								'</INVOICE_PARTY>' . "\n".
								// Shop Adresse
								'<SUPPLIER_PARTY>' . "\n".
									'<PARTY>' . "\n".
									'<ADDRESS>' . "\n".
										'<NAME>' . "DoubleM Neue Medien GmbH".'</NAME>' . "\n".
										'<NAME2>' . ''.'</NAME2>' . "\n".
										'<NAME3>' . ''.'</NAME3>' . "\n".
										'<STREET>' . ''.'</STREET>' . "\n".
										'<ZIP>' . ''.'</ZIP>' . "\n".
										'<CITY>' . ''.'</CITY>' . "\n".
										'<COUNTRY>' . ''.'</COUNTRY>' . "\n".
										'<VAT_ID>' . ''.'</VAT_ID>' . "\n".
										'<PHONE type="other">' . ''.'</PHONE>' . "\n".
										'<PHONE></PHONE>' . "\n".
										'<PHONE2></PHONE2>' . "\n".
										'<FAX>' . ''.'</FAX>' . "\n".
										'<EMAIL>' . ''.'</EMAIL>' . "\n".
									'</ADDRESS>' . "\n".
									'</PARTY>' . "\n".
								'</SUPPLIER_PARTY>' . "\n".
							'</ORDER_PARTIES>' . "\n".
							/*
									["debit_swift"]=>
									string(24) "1XjonT58uR+OLiHDJD4KEQ=="
									["debit_iban"]=>
									string(32) "gjKxYcmj+uHP8Dp26RBH3uHrY9iJqGz3"
									["debit_type"]=>
									string(4) "sepa"
									["debit_bankname"]=>
									string(32) "Bank Privat und GeschÃ¤ftskunden"
									["payment_id"]=>
									string(2) "74"
							*/
							'<PAYMENT>' . "\n";
								if ($order_infos[payment][method] == "todo") {
									// DEBIT
									$schema .= '<PAYMENT_TERM>Debit</PAYMENT_TERM>' . "\n";
								} else if ($order_infos[payment][method] == "bankpayment") {
									// CHECK
									$schema .= '<PAYMENT_TERM>Vorkasse</PAYMENT_TERM>' . "\n";
								} else {
									// CASH
									$schema .= '<PAYMENT_TERM>' . $order_infos[payment][method].'</PAYMENT_TERM>' . "\n";
								} // endif payment
								// GGfls Paypal TransaktionsId ermitteln
								$schema .= '<PAYMENT_TRANSACTION_ID>'.$order_infos[payment][last_trans_id].'</PAYMENT_TRANSACTION_ID>' . "\n";
								$schema .=
									'<CARD_NUM>' . $order_infos[payment][po_number].'</CARD_NUM>' . "\n".
									'<CARD_AUTH_CODE>' . $order_infos[payment][cc_number_enc].'</CARD_AUTH_CODE>' . "\n".
									'<CARD_EXPIRATION_DATE>' . $order_infos[payment][cc_exp_month].'/'.$order_infos[payment][cc_exp_year].'</CARD_EXPIRATION_DATE>' . "\n".
									// Typs: AMEX, Visa, MC (Master Card), JCB, Diners (and others (Maestro?))
									'<CARD_TYPE>' . $order_infos[payment][cc_type].'</CARD_TYPE>' . "\n".
									'<CARD_HOLDER_NAME>' . umlaute_order_export($order_infos[payment][cc_owner]).'</CARD_HOLDER_NAME>' . "\n".
									'<ACCOUNT_HOLDER>' . umlaute_order_export($order_infos[payment][cc_owner]).'</ACCOUNT_HOLDER>' . "\n".
									'<ACCOUNT_BANK_NAME>' . umlaute_order_export($order_infos[payment][debit_bankname]).'</ACCOUNT_BANK_NAME>' . "\n".
									'<ACCOUNT_BANK_COUNTRY>' . $order_infos[payment][method].'</ACCOUNT_BANK_COUNTRY>' . "\n".							
									'<ACCOUNT_BANK_CODE>' . Mage::helper('core')->decrypt($order_infos[payment][debit_swift]).'</ACCOUNT_BANK_CODE>' . "\n".
									'<ACCOUNT_BANK_ACCOUNT>' . Mage::helper('core')->decrypt($order_infos[payment][debit_iban]).'</ACCOUNT_BANK_ACCOUNT>' . "\n";
													
							
							$schema .='</PAYMENT>' . "\n".
							'<DELIVERY_METHOD>' . $order_infos[shipping_method].'</DELIVERY_METHOD>' . "\n".
							'<DELIVERY_FEE>' . $order_infos[shipping_amount].'</DELIVERY_FEE>' . "\n".	
							'<DELIVERY_FEE_TAX>' . $order_infos[shipping_tax_amount].'</DELIVERY_FEE_TAX>' . "\n".
							'<DELIVERY_FEE_GROS>' . ($order_infos[shipping_amount]+$order_infos[shipping_tax_amount]).'</DELIVERY_FEE_GROS>' . "\n".
							'<DELIVERY_WEIGHT>' . $order_infos[weight].'</DELIVERY_WEIGHT>' . "\n".
							'<DISCOUNT_AMOUNT>' . $order_infos[discount_amount].'</DISCOUNT_AMOUNT>' . "\n".		
							'<DISCOUNT_PERCENT>' . ($order_infos[base_discount_amount]*-1/$order_infos[base_subtotal_incl_tax]*100).'</DISCOUNT_PERCENT>' . "\n".
						
		 					// etc
						'</ORDER_INFO>' . "\n".
						'</ORDER_HEADER>' . "\n".
						'<ORDER_ITEM_LIST>' . "\n";
							
							// Order Item List		
							$produkte_kommission .= "";
							$produkte ="";
							$produkte_temp="";
							
							// Order Item List		
							$line_item_id=0;
							for ($product_no=0;$product_no<sizeof($order_infos[items]);$product_no++){	
								// Bei RECHNUNGEN NUR in Rechnung gestellte Produkte ausgeben
								// zugehoerigen Bestellartikel ermitteln
								$passt = false;
								$invoice_item_id=0;
							
								// Pruefen auf Kommissionsware 
								$produkt_lager = dmc_get_product_attribute_value($order_infos[items][$product_no][product_id],'lager');
								if ($produkt_lager=='2') {
									// Kommssionsartikel enthalten
									$kommissionsartikel=true;
								} else {
									$kommissionsartikel=false;
								}
							
								do {
									if ($order_infos[items][$product_no][item_id] == $invoice_infos[items][$invoice_item_id][order_item_id])
										$passt = true;
									else
										$invoice_item_id++;
								} while (!$passt && $invoice_item_id<sizeof($invoice_infos[items]));
								if ($passt) {
									// Werte des in Rechnung gestellten Produktes uebernehmen, wie die Anzahl
									if (DEBUGGER>=1) fwrite($dateihandle," **** PASSENDER INCOICE_ITEM $invoice_item_id *****\n");	
									if (DEBUGGER>=1) fwrite($dateihandle," **** qty_ordered = ".$order_infos[items][$product_no][qty_ordered]." *****\n");									   if (DEBUGGER>=1) fwrite($dateihandle," **** qty_invoiced = ".$invoice_infos[items][$invoice_item_id][qty]." *****\n");	
									$order_infos[items][$product_no][base_price] = $invoice_infos[items][$invoice_item_id][price];
									$order_infos[items][$product_no][row_total] = $invoice_infos[items][$invoice_item_id][row_total];
									$order_infos[items][$product_no][tax_amount]=$invoice_infos[items][$invoice_item_id][tax_amount];
									$order_infos[items][$product_no][discount_amount]=$invoice_infos[items][$invoice_item_id][discount_amount];
									$order_infos[items][$product_no][qty_ordered]=$invoice_infos[items][$invoice_item_id][qty];
								} else {
									if (DEBUGGER>=1) fwrite($dateihandle, "*** Zum BESTELLprodukt ".$order_infos[items][$product_no][item_id]." wurde kein Rechnungsprodukt gefunden.\n");
								}
								
								// Produkt ausgeben
								if ($passt || !EXPORT_INVOICES) {
									// Configurierbare Produkte liegen in Bestellung, Bestellt wurde das zugehörige Simple
									// Werte aus Conf zwischenspeichern, damit dem Simple zugewiesen werden können.
									
									if ($order_infos[items][$product_no][product_type] == "configurable") {
										// Aufbau Array mit Conf item_id und Wert
										$simpleToConf[$order_infos[items][$product_no][item_id]]['PRICE_AMOUNT'] = $order_infos[items][$product_no][base_price];
										$simpleToConf[$order_infos[items][$product_no][item_id]]['PRICE_LINE_AMOUNT'] = $order_infos[items][$product_no][row_total];
										$simpleToConf[$order_infos[items][$product_no][item_id]]['TAX'] = $order_infos[items][$product_no][tax_percent];
										$simpleToConf[$order_infos[items][$product_no][item_id]]['TAX_AMOUNT'] = $order_infos[items][$product_no][tax_amount];
										$simpleToConf[$order_infos[items][$product_no][item_id]]['DISCOUNT_AMOUNT'] = $order_infos[items][$product_no][discount_amount];
										$simpleToConf[$order_infos[items][$product_no][item_id]]['DISCOUNT_PERCENT'] = $order_infos[items][$product_no][discount_percent];
									} // end if conf
									// Dem Conf zugeordneten Simple Werte des Conf zuweisen
									if ($order_infos[items][$product_no][product_type] == "simple" 
											&& !is_null($order_infos[items][$product_no][parent_item_id]) 
											&& ''!==$order_infos[items][$product_no][parent_item_id] 
											&& $order_infos[items][$product_no][parent_item_id]>1 ) {
										// Aufbau Array mit Conf item_id und Wert
										$order_infos[items][$product_no][base_price] = $simpleToConf[$order_infos[items][$product_no][parent_item_id]]['PRICE_AMOUNT'];
										$order_infos[items][$product_no][row_total] = $simpleToConf[$order_infos[items][$product_no][parent_item_id]]['PRICE_LINE_AMOUNT'];
										$order_infos[items][$product_no][tax_percent]=$simpleToConf[$order_infos[items][$product_no][parent_item_id]]['TAX'];
										$order_infos[items][$product_no][tax_amount]=$simpleToConf[$order_infos[items][$product_no][parent_item_id]]['TAX_AMOUNT'];
										$order_infos[items][$product_no][discount_amount]=$simpleToConf[$order_infos[items][$product_no][parent_item_id]]['DISCOUNT_AMOUNT'];
										$order_infos[items][$product_no][discount_percent]=$simpleToConf[$order_infos[items][$product_no][parent_item_id]]['DISCOUNT_PERCENT'];
									} // end if conf
									
									// Individuelle Produktoptionen ermittelten
									$optionen="";
									$product_options=$order_infos[items][$product_no][product_options];
									$pos = strpos($product_options, "Länge");
									if ($pos !== false) {
										// " ab Zeichen $pos ermitteln
										$pos = strpos($product_options, ':"',$pos+1);
										$pos = strpos($product_options, ':"',$pos+1);
										$posende = strpos($product_options, '"',$pos+2)-$pos-2;
										$optionen=" - Länge:".substr($product_options, $pos+2, $posende)."\n";
										if (DEBUGGER>=1) fwrite($dateihandle,"optionen ".$optionen."\n");	
									}
										// Individuelle Produktoptionen ermittelten
									//$optionen="";
									$pos = strpos($product_options, "Gewünschte Länge");
									if ($pos !== false) {
										// " ab Zeichen $pos ermitteln
										$pos = strpos($product_options, ':"',$pos+1);
										$pos = strpos($product_options, ':"',$pos+1);
										$posende = strpos($product_options, '"',$pos+2)-$pos-2;
										$optionen.=" - Gewünschte Länge:".substr($product_options, $pos+2, $posende)."\n";
										if (DEBUGGER>=1) fwrite($dateihandle,"optionen ".$optionen."\n");	
									}
									// Individuelle Produktoptionen ermittelten
									//$optionen="";
									$pos = strpos($product_options, "Anzahl Rippen");
									if ($pos !== false) {
										// " ab Zeichen $pos ermitteln
										$pos = strpos($product_options, ':"',$pos+1); // Erstes :"
										$pos = strpos($product_options, ':"',$pos+1); // Zweites :"
										$posende = strpos($product_options, '"',$pos+2)-$pos-2;
										$optionen.=" - Anzahl Rippen:".substr($product_options, $pos+2, $posende)."\n";
										if (DEBUGGER>=1) fwrite($dateihandle,"optionen ".$optionen." pos von".($pos+2)." bis".($posende)."\n");	
									}
									
									// Allgemeine Produktoptionen ermittelten -> values vorhanden
									//$optionen="";
									$abbruch=false;
									$option_bez = array ();
									$option_id = array ();
									$option_value = array ();
									$jj=0;
									$pos = strpos($product_options, "value");
									do {
										if ($pos !== false) {
											// Bezeichnung von Optinen nach value, z.B. s:5:"value";s:22:"Zugabe Scooter/Torpedo";s:9:"option_id";s:3:"562";s:12:"option_value";s:4:"2469";
											// " ab Zeichen $pos ermitteln
											// Bezeichnung ermitteln
											$pos = strpos($product_options, ':"',$pos+1); // Erstes :"
											$posende = strpos($product_options, '"',$pos+2);
											$option_bez_tmp=substr($product_options, $pos+2, $posende-$pos-2);
												if (DEBUGGER>=1) fwrite($dateihandle,"349 Aktuelle Position: $pos und posende=$posende\n");
											// zugehoerige Options ID ermitteln
											$pos = strpos($product_options, "option_id",$posende+1);
											// Wenn nicht vorhanden, abbrechen
											if ($pos === false) {
												$option_bez_tmp="";
												$option_id_tmp="";
												$option_value_tmp="";
												$abbruch=true;
											}
											$pos = strpos($product_options, ':"',$pos+1); // Erstes :"
											$posende = strpos($product_options, '"',$pos+2);
											$option_id_tmp=substr($product_options, $pos+2, $posende-$pos-2);
												if (DEBUGGER>=1) fwrite($dateihandle,"362 Aktuelle Position: $pos  und posende=$posende\n");
											// zugehoeriges Options value ermitteln
											$pos = strpos($product_options, "option_value",$posende+1);
											// Wenn nicht vorhanden, abbrechen
											if ($pos === false) {
												$option_bez_tmp="";
												$option_id_tmp="";
												$option_value_tmp="";
												$abbruch=true;
											}
											$pos = strpos($product_options, ':"',$pos+1); // Erstes :"
											$posende = strpos($product_options, '"',$pos+2);
											$option_value_tmp=substr($product_options, $pos+2, $posende-$pos-2);
											if (DEBUGGER>=1) fwrite($dateihandle,"375 Aktuelle Position: $pos  und posende=$posende\n");
											if (DEBUGGER>=1) fwrite($dateihandle," - Allgemeine Produktoptionen ermitteln -> Bez[".$jj."]:".$option_bez_tmp." - id:".$option_id_tmp." value :".$option_value_tmp." \n");
											// Moeglicherweise sind mehrere Optionen in einem bez/value versteckt
											//if (strpos($option_value, ',') !== false) {
												$option_bez_tmp =  explode (', ', $option_bez_tmp);
												$option_value_tmp =  explode (', ', $option_value_tmp); 
												$option_value_tmp = explode (', ', $option_id_tmp); 
									
												$option_bez = array_merge($option_bez, $option_bez_tmp);
												$option_id = array_merge($option_id, $option_id_tmp);
												$option_value = array_merge($option_value, $option_value_tmp);
									
											/*} else {
												$option_bez = array_merge($option_bez, $option_bez_tmp);
												$option_id = array_merge($option_id, $option_id_tmp);
												$option_value = array_merge($option_value, $option_value_tmp);
											}*/
										}
										$jj++;
										// Im zweifel ABBRUCH
										if ($jj>10) $abbruch=true;
										// Weiteres value vorhanden?
										$pos = strpos($product_options, "value", $posende+1); 
										if (DEBUGGER>=1) fwrite($dateihandle,"383 Aktuelle Position: $pos  und posende=$posende\n");
									} while ($pos !== false && $abbruch==false);
										
									// Rabattierte Preise bei Bedarf berechnen
							//		if (DEBUGGER >= 1) fwrite($dateihandle,"discount_amount ".$order_infos[items][$product_no][discount_amount]."\n");
										
									if ($order_infos[items][$product_no][discount_amount]>0) {
										//fwrite($dateihandle,"discount_amount 403\n");
										// Produktpreise
										$price_amount_discounted = $order_infos[items][$product_no][base_price]-($order_infos[items][$product_no][base_price]*$order_infos[items][$product_no][discount_percent]/100);
										$price_line_amount_discounted = $order_infos[items][$product_no][row_total]-($order_infos[items][$product_no][row_total]*$order_infos[items][$product_no][discount_percent]/100);
										$price_amount_discounted_gros = $price_amount_discounted*($order_infos[items][$product_no][tax_percent]/100+1);
										$price_line_amount_discounted_gros = $price_line_amount_discounted*($order_infos[items][$product_no][tax_percent]/100+1);
										// Gesamtpreis Discount
										$order_sum_discounted_net += $price_line_amount_discounted;
										$order_sum_discounted_gros +=$price_line_amount_discounted_gros;
									} else {
										//fwrite($dateihandle,"discount_amount 413\n");
										// Produktpreise
										$price_amount_discounted = $order_infos[items][$product_no][base_price];
										$price_line_amount_discounted = $order_infos[items][$product_no][row_total];
										$price_amount_discounted_gros = $order_infos[items][$product_no][base_price]*(1+($order_infos[items][$product_no][tax_percent]/100));
										$price_line_amount_discounted_gros = ($order_infos[items][$product_no][row_total]*(1+($order_infos[items][$product_no][tax_percent]/100)));
										// Gesamtpreis Discount
										$order_sum_discounted_net += $price_line_amount_discounted;
										$order_sum_discounted_gros +=$price_line_amount_discounted_gros;
									}
									// Split if product_model contains attribute_id
									if (strpos($order_infos[items][$product_no][sku], '|') === false) {
										$art_nr=$order_infos[items][$product_no][sku];
										$attr_id = 0;
									} else {
										list ($art_nr, $attr_id) = explode ('|', $order_infos[items][$product_no][sku]);
									}
									// Wenn Optionen als zusaetzliche Artikel vorhanden, Artikelnummern neu generieren, Z.b. hat Magento Artikel 1234 mit Options-Artnr 126 die Artikelnummer 1234-126
									
									$jj=0; // OPTIONEN NICHT VERARBEITEN
									if ($jj>0) {
										$optionen_artnr= explode ('-', $order_infos[items][$product_no][sku]);
										$art_nr=$optionen_artnr[0];
									}
									
									if ($order_infos[items][$product_no][product_type] == "bundle") {
										// BUNDLE Artikel nicht exportieren
										$art_nr='';
									}
									
									// Artikelnummer Sage Classic Line -> Aufbau Schlüssel A + 20Stellinge Artikelnummer + Lieferanten 0000000000
									$cl_art_nr = "A".$art_nr;
									for ($ii=strlen($art_nr);$ii<20;$ii++)
										$cl_art_nr .= ' '; // Artikelnummer Classic Line auffuellen
									
									$cl_art_nr .= '00000000000'; // Lieferantennummer Classic Line  auffuellen	
									
									// Wert eines Attributes basierend auf flat products tabelle	ermitteln
									$cl_sku_temp = dmc_get_product_attribute_value($order_infos[items][$product_no][product_id],'new_cl_order_nr');
									if ($cl_sku_temp!="") $cl_art_nr = $cl_sku_temp;
									$cl_art_nr =  str_pad ( $cl_art_nr, 20, ' ', STR_PAD_RIGHT );
									
									// Classic Line Artikelnummer und Hersteller Nummer extrahieren
									$teile = explode("    ", $cl_sku_temp);
									$cl_artikel_nr =  trim ($teile[0]) ;
									$cl_art_hersteller_nr =  trim ($teile[1]) ;
									
									// Abfangroutine fuer nicht existente brutto Preise (insbes. conf)
									if ($order_infos[items][$product_no][base_price_incl_tax]=="") 
										$order_infos[items][$product_no][base_price_incl_tax]=$price_amount_discounted_gros;
									if ($order_infos[items][$product_no][row_total_incl_tax]=="") 
										$order_infos[items][$product_no][row_total_incl_tax]=$price_line_amount_discounted_gros;
									
									if ($attr_id=='') $attr_id=0;
									
								  $AnzahlProdukte=sizeof($order_infos[items]);
								  // Europa 3000 Positionsspalte
								// Zaehler Funktion für Europa 3000 Aufbau: 00000ZEAHLER00000ZAEHLER+1 (0000100004) mit ZAHLER=orderL+1
								if ($line_item_id==0) {
									// Erstes Produkt:
									$E3000_POS1_POS2 = "0000000002";
								}  else if ($line_item_id==($AnzahlProdukte-1)) {
									// Letztes Produkt:
									if ($line_item_id+1>=100) {
											$E3000_POS1_POS2 = "00".($line_item_id+1)."00000";
									} else if ($line_item_id+1>=10) {
											$E3000_POS1_POS2 = "000".($line_item_id+1)."00000";
									} else {
										$E3000_POS1_POS2 = "0000".($line_item_id+1)."00000";
									}
								} else {
									// Anderes Produkt
									 if ($line_item_id+1==9) {// Sonderfall
										$E3000_POS1_POS2 = "0000800010";
									} else if ($line_item_id+1==10) {// Sonderfall
										$E3000_POS1_POS2 = "0000900011";
									} else if ($line_item_id+1==99) {// Sonderfall
										$E3000_POS1_POS2 = "0009800100";
									} else if ($line_item_id+1==100) {// Sonderfall
										$E3000_POS1_POS2 = "0009900101";
									} else if ($line_item_id+1>=100) {
										$E3000_POS1_POS2 = "00".($line_item_id)."00".($line_item_id+2);
									} else if ($line_item_id+1>=10) {
										$E3000_POS1_POS2 = "000".($line_item_id)."000".($line_item_id+2);
									} else {
										$E3000_POS1_POS2 = "0000".($line_item_id)."0000".($line_item_id+2);
									}
								} 					
								  
								  if ($order_infos[items][$product_no][product_type] != "configurable" && $art_nr<>'') {
										$line_item_id++;
										$produkte_temp .='<ORDER_ITEM>' . "\n".
										// 0 and the inc
										'<LINE_ITEM_ID>' .$line_item_id.'</LINE_ITEM_ID>' . "\n".
										'<E3000_LINE_ITEM_ID>' .$E3000_POS1_POS2.'</E3000_LINE_ITEM_ID>' . "\n".
										'<PRODUCTS_ORDER_ID>' . $order_infos[order_id].'</PRODUCTS_ORDER_ID>' . "\n".
										'<ARTICLE_ID>' . "\n".
											'<SUPPLIER_AID>' . $art_nr.'</SUPPLIER_AID>' . "\n".
											'<SUPPLIER_AID_CLASSIC_LINE>' . $cl_art_nr.'</SUPPLIER_AID_CLASSIC_LINE>' . "\n".
											'<ATTRIBUTE_ID>' . $attr_id .'</ATTRIBUTE_ID>' . "\n".
											'<DESCRIPTION_SHORT>' . umlaute_order_export(trim($order_infos[items][$product_no][name])).'</DESCRIPTION_SHORT>' . "\n".
											'<DESCRIPTION_LONG>' . '' .'</DESCRIPTION_LONG>' . "\n".
											'<OPTIONS>' . $optionen .'</OPTIONS>' . "\n".							
									// EUROPA3000 M=Manueller Artikel, N=Standardartikel, B=weiterer Text zu Artikel, T=Text mit Inhalt in _F40, D=Text mit Inhalt in _F40
											'<ARTICLE_TYPE>N</ARTICLE_TYPE>' . "\n".
										'</ARTICLE_ID>' . "\n".	
										'<QUANTITY>' . $order_infos[items][$product_no][qty_ordered].'</QUANTITY>' . "\n". 	
										'<ORDER_UNIT>' . '1'.'</ORDER_UNIT>' . "\n". 	// Bestelleinheit, Z.b. "1"
										// Typs: net_list (netto Liste), gros_list (brutto Liste), net_customer (Kundenspezifischer Endpreis ohne Umsatzsteuer), nrp (UVP), udp_XXX (weitere selbstdefinierte Preise, Bsp: udp_aircargo_price)
										'<ARTICLE_PRICE_NET>' . "\n". // Immer Zahlen vorhanden -> +0
											'<PRICE_AMOUNT>' . ($order_infos[items][$product_no][base_price]+0).'</PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											'<PRICE_LINE_AMOUNT>' . ($order_infos[items][$product_no][row_total]+0).'</PRICE_LINE_AMOUNT>' . "\n".	// Gesamtpreis=PRICE_AMOUNT*QUANTITY
											'<PRICE_FLAG/>' .  "\n".	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
											'<TAX>' . ($order_infos[items][$product_no][tax_percent]+0).'</TAX>' . "\n".								// z.B. 19.0
											'<TAX_AMOUNT>' . (($order_infos[items][$product_no][tax_amount]/$order_infos[items][$product_no][qty_ordered])+0).'</TAX_AMOUNT>' . "\n".				// Steuerbetrag	
											'<TAX_LINE_AMOUNT>' . ($order_infos[items][$product_no][tax_amount]+0).'</TAX_LINE_AMOUNT>' . "\n".				// Steuerbetrag		
											'<DISCOUNT_AMOUNT>' . ($order_infos[items][$product_no][discount_amount]+0).'</DISCOUNT_AMOUNT>' . "\n".			
											'<DISCOUNT_PERCENT>' . ($order_infos[items][$product_no][discount_percent]+0).'</DISCOUNT_PERCENT>' . "\n".		// z.B. 20.0
											'<DISCOUNT_PRICE_AMOUNT>' .($price_amount_discounted+0).'</DISCOUNT_PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											'<DISCOUNT_PRICE_LINE_AMOUNT>' .($price_line_amount_discounted+0).'</DISCOUNT_PRICE_LINE_AMOUNT>' . "\n".	
										'</ARTICLE_PRICE_NET>' . "\n".	
										'<ARTICLE_PRICE_GROS>' . "\n".
											// BUG FUER fehlerhafte TAX_AMOUNT
											// '<PRICE_AMOUNT>' . ($order_infos[items][$product_no][base_price]+($order_infos[items][$product_no][tax_amount]/$order_infos[items][$product_no][qty_ordered])).'</PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											//'<PRICE_LINE_AMOUNT>' . ($order_infos[items][$product_no][row_total]+$order_infos[items][$product_no][tax_amount]).'</PRICE_LINE_AMOUNT>' . "\n".	// Gesamtpreis=PRICE_AMOUNT*QUANTITY
											// '<PRICE_AMOUNT>' . substr($order_infos[items][$product_no][base_price]*(1+($order_infos[items][$product_no][tax_percent]/100)),0, -2).'</PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											'<PRICE_AMOUNT>' . ($order_infos[items][$product_no][base_price_incl_tax]+0).'</PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
										//	'<PRICE_LINE_AMOUNT>' . substr($order_infos[items][$product_no][row_total]*(1+($order_infos[items][$product_no][tax_percent]/100)),0, -2).'</PRICE_LINE_AMOUNT>' . "\n".	// Gesamtpreis=PRICE_AMOUNT*QUANTITY
											'<PRICE_LINE_AMOUNT>' .($order_infos[items][$product_no][row_total_incl_tax]+0).'</PRICE_LINE_AMOUNT>' . "\n".	// '<PRICE_FLAG/>' .  "\n".	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
											'<TAX>' . ($order_infos[items][$product_no][tax_percent]+0).'</TAX>' . "\n".								// z.B. 19.0
											'<TAX_AMOUNT>' . ($order_infos[items][$product_no][tax_amount]/$order_infos[items][$product_no][qty_ordered]).'</TAX_AMOUNT>' . "\n".				// Steuerbetrag	
											'<TAX_LINE_AMOUNT>' . ($order_infos[items][$product_no][tax_amount]+0).'</TAX_LINE_AMOUNT>' . "\n".				// Steuerbetrag		
											'<DISCOUNT_AMOUNT>' . ($order_infos[items][$product_no][discount_amount]*(1+($order_infos[items][$product_no][tax_percent]/100))+0).'</DISCOUNT_AMOUNT>' . "\n".			
											'<DISCOUNT_PERCENT>'.($order_infos[items][$product_no][discount_percent]+0).'</DISCOUNT_PERCENT>'."\n".
											'<DISCOUNT_PRICE_AMOUNT>' .($price_amount_discounted_gros+0).'</DISCOUNT_PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											'<DISCOUNT_PRICE_LINE_AMOUNT>' .($price_line_amount_discounted_gros+0).'</DISCOUNT_PRICE_LINE_AMOUNT>' . "\n".	
										'</ARTICLE_PRICE_GROS>' . "\n".							
									    '</ORDER_ITEM>' . "\n";
										// Get and ADD products short description	EUROPA3000 - Short_Description as single product						
										if(WAWI_NAME=="europa3000") {
											$line_item_id++;
											$products_shortdescription = dmc_get_shortdescription($order_infos[items][$product_no][product_id]);
											 $produkte_temp .='<ORDER_ITEM>' . "\n".
												// 0 and the inc
												'<LINE_ITEM_ID>' .$line_item_id.'</LINE_ITEM_ID>' . "\n".
												'<E3000_LINE_ITEM_ID>' .$E3000_POS1_POS2.'</E3000_LINE_ITEM_ID>' . "\n".
												'<PRODUCTS_ORDER_ID>' . $order_infos[order_id].'</PRODUCTS_ORDER_ID>' . "\n".
										
												'<ARTICLE_ID>' . "\n".
													'<SUPPLIER_AID></SUPPLIER_AID>' . "\n".
													'<ATTRIBUTE_ID>0</ATTRIBUTE_ID>' . "\n".
													'<DESCRIPTION_SHORT>' . umlaute_order_export(trim($products_shortdescription)).'</DESCRIPTION_SHORT>' . "\n".
													'<DESCRIPTION_LONG></DESCRIPTION_LONG>' . "\n".
													// EUROPA3000 M=Manueller Artikel, N=Standardartikel, B=weiterer Text zu Artikel, T=Text mit Inhalt in _F40, D=Text mit Inhalt in _F40
													'<ARTICLE_TYPE>B</ARTICLE_TYPE>' . "\n".
												'</ARTICLE_ID>' . "\n".	
												'<QUANTITY></QUANTITY>' . "\n". 	
												'<ORDER_UNIT></ORDER_UNIT>' . "\n". 	// Bestelleinheit, Z.b. "1"
												// Typs: net_list (netto Liste), gros_list (brutto Liste), net_customer (Kundenspezifischer Endpreis ohne Umsatzsteuer), nrp (UVP), udp_XXX (weitere selbstdefinierte Preise, Bsp: udp_aircargo_price)
												'<ARTICLE_PRICE_NET>' . "\n".
													'<PRICE_AMOUNT></PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
													'<PRICE_LINE_AMOUNT></PRICE_LINE_AMOUNT>' . "\n".	// Gesamtpreis=PRICE_AMOUNT*QUANTITY
													'<PRICE_FLAG/>' .  "\n".	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
													'<TAX></TAX>' . "\n".								// z.B. 19.0
													'<TAX_AMOUNT></TAX_AMOUNT>' . "\n".				// Steuerbetrag	
													'<TAX_LINE_AMOUNT></TAX_LINE_AMOUNT>' . "\n".				// Steuerbetrag		
													'<DISCOUNT_AMOUNT></DISCOUNT_AMOUNT>' . "\n".			
													'<DISCOUNT_PERCENT></DISCOUNT_PERCENT>' . "\n".								// z.B. 19.0
												'</ARTICLE_PRICE_NET>' . "\n".	
												'<ARTICLE_PRICE_GROS>' . "\n".
													'<PRICE_AMOUNT></PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
													'<PRICE_LINE_AMOUNT></PRICE_LINE_AMOUNT>' . "\n".	// Gesamtpreis=PRICE_AMOUNT*QUANTITY
													'<PRICE_FLAG/>' .  "\n".	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
													'<TAX></TAX>' . "\n".								// z.B. 19.0
													'<TAX_AMOUNT></TAX_AMOUNT>' . "\n".				// Steuerbetrag	
													'<TAX_LINE_AMOUNT></TAX_LINE_AMOUNT>' . "\n".				// Steuerbetrag		
													'<DISCOUNT_AMOUNT></DISCOUNT_AMOUNT>' . "\n".			
													'<DISCOUNT_PERCENT></DISCOUNT_PERCENT>' . "\n".								// z.B. 19.0						
												'</ARTICLE_PRICE_GROS>' . "\n".							
											    '</ORDER_ITEM>' . "\n";
										} // end if Europa3000
										$total_tax += $order_infos[items][$product_no][tax_amount];
									} // end if != Conf
									
									// Artikeloptionen ($jj>0) als Produkte einfuegen
									if (isset($optionen_artnr))
									for ($ii=0;$ii<(sizeof($optionen_artnr)-1);$ii++) {
									//	fwrite($dateihandle,"*************  Optionen Nr = $ii\n");
										$line_item_id++;
										$produkte_temp .='<ORDER_ITEM>' . "\n".
										// 0 and the inc 
										'<LINE_ITEM_ID>' .$line_item_id.'</LINE_ITEM_ID>' . "\n".
										'<E3000_LINE_ITEM_ID>' .$E3000_POS1_POS2.'</E3000_LINE_ITEM_ID>' . "\n".
										'<PRODUCTS_ORDER_ID>' . $order_infos[order_id].'</PRODUCTS_ORDER_ID>' . "\n".
										'<ARTICLE_ID>' . "\n".
											'<SUPPLIER_AID>' . $optionen_artnr[$ii+1].'</SUPPLIER_AID>' . "\n".
											'<SUPPLIER_AID_CLASSIC_LINE>' . $optionen_artnr[$ii+1].'</SUPPLIER_AID_CLASSIC_LINE>' . "\n".
											'<ATTRIBUTE_ID>0</ATTRIBUTE_ID>' . "\n".
											'<DESCRIPTION_SHORT>' . umlaute_order_export(trim($option_bez[$ii])).'</DESCRIPTION_SHORT>' . "\n".
											'<DESCRIPTION_LONG>' . '' .'</DESCRIPTION_LONG>' . "\n".
											'<OPTIONS></OPTIONS>' . "\n".							
									// EUROPA3000 M=Manueller Artikel, N=Standardartikel, B=weiterer Text zu Artikel, T=Text mit Inhalt in _F40, D=Text mit Inhalt in _F40
											'<ARTICLE_TYPE>N</ARTICLE_TYPE>' . "\n".
										'</ARTICLE_ID>' . "\n".	
										'<QUANTITY>1</QUANTITY>' . "\n". 	
										'<ORDER_UNIT>' . '1'.'</ORDER_UNIT>' . "\n". 	// Bestelleinheit, Z.b. "1"
										// Typs: net_list (netto Liste), gros_list (brutto Liste), net_customer (Kundenspezifischer Endpreis ohne Umsatzsteuer), nrp (UVP), udp_XXX (weitere selbstdefinierte Preise, Bsp: udp_aircargo_price)
										'<ARTICLE_PRICE_NET>' . "\n".
											'<PRICE_AMOUNT>0</PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											'<PRICE_LINE_AMOUNT>0</PRICE_LINE_AMOUNT>' . "\n".	// Gesamtpreis=PRICE_AMOUNT*QUANTITY
											'<PRICE_FLAG/>' .  "\n".	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
											'<TAX>0</TAX>' . "\n".								// z.B. 19.0
											'<TAX_AMOUNT>0</TAX_AMOUNT>' . "\n".				// Steuerbetrag	
											'<TAX_LINE_AMOUNT>0</TAX_LINE_AMOUNT>' . "\n".				// Steuerbetrag		
											'<DISCOUNT_AMOUNT>0</DISCOUNT_AMOUNT>' . "\n".			
											'<DISCOUNT_PERCENT>0</DISCOUNT_PERCENT>' . "\n".		// z.B. 20.0
											'<DISCOUNT_PRICE_AMOUNT>0</DISCOUNT_PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											'<DISCOUNT_PRICE_LINE_AMOUNT>0</DISCOUNT_PRICE_LINE_AMOUNT>' . "\n".	
										'</ARTICLE_PRICE_NET>' . "\n".	
										'<ARTICLE_PRICE_GROS>' . "\n".
											'<PRICE_AMOUNT>0</PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											'<PRICE_LINE_AMOUNT>0</PRICE_LINE_AMOUNT>' . "\n".	// Gesamtpreis=PRICE_AMOUNT*QUANTITY
											'<PRICE_FLAG/>' .  "\n".	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
											'<TAX>0</TAX>' . "\n".								// z.B. 19.0
											'<TAX_AMOUNT>0</TAX_AMOUNT>' . "\n".				// Steuerbetrag	
											'<TAX_LINE_AMOUNT>0</TAX_LINE_AMOUNT>' . "\n".				// Steuerbetrag		
											'<DISCOUNT_AMOUNT>0</DISCOUNT_AMOUNT>' . "\n".			
											'<DISCOUNT_PERCENT>0</DISCOUNT_PERCENT>' . "\n".		// z.B. 20.0
											'<DISCOUNT_PRICE_AMOUNT>0</DISCOUNT_PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											'<DISCOUNT_PRICE_LINE_AMOUNT>0</DISCOUNT_PRICE_LINE_AMOUNT>' . "\n".	
										'</ARTICLE_PRICE_GROS>' . "\n".							
									    '</ORDER_ITEM>' . "\n";
										// $option_id[$jj]
										// $option_value[$jj]
									} // end for artikel optionen
									
								} // end if passt
								
								// Bei $kommissionsartikel=false;
								if ($kommissionsartikel==true)
									$produkte_kommission .= $produkte_temp;
								else
									$produkte .= $produkte_temp;
								$produkte_temp="";
								
							} // end for items
							// Versandkosten als Artikel
								// Artikelnummer Sage Classic Line
								$cl_art_nr = "A".$order_infos[shipping_method];
								$cl_art_nr = str_replace("flatrate_flatrate","9001",$cl_art_nr);
							
								for ($ii=strlen($order_infos[shipping_method]);$ii<20;$ii++)
									$cl_art_nr .= ' '; // Artikelnummer Classic Line auffuellen
								
								$cl_art_nr .= '00000000000'; // Lieferantennummer Classic Line  auffuellen	
								$cl_art_nr =  str_pad ( $cl_art_nr, 20, ' ', STR_PAD_RIGHT );
									
								
								if (SHIPPING_DISCOUNTED) { // $order_infos[items][$k][discount_amount]>0) {
										// Reduzierte Versandpreise
										$price_amount_discounted = $order_infos[shipping_amount]-$order_infos[shipping_discount_amount];
										$price_line_amount_discounted = $price_amount_discounted;
										$price_amount_discounted_gros = $price_amount_discounted+$order_infos[shipping_tax_amount];
										$price_line_amount_discounted_gros = $price_amount_discounted_gros;
										// Gesamtpreis Discount
										$order_sum_discounted_net += $price_line_amount_discounted;
										$order_sum_discounted_gros +=$price_line_amount_discounted_gros;
									} else {
										// Versandpreise
										$price_amount_discounted = $order_infos[shipping_amount];
										$price_line_amount_discounted = $price_amount_discounted;
										$price_amount_discounted_gros = $price_amount_discounted+$order_infos[shipping_tax_amount];
										$price_line_amount_discounted_gros = $price_amount_discounted_gros;
										// Gesamtpreis Discount
										$order_sum_discounted_net += $price_line_amount_discounted;
										$order_sum_discounted_gros +=$price_line_amount_discounted_gros;
									} // end if discout
									
								if(SHIPPING_AS_PRODUCT && $order_infos[shipping_amount]>0) {
									// Versandkosten
									
									$line_item_id++;
									 $versand_temp .= '<ORDER_ITEM>' . "\n".
										// 0 and the inc
										'<LINE_ITEM_ID>' .$line_item_id.'</LINE_ITEM_ID>' . "\n".
										'<E3000_LINE_ITEM_ID>' .$E3000_POS1_POS2.'</E3000_LINE_ITEM_ID>' . "\n".
										'<PRODUCTS_ORDER_ID>' . $order_infos[order_id].'</PRODUCTS_ORDER_ID>' . "\n".
										'<ARTICLE_ID>' . "\n".
											'<SUPPLIER_AID>'.$order_infos[shipping_method].'</SUPPLIER_AID>' . "\n".
											'<SUPPLIER_AID_CLASSIC_LINE>' . $cl_art_nr.'</SUPPLIER_AID_CLASSIC_LINE>' . "\n".
											'<ATTRIBUTE_ID>0</ATTRIBUTE_ID>' . "\n".
											'<DESCRIPTION_SHORT>Versandkosten</DESCRIPTION_SHORT>' . "\n".
											'<DESCRIPTION_LONG></DESCRIPTION_LONG>' . "\n".
									// EUROPA3000 M=Manueller Artikel, N=Standardartikel, B=weiterer Text zu Artikel, T=Text mit Inhalt in _F40, D=Text mit Inhalt in _F40	
											'<ARTICLE_TYPE>N</ARTICLE_TYPE>' . "\n".
										'</ARTICLE_ID>' . "\n".	
										'<QUANTITY>1</QUANTITY>' . "\n". 	
										'<ORDER_UNIT>1</ORDER_UNIT>' . "\n". 	// Bestelleinheit, Z.b. "1"
										'<DELIVERY_METHOD>' . $order_infos[shipping_method].'</DELIVERY_METHOD>' . "\n".
										'<ARTICLE_PRICE_NET>' . "\n".
												'<PRICE_AMOUNT>'.$order_infos[shipping_amount].'</PRICE_AMOUNT>' . "\n".
												'<PRICE_LINE_AMOUNT>'.$order_infos[shipping_amount].'</PRICE_LINE_AMOUNT>' . "\n".	
												'<PRICE_FLAG/>' .  "\n";	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
											// Wenn keine Versandkosten
											if ($order_infos[shipping_amount]>0) 
												$versand_temp .=	'<TAX>' .($order_infos[shipping_tax_amount]/$order_infos[shipping_amount]*100).'</TAX>' . "\n";
											else 
												$versand_temp .=	'<TAX>0</TAX>' . "\n";
											
											$versand_temp .=	'<TAX_AMOUNT>'.$order_infos[shipping_tax_amount].'</TAX_AMOUNT>' . "\n".
											'<TAX_LINE_AMOUNT>'.$order_infos[shipping_tax_amount].'</TAX_LINE_AMOUNT>' . "\n".										 
											'<DISCOUNT_AMOUNT>0</DISCOUNT_AMOUNT>' . "\n".			
											'<DISCOUNT_PERCENT>0</DISCOUNT_PERCENT>' . "\n".
											'<DISCOUNT_PRICE_AMOUNT>' .$price_amount_discounted.'</DISCOUNT_PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
											'<DISCOUNT_PRICE_LINE_AMOUNT>' .$price_line_amount_discounted.'</DISCOUNT_PRICE_LINE_AMOUNT>' . "\n".	
										'</ARTICLE_PRICE_NET>' . "\n".	
										'<ARTICLE_PRICE_GROS>' . "\n".
												'<PRICE_AMOUNT>'.($order_infos[shipping_amount]+$order_infos[shipping_tax_amount]).'</PRICE_AMOUNT>' . "\n".										
												'<PRICE_LINE_AMOUNT>'.($order_infos[shipping_amount]+$order_infos[shipping_tax_amount]).'</PRICE_LINE_AMOUNT>' . "\n".	
												'<PRICE_FLAG/>' .  "\n";	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
													// Wenn keine Versandkosten
													if ($order_infos[shipping_amount]>0) 
														$versand_temp .=	'<TAX>' .($order_infos[shipping_tax_amount]/$order_infos[shipping_amount]*100).'</TAX>' . "\n";
													else 
														$versand_temp .=	'<TAX>0</TAX>' . "\n";
													
												$versand_temp .='<TAX_AMOUNT>'.$order_infos[shipping_tax_amount].'</TAX_AMOUNT>' . "\n".
												'<TAX_LINE_AMOUNT>'.$order_infos[shipping_tax_amount].'</TAX_LINE_AMOUNT>' . "\n".
											  	'<DISCOUNT_AMOUNT>0</DISCOUNT_AMOUNT>' . "\n".			
												'<DISCOUNT_PERCENT>0</DISCOUNT_PERCENT>' . "\n".	
												'<DISCOUNT_PRICE_AMOUNT>' .$price_amount_discounted_gros.'</DISCOUNT_PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
												'<DISCOUNT_PRICE_LINE_AMOUNT>' .$price_line_amount_discounted_gros.'</DISCOUNT_PRICE_LINE_AMOUNT>' . "\n".	
										'</ARTICLE_PRICE_GROS>' . "\n".	
										 '</ORDER_ITEM>' . "\n";
										
								} // end if Versandkosten als Artikel
								 
								// Nachnahme als Artikel
								if (SHIPPING_AS_PRODUCT && $order_infos[payment][method] == "cashondelivery") {
									$art_nn_art='NN';
									$cl_art_nr = "A".$art_nn_art;
									for ($ii=strlen($art_nn_art);$ii<20;$ii++)
										$cl_art_nr .= ' '; // Artikelnummer Classic Line auffuellen
									
									$cl_art_nr .= '00000000000'; // Lieferantennummer Classic Line  auffuellen	
									$cl_art_nr =  str_pad ( $cl_art_nr, 20, ' ', STR_PAD_RIGHT );
									
											$line_item_id++; 
											$versand_temp .= '<ORDER_ITEM>' . "\n".
											// 0 and the inc
											'<LINE_ITEM_ID>' .$line_item_id.'</LINE_ITEM_ID>' . "\n".
											'<PRODUCTS_ORDER_ID>'.$order_infos[order_id].'</PRODUCTS_ORDER_ID>' . "\n".
											'<ARTICLE_ID>' . "\n".
												'<SUPPLIER_AID>NN</SUPPLIER_AID>' . "\n".
												'<SUPPLIER_AID_CLASSIC_LINE>'.$cl_art_nr.'</SUPPLIER_AID_CLASSIC_LINE>' . "\n".
												'<ATTRIBUTE_ID>0</ATTRIBUTE_ID>' . "\n".
												'<DESCRIPTION_SHORT>Nachnahme</DESCRIPTION_SHORT>' . "\n".
												'<DESCRIPTION_LONG></DESCRIPTION_LONG>' . "\n".
											// EUROPA3000 M=Manueller Artikel, N=Standardartikel, B=weiterer Text zu Artikel, T=Text mit Inhalt in _F40, D=Text mit Inhalt in _F40	
												'<ARTICLE_TYPE>N</ARTICLE_TYPE>' . "\n".
											'</ARTICLE_ID>' . "\n".	
											'<QUANTITY>1</QUANTITY>' . "\n". 	
											'<ORDER_UNIT>1</ORDER_UNIT>' . "\n". 	// Bestelleinheit, Z.b. "1"
											'<DELIVERY_METHOD>' . $order_infos[shipping_method].'</DELIVERY_METHOD>' . "\n".
											'<ARTICLE_PRICE_NET>' . "\n".
													'<PRICE_AMOUNT>'.$order_infos[cod_fee].'</PRICE_AMOUNT>' . "\n".
													'<PRICE_LINE_AMOUNT>'.$order_infos[cod_fee].'</PRICE_LINE_AMOUNT>' . "\n".	
													'<PRICE_FLAG/>' .  "\n";	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
												// Wenn keine Versandkosten
												if ($order_infos[cod_tax_amount]>0) 
													$versand_temp .=	'<TAX>' .($order_infos[cod_tax_amount]/$order_infos[cod_fee]*100).'</TAX>' . "\n";
												else 
													$versand_temp .=	'<TAX>0</TAX>' . "\n";
												
												$versand_temp .=	'<TAX_AMOUNT>'.$order_infos[cod_tax_amount].'</TAX_AMOUNT>' . "\n".
												'<TAX_LINE_AMOUNT>'.$order_infos[cod_tax_amount].'</TAX_LINE_AMOUNT>' . "\n".										 
												'<DISCOUNT_AMOUNT>0</DISCOUNT_AMOUNT>' . "\n".			
												'<DISCOUNT_PERCENT>0</DISCOUNT_PERCENT>' . "\n".
												'<DISCOUNT_PRICE_AMOUNT>' .$order_infos[cod_fee].'</DISCOUNT_PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
												'<DISCOUNT_PRICE_LINE_AMOUNT>' .$order_infos[cod_fee].'</DISCOUNT_PRICE_LINE_AMOUNT>' . "\n".	
											'</ARTICLE_PRICE_NET>' . "\n".	
											'<ARTICLE_PRICE_GROS>' . "\n".
													'<PRICE_AMOUNT>'.($order_infos[$order_infos[cod_fee]]+$order_infos[cod_tax_amount]).'</PRICE_AMOUNT>' . "\n".										
													'<PRICE_LINE_AMOUNT>'.($order_infos[cod_fee]+$order_infos[cod_tax_amount]).'</PRICE_LINE_AMOUNT>' . "\n".	
													'<PRICE_FLAG/>' .  "\n";	// Typs: incl_freight, incl_packing, incl_assurance, incl_duty
														// Wenn keine Versandkosten
														if ($order_infos[cod_tax_amount]>0) 
															$versand_temp .=	'<TAX>' .($order_infos[cod_tax_amount]/$order_infos[cod_fee]*100).'</TAX>' . "\n";
														else 
															$versand_temp .=	'<TAX>0</TAX>' . "\n";
														
													$versand_temp .='<TAX_AMOUNT>'.$order_infos[cod_tax_amount].'</TAX_AMOUNT>' . "\n".
													'<TAX_LINE_AMOUNT>'.$order_infos[cod_tax_amount].'</TAX_LINE_AMOUNT>' . "\n".
													'<DISCOUNT_AMOUNT>0</DISCOUNT_AMOUNT>' . "\n".			
													'<DISCOUNT_PERCENT>0</DISCOUNT_PERCENT>' . "\n".	
													'<DISCOUNT_PRICE_AMOUNT>' .$order_infos[cod_fee].'</DISCOUNT_PRICE_AMOUNT>' . "\n".			// Einzelpreis, zB. 399.99
													'<DISCOUNT_PRICE_LINE_AMOUNT>' .$order_infos[cod_fee].'</DISCOUNT_PRICE_LINE_AMOUNT>' . "\n".	
											'</ARTICLE_PRICE_GROS>' . "\n".	
											 '</ORDER_ITEM>' . "\n";
										
								}  // end if NN als Artikel
								
								// XML weiter aufbauen
							if ($produkte_kommission!="" && $produkte=="") {
								// Wenn nur Kommissionsartikel vorhanden - eine Bestellung
								$schema .= $produkte_kommission;
								$schema .= $versand_temp;
							} else if ($produkte_kommission=="" && $produkte=="")  {
								// Wenn nur Kommissionsartikel UND Standard vorhanden - zwei Bestellungen
								$schema .= $produkte;
								$schema .= $versand_temp;
								// Zweites Schema für Kommission
								$schema2 .= $schema.$produkte_kommission;
								$schema2 .= $versand_temp;								
							} else {
								// Standard - keine Kommission
								$schema .= $produkte; 
								$schema .= $versand_temp;
							}
						
							$schema_footer .='</ORDER_ITEM_LIST>' . "\n".
							/* '<ORDER_SUMMARY>' . "\n". 
							'<TOTAL_ITEM_NUM>' . $line_item_id .'</TOTAL_ITEM_NUM>' . "\n".
							'<SUBTOTAL_AMOUNT_NET>' . $order_infos[subtotal].'</SUBTOTAL_AMOUNT_NET>' . "\n". 	// subtotal = without shipping 
							'<TOTAL_AMOUNT_NET>' . $order_infos[grand_total].'</TOTAL_AMOUNT_NET>' . "\n". 	
							'<TOTAL_TAX_AMOUNT>' .$order_infos[tax_amount].'</TOTAL_TAX_AMOUNT>' . "\n".					
							'<SUBTOTAL_AMOUNT>' .($order_infos[subtotal]+$order_infos[tax_amount]+$order_infos[shipping_amount]).'</SUBTOTAL_AMOUNT>' . "\n". 
							'<TOTAL_AMOUNT>' . ($order_infos[grand_total]+$order_infos[tax_amount]+$order_infos[shipping_amount]).'</TOTAL_AMOUNT>' . "\n". 
							'<ORDER_CURRENCY_CODE>' .$order_infos[order_currency_code].'</ORDER_CURRENCY_CODE>' . "\n".	
							
						'</ORDER_SUMMARY>' . "\n". */
							'<ORDER_SUMMARY>' . "\n". 
								'<TOTAL_ITEM_NUM>' . $line_item_id .'</TOTAL_ITEM_NUM>' . "\n".
								'<SUBTOTAL_AMOUNT_NET>' . $order_infos[subtotal].'</SUBTOTAL_AMOUNT_NET>' . "\n". 	// subtotal = without shipping 
								'<TOTAL_AMOUNT_NET>' . ($order_infos[grand_total]-$order_infos[tax_amount]).'</TOTAL_AMOUNT_NET>' . "\n". 	
								'<TOTAL_TAX_AMOUNT>' .$order_infos[tax_amount].'</TOTAL_TAX_AMOUNT>' . "\n".					
								'<SUBTOTAL_AMOUNT>' .($order_infos[subtotal]+$order_infos[tax_amount]).'</SUBTOTAL_AMOUNT>' . "\n". 
								'<TOTAL_AMOUNT>' . $order_infos[grand_total].'</TOTAL_AMOUNT>' . "\n". 
								'<ORDER_CURRENCY_CODE>' .$order_infos[order_currency_code].'</ORDER_CURRENCY_CODE>' . "\n".		
								'<DISCOUNT_TOTAL_AMOUNT_NET>' . $order_sum_discounted_net.'</DISCOUNT_TOTAL_AMOUNT_NET>' . "\n". 	
								'<DISCOUNT_TOTAL_AMOUNT>' . $order_sum_discounted_gros.'</DISCOUNT_TOTAL_AMOUNT>' . "\n". 
								'<DISCOUNT_TOTAL_AMOUNT_TAX>' . ($order_sum_discounted_gros-$order_sum_discounted_net).'</DISCOUNT_TOTAL_AMOUNT_TAX>' . "\n". 	
							'</ORDER_SUMMARY>' . "\n";
							for ($iii=0;$iii<sizeof($order_infos['aitoc_order_custom_data']);$iii++) {
								// Kaffezentrale
								if ($order_infos['aitoc_order_custom_data'][$iii]['label']=='Zusatzinformation') {
									$schema_footer .= '<ZUSATZ_INFO>' .
													$order_infos['aitoc_order_custom_data'][$iii]['value'].	// KAFFEZENTRALE
												'</ZUSATZ_INFO>' . "\n";
								} else {
									$schema_footer .= '<ZUSATZ_INFO>' . '</ZUSATZ_INFO>' . "\n";
								}
								if ($order_infos['aitoc_order_custom_data'][$iii]['label']=='Anmerkung zur Bestellung') {
									$schema_footer .= '<ORDER_COMMENT>' .
													$order_infos['aitoc_order_custom_data'][$iii]['value'].	// KAFFEZENTRALE
												'</ORDER_COMMENT>' . "\n";
								} else {
									$schema_footer .= '<ORDER_COMMENT>' . "\n".

										$order_infos[onestepcheckout_customercomment].	// DESCH
									'</ORDER_COMMENT>' . "\n";
								}
							}
							
							$schema_footer .= 	'<ORDER_ACCOUNT_NUMBER>' . "\n".
											$order_infos[job_account_number].	// DESCH
										'</ORDER_ACCOUNT_NUMBER>' . "\n".
							
						'</ORDER>' . "\n";
						
						// Footer an Bestellung, ggfls auch an zweite fuer Kommissionsartikel
						$schema .= $schema_footer;
						if ($schema2 != "") {
							// Komssionsbestellung an Bestellung anhaengen
							$schema .= $schema2.$schema_footer;
						}		
						$schema_footer="";
						fwrite($dateihandle,"948 Update Order Status ? ");
						if (UPDATE_ORDER_STATUS) {
							fwrite($dateihandle,"950 to ".NEW_ORDER_STATUS);
							try {
								$client->call($session, 'sales_order.addComment', array($order_list[$i][increment_id], NEW_ORDER_STATUS,  'Bestellstatus geaendert',  NOTIFY_CUSTOMER));
								if (DEBUGGER>=1) fwrite($dateihandle,"Order Status updated to ".NEW_ORDER_STATUS."\n");	
							} catch (SoapFault $e) {
								if (DEBUGGER>=1) fwrite($dateihandle,"TRYed to Order (".$order_list[$i][increment_id].") Status update to ".NEW_ORDER_STATUS."\n");	
								if (DEBUGGER>=1) fwrite($dateihandle,'ERROR: Failed:\n'.$e.'\n'.$e->getMessage() );
							}						
						} 
						fwrite($dateihandle,"959 done \n ");
						
						
?>