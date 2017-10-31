<?php
/**
 * Product search result block
 * @首页获取 一些图片，分类图片，待写
 * @category   Mage
 * @package    Mage_CatalogSearch
 * @module     Catalog
 */
class Ado_SEO_Block_Catalog_Categories extends Mage_Core_Block_Template
{
    protected $identifier = 'home-categories';
    protected $_slideConllection=null;
    protected $_slideItemConllection=null;

    public function setIdentifier($identifier){
        $this->identifier = $identifier;
        return $this;
    }

    public function getIdentifier(){
        return $this->identifier;
    }
    /**
     * 获取home的分类
     * @return null
     */
    public function getSlideConllection(){
        $this->_setSlideItemCollection();
        return $this->_slideConllection;
    }

    /**
     * 获取home的分类
     * @return null
     */
    public function _getSlideConllection(){
        if($this->_slideConllection == null){
            // 加载有效数据
            $slideCollection = Mage::getModel('mapi/slide')->getCollection();
            $this->_slideConllection = $slideCollection->addIdentifierFilter($this->getIdentifier())
                ->addActiveFilter();
        }
        return $this->_slideConllection;
    }

    /**
     * @return $this
     */
    protected function _setSlideItemCollection(){
        $slides = $this->_getSlideConllection();
        foreach ($slides as $slide){
            $slide->getSlideItems();
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getCacheTags()
    {
        return parent::getCacheTags();
    }
}
