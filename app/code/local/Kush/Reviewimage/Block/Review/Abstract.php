<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/9
 * Time: 8:57
 */
class Kush_Reviewimage_Block_Review_Abstract extends Mage_Core_Block_Template
{
    protected $perPage = 15;
    protected $_reviewsCollection=null;
    protected $_productId=0;
    protected $_catalogId=null;
    protected $_filterImage = true;
    protected $dir='DESC';

    const CACHE_TAG = 'block_reviewimage_reviews';

    public function _construct()
    {
        $this->setCacheTags(array(self::CACHE_TAG));
        /*
        * setting cache to save the rss for 10 minutes
        * 不同店铺，不同模板独立缓存。不需要区分是否登陆。
        */
        $url =  $_SERVER['REQUEST_URI'];
        $url .= '_'.Mage::app()->getStore()->getId()
        . '_' . Mage::getDesign()->getPackageName()
        . '_' . Mage::getDesign()->getTheme('template');
        //. '_' . Mage::getSingleton('customer/session')->getCustomerGroupId();
        $this->setCacheKey($url);
        $this->setCacheLifetime(600);
    }
    /**
     * 设置每页列表数
     * @param $perPage
     * @return $this
     */
    public function setPerPage($perPage){
        $this->perPage=$perPage;
        return $this;
    }

    /**
     * 获取每页列表数
     * @return int
     */
    public function getPerPage(){
        return $this->perPage;
    }

    /**
     * 是否要有图片的评论
     * @param true|false
     */
    public function setFilterImage($filterImage){
        $this->_filterImage = $filterImage;
        return $this;
    }

    public function getFilterImage(){
        return $this->_filterImage;
    }

    public function getDir(){
        return $this->dir;
    }

    public function setDir($dir){
         $this->dir=$dir;
        return $this;
    }

    /**
     * 获得商品id
     * @return int
     */
    public function getProductId(){
        if (Mage::registry('current_product')) {
            $_product = Mage::registry('current_product');
            if($_product && $_product->getId())$this->_productId = $_product->getId();
        }
        return $this->_productId;
    }

    /**
     * 获得当前分类id
     * @return int
     */
    public function getCatalogId(){
        if($this->_catalogId===null){
            if (Mage::registry('current_category')) {
                $_catalog = Mage::registry('current_category');
                if($_catalog && $_catalog->getId())$this->_catalogId = $_catalog->getId();
            }
        }
        return $this->_catalogId;
    }

    public function setCatalogId($catalogId){
        $this->_catalogId = $catalogId;
        return $this;
    }

    /**
     * 获得评论组
     * @return null
     * @throws Exception
     */
    public function getReviewCollection(){
        if (null === $this->_reviewsCollection) {
            $catalogId =(int) $this->getRequest()->getParam('cid',false);
            if($catalogId)$this->setCatalogId($catalogId);
            /** 分页计算 */
            $page =(int) $this->getRequest()->getParam('p',false);
            if(!$page || $page<1)$page=1;

            /** 取数据 **/
            if($productId = $this->getProductId()){
                $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                    ->addEntityFilter('product', $productId)
                    ->setPositionOrder()
                    ->setDateOrder()
                    ->setCurPage($page)
                    ->setPageSize($this->getPerPage());
            }else if($catalogId = $this->getCatalogId()){
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
                    ->addHomeFilter()
                    ->addHasImageFilter()
                    ->setPositionOrder($this->getDir())
                    ->setDateOrder($this->getDir())
                    ->setCurPage($page)
                    ->setPageSize($this->getPerPage());
            }
        }
        return $this->_reviewsCollection;
    }
    
    
    public function getCatalogReviewsUrl($category=null){
        return Mage::helper('reviewimage')->getCatalogReviewsUrl($category);
    }

}