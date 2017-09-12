<?php class Magebird_Popup_Block_Review extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    protected function _construct()
    {
        $this->_serializer = new Varien_Object();
        $customerSession = Mage::getSingleton('customer/session');
        parent::_construct();
        $reviewData = Mage::getSingleton('review/session')->getFormData(true);
        $reviewData = new Varien_Object($reviewData);
        if (!$reviewData->getNickname()) {
            $customer = $customerSession->getCustomer();
            if ($customer && $customer->getId()) {
                $reviewData->setNickname($customer->getFirstname());
            }
        }
        $this->setAllowWriteReviewFlag($customerSession->isLoggedIn() || Mage::helper('review')->getIsGuestAllowToWrite());
        if (!$this->getAllowWriteReviewFlag) {
            $this->setLoginLink(Mage::getUrl('customer/account/login/', array(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME => Mage::helper('core')->urlEncode(Mage::getUrl('*/*/*', array('_current' => true)) . '#review-form'))));
        }
        $this->setTemplate($this->getData('template'))->assign('data', $reviewData)->assign('messages', Mage::getSingleton('review/session')->getMessages(true));
    }

    public function getProduct()
    {
        if ($this->getRequest()->getParam('url') && strpos($this->getRequest()->getParam('url'), 'popupProductId') !== false) {
            $url = $this->getRequest()->getParam('url');
            $url_query = parse_url($url, PHP_URL_QUERY);
            parse_str($url_query, $query);
            $popupProductId = $query['popupProductId'];
        } elseif (Mage::registry('current_product')) {
            $popupProductId = $this->getRequest()->getParam('id');
        } elseif ($this->getRequest()->getParam('popup_page_id') == 2 && $this->getRequest()->getParam('filterId')) {
            $popupProductId = $this->getRequest()->getParam('filterId');
        } else {
            return false;
        }
        $product = Mage::getModel('catalog/product')->load($popupProductId);
        return $product;
    }

    public function getFormAction()
    {
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $url = Mage::getUrl('magebird_popup/review/submit', array('_forced_secure' => true));
        } else {
            $url = Mage::getUrl('magebird_popup/review/submit');
        }
        return $url;
    }

    public function getRatings()
    {
        $ratings = Mage::getModel('rating/rating')->getResourceCollection()->addEntityFilter('product')->setPositionOrder()->addRatingPerStoreName(Mage::app()->getStore()->getId())->setStoreFilter(Mage::app()->getStore()->getId())->load()->addOptionToItems();
        return $ratings;
    }

    protected function brightness($color, $increment)
    {
        $color = str_replace('#', '', $color);
        $r = substr($color, 0, 2);
        $g = substr($color, 2, 2);
        $b = substr($color, 4, 2);
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        $r = max(0, min(255, $r + $increment));
        $g = max(0, min(255, $g + $increment));
        $b = max(0, min(255, $b + $increment));
        $dr = dechex($r);
        if (strlen($dr) == 1) $dr = "0" . $dr;
        $dg = dechex($g);
        if (strlen($dg) == 1) $dg = "0" . $dg;
        $db = dechex($b);
        if (strlen($db) == 1) $db = "0" . $db;
        return '#' . $dr . $dg . $db;
    }
} ?>