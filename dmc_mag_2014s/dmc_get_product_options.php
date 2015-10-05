<?php

  $mageFilename = '../app/Mage.php';
  require_once $mageFilename;

  umask(0);
  Mage::app();
  
  $product = Mage::getModel('catalog/product');
  $product->load(43);
     
  echo 'hasCustomOptions: '.$product->hasOptions.'<br>';
     
  $optionsArr = array_reverse($product->getOptions(), true);
  foreach ($optionsArr as $option) {
    echo '<pre>';             
    echo print_r($option->getData());
    echo '<hr>';
    foreach ($option->getValues() as $_value) {
      echo print_r($_value->getData());    
    }            
    echo '</pre><hr>';
  }

?> 