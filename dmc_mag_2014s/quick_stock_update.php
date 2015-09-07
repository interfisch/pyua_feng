<?php
/* ----------------------------------------------------------------------
   $Id: quick_stock_update.php,v 1.2 2006/01/03 18:18:19 r23 Exp $

   DoubleM GmbH
   http://www.doublem-gmbh.de/

   Copyright (c) 2008 by the doublem gmbh
   ----------------------------------------------------------------------
   Based on:

   File: quick_stockupdate.php v1.1 by Tomorn Kaewtong / http://www.phpthailand.com
         MODIFIED quick_stockupdate.php v2.4 by Dominic Stein
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

  ?>
  <?php
  
define('VALID_DMC',true);		// zugriff zu includes
define('C8a6899ef',true);		// zugriff zu includes

include ('definitions.inc.php');
include ('dmc_db_functions.php');


	$user = isset($_POST['user']) ?  $_POST['user'] : $_GET['user'];
	$password = isset($_POST['password']) ?  $_POST['password'] : $_GET['password'];
	
if (DEBUGGER>=1)
			{ 
				
				$daten = "\n***********************************************************************\n";
				$daten .= "************************* dmconnector magento *************************\n";
				$daten .= "***********************************************************************\n";
				if (LOG_ROTATION=='size' && is_numeric(LOG_ROTATION_VALUE))
					if ((filesize(LOG_FILE)/1048576)>LOG_ROTATION_VALUE) 
						$dateihandle = fopen(LOG_FILE,"w"); // LOG File erstellen
					else
						$dateihandle = fopen(LOG_FILE,"a");
				else
						$dateihandle = fopen(LOG_FILE,"a");
								
				fwrite($dateihandle, $daten);	
			}
			
			if (DEBUGGER>=1) fwrite($dateihandle,"session = ".$session."\n");	
			    
	// debug modus
	//if (!isset($session)) {
	  // soap authentification
		$zugriff=true;
	    try {		 
			// Get Soap Connection
				if (DEBUGGER>=1) fwrite($dateihandle,"Get Soap Connection to ".SOAP_CLIENT);	
			    $client = new SoapClient(SOAP_CLIENT);
			    //  api authentification, ->  get session token   
				$session = $client->login($user, $password);	
				 if (DEBUGGER>=1) fwrite($dateihandle,"api authentification, ->  get session token ".$session);			
		} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle, "user authentification Access denied for ".$user."/".$password." Error=:\n ".$e." \n");
				// echo "Access denied";
				$session=0;	
				$zugriff=false;
		}
	//} // endif
	
	$link=dmc_db_connect();
		
	?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
			<title></title>
		</head>
		<body topmargin="0" leftmargin="0" bgcolor="#FFFFFF">

		
			<table border="0" width="100%" cellspacing="2" cellpadding="2">
				<tr>
					<td class="columnLeft2" width="200px" valign="top">
						<table border="0" width="200px" cellspacing="1" cellpadding="1" class="columnLeft">
						&nbsp;
						</table>
					</td>
					<td class="boxCenter" width="100%" valign="top">
						<table border="0" width="100%" cellspacing="0" cellpadding="0">
							<tr>
								<td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td colspan="2"><hr></td>
            </tr>
            <tr>
              <td class="pageHeading">Thomas' * Schnell Artikel &Uuml;berarbeitung *<br><hr></td>
              <td class="pageHeading" align="right"><hr></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td valign="top">
                <table border="0" width="100%" cellspacing="0" cellpadding="1">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><hr></td>
                  </tr>
                  <tr>
                    <td class="main">Bitte Kategorie ausw&auml;hlen, deren Produkte ge&auml;ndert werden sollen.</td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td>
<?php 

	// wenn KOMPLETTE preissenkung -. preiserhöhung
	if (isset($_POST['price_change_complete']) && $_POST['price_change_complete_value']<>"0") {		
		$price_change_complete_value=$_POST['price_change_complete_value'];		
		if ($price_change_complete_value{1}=="-") 
			// preissenkung			
			$price_change_complete_value=(100-$price_change_complete_value)/100;			
		else
			// preiserhöhung
			$price_change_complete_value=1+($price_change_complete_value/100);					
		// Freisänderung durchführen
			$key = "%";	// alle Produkte
			//echo "alle preise * ".$price_change_complete_value;
			 // update the quantity and price in stock
			  $update_sql_data = array(															
							'value' => "products_price*$price_change_complete_value"		// neuer preis - new price
							); // aktiviert
						$table="catalog_product_entity_decimal";
						$what="value=".$price_change_complete_value;
						$where="attribute_id=60 AND value_id=".$key;
						dmc_sql_update($table, $what, $where); 
				   
			  // update the price of customer group 1
			/* $update_sql_data = array(						
							'personal_offer' => "personal_offer*$price_change_complete_value"		// neuer preis - new price
							); // aktiviert							
				       xtc_db_perform('personal_offers_by_customers_status_1', $update_sql_data, 'update', "products_id like '" . $key . "'");	*/	
		
	} // end if komplette preisänderung

  if (isset($_POST['stock_update']) && !empty($_POST['stock_update'])) {

  // wenn preissenkung -. preiserhöhung
	if (isset($_POST['price_change']) && $_POST['price_change_value']<>"0") {		
		$price_change_value=$_POST['price_change_value'];		
		if ($price_change_value{1}=="-") 
			// preissenkung			
			$price_change_value=(100-$price_change_value)/100;			
		else
			// preiserhöhung
			$price_change_value=1+($price_change_value/100);					
		// Freisänderung durchführen		
		foreach ($_POST['stock_update'] as $key => $items) {
			 // update the quantity and price in stock
				$table="catalog_product_entity_decimal";
						$what="value=".$items['price']*$price_change_value;
						$where="attribute_id=60 AND value_id=".$key;
						dmc_sql_update($table, $what, $where); 
				   			
		} // end foreach
	} else {
		// Standard: wenn keine preissenkung -. preiserhöhung
	  
	    foreach ($_POST['stock_update'] as $key => $items) {
		  
		  // update the quantity and price in stock
		  $update_sql_data = array(	
						'qty' => $items['stock'],
						// 'sku' => $items['model'],
						'price' => $items['price']/1,		// netto oder brutto (1) - net or gros
						'weight' => $items['weight'],
						'updated_at' => 'now()',
						'name' => $items['name'],
						// 'description' => $Artikel_Text,
						'short_description' => str_replace( "\"", "", $items['short_desc'] ),	
						'status' => '1'); // aktiviert
					  		 
			if (isset($_POST['update_status']) && !empty($_POST['update_status'])) {
	        if ($items['stock'] >= 1 ) {
	           $update_sql_data['status'] = '1'; // aktiviert
			    $status_a++;
	        } else {
	           $update_sql_data['status'] = '0'; // deaktiviert
			   $status_d++;
	        }
			} // end if
		 
			$stock_i++;
			
			 if (DEBUGGER>=1) fwrite($dateihandle,"\nSession= ".$session." / Update Product".$items['model']."\n");	
					
			try {
					// update product
			  		if ($client->call($session, 'product.update', array($items['model'], $update_sql_data)))	
							$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
					else $newProductId = 28021973;	// no update possible						
				
					// Bestand des Artikels und aktiv setzen - Update stock info
					if ($newProductId != 28021973) {
						$client->call($session, 'product_stock.update', array($items['model'], array('qty'=>$items['stock'], 'is_in_stock'=>1)));
						dmc_sql_update("cataloginventory_stock_status", "stock_status=1", "product_id=".$newProductId);
					}
					 
				} catch (SoapFault $e) {
					if (DEBUGGER>=1) fwrite($dateihandle, "Product Update failed:\nError:\n".$e."\n"."Session= ".$session." / Update Product".$items['model']."\n");		 
				} 
				if (DEBUGGER>=1) fwrite($dateihandle, "Product updated ".$items['model']." in quick_stock_update.php with newProductId=".$newProductId."\n");
	      
    }
  } 
 }
