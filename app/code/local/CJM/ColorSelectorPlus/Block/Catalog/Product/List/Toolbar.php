<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 15.10.15
 * Time: 16:42
 */

class CJM_ColorSelectorPlus_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar{
    public function isOrderCurrent($order, $direction)
    {
        return ($order == $this->getCurrentOrder() && $direction == $this->getCurrentDirection());
    }
}