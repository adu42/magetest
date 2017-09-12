<?php class Magebird_Popup_CouponController extends Mage_Core_Controller_Front_Action
{
    public function newAction()
    {
        $coupon_code = '';
        $popup = Mage::getModel('magebird_popup/popup')->load($this->getRequest()->getParam('popupId'));
        $widgetData = Mage::helper('magebird_popup')->getWidgetData($popup->getPopupContent(), $this->getRequest()->getParam('widgetId'));
        if (isset($widgetData['coupon_code']) && $widgetData['coupon_code']) {
            $coupon_code = $widgetData['coupon_code'];
        } elseif (isset($widgetData['rule_id'])) {
            $rule = Mage::getModel('salesrule/rule')->load($widgetData['rule_id']);
            if ($rule->getData('rule_id')) {
                $data = $widgetData;
                $data['cpnExpInherit'] = $this->getRequest()->getParam('cpnExpInherit');
                $coupon_code = Mage::helper('magebird_popup/coupon')->generateCoupon($data);
            }
        }
        if ($coupon_code && isset($widgetData['apply_coupon']) && $widgetData['apply_coupon'] == 1) {
            Mage::getSingleton("checkout/session")->setData("coupon_code", $coupon_code);
            Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon_code)->save();
        }
        $popup->setPopupData($popup->getData('popup_id'), 'goal_complition', $popup->getData('goal_complition') + 1);
        $result = json_encode(array('success' => 'success', 'coupon' => $coupon_code));
        $this->getResponse()->setBody($result);
    }
} ?>