?>
<br>
<form method="post" action="quick_stock_update.php">
<table border="0">
<tr>
<td class="smallText">
<?php

// Ask for sort order
	
echo '<select name="sort_order">';
	echo '	<option value="products_id">-- Sortierung --</option>';
	if (isset($_POST['sort_order']) && $_POST['sort_order']=="products_model") echo '	<option value="products_model" selected>nach Artikelnummer</option>';
	else echo '	<option value="products_model">nach Artikelnummer</option>';
	if (isset($_POST['sort_order']) && $_POST['sort_order']=="products_name") echo '	<option value="products_name" selected>nach Name A-Z</option>';
	else echo '	<option value="products_name">nach Name A-Z</option>';
	if (isset($_POST['sort_order']) && $_POST['sort_order']=="products_name_desc") echo '	<option value="products_name_desc" selected>nach Name Z-A</option>';
	else echo '	<option value="products_name_desc">nach Name Z-A</option>';
	if (isset($_POST['sort_order']) && $_POST['sort_order']=="products_price") echo '	<option value="products_price" selected>nach Preis</option>';
	else echo '	<option value="products_price">nach Preis</option>';
	echo '</select>';
echo '<br><select name="cat_id" onChange="this.form.submit();">';

echo '<option value="0">-- Kategorie --</option>';
// first select all categories that have 0 as parent:
  
		$query = "SELECT entity_id, name from catalog_category_flat WHERE parent_id = 2";
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_get_id-SQL= ".$query." .\n");
		$result = mysql_query ( $query );
		
	while ($cat = mysql_fetch_array($result)) {
    // check if the parent has products
    // $check = $db->Execute("SELECT products_id FROM ". $oosDBTable['products_to_categories'] . " WHERE categories_id = '" . $parents['categories_id'] . "'");unused!	
		echo '<option value="'.$cat['entity_id'].'">'.$cat['name'].'</option>';
	}
 
	// see if there is a category ID:
  if (isset($_POST['cat_id']) && !empty($_POST['cat_id'])) {
	echo '	<option value="'.$_POST['cat_id'].'">-- Unterkategorien --</option>'; 
	$query = "SELECT entity_id, name from catalog_category_flat WHERE parent_id = ".$_POST['cat_id']."";
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_get_id-SQL= ".$query." .\n");
		$result = mysql_query ( $query );
	while ($cat = mysql_fetch_array($result)) {
    // check if the categorie has products
    // $check = $db->Execute("SELECT products_id FROM ". $oosDBTable['products_to_categories'] . " WHERE categories_id = '" . $parents['categories_id'] . "'");unused!	
		echo '<option value="'.$cat['entity_id'].'"> - '.$cat['name'].'</option>';
	}
  
  } // endif

  echo '              </select>';
   // nur auf "Startseite"
  if (!isset($_POST['cat_id']) || empty($_POST['cat_id'])) {
	 echo '<br><input type="checkbox" name="price_change_complete" class="smallText"><b>KOMPLETTE Preiserh&ouml;hung/Preissenkung(-) f&uuml;r ALLE Artikel durchf&uuml;hren';
	 echo '<input type="text" size="3" name="price_change_complete_value" value="0">%';
	 echo '&nbsp; &nbsp;<input type="submit" value="start">';
  }
  echo '              </form>';					
  echo '              </td>';
  echo '              </tr>';
  echo '            </table>';
  echo '            </form>';
  echo '            </td>';
  echo '          </tr>';
  echo '        </table>';
  

  // see if there is a category ID:
  if (isset($_POST['cat_id']) && !empty($_POST['cat_id'])) {
  
    // start the table
    echo '            <br><form method="post" action="quick_stock_update.php">';
	
    echo '            <table width="100%" border="0" cellspacing="2" cellpadding="2">';

    // get all active products in that specific category
	$link=dmc_db_connect();
	
	$doQuery = "SELECT flat.sku as products_model, flat.entity_id as products_id, stock.qty as products_quantity, status.value as products_status, flat.weight as products_weight, flat.price as products_price, flat.name as products_name, flat.short_description as products_short_description from catalog_product_flat_1 flat, cataloginventory_stock_item stock, catalog_product_entity_int status WHERE flat.entity_id = stock.product_id AND flat.entity_id = status.entity_id AND status.attribute_id AND status.attribute_id=80 AND flat.category_ids = '" . $_POST['cat_id'] . "'";
	// Staus 1 = aktiv, 2=inaktiv
	if (isset($_POST['sort_order'])) {
		if ($_POST['sort_order']=="products_model") $doQuery .= " ORDER BY flat.sku";	
		if ($_POST['sort_order']=="products_name") $doQuery .= " ORDER BY flat.name";
		if ($_POST['sort_order']=="products_name_desc") $doQuery .= " ORDER BY flat.name desc";
		if ($_POST['sort_order']=="products_price") $doQuery .= " ORDER BY flat.price";
	} else {
		$doQuery .= " ORDER BY flat.name";	// standard sort order
	}

	if (DEBUGGER>=1) fwrite($dateihandle, "dmc_get_id-SQL= ".$doQuery." .\n");
		$result = mysql_query ( $doQuery );

    echo '<tr class="dataTableHeadingRow"><td class="dataTableContent" align="left"><b>Art-Nr</b></td><td class="dataTableContent" align="left"><b>ID#</b></td><td class="dataTableContent" align="left"><b>Name</b></td><td class="dataTableContent" align="left"><b>Gewicht</b></td><td class="dataTableContent" align="left"><b>Preis</b></td><td class="dataTableContent" align="left"><b>Bestand</b></td></tr>';
	while ($product = mysql_fetch_array($result)) {
	    {    
      echo '<tr class="dataTableRow"><td class="dataTableContent" align="left"><input type="text" size="16" name="stock_update[' . $product['products_id'] . '][model]" value="' . $product['products_model'] . '"><i>';
      echo '</td><td class="dataTableContent" align="left">' . $product['products_id'];
	   echo '</td><td class="dataTableContent" align="left"><input type="text" size="90" name="stock_update[' . $product['products_id'] . '][name]" value="' . $product['products_name'] . '"><i>';
      echo '</td><td class="dataTableContent" align="left"><input type="text" size="3" name="stock_update[' . $product['products_id'] . '][weight]" value="' . $product['products_weight'] . '"><i>';
      echo '</td><td class="dataTableContent" align="left"><input type="text" size="5" name="stock_update[' . $product['products_id'] . '][price]" value="' . ($product['products_price']*1) . '"><i>';
      echo '</td><td class="dataTableContent" align="left"><input type="text" size="4" name="stock_update[' . $product['products_id'] . '][stock]" value="' . $product['products_quantity'] . '"><i>';
      echo (($product['products_status'] != 1) ? '<font color="ff0000"><b>Deaktiviert</b></font>' : '<font color="009933"><b>Aktiv</b></font>');
      echo '</i></td></tr>';      
	  // Products Short Description
	  echo '<tr class="dataTableRow"><td colspan="2"></td>';
	  echo '<td colspan="3" class="dataTableContent" align="left"><textarea cols="100" rows="6" name="stock_update[' . $product['products_id'] . '][short_desc]">'.str_replace( "\"", "", $product['products_short_description'] ).'</textarea>';
	  echo '</td></tr>';
    } // end while
    echo '</table><table border="0" width="100%" cellspacing=2 cellpadding=2><tr>';
    echo '<input type="hidden" name="cat_id" value="' . $_POST['cat_id'] . '">';
	echo '<input type="hidden" name="user" value="' . $user . '">';
	echo '<input type="hidden" name="password" value="' .$password . '">';
	
    echo '</tr><br><td align="center" colspan="10" class="smallText">';
	echo '<p><input type="checkbox" name="price_change"><b>Komplette Preiserh&ouml;hung/Preissenkung(-) f&uuml;r Kategorie durchf&uuml;hren';
	echo '<input type="text" size="3" name="price_change_value" value="0">%</p>';
    echo '<input type="checkbox" name="update_status">Produkt Status anhand der Anzahl anpassen -><br><i>( Produkte mit Bestand sind <font color="009933"><b>aktiv</b></font> / Produkte mit ohne Bestand (0) werden <font color="ff0000"><b>deaktiviert.</b></font> )</i><p>';
    echo '<input type="submit" value="Update"></td></tr></form>';
  } //if
  }
?>
    </tr></table>
  </td>
</tr></table>
    </td>
							</tr>
						</table> 
					</td>
				</tr>
			</table>
			
		</body>
	</html>
	 

<br />
</body>
</html>
<?php
dmc_db_disconnect($link);
?>
