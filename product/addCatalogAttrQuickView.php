<?php
/**
 * by@adu
 * @copyright 2012
 */
$magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
require_once$magento_bootstrap;
Mage::app();
$model = Mage::getModel('catalog/resource_setup','catalog_resource');
$model->startSetup();
$attributeName='use_quick_view';
$attributeLabel='Use Quick View';
$getAttribute=$model->getAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName);
if(!$getAttribute){
    $model->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName, array(
        'group'         => 'General Information',
        'input'         => 'select',
        'type'          => 'int',
        'source'        => 'eav/entity_attribute_source_boolean',
        'label'         => $attributeLabel,
        'backend'       => '',
        'filterable'    => false,
        'visible'       => true,
        'required'      => false,
        'visible_on_front' => true,
        'sort_order'    =>  200,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    ));
    echo $attributeLabel.' ok<br>';
}else{
    echo $attributeLabel.' exsit<br>';
}

/*
$attributeName='catalog_recommend_product_ids';
$attributeLabel='Recommend product ids';
$getAttribute=$model->getAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName);
if(!$getAttribute){
    $model->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName, array(
        'group'         => 'General Information',
        'input'         => 'text',
        'type'          => 'varchar',
        'label'         => $attributeLabel,
        'backend'       => '',
        'filterable'    => false,
        'visible'       => true,
        'required'      => false,
        'visible_on_front' => true,
        'sort_order'    =>  20,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    ));
    echo $attributeLabel.'-ok<br>';
}else{
    echo $attributeLabel.'-exsit<br>';
}
$attributeName='catalog_product_about_ids';
$attributeLabel='Product about ids';
$getAttribute=$model->getAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName);
if(!$getAttribute){
    $model->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName, array(
        'group'         => 'General Information',
        'input'         => 'text',
        'type'          => 'varchar',
        'label'         => $attributeLabel,
        'backend'       => '',
        'filterable'    => false,
        'visible'       => true,
        'required'      => false,
        'visible_on_front' => true,
        'sort_order'    =>  20,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    ));
    echo $attributeLabel.'-ok<br>';
}else{
    echo $attributeLabel.'-exsit<br>';
}
*/

$model->endSetup();
?>