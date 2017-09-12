<?php class Magebird_Popup_UserController extends Mage_Core_Controller_Front_Action
{
    public function loginAction()
    {
        $data = $this->getRequest()->getParams();
        $customerSession = Mage::getSingleton('customer/session');
        try {
            $customerSession->login($data['login_email'], $data['login_password']);
            $customer = $customerSession->getCustomer();
            $customerSession->setCustomerAsLoggedIn($customer);
            $result = json_encode(array('success' => 'success'));
            $this->getResponse()->setBody($result);
        } catch (Exception $e) {
            $result['exceptions'][] = 'Wrong login';
            $result = json_encode($result);
            $this->getResponse()->setBody($result);
        }
    }

    public function registerAction()
    {
        $popup = Mage::getModel('magebird_popup/popup')->load($this->getRequest()->getParam('popupId'));
        $widgetData = Mage::helper('magebird_popup')->getWidgetData($popup->getPopupContent(), $this->getRequest()->getParam('widgetId'));
        $params = $this->getRequest()->getParams();
        $data = array_merge($widgetData, $params);
        $data['cpnExpInherit'] = $this->getRequest()->getParam('cpnExpInherit');
        $dyoc6UghQUr = false;
        $enablemagento = Mage::getStoreConfig('magebird_popup/services/enablemagento');
        $email = (string)$this->getRequest()->getParam('email');
        $first_name = $this->getRequest()->getParam('first_name');
        $last_name = $this->getRequest()->getParam('last_name');
        $aQ8amXOwGXb = false;
        if (isset($data['newsletter_option']) && $data['newsletter_option'] == 1) $data['is_subscribed'] = 1;
        $result = array();
        $customerSession = Mage::getSingleton('customer/session');
        $customerSession->setEscapeMessages(true);
        if ($this->getRequest()->isPost() || $this->getRequest()->isGet()) {
            $result = array();
            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }
            $customer_form = Mage::getModel('customer/form');
            $customer_form->setFormCode('customer_account_create')->setEntity($customer);
            $formData = $customer_form->extractData($this->getRequest());
            if (isset($data['is_subscribed']) && $data['is_subscribed'] && $data['email']) {
                if ($enablemagento) {
                    $customer->setIsSubscribed(1);
                }
            }
            $customer->getGroupId();
            try {
                $validateData = $customer_form->validateData($formData);
                if ($validateData !== true) {
                    $result = array_merge($validateData, $result);
                } else {
                    $customer_form->compactData($formData);
                    $customer->setPassword($this->getRequest()->getParam('password'));
                    $customer->setConfirmation($this->getRequest()->getParam('password'));
                    $customer->setPasswordConfirmation($this->getRequest()->getParam('password'));
                    $validateData = $customer->validate();
                    if (is_array($validateData)) {
                        $result = array_merge($validateData, $result);
                    }
                    if ($this->getRequest()->getPost('create_address')) {
                        $addAddressResult = $this->addAddress($customer);
                        if (!empty($addAddressResult)) {
                            $result = array_merge($addAddressResult, $result);
                        }
                    }
                }
                if (count($result) == 0) {
                    $customer->save();
                    Mage::dispatchEvent('customer_register_success', array('account_controller' => $this, 'customer' => $customer));
                    $coupon = '';
                    $data['coupon_option'] = isset($data['coupon_option']) ? $data['coupon_option'] : null;
                    if ($data['coupon_option'] == 1 || ($data['coupon_option'] == 2 && isset($data['is_subscribed']) && $data['is_subscribed'])) {
                        if (isset($data['coupon_code']) && $data['coupon_code']) {
                            $coupon = $data['coupon_code'];
                        } elseif (isset($data['rule_id']) && $data['rule_id']) {
                            $rule = Mage::getModel('salesrule/rule')->load($data['rule_id']);
                            $coupon = Mage::helper('magebird_popup/coupon')->generateCoupon($data);
                        }
                    }
                    if (isset($data['send_coupon']) && $data['send_coupon'] == 1 && $coupon) {
                        Mage::getModel('magebird_popup/subscriber')->mailCoupon($email, $coupon);
                    }
                    if (isset($data['apply_coupon']) && $data['apply_coupon'] == 1) {
                        Mage::getSingleton("checkout/session")->setData("coupon_code", $coupon);
                        Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon)->save();
                    }
                    /**
                     *  //不做第三方订阅
                     *   //by@ado
                     *   $this->subscribeNewsletter($data, $coupon);
                     */


                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail('confirmation', $customerSession->getBeforeAuthUrl(), Mage::app()->getStore()->getId());
                    } else {
                        $customerSession->setCustomerAsLoggedIn($customer);
                        $customer->sendNewAccountEmail('registered', '', Mage::app()->getStore()->getId());
                    }
                    $popup->setPopupData($popup->getData('popup_id'), 'goal_complition', $popup->getData('goal_complition') + 1);
                    $result = json_encode(array('success' => 'success', 'coupon' => $coupon));
                    $this->getResponse()->setBody($result);
                    return;
                } else {
                    if (is_array($result)) {
                        foreach ($result as $message) {
                            $result['exceptions'][] = $message;
                        }
                    } else {
                        $result['exceptions'][] = 'Invalid customer data';
                    }
                }
            } catch (Mage_Core_Exception $e) {
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $forgotpasswordUrl = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. <a href="%s">Click here</a> to get your password and access your account.', $forgotpasswordUrl);
                    $customerSession->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $result['exceptions'][] = $message;
                }
            } catch (Exception $e) {
                $result['exceptions'][] = 'Cannot save the customer.';
            }
        }
        $result = json_encode($result);
        $this->getResponse()->setBody($result);
    }

    public function subscribeNewsletter($data, $coupon)
    {   return;
        $mailchimp_list_id = isset($data['mailchimp_list_id']) ? $data['mailchimp_list_id'] : '';
        $gr_campaign_token = isset($data['gr_campaign_token']) ? $data['gr_campaign_token'] : '';
        $cm_list_id = isset($data['cm_list_id']) ? $data['cm_list_id'] : '';
        $mailchimp_option = Mage::getStoreConfig('magebird_popup/services/mailchimp_option');
        $mailchimp = Mage::getStoreConfig('magebird_popup/services/enablemailchimp');
        $campaignMonitor = Mage::getStoreConfig('magebird_popup/services/enablecampaignmonitor');
        $getResponse = Mage::getStoreConfig('magebird_popup/services/enablegetresponse');
        if ($mailchimp_list_id && $mailchimp) {
            $subscriber = Mage::getModel('magebird_popup/subscriber')->subscribeMailchimp($mailchimp_list_id, $data['email'], $data['firstname'], $data['lastname'], $coupon);
            if ($subscriber->errorCode) {
                $result['exceptions'][] = $subscriber->errorMessage;
                $result = json_encode($result);
                return $result;
            }
        }
        if ($cm_list_id && $campaignMonitor) {
            $subscriber = Mage::getModel('magebird_popup/subscriber')->subscribeCampaignMonitor($cm_list_id, $data['email'], $data['firstname'], $data['lastname'], $coupon);
            if (!$subscriber->was_successful()) {
                $result['exceptions'][] = 'Failed with code ' . $subscriber->http_status_code;
                $result = json_encode($result);
                $this->getResponse()->setBody($result);
                return $result;
            }
        }
        if ($gr_campaign_token && $getResponse) {
            $subscriber = Mage::getModel('magebird_popup/subscriber')->subscribeGetResponse($gr_campaign_token, $data['email'], $data['firstname'], $data['lastname'], $coupon);
            if (isset($subscriber->errorCode) && $subscriber->errorCode) {
                $result['exceptions'][] = $subscriber->errorMessage;
                $result = json_encode($result);
                $this->getResponse()->setBody($result);
                return $result;
            }
        }
        return '';
    }

    public function addAddress($customer)
    {

        $address = Mage::getModel('customer/address');
        $customer_form = Mage::getModel('customer/form');
        $customer_form->setFormCode('customer_register_address')->setEntity($address);
        $formData = $customer_form->extractData($this->getRequest(), 'address', false);
        $addAddressResult = $customer_form->validateData($formData);
        if (is_array($addAddressResult)) {
            $result = $addAddressResult;
        }
        $address->setId(null)->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
        $customer_form->compactData($formData);
        $customer->addAddress($address);
        $addAddressResult = $address->validate();
        $result = array();
        if (is_array($addAddressResult)) {
            $result = array_merge($result, $addAddressResult);
        }
        return $result;
    }
} ?>