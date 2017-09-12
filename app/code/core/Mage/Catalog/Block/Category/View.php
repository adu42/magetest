<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Category View block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Category_View extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->createBlock('catalog/breadcrumbs');

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $category = $this->getCurrentCategory();
            if ($title = $category->getMetaTitle()) {
                $headBlock->setTitle($title);
            }
            if ($description = $category->getMetaDescription()) {
                $headBlock->setDescription($description);
            }
            if ($keywords = $category->getMetaKeywords()) {
                $headBlock->setKeywords($keywords);
            }
            if ($this->helper('catalog/category')->canUseCanonicalTag()) {
                $headBlock->addLinkRel('canonical', $category->getUrl());
            }
            /*
            want to show rss feed in the url
            */
            if ($this->IsRssCatalogEnable() && $this->IsTopCategory()) {
                $title = $this->helper('rss')->__('%s RSS Feed',$this->getCurrentCategory()->getName());
                $headBlock->addItem('rss', $this->getRssLink(), 'title="'.$title.'"');
            }
        }

        return $this;
    }

    public function IsRssCatalogEnable()
    {
        return Mage::getStoreConfig('rss/catalog/category');
    }

    public function IsTopCategory()
    {
        return $this->getCurrentCategory()->getLevel()==2;
    }

    public function getRssLink()
    {
        return Mage::getUrl('rss/catalog/category',
            array(
                'cid' => $this->getCurrentCategory()->getId(),
                'store_id' => Mage::app()->getStore()->getId()
            )
        );
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }

    /**
     * Retrieve current category model object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', Mage::registry('current_category'));
        }
        return $this->getData('current_category');
    }

    public function getCmsBlockHtml()
    {
        if (!$this->getData('cms_block_html')) {
            $html = $this->getLayout()->createBlock('cms/block')
                ->setBlockId($this->getCurrentCategory()->getLandingPage())
                ->toHtml();
            $this->setData('cms_block_html', $html);
        }
        return $this->getData('cms_block_html');
    }

    /**
     * Check if category display mode is "Products Only"
     * @return bool
     */
    public function isProductMode()
    {
        return $this->getCurrentCategory()->getDisplayMode()==Mage_Catalog_Model_Category::DM_PRODUCT;
    }

    /**
     * Check if category display mode is "Static Block and Products"
     * @return bool
     */
    public function isMixedMode()
    {
        return $this->getCurrentCategory()->getDisplayMode()==Mage_Catalog_Model_Category::DM_MIXED;
    }

    /**
     * Check if category display mode is "Static Block Only"
     * For anchor category with applied filter Static Block Only mode not allowed
     *
     * @return bool
     */
    public function isContentMode()
    {
        $category = $this->getCurrentCategory();
        $res = false;
        if ($category->getDisplayMode()==Mage_Catalog_Model_Category::DM_PAGE) {
            $res = true;
            if ($category->getIsAnchor()) {
                $state = Mage::getSingleton('catalog/layer')->getState();
                if ($state && $state->getFilters()) {
                    $res = false;
                }
            }
        }
        return $res;
    }

    /**
     * Retrieve block cache tags based on category
     *
     * @return array
     */
    public function getCacheTags()
    {
        return array_merge(parent::getCacheTags(), $this->getCurrentCategory()->getCacheIdTags());
    }



    // 判断用户是否已在当前分类评过分
    function  getRatingStatues($currentCatId){
        $cookieRating = Mage::getSingleton('core/cookie')->get('rating');
        $_inRating = explode(',', $cookieRating);
      
        if(in_array((string)$currentCatId, $_inRating)) {
            return true;
        }else{
            return false;
        }
    }

