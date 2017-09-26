<?php
/**
 * Email: 114458573@qq.com .
 * User: 杜兵
 * Date: 15-10-31
 * Time: 上午1:25
 */
$magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
require_once$magento_bootstrap;
Mage::app(); //加载……
$model = Mage::getModel('catalog/resource_setup','catalog_resource');
$model->startSetup();
$attributeName='catalog_show_in_breadcrumbs';
$attributeLabel='Show In Breadcrumbs';
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
$model->endSetup();
