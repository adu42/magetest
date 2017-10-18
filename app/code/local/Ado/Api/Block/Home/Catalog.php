<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-8-22
 * Time: 14:03
 */
class Ado_Api_Block_Home_Catalog extends Mage_Core_Block_Template
{
    /**
     * 获取首页商品展示所在的分类
     * @return mixed
     */
    public function getHomeCategories(){
        return Mage::getModel('mapi/category')->getHomeCategories();
    }


}