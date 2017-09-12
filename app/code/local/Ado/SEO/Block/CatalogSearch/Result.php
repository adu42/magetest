<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product search result block
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 * @module     Catalog
 */
class Ado_SEO_Block_CatalogSearch_Result extends Mage_CatalogSearch_Block_Result
{
    protected $_helper=null;
    /**
     * Prepare layout
     *
     * @return Mage_CatalogSearch_Block_Result
     */
    protected function _prepareLayout()
    {
        $helper =$this->_getHelper();
        if (!$helper->isEnabled()){
            return parent::_prepareLayout();
        }
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $title = $this->__("Search results for: '%s'", $this->helper('catalogsearch')->getQueryText());

            $breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link'  => Mage::getBaseUrl()
            ))->addCrumb('search', array(
                'label' => $title,
                'title' => $title
            ));
        }

        // modify page title
        $title = $this->__("Search results for: '%s'", $this->helper('catalogsearch')->getEscapedQueryText());
        // 设置robots
        $_robots = $this->getRobots();
        $this->getLayout()->getBlock('head')->setRobots($_robots);

        // 设置关键词/描述/标题
       $_meta_title = Mage::helper('catalogsearch')->getQuery()->getMetaTitle() ? Mage::helper('catalogsearch')->getQuery()->getMetaTitle() : '' ;
       $_keywords = Mage::helper('catalogsearch')->getQuery()->getKeywords() ? Mage::helper('catalogsearch')->getQuery()->getKeywords() : '' ;
       $_descriptions = Mage::helper('catalogsearch')->getQuery()->getDescriptions() ? Mage::helper('catalogsearch')->getQuery()->getDescriptions() : '';




        $this->getActiveFilterStrs();
     
        if($_meta_title) $this->getLayout()->getBlock('head')->setTitle(trim($_meta_title));          
        if($_keywords) $this->getLayout()->getBlock('head')->setKeywords(trim($_keywords));
        if($_descriptions) $this->getLayout()->getBlock('head')->setDescription(trim($_descriptions));              
    
         parent::_prepareLayout();
         return $this;
    }

   // 
    public function getRobots()
    {
        // 获取robots状态
        $query = Mage::helper('catalogsearch')->getQuery();
        $_robots = $query->getRobotsTail();


        if($_robots) {
            $_robots_info='INDEX,FOLLOW';
        }else{
            $_robots_info='NOINDEX,NOFOLLOW';
        }

        return $_robots_info;
    }
    
    

    protected function getActiveFilterStrs(){
        $head = $this->getLayout()->getBlock('head');
        if($head){
        $helper =$this->_getHelper();    
        
        $title_template=$helper->getTitleTemplate();
        $desc_template=$helper->getDescTemplate();
        $keywords_template=$helper->getKeywordsTemplate();
          
        $siteurl=Mage::getBaseUrl();
        $siteurl=str_replace(array('https://','http://','www.'),'',$siteurl);
        $siteurl=rtrim($siteurl,'/');
        
		$_filters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
		$str='';
		if(!empty($_filters)){
			foreach($_filters as $key=>$_filter){
				if($key!=0){
					$str.=' ';
				}
				$str.=Mage::helper('catalog')->__($_filter->getLabel());
			}
		}
        $str=trim($str);
        
        $catalog=$this->helper('catalogsearch')->getEscapedQueryText();
        
        $replaceWords[]=$str;
        $replaceWords[]=$catalog;
        $replaceWords[]=$siteurl;
        $title_template=$this->_replaceWords($title_template,$replaceWords);
        $desc_template=$this->_replaceWords($desc_template,$replaceWords);
        $keywords_template=$this->_replaceWords($keywords_template,$replaceWords);
        $head->setTitle(trim($title_template));
        $head->setKeywords(trim($keywords_template));
        $head->setDescription(trim($desc_template));
       }
	}
    
    protected function _replaceWords($str,$replaceWords=array()){
        $words=array('filter','catalog','siteurl');
        return str_replace($words,$replaceWords,$str);
    }



    
    protected function _getHelper(){
        if($this->_helper===null){
            $this->_helper=Mage::helper('ado_seo');
        }
        return $this->_helper;
    }
}
