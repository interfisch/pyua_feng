<?php
  class Elk_Export_Model_Observer extends Mage_Core_Model_Abstract {

		public function exportCustomer($observer) {
			$orderId = reset($observer->getOrderIds());
			$order = Mage::getModel('sales/order')->load($orderId);

			$billing_address = $order->getBillingAddress();
			$shipping_address = $order->getShippingAddress();
			$payment_method = $order->getPayment();

			if($order['customer_id']) {
				$customer = Mage::getModel('customer/customer')->load($order['customer_id']);
				$this->checkCustomerId($customer);
				$customerNr = $customer->getCustomerId();

				$att_cust = array(
					'fname'			=>	'WEBSHOP_K',
					'nr'			=>	$customerNr,
					'kurzname'		=>	'',
					'Firmenname'	=>	$billing_address['company'],
					'Anrede'		=>	$order['customer_prefix'],
					'Name'			=>	$order['customer_firstname'] .' '. $order['customer_lastname'],
					'Strasse'		=>	$billing_address['street'],
					'plz'			=>	$billing_address['postcode'],
					'Ort'			=>	$billing_address['city'],
					'Telefon'		=>	$billing_address['telephone'],
					'mwst'			=>	'',
					'Fax'			=>	$billing_address['fax'],
					'email'			=>	$order['customer_email'],
					'Land'			=>	$billing_address['country_id'],
					'Zahlungsart'	=>	$payment_method['method'], //'ONLINE',
					'Bank'			=>	'',
					'Blz'			=>	'',
					'Konto'			=>	'',
					'username'		=>	$order['customer_username'],
					'password'		=>	$order['customer_password'],
					'customers_hash'		=>	'',
					'zahlungsart_id'		=>	$this->getPaymentMethod($payment_method['method'], $payment_method['cc_type'])
				);

				$this->writeCsv(DOCROOT . "export/CUST_{$customerNr}.csv", array($att_cust));
			}
		}

		public function checkCustomerId(Mage_Core_Model_Abstract &$customer) {
			$customerNr = $customer->getCustomerId();

			$customerCollection = $customer->getCollection()
				->addFieldToFilter('customer_id', $customerNr);

			if ($customerCollection->count() > 1) {
				$template = Mage::getStoreConfig('customer/customer_id/id_template');

				$nextId = (int)Mage::getStoreConfig('customer/customer_id/next_increment');

				$nextIdFormatted = sprintf($template, $nextId);

				while (Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('customer_id', $nextIdFormatted)->count()) {
					$nextId++;
					$nextIdFormatted = sprintf($template, $nextId);
				}

				$customer->setCustomerId($nextIdFormatted);
				$customer->save();

				$nextId++;
				Mage::getConfig()->saveConfig('customer/customer_id/next_increment', $nextId);
				Mage::getConfig()->reinit();
				Mage::app()->reinitStores();
			}
		}

		public function exportOrder($observer) {

			$currentStoreId = Mage::app()->getStore()->getId();
			Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

			$orderId = reset($observer->getOrderIds());
			$order = Mage::getModel('sales/order')->load($orderId);

			$payment_method = $order->getPayment();
			$shipping_method = $order->getShipping();

			$billing_address = $order->getBillingAddress();
			$shipping_address = $order->getShippingAddress();

			$createdate = date('Ymd', strtotime(Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(),'medium',false)));

			$lines = array();

			if($order['customer_id']) {
				$customer = Mage::getModel('customer/customer')->load($order['customer_id']);
				$this->checkCustomerId($customer);
				$customerNr = $customer->getCustomerId();
			} else {
				$customerNr = 'guest';
			}

			$versandart_ids = array(
				'25' => array( // DHL Online - National
					'de', // Deutschland
				),
				'42' => array( // DHL Online - International
					'be', // Belgien
					'bg', // Bulgarien
					'dk', // Dänemarkt
					'ee', // Estland
					'fi', // Finnland
					'fr', // Frankreich
					'gr', // Griechenland
					'ie', // Irland
					'it', // Italien
					'lv', // Lettland
					'lt', // Litauen
					'lu', // Luxemburg
					'nl', // Niederlande
					'pl', // Polen
					'pt', // Portugal
					'ro', // Rumänien
					'se', // Schweden
					'sk', // Slowakei
					'si', // Slowenien
					'es', // Spanien
					'cz', // Tschechische Republik
					'hu', // Ungarn
					'gb', // Vereinigtes Königreich
					'no', // Norwegen
				),
				'44' => array( // DHL Online - AT
					'at', // Österreich
				)
			);

			// Alle Länder die nicht in der Liste sind kriegen die Versandart außerhalb der EU
			$Versandart_ID = 43; // DHL Online - ex. EU
			foreach ($versandart_ids as $id => $countries) {
				if (in_array(strtolower($shipping_address['country_id']), $countries)) {
					$Versandart_ID = $id;
					break;
				}
			}

			// Ups Express wird mit 49 exportiert
			if($order->getShippingMethod() === 'flatrate_flatrate') {
				$Versandart_ID = 49;
			}

			$packstation = $order->getPackstationObject();

			$order_header = array(
				'art'							=>	'H',
				'iln_absender'					=>	'WEBSHOP_K',
				'iln_empfaenger' 				=> 	'',
				'Nachrichtentyp' 				=> 	'ORDERS',
				'Version' 						=>	'',
				'Freigabenummer' 				=>	'',
				'Auftragsart' 					=> 	'',
				'Zusatztext' 					=> 	'',
				'Auftragsnummer'				=> 	$order['increment_id'],	//order_id,
				'Auftragsdatum' 				=>	$createdate,	//order_date
				'gewuenschter_liefertermin' 	=>	'',
				'lieferdatum_von' 				=> 	'',	//true????
				'lieferdatum_bis' 				=> 	'',	//true???
				'auftragsanweisung' 			=> 	'',
				'rahmenauftrags_nummer' 		=> 	'',
				'iln_by' 						=> 	$customerNr,	//kunden nummer
				'einkaufs_abteilung' 			=> 	$order->getPayment()->getLastTransId() ? $order->getPayment()->getLastTransId() : '',
				'iln_su' 						=> 	'',
				'ansprech_partner' 				=> 	'',
				'zus_partneridentifikation' 	=> 	'',
				'iln_dp' 						=> 	'',
				'iln_iv' 						=> 	'',
				'iln_uc' 						=> 	'',
				'waehrung' 						=> 	$order['order_currency_code'],  // order
				'positions_nummer' 				=> 	'',	//
				'ean' 							=> 	'',
				'artikel_nr' 					=> 	'',
				'kaeufer_artikel_nr' 			=> 	'',
				'menge' 						=> 	'',
				'einheit'						=> 	'',
				'netto_preis' 					=> 	'',
				'brutto_preis'					=> 	'',
				'verpackungsart' 				=> 	'23',//Versandart
				'fehler' 						=> 	'',
				'nachrichten_name' 				=> 	'',
				'vertreter1_nr' 				=> 	'',
				'farb_nr' 						=> 	'',
				'groesse' 						=> 	'',
				'vertreter2_nr' 				=> 	'',
				'vertreter3_nr' 				=> 	'',
				'lager_nr' 						=> 	'',
				'position_zusatztext' 			=> 	'',
				'groessen_pos' 					=> 	'',
				'bezahlart' 					=> 	$this->getPaymentMethod($payment_method['method'], $payment_method['cc_type']),
				'saison_nr' 					=> 	'',
				'auftragsbezeichnung' 			=>	"OS via elkline_de",
				'position_bemerkung' 			=> 	'',
				'Frachtkosten' 					=> 	$this->numC($order['shipping_amount']), //'4,16',
				'Versandart_ID' 				=> 		$Versandart_ID, //((strtolower($shipping_address['country_id']) == 'de') ? '22' : '37'),
				'pg_kunden_artikel_nummer' 		=> 	'',
				'valutadatum' 					=> 	'',
				'bestellung_zuordnung' 			=> 	'',
				'53' 							=> 	'',
				'bearbeitungskosten' 			=> 	'',
				'verpackungskosten'				=> 	'',
				'vk_preis' 						=> 	''//$this->numC($item['original_price'])
				// 'packstation'				=> isset($packstation) ? $packstation->getStation() : '',
				// 'packstation_nummer'			=> isset($packstation) ? $packstation->getNumber() : ''
			);

			if ($shipping_address->getData('ship_to_packstation') == Uandi_Packstation_Model_Config::SHIP_TO_PACKSTATION) {
				$shipping_address['street'] = 'Packstation ' . $shipping_address['street'];
			}


			$lines['HEADER'] = $order_header;

			// Rechnungsanschrift falls Gast
			if(!$order['customer_id'])  {
				$anr_guest = array(
					'fname'			=>	'ANR',
					'name_1'		=>	$order['customer_prefix'],
					'name_2'		=>	$order['customer_firstname'],
					'name_3'		=>	$order['customer_lastname'],
					'Strasse'		=>	$billing_address['street'],
					'Land'			=>	'',
					'Land ISO' 		=> 	$billing_address['country_id'],
					'plz'			=>	$billing_address['postcode'],
					'Ort'			=>	$billing_address['city'],
					'email'			=>	$order['customer_email'],
					'Telefon'		=>	$billing_address['telephone'],
					'Fax'			=>	$billing_address['fax']
				);

				$lines['GUEST ADDRESS'] = $anr_guest;
			}

			// Abweichende Liferanschrift
			// if(
			// 	$shipping_address['prefix'] 		!= $billing_address['prefix'] OR
			// 	$shipping_address['firstname'] 	!= $billing_address['firstname'] OR
			// 	$shipping_address['lastname'] 	!= $billing_address['lastname'] OR
			// 	$shipping_address['street'] 		!= $billing_address['street'] OR
			// 	$shipping_address['country_id']	!= $billing_address['country_id'] OR
			// 	$shipping_address['postcode'] 	!= $billing_address['postcode'] OR
			// 	$shipping_address['city'] 			!= $billing_address['city'] OR
			// 	$shipping_address['telephone'] 	!= $billing_address['telephone'] OR
			// 	$shipping_address['fax'] 				!= $billing_address['fax'] OR
			// 	$shipping_address['company']			!= $billing_address['company']
			// ) {

			// 22.11.2013 MF - Wir senden ab jetzt IMMER die Liefer und Rechnungsadresse mit


			$billingCompany = ($billing_address['company'] AND strlen($billing_address['company'])) ? $billing_address['company'] : false;

			$billingAnl = array(
				'fname'			=>	'ANR',
				'name_1'		=>	$billing_address['prefix'] . ' ' . $billing_address['firstname'] . ' ' . $billing_address['lastname'],
				'name_2'		=>	$billingCompany ? $billingCompany : '',
				'name_3'		=>	'',
				'Strasse'		=>	$billing_address['street'],
				'Land'			=>	'',
				'Land ISO'		=>	$billing_address['country_id'],
				'plz'			=>	$billing_address['postcode'],
				'Ort'			=>	$billing_address['city'],
				'email'			=>	$order['customer_email'],
				'Telefon'		=>	$billing_address['telephone'],
				'Fax'			=>	$billing_address['fax']
			);

			$lines['BILLING ADDRESS'] = $billingAnl;

			$shippingCompany = ($shipping_address['company'] AND strlen($shipping_address['company'])) ? $shipping_address['company'] : false;

			$shippingAnl = array(
				'fname'			=>	'ANL',
				'name_1'		=>	$shipping_address['prefix'] . ' ' . $shipping_address['firstname'] . ' ' . $shipping_address['lastname'],
				'name_2'		=>	$shippingCompany ? $shippingCompany : '',
				'name_3'		=>	'',
				'Strasse'		=>	$shipping_address['street'],
				'Land'			=>	'',
				'Land ISO'		=>	$shipping_address['country_id'],
				'plz'			=>	$shipping_address['postcode'],
				'Ort'			=>	$shipping_address['city'],
				'email'			=>	$order['customer_email'],
				'Telefon'		=>	$shipping_address['telephone'],
				'Fax'			=>	$shipping_address['fax']
			);

			$lines['SHIPPING ADDRESS'] = $shippingAnl;
			// }

			$items = $order->getAllVisibleItems();

			$i = 1;

			foreach($items as $item){
				$attributes_info = $item['product_type'] == 'configurable' ? $item->getProductOptionByCode('attributes_info') : $attributes_info = $item->getAttributes_info();
				$sku = $item['product_type'] == 'configurable' ? $item->getProductOptionByCode('simple_sku') : $item->sku;
				$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

				$order_item = array(
					'art'							=>	'P',
					'iln_absender'					=>	'',
					'iln_empfaenger' 				=> 	'',
					'Nachrichtentyp' 				=> 	'ORDERS',
					'Version' 						=>	'',
					'Freigabenummer' 				=>	'',
					'Auftragsart' 					=> 	'',
					'Zusatztext' 					=> 	'',
					'Auftragsnummer'				=> 	'',
					'Auftragsdatum' 				=>	'',
					'gewuenschter_liefertermin' 	=>	'',
					'lieferdatum_von' 				=> 	'',
					'lieferdatum_bis' 				=> 	'',
					'auftragsanweisung' 			=> 	'',
					'rahmenauftrags_nummer' 		=> 	'',
					'iln_by' 						=> 	'',
					'einkaufs_abteilung' 			=> 	'',
					'iln_su' 						=> 	'',
					'ansprech_partner' 				=> 	'',
					'zus_partneridentifikation' 	=> 	'',
					'iln_dp' 						=> 	'',
					'iln_iv' 						=> 	'',
					'iln_uc' 						=> 	'',
					'waehrung' 						=> 	$order['order_currency_code'],
					'positions_nummer' 				=> 	$i,
					'ean' 							=> 	$sku,
					'artikel_nr' 					=> 	'',
					'kaeufer_artikel_nr' 			=> 	'',
					'menge' 						=> 	round($item['qty_ordered']),
					'einheit'						=> 	'',
					'netto_preis' 					=> 	$this->numC($item->getPrice()),
					'brutto_preis'					=> 	$this->numC($item['original_price']),
					'verpackungsart' 				=> 	'',
					'fehler' 						=> 	'',
					'nachrichten_name' 				=> 	'',
					'vertreter1_nr' 				=> 	'',
					'farb_nr' 						=> 	'',
					'groesse' 						=> 	'',
					'vertreter2_nr' 				=> 	'',
					'vertreter3_nr' 				=> 	'',
					'lager_nr' 						=> 	'110',
					'position_zusatztext' 			=> 	'',
					'groessen_pos' 					=> 	'',
					'bezahlart' 					=> 	'',
					'saison_nr' 					=> 	'',
					'auftragsbezeichnung' 			=> 	'',
					'position_bemerkung' 			=> 	'',
					'Frachtkosten' 					=> 	'',
					'Versandart_ID' 				=> 	'',
					'pg_kunden_artikel_nummer' 		=> 	'',
					'valutadatum' 					=> 	'',
					'bestellung_zuordnung' 			=> 	'',
					'53' 							=> 	'',
					'bearbeitungskosten' 			=> 	'',
					'verpackungskosten'				=> 	'',
					'vk_preis' 						=> 	$this->numC($item['original_price']),
                    '57'                            =>  '',
                    '58'                            =>  '',
                    '59'                            =>  '',
                    '60'                            =>  '',
                    '61'                            =>  '',
                    '62'                            =>  '',
                    'Rabatt_1'                      =>  ''
				);

                if($this->getDiscountPercentage($order) && $item->getDiscountPercent()){
                    $order_item['Rabatt_1'] = str_replace('.',',',round($item->getDiscountPercent(),2));
                }

				// Prüfen on Artikel im Blocklager liegen
				if(($blockQty = (int) $_product->getElkBlocklagerQty()) > 0) {
					$qty = round($item['qty_ordered']);

					// Es sind mehr Artikel bestellt als im Blocklager vorhanden sind
					if($qty > $blockQty) {

						// Alle Artikel aus dem Blocklager nehmen
						$order_item['lager_nr'] = '197';
						$order_item['menge'] = $blockQty;
						$_product->setElkBlocklagerQty('0');
						$lines['ORDER POSITION ' . $i++] = $order_item;

						// Restlichen Artikel aus dem Normalen Lager
						$order_item['lager_nr'] = '110';
						$order_item['menge'] = $qty - $blockQty;
						$order_item['positions_nummer'] = $i;
						$lines['ORDER POSITION ' . $i++] = $order_item;
					}
					// Es sind genügend Artikel im Blocklager
					else {
						$order_item['lager_nr'] = '197';
						$order_item['menge'] = $qty;
						$_product->setElkBlocklagerQty((string) $blockQty - $qty);
						$lines['ORDER POSITION ' . $i++] = $order_item;
					}
					$_product->dontSaveToPimcore = true;
					$_product->save();

				} else {
					$lines['ORDER POSITION ' . $i++] = $order_item;
				}
			}

			if ($order['giftcert_amount'] != 0){
				$att_discount = array(
					'art'							=>	'P',
					'iln_absender'					=>	'',
					'iln_empfaenger' 				=> 	'',
					'Nachrichtentyp' 				=> 	'ORDERS',
					'Version' 						=>	'',
					'Freigabenummer' 				=>	'',
					'Auftragsart' 					=> 	'',
					'Zusatztext' 					=> 	'',
					'Auftragsnummer'				=> 	'',
					'Auftragsdatum' 				=>	'',
					'gewuenschter_liefertermin' 	=>	'',
					'lieferdatum_von' 				=> 	'',
					'lieferdatum_bis' 				=> 	'',
					'auftragsanweisung' 			=> 	'',
					'rahmenauftrags_nummer' 		=> 	'',
					'iln_by' 						=> 	'',
					'einkaufs_abteilung' 			=> 	'',
					'iln_su' 						=> 	'',
					'ansprech_partner' 				=> 	'',
					'zus_partneridentifikation' 	=> 	'',
					'iln_dp' 						=> 	'',
					'iln_iv' 						=> 	'',
					'iln_uc' 						=> 	'',
					'waehrung' 						=> 	$order['order_currency_code'],
					'positions_nummer' 				=> 	$i,
					'ean' 							=> 	'4051533122970',
					'artikel_nr' 					=> 	'9090004',
					'kaeufer_artikel_nr' 			=> 	'',
					'menge' 						=> 	1,
					'einheit'						=> 	'',
           			'netto_preis' 					=> 	$this->numC(($order['giftcert_amount']/1.19) * -1),
           			'brutto_preis' 					=> 	$this->numC(($order['giftcert_amount']) * -1),
					'verpackungsart' 				=> 	'',
					'fehler' 						=> 	'',
					'nachrichten_name' 				=> 	'',
					'vertreter1_nr' 				=> 	'',
					'farb_nr' 						=> 	'',
					'groesse' 						=> 	'',
					'vertreter2_nr' 				=> 	'',
					'vertreter3_nr' 				=> 	'',
					'lager_nr' 						=> 	'007',
					'position_zusatztext' 			=> 	'',
					'groessen_pos' 					=> 	'',
					'bezahlart' 					=> 	'',
					'saison_nr' 					=> 	'',
					'auftragsbezeichnung' 			=> 	'',
					'position_bemerkung' 			=> 	'',
					'Frachtkosten' 					=> 	'',
					'Versandart_ID' 				=> 	'',
					'pg_kunden_artikel_nummer' 		=> 	'',
					'valutadatum' 					=> 	'',
					'bestellung_zuordnung' 			=> 	'',
					'53' 							=> 	'',
					'bearbeitungskosten' 			=> 	'',
					'verpackungskosten'				=> 	'',
					'vk_preis' 						=> 	$this->numC(($order['giftcert_amount']) * -1)
				);

				$lines['GIFT_CERT'] = $att_discount;

			}

			if ($order['discount_amount'] != 0 AND !$this->isPercentageDiscount($order)){
				$att_discount = array(
					'art'							=>	'P',
					'iln_absender'					=>	'',
					'iln_empfaenger' 				=> 	'',
					'Nachrichtentyp' 				=> 	'ORDERS',
					'Version' 						=>	'',
					'Freigabenummer' 				=>	'',
					'Auftragsart' 					=> 	'',
					'Zusatztext' 					=> 	'',
					'Auftragsnummer'				=> 	'',
					'Auftragsdatum' 				=>	'',
					'gewuenschter_liefertermin' 	=>	'',
					'lieferdatum_von' 				=> 	'',
					'lieferdatum_bis' 				=> 	'',
					'auftragsanweisung' 			=> 	'',
					'rahmenauftrags_nummer' 		=> 	'',
					'iln_by' 						=> 	'',
					'einkaufs_abteilung' 			=> 	'',
					'iln_su' 						=> 	'',
					'ansprech_partner' 				=> 	'',
					'zus_partneridentifikation' 	=> 	'',
					'iln_dp' 						=> 	'',
					'iln_iv' 						=> 	'',
					'iln_uc' 						=> 	'',
					'waehrung' 						=> 	$order['order_currency_code'],
					'positions_nummer' 				=> 	$i,
					'ean' 							=> 	'4051533122970',
					'artikel_nr' 					=> 	'9090004',
					'kaeufer_artikel_nr' 			=> 	'',
					'menge' 						=> 	1,
					'einheit'						=> 	'',
           			'netto_preis' 					=> 	$this->numC($order['discount_amount']/1.19),
           			'brutto_preis' 					=> 	$this->numC($order['discount_amount']),
					'verpackungsart' 				=> 	'',
					'fehler' 						=> 	'',
					'nachrichten_name' 				=> 	'',
					'vertreter1_nr' 				=> 	'',
					'farb_nr' 						=> 	'',
					'groesse' 						=> 	'',
					'vertreter2_nr' 				=> 	'',
					'vertreter3_nr' 				=> 	'',
					'lager_nr' 						=> 	'007',
					'position_zusatztext' 			=> 	'',
					'groessen_pos' 					=> 	'',
					'bezahlart' 					=> 	'',
					'saison_nr' 					=> 	'',
					'auftragsbezeichnung' 			=> 	'',
					'position_bemerkung' 			=> 	'',
					'Frachtkosten' 					=> 	'',
					'Versandart_ID' 				=> 	'',
					'pg_kunden_artikel_nummer' 		=> 	'',
					'valutadatum' 					=> 	'',
					'bestellung_zuordnung' 			=> 	'',
					'53' 							=> 	'',
					'bearbeitungskosten' 			=> 	'',
					'verpackungskosten'				=> 	'',
					'vk_preis' 						=> 	$this->numC($order['discount_amount'])
				);

				$lines['DISCOUNT'] = $att_discount;

			}

			$this->writeCsv(DOCROOT . "export/ORDER_{$order['increment_id']}.csv", $lines);

			Mage::app()->setCurrentStore($currentStoreId);
		}

		public function uploadFiles($event) {
			$files = array_merge(glob(DOCROOT . "export/CUST_*.csv"), glob(DOCROOT . "export/ORDER_*.csv"));
			if(count($files)) {
				$conn_id = ftp_connect('erp.elkline.net');
				$login_result = ftp_login($conn_id, 'bschultz', 'elkline$FTP');

				if (($conn_id) AND ($login_result)) {
        	foreach($files as $file) {
	        	$remote_file = 'webshop_elkline/import/' . basename($file);
						if(ftp_put($conn_id, $remote_file , $file , FTP_ASCII)) {
							$archiveFile = DOCROOT . "export/archiv/" . basename($file);
							if(file_exists($archiveFile)) {
								@unlink($archiveFile);
							}
							rename( $file, $archiveFile);
						}
					}
				}

				ftp_close($conn_id);
			}
		}

		private function writeCsv($file, array $data, $seperator = "\t", $quote = "") {
			$lines = array();
			foreach($data as $line) {
				$lines[] = $quote . implode($quote.$seperator.$quote, array_map(function($value) {
					return utf8_decode(print_r($value, true));
				}, (array) $line)) . $quote;
			}

			file_put_contents($file, implode("\n", $lines));
		}

		private function getPaymentMethod($method, $cctype) {

			switch($method) {
				case 'bankpayment':
					$id = '27';
					break;
				case 'payone_wlt':
					$id = '53';
					break;
				case 'payone_sb':
					if($cctype == 'PNT') {
						$id = '51'; // Sofortüberweisung
						break;
					} elseif($cctype == 'GPY') {
						$id = '52'; // Giropay
						break;
					}
				case 'payone_cc':
					$id = '50';
					break;
				case 'ugiftcert':
					$id = '54';
					break;
				case 'invoice':
					$id = '55';
					break;
				default: $id = '56'; //$method;
			}

			// 54 Gutschein wenn restbetrag 0€

			return $id;
		}

		private function numC ($num){
			return number_format($num, 2, ',', '');
		}

        public function getDiscountPercentage($order)
        {
            if ($order->getAppliedRuleIds()) {
                $rule = Mage::getModel('salesrule/rule')->load($order->getAppliedRuleIds());
                if ($rule['simple_action'] == 'by_percent') {
                    return str_replace('.', ',', round($rule['discount_amount'], 2));
                } else {
                    return '';
                }
            }
        }

        public function isPercentageDiscount($order){
            if($order->getAppliedRuleIds()){
                $rule = Mage::getModel('salesrule/rule')->load($order->getAppliedRuleIds());
                if ($rule['simple_action'] == 'by_percent') {return true; } else { return false;}
            }
        }

		public function updateCustomer($observer) {
			if($customer = $observer->getCustomer()) {
				$customerNr = $customer->getCustomerId();

				if(!$address = $customer->getPrimaryBillingAddress()) {
					$address = $customer->getAddressesCollection()->getFirstItem();
				}


				$att_cust = array(
					'fname'				=>	'WEBSHOP_K',
          			'nr'				=>	$customerNr,
          			'kurzname'			=>	'',
					'Firmenname'		=>	!$address ? '' : $address['company'],
					'Anrede'			=>	$customer['prefix'],
					'Name'				=>	$customer['firstname'] .' '. $customer['lastname'],
					'Strasse'			=>	!$address ? ' ' : $address['street'],
					'plz'				=>	!$address ? ' ' : $address['postcode'],
					'Ort'				=>	!$address ? ' ' : $address['city'],
					'Telefon'			=>	!$address ? '' : $address['telephone'],
					'mwst'				=>	'',
					'Fax'				=>	!$address ? '' : $address['fax'],
					'email'				=>	$customer['email'],
					'Land'				=>	!$address ? 'de' : $address['country_id'],
					'Zahlungsart'		=>	'',
					'Bank'				=>	'',
					'Blz'				=>	'',
					'Konto'				=>	'',
					'username'			=>	$customer['username'],
					'password'			=>	$customer['password'],
					'customers_hash'	=>	'',
					'zahlungsart_id'	=>	'56'
				);

				$this->writeCsv(DOCROOT . "export/CUST_{$customerNr}.csv", array($att_cust));
			}
		}
	}
?>
