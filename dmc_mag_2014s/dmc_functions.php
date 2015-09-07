<?php
/********************************************************************************
*                                                                               *
*  dm.connector  for magento shop												*
*  dmc_functions.php															*
*  Allgemeine Funktionen														*
*  Copyright (C) 2008 DoubleM-GmbH.de											*
*                                                                               *
********************************************************************************/
//  21.05.2010 - Neue Funktion print_post
// 21.07.2010 - Neue funktion log_array
// 06.01.2011 - nicht UTF8 Buchstaben zu # - function prove_utf8($str)
// 12.01.2011 - rtf Unterstuetzung
// 27.02.2011 - rtf Unterstuetzung convert_rtf_2_html
// 27.02.2011 - umlaute_order_export mit unbekannte / nicht UTF8 Zeichen zu #
// 14.01.2013 - checkOrders ueberarbeitet, z.B. bis OrderStatus10 moeglich 
// 06.01.2014 - delFiles($verzeichnis,$endung,$sekundenalt) um Dateien aus einem Ordner zu loeschen, zB session oder cache

defined( 'VALID_DMC' ) or die( 'Direct Access to this location (functions) is not allowed.' );
	
	/**
	 *
	 * @check if string is utf8
	 * @param string $s
	 * @return string $s
	 *
	 */
	function is_utf8($str){
		global $dateihandle;
		// fwrite($dateihandle, "\is_utf8?:.$str\n");
		for($i=0; $i<strlen($str); $i++){
		//  fwrite($dateihandle, "\is_utf8 27 $i von ".strlen($str)."\n");
			$ord = ord($str[$i]);
			if($ord < 0x80) continue; // 0bbbbbbb
			elseif(($ord&0xE0)===0xC0 && $ord>0xC1) $n = 1; // 110bbbbb (exkl C0-C1)
			elseif(($ord&0xF0)===0xE0) $n = 2; // 1110bbbb
			elseif(($ord&0xF8)===0xF0 && $ord<0xF5) $n = 3; // 11110bbb (exkl F5-FF)
			else return false; // ungültiges UTF-8-Zeichen
			for($c=0; $c<$n; $c++) // $n Folgebytes? // 10bbbbbb
			  if(++$i===$strlen || (ord($str[$i])&0xC0)!==0x80)
				return false; // ungültiges UTF-8-Zeichen
		}
	  return true; // kein ungültiges UTF-8-Zeichen gefunden
	}
	
	// nicht UTF8 Buchstaben zu #
	function prove_utf8($str){
		global $dateihandle;
		// fwrite($dateihandle, "\is_utf8?:.$str\n");
		for($i=0; $i<strlen($str); $i++){
		//  fwrite($dateihandle, "\is_utf8 27 $i von ".strlen($str)."\n");
			$fehler=false;
			$ord = ord($str[$i]);
			if($ord < 0x80) continue; // 0bbbbbbb
			elseif(($ord&0xE0)===0xC0 && $ord>0xC1) $n = 1; // 110bbbbb (exkl C0-C1)
			elseif(($ord&0xF0)===0xE0) $n = 2; // 1110bbbb
			elseif(($ord&0xF8)===0xF0 && $ord<0xF5) $n = 3; // 11110bbb (exkl F5-FF)
			else $fehler=true; // ungültiges UTF-8-Zeichen
			for($c=0; $c<$n; $c++) // $n Folgebytes? // 10bbbbbb
			  if(++$i===$strlen || (ord($str[$i])&0xC0)!==0x80)
				$fehler=true; // ungültiges UTF-8-Zeichen
			if ($fehler==false) $rueckgabe .= $str[$i];
			else  $rueckgabe .= "#";
		}
	  return $rueckgabe; 
	}
	
	function sonderzeichen2html($s) {
		
		global $dateihandle;
		 // decode any entities 
		 //	 $s = strtr($s,array_flip(get_html_translation_table(HTML_ENTITIES)));
		fwrite($dateihandle, "sonderzeichen2html ".substr($s,0,1) ."\n");
		// ggfls RTF TEXT umwandeln
		if (substr($s,0,1) == "{") {
			// include rtf functions
			convert_rtf_2_html ($s);
		} // end 

		// convert & 
		// $s = preg_replace('@&@i','&amp;',$s);
		 //		 fwrite($dateihandle, "\nsonderzeichen2html\n");
		$s=	 htmlspecialchars($s);
		 //  Umlaute (für GS Auftrag)
		// $d1 = array("Ä", "Ö", "Ü", "ä" , "ö", "ü", "ß","é");
		// $d2 = array("&#196;","&#214;","&#220;","&#228;","&#246;","&#252;","&#223;","e");
		// $s = str_replace($d1, $d2, $s);
		 $s = str_replace("é", "e", $s);		 

		 $s = str_replace('@Ã„@i','&#196;',$s);
		 $s = str_replace('@Ã–@i','&#214;',$s);
		 $s = str_replace('@Ãœ@i','&#220;',$s);
		 $s = str_replace('@Ã¤@i','&#228;',$s);
		 $s = str_replace('@Ã¶@i','&#246;',$s);
		 $s = str_replace('@Ã¼@i','&#252;',$s);
		 $s = str_replace('@ÃŸ@i','&#223;',$s);
		 $s = str_replace('@Ã˜@i','&Oslash;',$s);	// durchmesser
		 
		 $s = str_replace('@Ã˜@i','&Oslash;',$s);	// durchmesser
		 $s = str_replace('@Âº@i','&deg;',$s);	// grad 
		 $s = str_replace('@Â°@i','&deg;',$s);	// grad 
		 $s = str_replace('@Ã©@i','&eacute;',$s);	// e akzent degue
		 $s = str_replace('@Ã©@i','&eacute;',$s);	// e akzent degue
		 $s = str_replace('@Ãš@i','&egrave;',$s);	// e akzent grave
		 $s = str_replace('@Ã¨@i','&egrave;',$s);	// e akzent grave 
		 $s = str_replace('@é@i','&egrave;',$s);	// e akzent degue 
		 $s = str_replace('@â€@i','&quot;',$s);	// anführungszeichen 
		// $s = preg_replace('@â@i','&quot;',$s);	// anführungszeichen 
		$s = str_replace('@â@i','&quot;',$s);	// anführungszeichen 
				 	
		// Zoll
		 $s = str_replace("\'\'", "&Prime;", $s);		

		// EXPORT PRODUKT FEHLER Magento -> ' wird von Magento zu \', jedoch nicht immer
		
		$s = str_replace("\'", "RCMRCM", $s);
			// FUER MSSQL NICHT \' SONDERN ''
		$s = str_replace("'", "''", $s);		
		$s = str_replace("RCMRCM", "''", $s);	
				 	
		//$s = str_replace("a�", "Euro", $s);	
		//$s = str_replace("a�,", "Euro", $s);	
		//$s=utf8_decode($s);
		// $s = str_replace("é", "e", $s);		 
 		// $d1 = array("&#196;","&#214;","&#220;","&#228;","&#246;","&#252;","&#223;");
		// $d2 = array("�", "�", "�", "�" , "�", "�", "�");
  
		// $s = str_replace($d1, $d2, $s);
						
		// &nbsp;
		 $s = str_replace("&nbsp;", " ", $s);	
		 $s = str_replace("& ", "+ ", $s);	
		 				 	
		# pruefen auf utf8 konformitaet und ggfls konvertieren
		is_utf8($s) ? $s : utf8_encode($s);
						 		
		$s = nl2br($s); // \n -> br	Zeilenumbruch 
		
		// nicht UTF8 Buchstaben zu #
		// $s=prove_utf8($str);
		
		 // return the string
		 return $s;
	}// end function 
	
	function short_text($s){		 		 
		// $s=	 htmlspecialchars($s);
		$s = str_replace("é", "e", $s);		 
		$s = str_replace("�", "a", $s);		 
		$s = str_replace("�", ",", $s);		 
		 // return the string
		 return $s;
	}// end function    

	function getStatus()
	{		
		// Return Status as XML
	
	  echo '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
	       "<STATUS>\n" .
	       "  <STATUS_DATA>\n" .
	       "    <ACTION>$action</ACTION>\n" .
	       "    <CODE>STATUS OK</CODE>\n" .
	       "    <SCRIPT_TYPE>dmconnector</SCRIPT_TYPE>\n" .	       
	       "    <SCRIPT_DATE>$version_datum</SCRIPT_DATE>\n" .
	       "    <SCRIPT_DEFAULTCHARSET>" . htmlspecialchars(ini_get('default_charset')) . "</SCRIPT_DEFAULTCHARSET>\n" .
	       "  </STATUS_DATA>\n" .
	       "</STATUS>\n\n";
	}
	
	// Puefen der Anzahl der eingegangenen Bestellungen
	
	function checkOrders($sessionId, $client)
	{	
		// global $debugger, $dateihandle, $action,  $session, $client;
		global $debugger, $dateihandle;
		$bestellanzahl=0;
		if (isset($_POST['orderStatus'])) {
			$orderStatus = $_POST['orderStatus'];
		} else if (isset($_GET['orderStatus'])) { 
			$orderStatus = $_GET['orderStatus'];
		} else {
			$orderStatus = ORDER_STATUS;
		}
		fwrite($dateihandle, "*** checkOrders *** ");
		// Standard ist pending
		if ($orderStatus=='') $orderStatus = 'pending';
		// Aus Definitions - > alternativ aus Datei
	//	if (defined('GET_ORDERS_FROM' )){
	//		$last_order=GET_ORDERS_FROM;
	//	} else {
			// Letzte Abgerufene Bestellung ermitteln
			$dateihandleOrderID = fopen("./order_id.txt","r");
			$last_order = fread($dateihandleOrderID, 20);
			fclose($dateihandleOrderID); 
	//	}
		
		if ($last_order=='') $last_order='2013-10-01 00:00:00';
		
		for ($statusnummer=1;$statusnummer<10;$statusnummer++) {
			if ($statusnummer==1) 
				$orderstatusnummer='';
			else $orderstatusnummer=$statusnummer;
			if (defined('ORDER_STATUS' . $orderstatusnummer))
				if (constant('ORDER_STATUS' . $orderstatusnummer)!=''){  	
					$orderStatus = constant('ORDER_STATUS' . $orderstatusnummer);
					if (DEBUGGER>=1)
					{
							fwrite($dateihandle, " mit Status= ".constant('ORDER_STATUS' . $orderstatusnummer)."/".$orderStatus." seit $last_order mit session $sessionId ... ");	
							if (EXPORT_INVOICES) fwrite($dateihandle, "checkOrders fuer Rechnungen\n");
					}
					
			
					// decrepated API
					/*$order_list=$client->call($sessionId, 'sales_order.list', array(array('created_at'=>array('from'=>$last_order),
																						'status'=> $orderStatus)));
					$anzahl=count($order_list);
					if (DEBUGGER>=1) fwrite($dateihandle, "Anzahl der Bestellungen: ".$anzahl."\n\n");
					*/
					
					// Auf DB Basis
					$where = "status='".$orderStatus."' AND created_at > '".$last_order."'";
					$anzahl = dmc_sql_select_value("count(*)", 'sales_flat_order', $where);
			
					
					// Wenn  Rechnungen zu exportieren sind, an Stelle Bestellungen nur Anzahl von Rechnungen zu den Bestellungen prüfen
					$order_infos = array();
					if (EXPORT_INVOICES) {
						for ($i=0;$i<count($order_list);$i++){
							$order_infos = $client->call($sessionId, 'sales_order.info', $order_list[$i][increment_id]);
							if (SHOP_VERSION>1.3) {
								// Zugehörige NOCH NICHT ABGRERUFENE Rechnungsnummern ermitteln
								$invoice_list= $client->call($sessionId, 
														'sales_order_invoice.list', 
														array(
																array('order_id'=>$order_infos[order_id], 
																		'cybersource_token'=>array('eq'=>'')
																		)
																)
															);
								$bestellanzahl=count($invoice_list);
							} else { // (SHOP_VERSION<1.4
									if (DEBUGGER>=1) fwrite($dateihandle, "130 Rechnungen zu Bestellung: ".$order_infos[order_id]."\n");
								// Zugehörige NOCH NICHT ABGRERUFENE Rechnungsnummern ermitteln
								$invoice_list= $client->call($sessionId, 
														'sales_order_invoice.list', 
														array(
																array('order_id'=>$order_infos[order_id])
																)
															);
								for($x = 0; $x < count($invoice_list); $x++) {
									$re_nr=$invoice_list[$x]['increment_id'];
									$where="invoice_id ='".$re_nr."' AND status <>''";
									// Rechnung bereits abgerufen -> Bestellung nicht mit abrufen
									if (dmc_get_id('id','dmc_invoices',$where)<>'') {
											if (DEBUGGER>=99) fwrite($dateihandle,"\n194  - Rechnungsnummer wurde bereits abgerufen= $re_nr\n");
									} else {
											// Anzahl der zugehoerigen Rechnungen
											$bestellanzahl++;
									}
								 }
							}
							// Anzahl der zugehoerigen Rechnungen
							if (DEBUGGER>=1) fwrite($dateihandle, "Anzahl der Rechnungen zu der Bestellung: ".$bestellanzahl."\n");
						} // end for
					} else {	// NUR AUF BESTELLUNGEN PRUEFEN
						$bestellanzahl = $bestellanzahl + $anzahl;
					} // end if if (EXPORT_INVOICES)
				} // end if (constant('ORDER_STATUS' . $orderstatusnummer)!=''){
		} // end for Anzahl Orderstatus
		if (DEBUGGER>=1) fwrite($dateihandle, "Anzahl der Bestellungen: *".$bestellanzahl."*\n");
		// Return No of orders
		return $bestellanzahl;
	} // end checkOrders
	
	function dmc_set_OrderStatus($StoreView='default', $client, $sessionId) {
		
		if (DEBUGGER>=1) {
			$dateiname=LOG_FILE;	
			$dateihandle = fopen($dateiname,"a");			
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_OrderStatus Session=".$sessionId." with order:".$_POST['Order_ID']." to ".$status_id."\n");
		}
	
		// Post ermitteln		  
		  $order_id = (integer)($_POST['Order_ID']);	
		  $status_id = $_POST['Status_ID'];
		  
		// status from ERP
		if ($status_id=="written") $status_id = NEW_ORDER_STATUS_ERP;
		if ($status_id=="error") $status_id = NEW_ORDER_STATUS_FAILED;
		
		 if (EXPORT_INVOICES && UPDATE_ORDER_STATUS_ERP) {
			// Status Abgerufen in db scheiben
			if (SHOP_VERSION>1.3) {
				set_cybersource_token($status_id , $order_id);
			} else { // (SHOP_VERSION<1.4
				set_dmc_invoice($status_id , $order_id);
			}
		} else if (UPDATE_ORDER_STATUS_ERP)
			try {		  
						// Status eintragen
					   // if (UPDATE_ORDER_STATUS) {
							// array (orderIncrementId - order increment id, status - order status,  comment - order comment (optional),  notify - notification flag (optional)
							$client->call($sessionId, 'sales_order.addComment', array($order_id,  $status_id,  'Bestellstatus geaendert',  NOTIFY_CUSTOMER));
							if (DEBUGGER>=1) fwrite($dateihandle,"Order Status ".$order_id." updated to".$status_id."\n");	
						// } 
			} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle,'Set OrderStatus failed for: '.$order_id."\n".$e);		    
			}
		
	} // end function dmc_set_OrderStatus
	
	// Debug 
	function showDebug()
	{
	  global $debugger, $dateihandle, $action;

	  echo "<DEBUG>\n";

	  echo "  <GetAction>$_GET[action]</GetAction>\n";
	  echo "  <PostAction>$_POST[action]</PostAction>\n";

	  echo "  <GetDaten>\n";
	  foreach ($_GET as $Key => $Value)
	  {
	    echo "    <$Key>$Value</$Key>\n";
	  }
	  echo "  </GetDaten>\n";

	  echo "  <PostDaten>\n";
	  foreach ($_POST as $Key => $Value)
	  {
	    echo "    <$Key>$Value</$Key>\n";
	  }
	  echo "  </PostDaten>\n";
	  echo "</DEBUG>\n";
	} // showDebug
	
	/**
	 *
	 * @filter string to utf8
	 * @param string $str
	 * @return string rueckgabe
	 *
	 */
	function umlaute_order_export($str){
		global $dateihandle;
		//if (DEBUGGER>=1) 
		//fwrite($dateihandle,'umlaute_order_export '.$str."\n");		
		
		// Kaufmännisches & entfernen		
		//$str = str_replace("&", "+", $str);		  
		
		$strlen = strlen($str);
		for($i=0; $i<$strlen; $i++){
			$ord = ord($str[$i]);
			if($ord < 0x80) {
				$rueckgabe .= $str[$i];
			//	fwrite($dateihandle,'340 '.$rueckgabe."\n");		    
				continue; // 0bbbbbbb
			}
			elseif(($ord&0xE0)===0xC0 && $ord>0xC1) $n = 1; // 110bbbbb (exkl C0-C1)
			elseif(($ord&0xF0)===0xE0) $n = 2; // 1110bbbb
			elseif(($ord&0xF8)===0xF0 && $ord<0xF5) $n = 3; // 11110bbb (exkl F5-FF)
			else {
				//if (strpos ( $str, 'nde' )!==false)  fwrite($dateihandle,'* 347 umlaute_order_export  *\n');	
				
			//	fwrite($dateihandle,'348 str='.$str."\n");		    
				// ungültiges UTF-8-Zeichen
				// Versuch Gültigkeit durch en/dekodierung zu bekommen
				if (is_utf8("A".utf8_decode($str[$i])) && utf8_decode($str[$i])!='?') $rueckgabe .= utf8_decode($str[$i]);
				else if (is_utf8("B".utf8_encode($str[$i]))) $rueckgabe .= utf8_encode($str[$i]);
				else $rueckgabe .= "#";
				//fwrite($dateihandle,'356 rueckgabe='.$rueckgabe."\n");		    
			}
			//fwrite($dateihandle,'357 mit n= '.$n."\n");
			// ACHTUNG:: Nur 1 BYTE ist zu bearbeiten, daher n=1
			$n=1;
			for($c=0; $c<$n; $c++) // $n Folgebytes? // 10bbbbbb
				if(++$i===$strlen || (ord($str[$i])&0xC0)!==0x80) {
					// ungültiges UTF-8-Zeichen
					// Versuch Gültigkeit durch en/dekodierung zu bekommen
					if (is_utf8("A".utf8_decode($str[$i])) && utf8_decode($str[$i])!='?') $rueckgabe .= utf8_decode($str[$i]);
					else if (is_utf8("B".utf8_encode($str[$i]))) $rueckgabe .= utf8_encode($str[$i]);
					else $rueckgabe .= "#"; 
					//	fwrite($dateihandle,'365 rueckgabe='.$rueckgabe."\n");		    
				} else {
					// $rueckgabe .= $str[$i]; BUG PHP bei Umlauten -> Buchstabe nicht ermittelbar.
					// ACHTUNG: Zeichen  einfach weglassen - rcm
					// $rueckgabe .= substr($str,$i-1,$i);
					$ursprung=$str;
					//	fwrite($dateihandle,'370 rueckgabe='.$rueckgabe."\n");		    
					//	if (strpos ( $str, 'nde' )!==false) fwrite($dateihandle,'408= '.$rueckgabe.' Ursprung ist ='.$str.', An 1= '.$ursprung[1].', Buchstabe an Stelle '.$i.' ist '.substr($str,$i-1,$i).', davor: '.$str[$i-1].'*\n');
				}
		}
	  	
		$rueckgabe=$str;
	  	  return "<![CDATA[".$rueckgabe."]]>"; 

	}
	
	/**
	 *
	 * @convert RTF text to HTNL
	 * @param string $s
	 * @return $s
	 *
	 */
	// rtf in html 
	function convert_rtf_2_html ($s) {
	
			global $dateihandle;
			
			// include rtf functions
			fwrite($dateihandle, "rtf alt= $s \n");
			require_once('functions/dmc_rtfclass.php');
			$rtf=$s;
			$r = new rtf( stripslashes( $rtf));
			$r->output( "html");
			$r->parse();
			if( count( $r->err) == 0) { // no errors detected
				$s=$r->out;
				$s.='.'.$s;
			} else { // Fehlerabfangroutine
				// RTF entfernen
				$s = str_replace("\\\\", '||', $s);
				$s = str_replace('\\\\', '||', $s);
				$s = str_replace("||||", '||', $s); 
								
				$s = str_replace("{||f1||fnil||fcharset0 Verdana;||viewkind4||uc1d||f0||fs20", '', $s);
				$s = str_replace("{||rtf1||ansi||ansicpg1252||deff0||deflang2055{||fonttbl{||f0||froman||fcharset0 Times New Roman;", '', $s);
				$s = str_replace("{||rtf1||ansi||ansicpg1252||deff0||deflang2055{||fonttbl{||f0||fnil||fcharset0 Microsoft Sans Serif;}}", '', $s);
				$s = str_replace('{||rtf1||ansi||ansicpg1252||deff0||deflang2055{||fonttbl{||f0||fnil||fcharset0 Microsoft Sans Serif;}}', '', $s);
				$s = str_replace("{||rtf1||ansi||ansicpg1252||deff0||deflang2055{||fonttbl{||f0||fnil||fcharset0 Microsoft Sans Serif;", '', $s);
					$s = str_replace("||viewkind4||uc1||pard||f0||fs17||\\'b0", '', $s);  
				$s = str_replace("||\\'b0", '<br>', $s);
				$s = str_replace("||\\'e4", '&auml;', $s);
				$s = str_replace("||\'f6", '&ouml;', $s);
				
				$s = str_replace("||\\'fc", '&uuml;', $s);
				$s = str_replace("||par", '', $s);
				$s = str_replace("}", '', $s);
				if (strpos ( $s, 'Abbildung kann vom Original' )!==false) {
					$s='Abbildung kann vom Original abweichen';
				} 
				$s = "RTF Fehler"; 
			} // end 
			fwrite($dateihandle, "rtf neu= $s \n");
			
			return $s;
	} // end function convert_rtf_2_html
	
			
	// die uebergabenen Daten loggen
	function print_post($dateihandle) {
				
				global $debugger, $dateihandle, $action;
				
				  $ergebnis = "  <GetDaten>\n";
				  foreach ($_GET as $Key => $Value)
				  {
				    //if ($Key!='password' && $Key!='user' ) 
					$ergebnis .= "    <$Key>".substr($Value,0,40)."</$Key>\n";
				  }
				  $ergebnis .= "  </GetDaten>\n";

				  $ergebnis .= "  <PostDaten>\n"; 
				  foreach ($_POST as $Key => $Value)
				  {
				    //if ($Key!='password' && $Key!='user') 
					$ergebnis .= "    <$Key>".substr($Value,0,40)."</$Key>\n";
				  }
				  $ergebnis .= "  <PostDaten>\n";
				  fwrite($dateihandle, "\nUebergebene Daten:\n".$ergebnis."\n ********************************** \n");
	} // end print post
	
			// die uebergabenen Daten loggen
	function print_array($logarray) {
				
				global $debugger, $dateihandle, $action;
				
				  $ergebnis = "  <ARRAY>\n";
				  foreach ($logarray as $Key => $Value)
				  {
				    $ergebnis .= "    <$Key>$Value</$Key>\n";
				  }
				  $ergebnis .= "  </ARRAY>\n";

				  fwrite($dateihandle, "\nARRAY:\n".$ergebnis."\n ********************************** \n");
	} // end print post
	
	
	// resize_images 
	function resize_image($PicPathIn, $PicPathOut, $bild, $width) {
		
		// Bilddaten ermitteln
		$size= GetImageSize("$PicPathIn"."$bild");
		$breite=$size[0];
		$hoehe=$size[1];
		$neueBreite=$width;
		$neueHoehe= intval($hoehe*$neueBreite/$breite);

		if($size[2]==1) {
		// GIF
		$altesBild= imagecreatefromgif("$PicPathIn"."$bild");
		$neuesBild= imagecreate($neueBreite,$neueHoehe);
		 imageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe);
		 imageGIF($neuesBild,"$PicPathOut"."$bild");
		}

		if($size[2]==2) {
		// JPG
		$altesBild= ImageCreateFromJPEG("$PicPathIn"."$bild");
		$neuesBild= imagecreatetruecolor($neueBreite,$neueHoehe);
		 imageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe);
		 ImageJPEG($neuesBild,"$PicPathOut"."$bild");
		}

		if($size[2]==3) {
		// PNG
		$altesBild= ImageCreateFromPNG("$PicPathIn"."$bild");
		$neuesBild= imagecreatetruecolor($neueBreite,$neueHoehe);
		 imageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe);
		 ImagePNG($neuesBild,"$PicPathOut"."$bild");
		}
	} // end function resize imgae

	// L�schen von Dateien aus einem Verzeichnis ggfls mit spezieller Endung, ggfls nur mit einer Zeit in Sekunden, zB 3600, aelter als 3600 Sekunden
	function delFiles($verzeichnis,$endung,$sekundenalt)
	{
		$time = gettimeofday();
        
		if (substr($verzeichnis,-1)!="/") $verzeichnis = $verzeichnis."/";
		// Nur wenn Variable deklarieren
		if (is_dir($verzeichnis)) {
			// Variable deklarieren und Verzeichnis �ffnen
			$verz = opendir($verzeichnis);
			// Verzeichnisinhalt auslesen
			while ($file = readdir ($verz)) 
			{
			  // "." und ".." bei der Ausgabe unterdr�cken
			  if($file != "." && $file != "..") 
			  {
				if ($sekundenalt!="") {
					if ( $time[sec] - date(filemtime($verzeichnis.$file)) >= $sekundenalt )  
					{ 
						//echo " Datei $verzeichnis".$file;
						if (substr($file, -strlen($endung)) == $endung || $endung=="") {
						//	echo " loeschen";
							unlink($verzeichnis.$file); 
						} else {
							//echo " nicht loeschen, da endung ".substr($file, -strlen($endung))." <> ".$endung;						
						}
					} else {
						// echo " Datei ".$file." nicht �lter als ".$sekundenalt."s gefunden , da aktuelle Zeit (".$time[sec].") - Dateizeit (". date(filemtime($verzeichnis.$file)).") = ".($time[sec] - date(filemtime($verzeichnis.$file)));
					}
					
				} else {
					// File l�schen, wenn Endung vohanden
					if (substr($filename, strlen($endung)) == $endung || $endung=="") unlink($verzeichnis.$file);
				}
			  }
			}
			// Verzeichnis schlie�en
			closedir($verz); 
		}
	}  // end function  delFiles($verzeichnis,$endung,$sekundenalt) 
	
?>