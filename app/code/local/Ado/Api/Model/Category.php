<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-8-22
 * Time: 14:10
 */
class Ado_Api_Model_Category extends Ado_Api_Model_Abstract
{
    public function getHomeCategories($cmd='catalog'){
        $catalogIds = Mage::getStoreConfig('mapi/info/home_categories');
        $_categorylist = $ids = array();
        if(!empty($catalogIds)) {
            $_categories = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect(array('name', 'url_path', 'is_active'))
                ->addIdFilter($catalogIds);

            foreach ($_categories as $_category) {
                $ids[] = $_category->getId();
                $_categorylist_sample = array(
                    'category_id' => $_category->getId(),
                    'name' => $_category->getName(),
                    'as' => strtolower(str_replace(' ','_',$_category->getName())) ,
                    'is_active' => $_category->getIsActive(),
                    'position ' => $_category->getPosition(),
                    //  'level ' => $_category->getLevel(),
                    'url_key' => $_category->getUrlPath(),
                    'product_base_url' => Mage::getSingleton('core/url')->getUrl('mapi', array('cmd' => $cmd, 'cat_id' => $_category->getId())),
                    // 'thumbnail_url' => $_category->getThumbnailUrl(),
                    // 'image_url' => $_category->getImageUrl(),
                    // 'children' => Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getAllChildren (),
                    // 'child' => $this->getChildCatalog($_category)
                );
                $_categorylist[$_category->getId()] = $_categorylist_sample;
            }
            //按照给定的顺序排序
            $catalogIds = explode(',',$catalogIds);
            $catalogIds = array_intersect($catalogIds,$ids);
            $data = array();
            foreach ($catalogIds as $catalogId){
                $data[] = $_categorylist[$catalogId];
                unset($_categorylist[$catalogId]);
            }
            $_categorylist = $data;
        }
        return $_categorylist;
    }
}