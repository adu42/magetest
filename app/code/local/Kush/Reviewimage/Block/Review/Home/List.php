<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/8
 * Time: 9:28
 */
class Kush_Reviewimage_Block_Review_Home_List extends Kush_Reviewimage_Block_Review_Abstract
{
    protected $perPage = 10;
    
    public function _construct()
    {
        /**
         * 是否使用父类的缓存
         * 不用就注释掉下面的语句
         */
       parent::_construct();
    }
}