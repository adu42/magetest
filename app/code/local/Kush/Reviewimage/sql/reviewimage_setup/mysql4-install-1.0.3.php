<?php
$installer = $this;
$installer->startSetup();
$tablename = $this->getTable('review/review_detail');
try{
$installer->run("ALTER TABLE `$tablename` ADD `review_position` INT(11) NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_home` TINYINT(2) NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_sidebar` TINYINT(2) NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_likes` INT( 11 )  NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_image_g` VARCHAR( 255 )  NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_image_f` VARCHAR( 255 )  NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_image_e` VARCHAR( 255 )  NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_image_d` VARCHAR( 255 )  NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_image_c` VARCHAR( 255 )  NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_image_b` VARCHAR( 255 )  NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_image_a` VARCHAR( 255 )  NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_rating` TINYINT(2) NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_catalog` INT(11) NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` MODIFY COLUMN `title`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'Title' AFTER `store_id`;");
}catch (exception $e){

}
$installer->endSetup();

try{
$model = Mage::getModel('catalog/resource_setup','catalog_resource');
$model->startSetup();
$attributeName='show_in_reviews';
$attributeLabel='Show In Reviews';
$getAttribute=$model->getAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName);
if(!$getAttribute){
    $model->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeName, array(
        'group'         => 'General Information',
        'input'         => 'select',
        'type'          => 'varchar',
		'source'        => 'eav/entity_attribute_source_boolean',
        'label'         => $attributeLabel,
        'backend'       => '',
        'filterable'    => false,
        'visible'       => true,
        'required'      => false,
        'visible_on_front' => true,
        'sort_order'    =>  19,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    ));
    echo $attributeLabel.' ok<br>';
}else{
    echo $attributeLabel.' exsit<br>';
}
$model->endSetup();
}catch (exception $e){

}
?>

