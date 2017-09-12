<?php class Magebird_Popup_ReviewController extends Mage_Core_Controller_Front_Action
{
    protected $yLgzTwCwvYI = array('post');

    public function preDispatch()
    {
        parent::preDispatch();
        $isGuestAllowToWrite = Mage::helper('review')->getIsGuestAllowToWrite();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }
        $actionName = $this->getRequest()->getActionName();
        if (!$isGuestAllowToWrite && $actionName == 'post' && $this->getRequest()->isPost()) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
                Mage::getSingleton('review/session')->setFormData($this->getRequest()->getPost())->setRedirectUrl($this->_getRefererUrl());
                $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
            }
        }
        return $this;
    }

    public function submitAction()
    {
        $result = array();
        $params = $this->getRequest()->getPost();
        $rating = $this->getRequest()->getParam('ratings', array());
        $productId = (int)$this->getRequest()->getParam('productId');
        $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load($productId);
        $coupon = isset($params['coupon'])?$params['coupon']:'';
        if (!$product->getId()) {
            $result['exceptions'][] = $this->__('Missing product.');
        } elseif (empty($params['title']) || empty($params['nickname']) || empty($params['detail'])) {
            $result['exceptions'][] = $this->__('Missing review data.');
        } elseif (sizeof($rating) < 1) {
            $result['exceptions'][] = $this->__('Missing review.');
        } else {
            $review = Mage::getModel('review/review')->setData($params);
            try {
                $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))->setEntityPkValue($product->getId())->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())->setStoreId(Mage::app()->getStore()->getId())->setStores(array(Mage::app()->getStore()->getId()))->save();
                foreach ($rating as $ratingId => $ratingValue) {
                    Mage::getModel('rating/rating')->setRatingId($ratingId)->setReviewId($review->getId())->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())->addOptionVote($ratingValue, $product->getId());
                }
                $review->aggregate();
                $result = json_encode(array('success' => 'success', 'coupon' => $coupon));
                $this->getResponse()->setBody($result);
                return;
            } catch (Exception $e) {
                $result['exceptions'][] = $this->__('Unable to post the review.');
            }
        }
        $result = json_encode($result);
        $this->getResponse()->setBody($result);
    }
} ?>