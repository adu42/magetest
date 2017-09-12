<?php class Magebird_Popup_Helper_Coupon extends Mage_Core_Helper_Abstract
{
    public function generateCoupon($data)
    {
        $rule = Mage::getModel('salesrule/rule')->load($data['rule_id']);
        $coupon = $rule->getCouponMassGenerator();
        $data['format'] = 'alphanum';
        $data['length'] = isset($data['coupon_length']) ? $data['coupon_length'] : 12;
        $data['qty'] = 1;
        $data['prefix'] = isset($data['coupon_prefix']) ? $data['coupon_prefix'] : '';
        $data['rule_id'] = isset($data['rule_id']) ? $data['rule_id'] : '';
        $data['uses_per_coupon'] = 1;
        $data['uses_per_customer'] = 1;
        $data['coupon_expiration'] = isset($data['coupon_expiration']) ? $data['coupon_expiration'] : '';
        $data['cpnExpInherit'] = isset($data['cpnExpInherit']) ? $data['cpnExpInherit'] : '';
        $data['expiration_date'] = isset($data['expiration_date']) ? $data['expiration_date'] : '';
        if (isset($data['user_ip'])) {
            $ip = $data['user_ip'];
        } elseif (isset($data['coupon_limit_ip']) && $data['coupon_limit_ip'] == 1) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        }
        if (!$coupon->validateData($data)) {
            $result['error'] = Mage::helper('salesrule')->__('Not valid data provided');
        } else {
            $coupon->setData($data);
            $coupon->generatePool();
            $collection = Mage::getResourceModel('salesrule/coupon_collection')->addRuleToFilter($rule)->addGeneratedCouponsFilter();
            $coupon_rule = $collection->getLastItem();
            if (($data['coupon_expiration'] && $data['coupon_expiration'] != 'inherit') || ($data['coupon_expiration'] == 'inherit' && $data['cpnExpInherit'])) {
                if ($data['coupon_expiration'] == 'inherit') {
                    $expirationDate = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()) + $data['cpnExpInherit']);
                } else {
                    $expirationDate = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()) + ($data['coupon_expiration'] * 60));
                }
                $coupon_rule->setExpirationDate($expirationDate);
            } elseif ($data['expiration_date']) {
                $coupon_rule->setExpirationDate($data['expiration_date']);
            }
            $coupon_rule->setUserIp($ip);
            if (isset($data['popup_cookie_id'])) {
                $coupon_rule->setPopupCookieId($data['popup_cookie_id']);
            }
            $popupId = Mage::app()->getRequest()->getParam('popupId');
            if ($popupId) {
                $coupon_rule->setIsPopup($popupId);
            } else {
                $coupon_rule->setIsPopup(5000);
            }
            $coupon_rule->save();
            $coupon_code = $coupon_rule->getData('code');
        }
        return $coupon_code;
    }
} ?>