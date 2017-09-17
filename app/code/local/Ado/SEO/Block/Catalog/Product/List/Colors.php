<?php

/**
 * by@Ado
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Ado_Seo
 * @copyright   Copyright (c) 2013 Ado Ciobanu
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ado_SEO_Block_Catalog_Product_List_Colors extends Mage_Core_Block_Template
{
    const COLOR_ATTRIBUTE_CODE = 'shown_color';

    public function getColorFilterHtml($template='catalog/product/list/colors.phtml'){
        $catalog = $this->getCurrentCategory();
        if(!$catalog || !$catalog->getColorFilter())return '';
        $this->setTemplate($template);
        return $this->toHtml();
    }

    /**
     * 手机的端不用展示
     * 获取分类页的urls
     * @return array
     */
    public function getUrls(){
        $urls = array();
        if(Mage::helper('ado_seo')->isMobile())return $urls;
        $category = $this->getCurrentCategory();
        if($category && $category->getColorFilter()){
            $colors = $this->getColors();
            foreach ($colors as $color){
                $selected=false;
                $url = $category->getUrl();
                if($color == $this->getCurrentColor())$selected=true;
                $title = str_replace(array('-','_'),' ',$color);
                $title = ucwords($title);
                $class = strtolower(str_replace(' ','-',$color));
                $urls[]= array('url'=> $url.'?'.self::COLOR_ATTRIBUTE_CODE.'='.$color,'selected'=>$selected,'label'=>$title,'color'=>$class);
            }
        }
        return $urls;
    }

    /**
     * 获得当前分类
     * @return bool|mixed
     */
    public function getCurrentCategory(){
        $current_category =  Mage::registry('current_category');
        if($current_category && $current_category->getId()){
            return $current_category;
        }
        return false;
    }

    /**
     * 缓存
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $id = 0;
        $current_category =  $this->getCurrentCategory();
        if($current_category && $current_category->getId())$id = $current_category->getId();
        return array(
           'BLOCK_TPL',
            Mage::app()->getStore()->getCode(),
            $this->getTemplateFile(),
            'TEMPLATE' => $this->getTemplate(),
            'CURRENT_CATEGORY'=>$id,
        );
    }

    /**
     * 获得所有在后台属性里定义的颜色
     * @return array
     */
    public function getColors(){
        return Mage::helper('catalog/image')->getColors();
    }

    /**
     * 获取当前选中的颜色
     * @return mixed
     */
    public function getCurrentColor(){
        return $this->getRequest()->getParam(self::COLOR_ATTRIBUTE_CODE,false);
    }

}