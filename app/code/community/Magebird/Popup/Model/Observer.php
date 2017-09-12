<?php class Magebird_Popup_Model_Observer
{
    public function updatePopupSession(Varien_Event_Observer $observer)
    {
        if ($observer->getData('event')->getName() != 'customer_logout') {
            $quote = Mage::getModel('checkout/cart')->getQuote();
            $productIds = array();
            $i = 0;
            foreach ($quote->getAllItems() as $item) {
                if ($i > 20) break;
                $productIds[] = $item->getProduct()->getId();
                $i++;
            }
            $productIds = implode(",", $productIds);
            $baseSubtotal = Mage::helper('magebird_popup')->getBaseSubtotal();
        } else {
            $productIds = '';
            $baseSubtotal = 0;
        }
        $cookies[] = array('cookieName' => 'cartSubtotal', 'value' => $baseSubtotal, 'expired' => false);
        $cookies[] = array('cookieName' => 'cartProductIds', 'value' => $productIds, 'expired' => false);
        $subscriberd = false;
        if ($observer->getData('event')->getName() == 'customer_logout') {
            $cookies[] = array('cookieName' => 'customerGroupId', 'value' => 0, 'expired' => false);
            $cookies[] = array('cookieName' => 'loggedIn', 'value' => 0, 'expired' => false);
        } elseif (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $cookies[] = array('cookieName' => 'loggedIn', 'value' => '1', 'expired' => false);
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $cookies[] = array('cookieName' => 'customerGroupId', 'value' => $customerGroupId, 'expired' => false);
            $email = Mage::getSingleton('customer/session')->getCustomer()->getData('email');
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            if ($subscriber->getId()) {
                $subscriberd = $subscriber->getData('subscriber_status') == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;
            }
        } else {
            $cookies[] = array('cookieName' => 'customerGroupId', 'value' => 0, 'expired' => false);
            $cookies[] = array('cookieName' => 'loggedIn', 'value' => 0, 'expired' => false);
        }
        if ($observer->getData('event')->getName() == 'checkout_cart_save_after' && !Mage::helper('magebird_popup')->getPopupCookie('cartAddedTime')) {
            $cookies[] = array('cookieName' => 'cartAddedTime', 'value' => Mage::getModel('core/date')->timestamp(time()), 'expired' => time() + 7200);
        }
        $cookies[] = array('cookieName' => 'isSubscribed', 'value' => $subscriberd, 'expired' => false);
        $cookies[] = array('cookieName' => 'pendingOrder', 'value' => $this->checkPendingOrder(), 'expired' => false);
        $cookies[] = array('cookieName' => 'magentoSessionId', 'value' => '', 'expired' => false);
        Mage::helper('magebird_popup')->setPopupMultiCookie($cookies);
    }

    public function newOrder(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $order_ids = $event->getData('order_ids');
        $orderId = intval($order_ids[0]);
        $coupon_code = Mage::getModel('sales/order')->load($orderId)->getData('coupon_code');
        $is_popup = Mage::getModel('salesrule/coupon')->load($coupon_code, 'code')->getData('is_popup');
        if (!$is_popup) {
            $lastCoupon = Mage::helper('magebird_popup')->getPopupCookie('lastCoupon');
            $lastCoupon = explode("-", $lastCoupon);
            if (isset($lastCoupon[1]) && $lastCoupon[1] == $coupon_code) {
                $is_popup = $lastCoupon[0];
            }
        }
        $core_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        if ($is_popup) {
            $table_magebird_popup_orders = Mage::getSingleton("core/resource")->getTableName('magebird_popup_orders');
            $is_popup = intval($is_popup);
            $sql = "INSERT IGNORE INTO `{$table_magebird_popup_orders}` (popup_id,order_id) VALUES ($is_popup,$orderId)";
            $core_write->query($sql);
        }
        $lastPopups = Mage::helper('magebird_popup')->getPopupCookie('lastPopups');
        $lastPopups = explode(",", $lastPopups);
        $table_magebird_popup_stats = Mage::getSingleton("core/resource")->getTableName('magebird_popup_stats');
        foreach ($lastPopups as $is_popup) {
            $is_popup = intval($is_popup);
            $sql = "UPDATE $table_magebird_popup_stats SET popup_purchases=popup_purchases+1 WHERE popup_id=$is_popup";
            $core_write->query($sql);
        }
        $sql = "UPDATE $table_magebird_popup_stats SET purchases=purchases+1";
        $core_write->query($sql);
    }

    public function checkPendingOrder()
    {
        $sessionid = Mage::getSingleton('customer/session')->getId();
        if (!$sessionid) return 0;
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('status', 'pending')->addFieldToFilter('customer_id', $sessionid)->getSelect()->limit(1);
        if (count($collection)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function deleteExpired(Varien_Event_Observer $observer)
    {
        $coupon = $observer->getData('quote')->getData('coupon_code');
        if ($coupon) {
            $collection = Mage::getModel('salesrule/coupon')->getCollection();
            $collection->addFieldToFilter('expiration_date', array('notnull' => true));
            $collection->addFieldToFilter('expiration_date', array('to' => date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())), 'datetime' => true));
            $collection->addFieldToFilter('is_popup', 1);
            $collection->getSelect()->where("(`times_used` = 0) OR (`user_ip` IS NULL) \r\n          OR `expiration_date`< '" . date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time() - (60 * 60 * 24 * 365))) . "'");
            foreach ($collection as $_coupon) {
                $_coupon->delete();
            }
        }
    }

    public function sendCoupon(Varien_Event_Observer $observer)
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        if (strpos($currentUrl, 'subscriber/confirm') !== false) {
            $url_parts = explode("confirm/id/", $currentUrl);
            $url_parts = explode("/code/", $url_parts[1]);
            $id = $url_parts[0];
            $subscriber = Mage::getModel('newsletter/subscriber')->load($id);
            $email = $subscriber->getData('subscriber_email');
            $collection = Mage::getModel('magebird_popup/subscriber')->getCollection();
            $collection->addFieldToFilter('subscriber_email', $email);
            $subscriberData = $collection->getLastItem()->getData();
            if ($subscriberData) {
                if (isset($subscriberData['cart_rule_id'])) {
                    $subscriberData['rule_id'] = $subscriberData['cart_rule_id'];
                }
                $coupon = $subscriberData['coupon_code'];
                if (!$coupon && isset($subscriberData['rule_id']) && $subscriberData['rule_id']) {
                    $coupon = Mage::helper('magebird_popup/coupon')->generateCoupon($subscriberData);
                }
                if ($subscriberData['apply_coupon'] == 1) {
                    Mage::getSingleton("checkout/session")->setData("coupon_code", $coupon);
                    Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon)->save();
                }
                if ($subscriberData['send_coupon'] == 1) {
                    Mage::getModel('magebird_popup/subscriber')->mailCoupon($email, $coupon);
                }
                Mage::getModel('magebird_popup/subscriber')->cleanOldEmails();
                $session = Mage::getSingleton('core/session');
                $session->addSuccess(Mage::helper('core')->__('Your coupon code is:') . " " . $coupon);
                Mage::getModel('magebird_popup/subscriber')->deleteTempSubscriber($email);
            }
        }
    }

    public function applyCoupon(Varien_Event_Observer $observer)
    {
        $coupon_code = trim(Mage::getSingleton("checkout/session")->getData("coupon_code"));
        if ($coupon_code) {
            Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon_code)->save();
        }
        $this->cartAdded();
    }

    public function cartAdded()
    {
        if (Mage::helper('checkout/cart')->getItemsCount()) return;
        if (!Mage::helper('magebird_popup')->getPopupCookie('cartAdded')) {
            Mage::helper('magebird_popup')->setPopupCookie('cartAdded', 1, time() + 10800);
            $table_magebird_popup_stats = Mage::getSingleton("core/resource")->getTableName('magebird_popup_stats');
            $core_write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = "UPDATE $table_magebird_popup_stats SET total_carts=total_carts+1";
            $core_write->query($sql);
            $lastPopups = Mage::helper('magebird_popup')->getPopupCookie('lastPopups');
            $lastPopups = explode(",", $lastPopups);
            foreach ($lastPopups as $is_popup) {
                $is_popup = intval($is_popup);
                $sql = "UPDATE $table_magebird_popup_stats SET popup_carts=popup_carts+1 WHERE popup_id=$is_popup";
                $core_write->query($sql);
            }
        }
    }
} ?>