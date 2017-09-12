<?php
/**
 * Product search result block
 * @��дmeta���ɹ���
 * @category   Mage
 * @package    Mage_CatalogSearch
 * @module     Catalog
 */
class Ado_SEO_Block_Catalog_View extends Mage_Catalog_Block_Category_View
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


        parent::_prepareLayout();
        $this->getLayout()->createBlock('catalog/breadcrumbs');
		$this->getActiveFilterStrs();
        return $this;
    }
    
    protected function getActiveFilterStrs(){

        $head = $this->getLayout()->getBlock('head');
        if($head){
        $helper =$this->_getHelper();
        $category = $this->getCurrentCategory();
        $title_template=$helper->getTitleTemplate();

        $desc_template=$helper->getDescTemplate();
        $keywords_template=$helper->getKeywordsTemplate();
  
        $overWrite= $helper->getOverWriteTemplate();
        // 1，没有内容,才使用规则   2，有内容，先取内容，没有内容，用模版规则； 广告站  Yes
        //seo站  1，都使用模版规则，keyword与title类似或等同   No

        $category->setStoreId($this->getStoreId());
        $catalog=$category->getMetaTitle();
        $catalog=trim($catalog);
	    $catalogname = $category->getName();
        if(!$catalog){
            $catalog=$catalogname;
        }
        $keywords = $category->getMetaKeywords();
        $keywords=trim($keywords);
        if (!$keywords) {
             $keywords=$catalog;
        }
        $description = $category->getMetaDescription();
        
        $siteurl=Mage::getBaseUrl();
        $siteurl=str_replace(array('https://','http://','www.'),'',$siteurl);
        $siteurl=rtrim($siteurl,'/');
        
		$_filters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
		$str='';
		$level1='';
		if(!empty($_filters)){
			foreach($_filters as $key=>$_filter){
			   // if($_filter->getName()=='Category')continue;
				if($key!=0){
					$str.=',';
				}else{
					$level1 = Mage::helper('catalog')->__(strip_tags($_filter->getLabel()));
				}
				$str.=Mage::helper('catalog')->__(strip_tags($_filter->getLabel()));
			}
            $str=trim($str,',');
		}

        $replaceWords[0]=$str;
        $replaceWords[1]=empty($str)?$catalog:$catalogname;
        $replaceWords[2]=$siteurl;
        $title_template=$this->_replaceWords($title_template,$replaceWords);


          //  @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($title_template,true).microtime()."==\n",FILE_APPEND);
         //   @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($replaceWords,true)."=1=\n",FILE_APPEND);
        if($overWrite)$replaceWords[1]=empty($str)?$keywords:$catalogname;
        $keywords_template=$this->_replaceWords($keywords_template,$replaceWords);
        if(empty($str)){
            $desc_template = $description;
        }else{
            $replaceWords[1]=$description;
            $desc_template=$this->_replaceWords($desc_template,$replaceWords);
        }
        $catalogname = $level1.' '.$catalogname;
		$catalogname = trim($catalogname);
		$category->setName($catalogname);

       // @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($title_template,true)."\n",FILE_APPEND);
        $head->setTitle(trim($title_template));
        $head->setKeywords(trim($keywords_template));
        $head->setDescription(trim($desc_template));
        if ($this->IsRssCatalogEnable() && $this->IsTopCategory()) {
                $title = Mage::helper('rss')->__('%s RSS Feed',$catalog);
                $head->addItem('rss', $this->getRssLink(), 'title="'.$title.'"');
            }
       }
       if (Mage::helper('catalog/category')->canUseCanonicalTag()) {
               $catalogUrl = $category->getUrl();
               $currentUrl = Mage::helper('core/url')->getCurrentUrl();
			   $pattern = '/_p_[0-9]+/';
               $currentUrl = preg_replace($pattern,'',$currentUrl);
               $pattern = '/_order_[a-zA_Z_]*\/$/';
               $currentUrl = preg_replace($pattern,'',$currentUrl);
               if(!empty($currentUrl)&& $currentUrl != $catalogUrl){
                    $head->removeItem('link_rel', $catalogUrl);
                    $head->removeItem('link_rel', $catalogUrl.'_as');
                    $catalogUrl = str_replace('http://www.','http://m.',$catalogUrl);
                    $head->removeItem('link_rel', $catalogUrl);
                    $head->removeItem('link_rel', $catalogUrl.'_as');
                    $head->addLinkRel('canonical', $currentUrl);
               }
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



    public function getCacheTags()
    {
        return parent::getCacheTags();
    }
}
