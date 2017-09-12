<?php

/**
 * Ado Ciobanu
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
class Ado_SEO_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
    
    public function __construct()
    {
        parent::__construct();
        //$this->_requestVar = 'cat';
    }
    
     /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Mage_Core_Block_Abstract $filterBlock
     * @return  Mage_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        if (!Mage::helper('ado_seo')->isEnabled()) {
            return parent::apply($request, $filterBlock);
        }
        
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }
		
        
         
        
        // Load the category filter by url_key
        $this->_appliedCategory = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->loadByAttribute('url_key', $filter);

        // Extra check in case it is a category id and not url key
        if (! ($this->_appliedCategory instanceof Mage_Catalog_Model_Category)) {
            return parent::apply($request, $filterBlock);
        }        
        Mage::register('current_category_filter', $this->getCategory(), true); 
        $this->_categoryId = $this->_appliedCategory->getId();
              
        
        if ($this->_isValidCategory($this->_appliedCategory)) {
            $this->getLayer()->getProductCollection()
                ->addCategoryFilter($this->_appliedCategory);

            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_appliedCategory->getName(), $filter)
            );
        }

        return $this;
    }
    
    
    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if (!Mage::helper('ado_seo')->isEnabled()) {
            return parent::_getItemsData();
        }
        
        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $categoty   = $this->getCategory();
            /** @var $categoty Mage_Catalog_Model_Categeory */
            $categories = $categoty->getChildrenCategories();

            $this->getLayer()->getProductCollection()
                ->addCountToCategories($categories);

            $data = array();
            foreach ($categories as $category) {
                // @file_put_contents(dirname(__FILE__).'/aa.txt',"---3---\n".$category->getId().'=='.print_r($category->getName(),true).'='.$category->getIsActive().'=='.$category->getProductCount()."\n---33---",FILE_APPEND);
                if ($category->getIsActive() && $category->getProductCount()) { // 
                    $urlKey = $category->getUrlKey();
                    if (empty($urlKey)) {
                        $urlKey = $category->getId();
                    }
                    
                    $data[] = array(
                        'label' => Mage::helper('core')->htmlEscape($category->getName()),
                        'value' => $urlKey,
                        'count' => $category->getProductCount(),
                    );
                }
            }
          //  @file_put_contents(dirname(__FILE__).'/aa.txt',"---11---\n".print_r($data,true)."\n---22---",FILE_APPEND);
            $tags = $this->getLayer()->getStateTags();
          //  $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
       // @file_put_contents(dirname(__FILE__).'/aa.txt',"---1---\n".print_r($data,true)."\n---2---",FILE_APPEND);
        return $data;
    }
    
   

}