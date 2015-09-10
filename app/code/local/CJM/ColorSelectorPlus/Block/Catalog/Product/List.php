<?php

class CJM_ColorSelectorPlus_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{
    public function getListSwatchAttributes()
    {
        $swatch_attributes = array();
        $swatchattributes = Mage::getStoreConfig('color_selector_plus/colorselectorplusgeneral/toshow',Mage::app()->getStore());
        $swatch_attributes = explode(",", $swatchattributes);

        foreach($swatch_attributes as &$attribute) {
            $attribute = Mage::getModel('eav/entity_attribute')->load($attribute)->getAttributeCode();
        }
        unset($attribute);

        return $swatch_attributes;
    }

    public function getProductAccountFrontend($_attributes)
    {
        $swatch_attributes = $this->getListSwatchAttributes();
        $count = 0;
        foreach($_attributes as $_attribute):
            if(in_array($_attribute['attribute_code'], $swatch_attributes)):
                $_option_vals = array();
                $attributed = Mage::getModel('eav/config')->getAttribute('catalog_product', $_attribute['attribute_code']);
                foreach($attributed->getSource()->getAllOptions(true, true) as $option){
                    $_option_vals[$option['value']] = array( 'internal_label' => $option['label'] );
                }
                foreach($_attribute['values'] as $value):
                    $adminLabel = $_option_vals[$value['value_index']]['internal_label'];
                    preg_match_all('/((#?[A-Za-z0-9]+))/', $adminLabel, $matches);
                    if (count($matches[0]) > 0):
                        $count++;
                    endif;
                endforeach;
            endif;
        endforeach;
        return $count;
    }
}