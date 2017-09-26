<?php
/**
 * @É¾³ýmagentoµÄcustom optionÊôÐÔ
 * @author ado ¶Å±ø
 * @copyright 2012
 */
$optionLabels=array('Rush Order','Event Date');
$is_not_write_noOptionProduct=true;

$magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
require_once$magento_bootstrap;
Mage::app(); //¼ÓÔØ¡­¡­
$db=Mage::getSingleton('core/resource')->getConnection('core_read'); 

foreach($optionLabels as $optionLabel){
    $sql = "SELECT * from catalog_product_option a,catalog_product_option_title b where a.option_id=b.option_id and b.title='$optionLabel'"; 
    $rs = $db->fetchAll($sql);
    if(!empty($rs)){ 
        foreach($rs as $row){
            $optionId=$row['option_id'];
            $sql="select * from catalog_product_option_type_value where option_id='$optionId'";
            $types = $db->fetchAll($sql);
            
            foreach($types as $type){
                $option_type_id=$type['option_type_id'];
                $sql="delete from catalog_product_option_type_value where option_id='$optionId'";
                $db->query($sql);
                $sql="delete from catalog_product_option_type_title where option_type_id='$option_type_id'";
                $db->query($sql);
                $sql="delete from catalog_product_option_type_price where option_type_id='$option_type_id'";
                $db->query($sql);
            }
            $sql="delete from catalog_product_option_title where option_id='$optionId'";
            $db->query($sql);
            $sql="delete from catalog_product_option where option_id='$optionId'";
            $db->query($sql);
        }
    }
}
$sql='truncate table catalog_product_option_price;';
$db->query($sql);
?>