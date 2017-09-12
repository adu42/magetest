<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/8
 * Time: 9:28
 */
class Kush_Reviewimage_Block_Review_Catalog_Side extends Kush_Reviewimage_Block_Review_Abstract
{
    protected $perPage = 10;
    protected $_catalogId = null;

    public function _construct()
    {
        parent::_construct();
    }

    public function getReviewCollection(){
        return $this->getSidebarReviewCollection();
    }
    /**
     * 获得当前分类id
     * @return int
     */
    public function getCatalogId(){
        if($this->_catalogId===null){
            if (Mage::registry('current_category')) {
                $_catalog = Mage::registry('current_category');
            //    print_r($_catalog->getData());
                if($_catalog && $_catalog->getId() && $_catalog->getShowInReviews())$this->_catalogId = $_catalog->getId();
            }
        }
        return $this->_catalogId;
    }

    public function setCatalogId($catalogId){
        $this->_catalogId = $catalogId;
        return $this;
    }

    /**
     *
     * 获得当前分类的id，只在分类页展示
     * 如果没有分类id，不展示
     * 分类页侧边栏的数据的数据
     * @return mixed
     */
    public function getSidebarReviewCollection(){

        if (null === $this->_reviewsCollection) {
            $catalogId =(int) $this->getRequest()->getParam('cid',false);
            if($catalogId)$this->setCatalogId($catalogId);
            /** 分页计算 */
            //$page =(int) $this->getRequest()->getParam('p',false);
            //if(!$page || $page<1)$page=1;  //跟随分页了，不需要跟随分页
			$page=1;	

            /** 取数据 **/
            $catalogId = $this->getCatalogId();
            if($catalogId){
                $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                    ->addCategoryFilter($catalogId)
                    ->addHasImageFilter()
                    ->setPositionOrder()
                    ->setDateOrder()
                    ->setCurPage($page)
                    ->setPageSize($this->getPerPage());
                $count = $this->_reviewsCollection->getSize();
                if($count < $this->getPerPage()){
                    $items = Mage::getModel('review/review')->getCollection()
                        ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                        ->addHomeFilter()
                        ->addHasImageFilter()
                        ->setPositionOrder()
                        ->setDateOrder()
                        ->setCurPage($page)
						->setPageSize($this->getPerPage());
                    if($items && $items->getSize()){
						$j = $this->getPerPage()-$count;
						$i=1;
                        foreach ($items as $item){
                            try{
								if(!$this->_reviewsCollection->getItemById($item->getId())){
									$this->_reviewsCollection->addItem($item);
									if($i==$j)break;
									$i++;
								}
                            }catch (Exception $e){ }
							
                        }
                    }
                }
            }else{
                $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                    ->addHomeFilter()
                    ->addHasImageFilter()
                    ->setPositionOrder()
                    ->setDateOrder()
                    ->setCurPage($page)
                    ->setPageSize($this->getPerPage());
            }
        }
        return $this->_reviewsCollection;
    }

}