<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_create_superattribute.php											*
*  inkludiert von /functions/products/dmc_map_super_attributes.php 			*
*  SuperAttribute anlegen													*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
06.04.2012 - neu
11.01.2013 - dmc_set_attribute_store_label
*/

		// Merkmale ermitteln - werden als attribue1@attribe2@... übergeben
		$Attributebezeichnung = $Superattribute[$i];
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_create_superattribute Bezeichnung = ".$Attributebezeichnung);
		$seo_text = $Attributebezeichnung;

		// aus functions/products/dmc_art_functions.php
		$code = "dmc_".dmc_generate_attribute_code($seo_text);
		
		if (DEBUGGER>=1) fwrite($dateihandle, " - mit Code = ".$code." anlegen\n");

		$attr = array(
			// 'entity_type_id' => $attr_type_id,
			'label' => $Attributebezeichnung,
			'input' => 'select',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,    
			'required' => false,
			'user_defined' => true,    
			'backend_type' => 'int',
			'frontend_input' => 'select',
			//'default' => '1'
		);
		$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
		$setup->addAttribute('catalog_product', $code, $attr);
		// Folgende Werte lassen sich nur durch ein Update realisieren
		$attr = array(
			//'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'attribute_set'     => $attribute_set_name,
			'group'	=>'General',
			'is_visible' => true,
			'is_filterable' => false,
			'is_configurable'  => true,
			'is_searchable' => false,
			'is_comparable' => false,
			'is_visible_on_front' => false,
			'is_visible_in_advanced_search' => false,
			'used_in_product_listing' => false, 
			'position' => 0,
			'is_used_for_promo_rules'=>0			
			// eav_attribute.frontend_label admin input label
			/* Moegliche Parameter
			'label' => 'Label',
			'group' => 'General', // (not a column) tab in product edit screen
			'sort_order' => 0 // eav_entity_attribute.sort_order sort order in group
			'backend' => 'module/class_name', // eav_attribute.backend_model backend class (module/class_name format)
			'type' => 'varchar', // eav_attribute.backend_type backend storage type (varchar, text etc)
			'frontend' => 'module/class_name', // eav_attribute.frontend_model admin class (module/class_name format)
			'note' => null, // eav_attribute.note admin input note (shows below input)
			'default' => null, // eav_attribute.default_value admin input default value
			'wysiwyg_enabled' => false, // catalog_eav_attribute.is_wysiwyg_enabled (products only) admin input wysiwyg enabled
			'input' => 'input_name', // eav_attribute.frontend_input admin input type (select, text, textarea etc)
			'input_renderer' => 'module/class_name', // catalog_eav_attribute.frontend_input_renderer (products only) admin input renderer (otherwise input is used to resolve renderer)
			'source' => null, // eav_attribute.source_model admin input source model (for selects) (module/class_name format)
			'required' => true, // eav_attribute.is_required required in admin
			'user_defined' => false, // eav_attribute.is_user_defined editable in admin attributes section, false for not
			'unique' => false, // eav_attribute.is_unique unique value required
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL, // catalog_eav_attribute.is_global (products only) scope
			'visible' => true, // catalog_eav_attribute.is_visible (products only) visible on admin
			'visible_on_front' => false, // catalog_eav_attribute.is_visible_on_front (products only) visible on frontend (store) attribute table
			'used_in_product_listing' => false, // catalog_eav_attribute.used_in_product_listing (products only) made available in product listing
			'searchable' => false, // catalog_eav_attribute.is_searchable (products only) searchable via basic search
			'visible_in_advanced_search' => false, // catalog_eav_attribute.is_visible_in_advanced_search (products only) searchable via advanced search
			'filterable' => false, // catalog_eav_attribute.is_filterable (products only) use in layered nav
			'filterable_in_search' => false, // catalog_eav_attribute.is_filterable_in_search (products only) use in search results layered nav
			'comparable' => false, // catalog_eav_attribute.is_comparable (products only) comparable on frontend
			'is_html_allowed_on_front' => true, // catalog_eav_attribute.is_visible_on_front (products only) seems obvious, but also see visible
			'apply_to' => 'simple,configurable', // catalog_eav_attribute.apply_to (products only) which product types to apply to
			'is_configurable' => false, // catalog_eav_attribute.is_configurable (products only) used for configurable products or not
			'used_for_sort_by' => false, // catalog_eav_attribute.used_for_sort_by (products only) available in the 'sort by' menu
			'position' => 0, // catalog_eav_attribute.position (products only) position in layered naviagtion
			'used_for_promo_rules' => false, // catalog_eav_attribute.is_used_for_promo_rules (products only) available for use in promo rules
			*/
		);
		$setup->updateAttribute('catalog_product', $code, $attr);
	
		// Weiter Arbeiten mit dem Attribute Code
		$Superattribute[$i]=$code;
		
		$attributeId=dmc_get_attribute_id_by_attribute_code($attr_type_id,$Superattribute[$i]);		
	
		// Storespezifische Bezeichnung hinterlegen
		$store_id=1;
		dmc_set_attribute_store_label($attributeId, $store_id, $Attributebezeichnung);
	
		//add attribute (attribute_featured) to attribute set (Attributeset)
		// Workflow, da Magento API BUG 
		$attribute_group_name = STD_ART_SET_GROUP;

		dmc_attach_attribute_to_attributeset($attr_type_id,$attribute_set_id,dmc_get_attribute_group_id($attribute_group_name,$attribute_set_id),$attributeId,$sort_order=0);
	
		
?>