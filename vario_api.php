<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 16.09.15
 * Time: 14:13
 */

//
// Atrribute einem Konfigurierbaren Artikel zuordnen.
//

//
//      Insert into Super Attribute, damit dem conf. Artikel die Attribute zugewiesen werden
//

$insertCatalogProductEntityInt = "insert into catalog_product_entity_int values ('', '4', '" . $_GET['ATTRIBUTE_ID'] . "', '0', '" . $_GET['PRODUCT_ID'] ."', NULL)";
$db->query($insertCatalogProductEntityInt);

$superAttribute = " insert into catalog_product_super_attribute (product_id, attribute_id)
                        values ( " . $_GET['PRODUCT_ID'] . ", " . $_GET['ATTRIBUTE_ID'] .
    " )  ";
$db->query($superAttribute);


//
//      $mysqlInsertID nutzen um nächsten insert aufzubauen
//
$superAttributeLabel =
    "    insert into catalog_product_super_attribute_label (product_super_attribute_id, store_id, use_default, value)
                            values ('" . $db->insert_id . "','" . $_GET['STORE_ID'] .
    "' , '0','" . $_GET['ATTRIBUTE_LABEL'] . "') ";
$db->query($superAttributeLabel);

$changeEntity = "update catalog_product_entity set required_options = 1 where entity_id = " . $_GET['PRODUCT_ID'] ;
$db->query($changeEntity);





if ($_GET['JoB'] == "AddSimpleProductToConfigurableProduct") {


    $productRelation = "insert into catalog_product_relation (parent_id, child_id) values ('" . $_GET['PARENT'] . "', '" . $_GET['CHILD'] . "' ) ";
    $db->query($productRelation);

    echo $db->error .'<br /><br />';


    $superLink = " insert into catalog_product_super_link (parent_id, product_id) values ('" . $_GET['PARENT'] . "', '" . $_GET['CHILD'] . "' )";
    $db->query($superLink);
    echo $db->error .'<br /><br />';


    $indexEAV = "insert into catalog_product_index_eav values ('" .$_GET['PARENT'] . "','" .$_GET['ATTRIBUTE_ID'] . "','1','" .$_GET['OPTION_ID'] . "')";
    $db->query($indexEAV);


}