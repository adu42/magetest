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
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Catalog category helper
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Helper_Category extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CATEGORY_URL_SUFFIX          = 'catalog/seo/category_url_suffix';
    const XML_PATH_USE_CATEGORY_CANONICAL_TAG   = 'catalog/seo/category_canonical_tag';
    const XML_PATH_CATEGORY_ROOT_ID             = 'catalog/category/root_id';

    /**
     * Store categories cache
     *
     * @var array
     */
    protected $_storeCategories = array();

    /**
     * Cache for category rewrite suffix
     *
     * @var array
     */
    protected $_categoryUrlSuffix = array();

    /**
     * Retrieve current store categories
     *
     * @param   boolean|string $sorted
     * @param   boolean $asCollection
     * @return  Varien_Data_Tree_Node_Collection|Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection|array
     */
    public function getStoreCategories($sorted=false, $asCollection=false, $toLoad=true)
    {
        $parent     = Mage::app()->getStore()->getRootCategoryId();
        $cacheKey   = sprintf('%d-%d-%d-%d', $parent, $sorted, $asCollection, $toLoad);
        if (isset($this->_storeCategories[$cacheKey])) {
            return $this->_storeCategories[$cacheKey];
        }

        /**
         * Check if parent node of the store still exists
         */
        $category = Mage::getModel('catalog/category');
        /* @var $category Mage_Catalog_Model_Category */
        if (!$category->checkId($parent)) {
            if ($asCollection) {
                return new Varien_Data_Collection();
            }
            return array();
        }

        $recursionLevel  = max(0, (int) Mage::app()->getStore()->getConfig('catalog/navigation/max_depth'));
        $storeCategories = $category->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);

        $this->_storeCategories[$cacheKey] = $storeCategories;
        return $storeCategories;
    }

    /**
     * Retrieve category url
     *
     * @param   Mage_Catalog_Model_Category $category
     * @return  string
     */
    public function getCategoryUrl($category)
    {
        if ($category instanceof Mage_Catalog_Model_Category) {
            return $category->getUrl();
        }
        return Mage::getModel('catalog/category')
            ->setData($category->getData())
            ->getUrl();
    }

    /**
     * Check if a category can be shown
     *
     * @param  Mage_Catalog_Model_Category|int $category
     * @return boolean
     */
    public function canShow($category)
    {
        if (is_int($category)) {
            $category = Mage::getModel('catalog/category')->load($category);
        }

        if (!$category->getId()) {
            return false;
        }

        if (!$category->getIsActive()) {
            return false;
        }
        if (!$category->isInRootCategoryList()) {
            return false;
        }

        return true;
    }

/**
     * Retrieve category rewrite sufix for store
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryUrlSuffix($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!isset($this->_categoryUrlSuffix[$storeId])) {
            $this->_categoryUrlSuffix[$storeId] = Mage::getStoreConfig(self::XML_PATH_CATEGORY_URL_SUFFIX, $storeId);
        }
        return $this->_categoryUrlSuffix[$storeId];
    }

    /**
     * Retrieve clear url for category as parrent
     *
     * @param string $url
     * @param bool $slash
     * @param int $storeId
     *
     * @return string
     */
    public function getCategoryUrlPath($urlPath, $slash = false, $storeId = null)
    {
        if (!$this->getCategoryUrlSuffix($storeId)) {
            return $urlPath;
        }

        if ($slash) {
            $regexp     = '#('.preg_quote($this->getCategoryUrlSuffix($storeId), '#').')/$#i';
            $replace    = '/';
        }
        else {
            $regexp     = '#('.preg_quote($this->getCategoryUrlSuffix($storeId), '#').')$#i';
            $replace    = '';
        }

        return preg_replace($regexp, $replace, $urlPath);
    }

    /**
     * Check if <link rel="canonical"> can be used for category
     *
     * @param $store
     * @return bool
     */
    public function canUseCanonicalTag($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_USE_CATEGORY_CANONICAL_TAG, $store);
    }

    /**
     * 计算评分
     * @return 
     */
    public function countrating($_count,$cat_id,$store_id)
    {
        return $this->_countrating($_count,$cat_id,$store_id);  
    }

    /**
     * 计算返回结果的星星个数
     * @return 
     */    
    public function countratingrang($count)
    {
        return $this->_countratingrang($count); 
    }


    protected function _countratingrang($count)
    {
        $counts = explode('.', $count);
        $_rangTemplete = '';
        $remainder_y = $counts[1] ; //余数
        $_rcount_y = 0.5;
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
  
    protected function _countrating($rating,$cat_id,$store_id)
    {
        // 获取评分      
        $_getRating = $this->_getCountRating($cat_id);

        // 获取投票人数 
        $_getCountTotal = $this->_getCountTotal($cat_id);

        
        // 计算平均分 先计算总分，在加上当前分，计算现在的平均分
        $_defaultTotal = ( $_getRating * $_getCountTotal ) +  $rating ;  //当前总分
        $_ratingNow = $_defaultTotal / ( $_getCountTotal + 1) ;  //获得当前评分
        $_ratingNow = substr($_ratingNow,0,3) ;



        // 保存人数 
        $this->saveCountTotal(($_getCountTotal+1),$cat_id,$store_id) ;

        // 保存评分
        $this->saveCountRating($_ratingNow,$cat_id,$store_id) ;

        $_final_result = array('rating'=>$_ratingNow ,'total'=>($_getCountTotal+1) );

        return  $_final_result ;
    }


    /**
     * 获取评分
     * @return 
     */  
    protected function _getCountRating($cat_id)
    {
        $_category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($cat_id);

        $countRating = $_category->getRatingvalues();
            
        return $countRating ;           
    }   



    /**
     * 获取投票数
     * @return 
     */  
    protected function _getCountTotal($cat_id)
    {
        $_category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($cat_id);

        $countTotal = $_category->getTotalvotes();

        return $countTotal ;
    }   


    /**
     * 保存投票数
     * @return 
     */ 
    protected function saveCountTotal($total,$cat_id,$store_id)
    {
        $_attribute_code = 'totalvotes' ;
        $table_name = 'catalog_category_entity_int' ;

        $model = Mage::getModel('catalog/category') ;
        $attribute_id = $model->getAttributeId($_attribute_code) ;

        $countTotal = $model->_saveRatingValue($total,$attribute_id,$cat_id,$store_id,$table_name);     

    }

    /**
     * 保存评分
     * @return 
     */ 
    protected function saveCountRating($rating,$cat_id,$store_id)
    {
        $_attribute_code = 'ratingvalues' ;
        $table_name = 'catalog_category_entity_varchar' ;

        $model = Mage::getModel('catalog/category') ;
        $attribute_id = $model->getAttributeId($_attribute_code) ;

        $countRating = $model->_saveRatingValue($rating,$attribute_id,$cat_id,$store_id,$table_name);       
                        
    }



    
}
