<?php

ini_set("memory_limit","1000M");
require_once "/home/www/production/app/Mage.php";
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); 

$category = Mage::getModel ( 'catalog/category' );
$tree = $category->getTreeModel();
$tree->load();
$ids = $tree->getCollection()->getAllIds();

$collection = array();
foreach($ids as $id){
    
    $item = Mage::getModel('catalog/category')->setStoreId(0)->load($id); 
    //set products
    $products = $item->getProductCollection()->getItems();              //echo $item->getProductCollection()->getSelect()->__toString();
    foreach($products as &$product){
        $product = $product->getSku();                                  //$product = array('sku' => $product->getSku(), 'position' => $product->getCatIndexPosition() );
    }unset($product);
    $item->setData( 'products', $products );
    
    //set path
    $pathArr = explode("/", $item->getPath() );
    foreach($pathArr as &$node){
        $node = Mage::getModel('catalog/category')->load($node)->getName();
    }unset($node);
    $item->setData('fullpath', implode("/", $pathArr ));
    array_pop($pathArr);
    $path = implode("/", $pathArr );                         //delete current category from path, because of at import current category id add to path automatically by magento.
    $item->setPath($path);
    $parentName = Mage::getModel('catalog/category')->load( $item->getData('parent_id') )->getName();
    $item->setData( 'parent_id', $parentName );                         //mage::d($item->getData()); 
    $collection[] = $item;
}

$content = serialize($collection);
$filename = 'categories.ser';
$fo = fopen($filename, "w");
fputs($fo, $content);
fclose($fo);
echo date("\nY-d-m H:i:s\n")."End\n";
