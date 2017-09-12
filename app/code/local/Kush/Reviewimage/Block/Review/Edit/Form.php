<?php
/**
 * Review Edit Form
 * 前端独立的写评论方式
 * 如果有商品，商品sku不用填。否则填
 * 如果有分类，直接取分类id，否则，不填
 * 填名称/内容/[标题可以不填]
 * @category   Mage
 * @package    Mage_Review
 * @author      ado <114458573@qq.com>
 *
 */
class Kush_Reviewimage_Block_Review_Edit_Form extends Mage_Review_Block_Form
{

    public function __construct()
    {
        $customerSession = Mage::getSingleton('customer/session');

        parent::__construct();
        $productId = $this->getRequest()->getParam('id',false);
        if($productId){
            $product =  Mage::registry('current_product');
            if(!$product){
                $product = Mage::getModel('catalog/product')->load($productId);
                Mage::register('current_product', $product);
            }
        }

        $data =  Mage::getSingleton('review/session')->getFormData(true);
        $data = new Varien_Object($data);

        // add logged in customer name as nickname
        if (!$data->getNickname()) {
            $customer = $customerSession->getCustomer();
            if ($customer && $customer->getId()) {
                $data->setNickname($customer->getFirstname());
            }
        }

        $this->setAllowWriteReviewFlag($customerSession->isLoggedIn() || Mage::helper('review')->getIsGuestAllowToWrite());
        if (!$this->getAllowWriteReviewFlag) {
            $this->setLoginLink(
                Mage::getUrl('customer/account/login/', array(
                        Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME => Mage::helper('core')->urlEncode(
                            Mage::getUrl('*/*/*', array('_current' => true)) .
                            '#review-form')
                    )
                )
            );
        }
        $this->setTemplate('reviewimage/custom_form.phtml')
            ->assign('data', $data)
            ->assign('messages', Mage::getSingleton('review/session')->getMessages(true));

    }


    /**
     * @return product|null
     */
    public function getProductInfo()
    {
        $product = Mage::registry('current_product');
		// $product = Mage::getModel('catalog/product');
       //  $product->load($this->getRequest()->getParam('id'));
		 if($product && $product->getId()){
			 return $product;
		 }
		 return false;
      //  return $product;
    }
    /**
     * @return category|null
     */
    public function getCatalogInfo(){
        $category = Mage::registry('current_category');
        return $category;
    }

    /**
     * 不是商品的评论提交
     * @return string
     */
    public function getAction()
    {
        return Mage::getUrl('review/product/save');
    }

    public function getRatings()
    {
        /*
                $ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection()
                    ->addEntityFilter('product')
                    ->setPositionOrder()
                    ->addRatingPerStoreName(Mage::app()->getStore()->getId())
                    ->setStoreFilter(Mage::app()->getStore()->getId())
                    ->load()
                    ->addOptionToItems();
        */
        $ratingCollection = Mage::helper('reviewimage')->getCustomRatingCollection();

        return $ratingCollection;
    }
}
