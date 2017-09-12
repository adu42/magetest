<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/8
 * Time: 9:28
 */
class Kush_Reviewimage_Block_Review_Catalog_List extends Kush_Reviewimage_Block_Review_Abstract
{
    protected $perPage = 6;
    protected $_catagories =null;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $helper = Mage::helper('reviewimage');
            $cataName = '';

            $cid =(int) $this->getRequest()->getParam('cid',false);
            if($cid){
                $catalog = Mage::getModel('catalog/category')->load($cid);
                if($catalog && $catalog->getId()){
                    $cataName = $catalog->getName();
                }
            }
            
            if($title = $helper->listTitle()){
                $title = str_replace('catalog',$cataName,$title);
				$title = str_replace('  ',' ',$title);
                $headBlock->setTitle($title);
            }

            if($keywords = $helper->listKeywords()){
                $keywords = str_replace('catalog',$cataName,$keywords);
				$keywords = str_replace('  ',' ',$keywords);
                $headBlock->setKeywords($keywords);
            }

            if($description = $helper->listDescription()){
                $description = str_replace('catalog',$cataName,$description);
				$description = str_replace('  ',' ',$description);
                $headBlock->setDescription($description);
            }
        }
        return $this;
    }

    public function getReviewCollection(){
        if (null === $this->_reviewsCollection) {
            $catalogId =(int) $this->getRequest()->getParam('cid',false);
            if($catalogId)$this->setCatalogId($catalogId);
            /** 分页计算 */
            $page =(int) $this->getRequest()->getParam('p',false);
            if(!$page || $page<1)$page=1;

            /** 取数据 **/
          if($catalogId = $this->getCatalogId()){
                $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                    ->addCategoryFilter($catalogId)
                    ->addHasImageFilter()
                    ->setPositionOrder()
                    ->setDateOrder()
                    ->setCurPage($page)
                    ->setPageSize($this->getPerPage());
            }else{
                $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                    ->addHasImageFilter()
                    ->setPositionOrder($this->getDir())
                    ->setDateOrder($this->getDir())
                    ->setCurPage($page)
                    ->setPageSize($this->getPerPage());
            }
        }
        return $this->_reviewsCollection;
    }


    /**
     * 图片列表的分类地址
     */
    public function getCategories(){
        if($this->_catagories===null){
            $this->_catagories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('show_in_reviews')
            ->addAttributeToFilter('show_in_reviews',1)
            ->setOrder('level')
            ->setOrder('position')
            ->setOrder('entity_id');
        }
        return $this->_catagories;
    }

}