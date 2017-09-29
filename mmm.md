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
