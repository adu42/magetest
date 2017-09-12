<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/8
 * Time: 9:28
 */
class Kush_Reviewimage_Block_Review_Catalog_View extends Kush_Reviewimage_Block_Review_Abstract
{
    protected $perPage = 10;
    protected $_pId = null;


    public function getReviewCollection(){
        return $this->getProductReviewCollection();
    }


    /**
     * 获得当前分类id
     * @return int
     */
    public function getProductId(){
        if($this->_pId===null){
            if ($_product = Mage::registry('current_product')) {
                if($_product && $_product->getId())$this->_pId = $_product->getId();
            }
        }
        return $this->_pId;
    }

    public function setProductId($_pId){
        $this->_pId = $_pId;
        return $this;
    }

    /**
     *
     * 获得当前分类的id，只在分类页展示
     * 如果没有分类id，不展示
     * 分类页侧边栏的数据的数据
     * @return mixed
     */
    public function getProductReviewCollection(){

        if (null === $this->_reviewsCollection) {
            $pId =(int) $this->getRequest()->getParam('id',false);
            if($pId)$this->setProductId($pId);
            /** 分页计算 */
            $page =(int) $this->getRequest()->getParam('p',false);
            if(!$page || $page<1)$page=1;

            /** 取数据 **/
            $productId = $this->getProductId();
            if($productId){
                $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                    ->addProductFilter($productId)
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