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
 * Rich Snippets config model
 *
 * @category   Ayaline
 * @package    Ayaline_RichSnippets
 */
class Ayaline_RichSnippets_Model_Config {

	protected $_store = null;

	const XML_PATH_PRODUCT_ENABLED 				= 'ayalinerichsnippets/product/enabled';
	const XML_PATH_PRODUCT_ENABLED_IMAGE 		= 'ayalinerichsnippets/product/image';
	const XML_PATH_PRODUCT_ENABLED_DESCRIPTION 	= 'ayalinerichsnippets/product/description';
	const XML_PATH_PRODUCT_ENABLED_BRAND	 	= 'ayalinerichsnippets/product/brand';
	const XML_PATH_PRODUCT_ENABLED_SKU	 		= 'ayalinerichsnippets/product/sku';
	const XML_PATH_PRODUCT_ENABLED_CATEGORY		= 'ayalinerichsnippets/product/category';
	const XML_PATH_PRODUCT_ENABLED_REVIEW		= 'ayalinerichsnippets/product/review';
	
	public function isProductEnabled($store = null){
		return Mage::getStoreConfigFlag(self::XML_PATH_PRODUCT_ENABLED, $store);
	}
	
	public function canSendProductImage($store = null){
		return Mage::getStoreConfigFlag(self::XML_PATH_PRODUCT_ENABLED_IMAGE, $store);
	}
	
	public function canSendProductDescription($store = null){
		return Mage::getStoreConfigFlag(self::XML_PATH_PRODUCT_ENABLED_DESCRIPTION, $store);
	}

	public function canSendProductBrand($store = null){
		return Mage::getStoreConfigFlag(self::XML_PATH_PRODUCT_ENABLED_BRAND, $store);
	}
	
	public function canSendProductSku($store = null){
		return Mage::getStoreConfigFlag(self::XML_PATH_PRODUCT_ENABLED_SKU, $store);
	}
	
	public function canSendProductCategory($store = null){
		return Mage::getStoreConfigFlag(self::XML_PATH_PRODUCT_ENABLED_CATEGORY, $store);
	}
	
	public function canSendProductReview($store = null){
		return Mage::getStoreConfigFlag(self::XML_PATH_PRODUCT_ENABLED_REVIEW, $store);
	}
}