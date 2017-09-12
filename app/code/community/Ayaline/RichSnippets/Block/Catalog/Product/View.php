<?php
/**
 * created : 23/02/2012
 * 
 * @category Ayaline
 * @package Ayaline_RichSnippets
 * @author aYaline
 * @copyright Ayaline - 2012 - http://magento-shop.ayaline.com
 * @license http://shop.ayaline.com/magento/fr/conditions-generales-de-vente.html
 */

/**
 * Product View block
 *
 * @category   Ayaline
 * @package    Ayaline_RichSnippets
 */
class Ayaline_RichSnippets_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
	protected $_config = null;
	
	protected function _prepareLayout() {
		parent::_prepareLayout();
		$this->_config = Mage::getSingleton('ayalinerichsnippets/config');
		
		$product = $this->getProduct();
		if (!$this->getProduct()->getRatingSummary()) {
            Mage::getModel('review/review')
               ->getEntitySummary($product, Mage::app()->getStore()->getId());
        }
        $this->setProduct($product);
        
		return $this;
	}
	
	/**
	 * @return Ayaline_RichSnippets_Model_Config
	 */
	protected function _getConfig(){
		return $this->_config;
	}
	
	/**
	 * Rich Snippets is enabled
	 * @return boolean
	 */
	public function isEnabled(){
		return $this->_getConfig()->isProductEnabled();
	}
	
	/**
	 * Product's image should be sent
	 * @return boolean
	 */
	public function canSendImage(){
		return $this->_getConfig()->canSendProductImage();
	}
	
	/**
	 * Product's description should be sent
	 * @return boolean
	 */
	public function canSendDescription(){
		return $this->_getConfig()->canSendProductDescription();
	}
	
	/**
	 * Product's brand should be sent
	 * @return boolean
	 */
	public function canSendBrand(){
		return $this->_getConfig()->canSendProductBrand();
	}
	
	/**
	 * Product's sku should be sent
	 * @return boolean
	 */
	public function canSendSku(){
		return $this->_getConfig()->canSendProductSku();
	}
	
	/**
	 * Product's category should be sent
	 * @return boolean
	 */
	public function canSendCategory(){
		return $this->_getConfig()->canSendProductCategory();
	}
	
	/**
	 * Return the first category
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCategory(){
		$_collectionCategory = $this->getProduct()->getCategoryCollection()
			->addAttributeToSelect('name')
			->addAttributeToFilter('level', '3')
		;
		if($_collectionCategory->getSize()){
			return $_collectionCategory->getFirstItem();
		}
		return false;
	}
	
	/**
	 * Product's review should be sent
	 * @return boolean
	 */
	public function canSendReview(){
		return $this->_getConfig()->canSendProductReview();
	}
	
	/**
	 * Review
	 */
    public function getRating()
    {
        $summary = $this->getProduct()->getRatingSummary()->getRatingSummary();
        $rating = 5 * $summary / 100;
        return $rating;
    }

    public function getFirstReview(){
		$collection = Mage::getModel('review/review')->getCollection()
				->addStoreFilter(Mage::app()->getStore()->getId())
				->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
				->addEntityFilter('product', $this->getProduct()->getId())
				->setDateOrder();
				
		if($collection->getSize()){
			return $collection->getFirstItem();
		}
		return false;
    }
    
    public function getReviewsCount() {
        return $this->getProduct()->getRatingSummary()->getReviewsCount();
    }
    
	public function getCleanDate($dateAndHour) {
		$date = new Zend_Date($dateAndHour, 'yyyy-MM-dd HH:mm:ss');
		return $date->get('yyyy-MM-dd');
	}
}
