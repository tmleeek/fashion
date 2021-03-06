<?php
// select attribute_code from eav_attribute where frontend_input='multiselect' and entity_type_id=4; 
ini_set("memory_limit","1000M");
require_once "/home/www/demo/app/Mage.php";
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); 
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');

//$attr_code = 'frame_gender';
//$attr_code = 'frame_shape';
//$attr_code = 'lens_tint';
//$attr_code = 'framecolour';
$multi = array('frame_gender','frame_shape','lens_tint','framecolour');
foreach($multi as $attr_code){

    $attr_id = $connection->query("select attribute_id from fashione_magento3.eav_attribute where attribute_code='{$attr_code}'")->fetchColumn(0); //128

    $opt_ids = $connection->query("select option_id from fashione_magento3.eav_attribute_option where attribute_id = {$attr_id}")->fetchAll();
    $options = array();
    foreach($opt_ids as $opt){
        $options[$opt['option_id']] = $connection->query("select value from fashione_magento3.eav_attribute_option_value where option_id={$opt['option_id']}")->fetchColumn(0);
    }

    $live = $connection->query("
        select e.sku, v.value from fashione_magento3.catalog_product_entity_varchar v
        join fashione_magento3.catalog_product_entity e on v.entity_id = e.entity_id
        where v.attribute_id={$attr_id}
    ")->fetchAll();

    foreach($live as &$a){
        if( strpos($a['value'], ',') !== false ){
            $x = explode(',', $a['value']);
            
            foreach($x as &$v){
                $v = $options[$v];
            }unset($v);
            $a['value'] = implode(',', $x);
            continue;
        }
        $a['value'] = $options[$a['value']];
    }unset($a);

    $demo_attr_id = $connection->query("SELECT attribute_id FROM eav_attribute WHERE attribute_code='{$attr_code}'")->fetchColumn(0);
    $demo_opt_ids = $connection->query("SELECT option_id FROM eav_attribute_option WHERE attribute_id = {$demo_attr_id}")->fetchAll();

    $demo_options = array();
    foreach($demo_opt_ids as $opt){
        $demo_options[$opt['option_id']] = $connection->query("SELECT value FROM eav_attribute_option_value WHERE option_id={$opt['option_id']}")->fetchColumn(0);
    }

    $demo_options = array_flip($demo_options);

    foreach($live as &$a){
        if( strpos($a['value'], ',') !== false ){
            $x = explode(',', $a['value']);
            
            foreach($x as &$v){
                $v = $demo_options[$v];
            }unset($v);
            $a['value'] = implode(',', $x);
            continue;
        }
        $a['value'] = (string)$demo_options[$a['value']];
    }unset($a);

    echo date("\nY-d-m H:i:s\n")."\n";
    echo "-- start {$attr_code} --\n";
    foreach($live as $li){
        $query = "update catalog_product_entity_varchar v join catalog_product_entity e on v.entity_id=e.entity_id set value={$connection->quote($li['value'])} where e.sku={$connection->quote($li['sku'])} and v.attribute_id={$demo_attr_id}";
        //echo($li['sku']); //echo $query."\n"; continue;
        try{
            $connection->exec($query);
            
        }catch(Exception $e){
            echo $e->getMessage()."\n\n";
            echo $query."\n";
        }
    }
    echo date("\nY-d-m H:i:s\n")."\n";
    echo "-- end {$attr_code} --\n";
}
//$connection->query("select * from catalog_product_entity_varchar where attribute_id =")