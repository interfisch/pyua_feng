<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 23.10.15
 * Time: 16:34
 */
class MCorner_Ordercomments_Model_Observer extends Varien_Object
{
    /**
     * Add a customer order comment when the order is placed
     * @param object $event
     * @return
     */
    public function saveOrder($evt)
    {
        $_order   = $evt->getOrder();
        $_request = Mage::app()->getRequest();

        $_comments = strip_tags($_request->getParam('oderCommentsSelect').$_request->getParam('Magazin').$_request->getParam('googlesuchen').$_request->getParam('google-so').$_request->getParam('Andere'));

        if(!empty($_comments)){
            $_comments = 'Wie bist Du auf uns aufmerksam geworden: ' . $_comments;
            $_order->setCustomerNote($_comments);
        }

        return $this;
    }
}