// 计算返回结果的星星个数
     protected function _countratingrang($count)
    {
        $counts = explode('.', $count);
        // 判断余数
        $remainder_y = $counts[1] ; //余数
        if($remainder_y >= 0 && $remainder_y < 0.5){
            $_rcount_y = 0;
        }elseif(($remainder_y >= 5 &&  $remainder_y < 10) || ($remainder_y >= 50 &&  $remainder_y < 100)){
            $_rcount_y = 0.5 ;
        }

        // 组装星星
        $remainder_z = $counts[0] ;  //整数
        $remainder_z = $remainder_z * 2 + $_rcount_y * 2 ; // 4*2=8 0.5*2=1   合计9 深色星星的div个数
        $_rcount_z = max(0,(10 - $remainder_z)) ; //浅色星星的div个数

       
        // 组装深色星星
        $j=1;
        for($i=1;$i <= $remainder_z ; $i++ ) {
            $j++;
            if($j%2 == 0) {
                $class = 'trueodd_medium_gd_bdazzle' ;
            }else{
                $class = 'trueeven_medium_gd_bdazzle' ;
            }
            
            $_rangTemplete .= '<div class=" rate_pink_medium_heart_gd_bdazzle star_pink_medium_gd_bdazzle '.$class.'"></div>';
        }

        // 组装浅色星星
        for($i=1;$i <= $_rcount_z ; $i++ ) {
            $j++;
            if($j%2 == 0) {
                $class = 'falseodd_medium_gd_bdazzle' ;
            }else{
                $class = 'falseeven_medium_gd_bdazzle' ;
            }           
            $_rangTemplete .= '<div class=" rate_pink_medium_heart_gd_bdazzle star_pink_medium_gd_bdazzle '.$class.' "></div>';
        }


        $heart_img = Mage::getDesign()->getSkinUrl('images/pink_medium_heart.png');

        $_rangTemplete .= "<style scoped>
            .star_pink_medium_gd_bdazzle{width:10px;height:20px;float:left;-webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                }
        .rate_pink_medium_heart_gd_bdazzle{background-color:transparent;background-image: url('" .$heart_img. "');background-repeat: no-repeat;}

        .trueodd_medium_gd_bdazzle{background-position: -8px -36px;padding-left:0px;}
        .trueeven_medium_gd_bdazzle{background-position: -18px -36px;padding-right:3px;}
        .falseodd_medium_gd_bdazzle{background-position: -8px -1px;padding-left:0px;}
        .falseeven_medium_gd_bdazzle{background-position: -18px -1px;padding-right:3px;}
        </style><input type='hidden' id='countrating' value='". $count ."'>";
        // 
        return $_rangTemplete;

    }



    // 计算当前星星（没有cokies值的时候）
    public function _defaultcountratingrang($countRating,$currentCatId)
    {


        $counts = explode('.', $countRating);
        // 判断余数
        $remainder_y = $counts[1] ; //余数
        if($remainder_y >= 0 && $remainder_y < 0.5){
            $_rcount_y = 0;
        }elseif(($remainder_y >= 5 &&  $remainder_y < 10) || ($remainder_y >= 50 &&  $remainder_y < 100)){
            $_rcount_y = 0.5 ;
        }

        // 组装星星
        $remainder_z = $counts[0] ;  //整数
        $remainder_z = $remainder_z * 2 + $_rcount_y * 2 ; // 4*2=8 0.5*2=1   合计9 深色星星的div个数
        $_rcount_z = max(0,(10 - $remainder_z)) ; //浅色星星的div个数



         // 组装深色星星
        $j=1;
        $n=0;
        for($i=1;$i <= $remainder_z ; $i++ ) {
            $j++;
            $n++;
            if($j%2 == 0) {
                $class = 'trueodd_medium_gd_bdazzle' ;
                $bgpositin = 'style="background-position: -8px -36px;" ' ;
            }else{
                $class = 'trueeven_medium_gd_bdazzle' ;
                $bgpositin = 'style="background-position: -18px -36px;" ' ;
            }

            $num = $n / 2 ;
            

           $_rangTemplete .= '<div title="'.$num.'/5" class=" rate_pink_medium_heart_gd_bdazzle star_pink_medium_gd_bdazzle  '.$class.' hand" id="rategd_bdazzle'.$num.'" onmouseover="rateover(\'gd_bdazzle\',\''.$num.'\',\'medium\',\'5\',\'5\',\'/\')" onmouseout="rateout(\'gd_bdazzle\',\''.$num.'\',\''.$countRating.'\',\'medium\',\'5\',\'5\')" onclick="ratethis(\'gd_bdazzle\',\''.$num.'\',\'cookie\',\'medium\',\'pink\',\'5\',\'no\',\'yes\',\'yes\',\'heart\',\''.$currentCatId.'\',\'yes\',\'\',\'/catalog/category/ajaxcatmark\')" '.$bgpositin.'></div>';
        }




        // 组装浅色星星
        for($i=1;$i <= $_rcount_z ; $i++ ) {
            $j++;
            $n++;
            if($j%2 == 0) {
                $class = 'falseodd_medium_gd_bdazzle' ;
                $bgpositin = 'style="background-position: -8px -1px;" ' ;
            }else{
                $class = 'falseeven_medium_gd_bdazzle' ;
                $bgpositin = 'style="background-position: -18px -1px;" ' ;
            }           

            $num = $n / 2 ;

           $_rangTemplete .= '<div title="'.$num.'/5" class=" rate_pink_medium_heart_gd_bdazzle star_pink_medium_gd_bdazzle  '.$class.' hand" id="rategd_bdazzle'.$num.'" onmouseover="rateover(\'gd_bdazzle\',\''.$num.'\',\'medium\',\'5\',\'5\',\'/\')" onmouseout="rateout(\'gd_bdazzle\',\''.$num.'\',\''.$countRating.'\',\'medium\',\'5\',\'5\')" onclick="ratethis(\'gd_bdazzle\',\''.$num.'\',\'cookie\',\'medium\',\'pink\',\'5\',\'no\',\'yes\',\'yes\',\'heart\',\''.$currentCatId.'\',\'yes\',\'\',\'/catalog/category/ajaxcatmark\')" '.$bgpositin.'></div>';

        }         



        return $_rangTemplete;

    }



    
}
