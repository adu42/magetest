移除  Mage_AdminNotification
Mage_GoogleCheckout

  Mage_All去掉，然后注释掉引用
    Mage_Core_Model_Observer
    app\code\core\Mage\Adminhtml\controllers\SurveyController.php

增加表结构列：    
ALTER TABLE `catalog_product_option_type_title` ADD `is_default` TINYINT( 1 ) NULL DEFAULT '0';
ALTER TABLE `catalog_product_option_type_title` ADD `note` VARCHAR( 200 ) NULL ;    


quick view js执行处理：
尽量使用jquery，prototype.js在这方面就比较弱。。


1、左侧筛选去掉、隐藏掉
2、快速注册
3、会员制


footer_links  top
block_footer_links  top
block_footer_links2  top

block_footer_column   primary
block_footer_primary_bottom_left  primary_bottom
block_footer_primary_bottom_right  primary_bottom
newsletter  primary_bottom
bottomContainer   primary_top

block_footer_row2_column  secondary
copyright bottom
block_footer_payment bottom
store_switcher  bottom


随机促销商品===好像直接取不可行，可以分开取，
1、取客户浏览过的商品，随机再插入到列表页里 list m个数 
    取ids数据，需要反序列化（$items = $this->_getCatalogSession()->getData($this->_visited_key);）
2、促销商品，随机插入到列表页里 list n个数
    $collection = Mage::getResourceModel('catalog/product_collection');
                Mage::getModel('catalog/layer')->prepareProductCollection($collection);
    // your custom filter
                $collection->addAttributeToFilter('promotion', 1)
                    ->addStoreFilter();
3、补足商品数随机插入到列表里 list d个数
    有上一页就取上一页数据，一般是这种情况
    有下一页就相当于数据完整，不需要补足.
    拿什么来补足一页数据？
计算规则优化：
    针对会话级别做缓存
    
    
   