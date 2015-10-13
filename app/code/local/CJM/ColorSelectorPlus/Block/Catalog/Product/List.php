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

    public function decodeImagesForList($productId)
    {
        $_product = Mage::getModel('catalog/product')->load($productId);
        $_gallery = $_product->getMediaGalleryImages();
        $imgcount = count($_gallery);
        $product_base = array();

        if($imgcount > 1){
            foreach ($_gallery as $_image )
            {
                $product_base['color'][] = $_image['selectorbase'];
                $product_base['image'][] = strval(Mage::helper('catalog/image')->init($_product, 'base', $_image->getFile())->resize(275,275));
            }
        }
        return $product_base;
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
    public function getListSwatchProductImage($_attributes, $productId, $i)
    {
        $product_image = array();
        $swatch_attributes = $this->getListSwatchAttributes();

        foreach($_attributes as $_attribute):

            if(in_array($_attribute['attribute_code'], $swatch_attributes)):

                $_option_vals = array();
                $attributed = Mage::getModel('eav/config')->getAttribute('catalog_product', $_attribute['attribute_code']);

                foreach($attributed->getSource()->getAllOptions(true, true) as $option){
                    $_option_vals[$option['value']] = array( 'internal_label' => $option['label'] );
                }

                foreach($_attribute['values'] as $value):

                    $theId = $value['value_index'];
                    $adminLabel = $_option_vals[$value['value_index']]['internal_label'];

                    preg_match_all('/((#?[A-Za-z0-9]+))/', $adminLabel, $matches);

                    if (count($matches[0]) > 0):


                        $product_base = $this->decodeImagesForList($productId);
                        $product_image[] = Mage::helper('colorselectorplus')->findColorImage($theId,$product_base,'color', 'image');//returns url for base image

                    endif;

                endforeach;
            endif;

        endforeach;

        return $product_image[$i];
    }
    function isProductNew(Mage_Catalog_Model_Product $product)
    {
        $newsFromDate = $product->getNewsFromDate();
        $newsToDate   = $product->getNewsToDate();
        if (!$newsFromDate && !$newsToDate) {
            return false;
        }
        return Mage::app()->getLocale()
            ->isStoreDateInInterval($product->getStoreId(), $newsFromDate, $newsToDate);
    }
}