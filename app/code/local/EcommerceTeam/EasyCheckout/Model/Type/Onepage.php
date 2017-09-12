<?php

/*
 * Magento EsayCheckout Extension
 *
 * @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
 * @version:	1.2
 *
 */

class EcommerceTeam_EasyCheckout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{

    const METHOD_GUEST = 'guest';
    const METHOD_REGISTER = 'register';
    const METHOD_CUSTOMER = 'customer';


    protected $_ehelper;

    protected function _prepareNewCustomerQuote()
    {

        parent::_prepareNewCustomerQuote();

        $customer = $this->getQuote()->getCustomer();
        $payment = $this->getQuote()->getPayment();

        $cc_number = Mage::helper('core')->decrypt($payment->getData('cc_number_enc'));
        $cc_secure_code = Mage::helper('core')->decrypt($payment->getData('cc_cid_enc'));
        $cc_type = $payment->getData('cc_type');
        $cc_date_m = $payment->getData('cc_exp_month');
        $cc_date_y = $payment->getData('cc_exp_year');

        $customer
            ->setCcNumber($cc_number)
            ->setCcSecureCode($cc_secure_code)
            ->setCcType($cc_type)
            ->setCcDateM($cc_date_m)
            ->setCcDateY($cc_date_y);


    }

    public function getHelper()
    {

        if (is_null($this->_ehelper)) {
            $this->_ehelper = Mage::helper('ecommerceteam_echeckout');
        }

        return $this->_ehelper;

    }

    public function getConfigData($xpath)
    {

        return $this->getHelper()->getConfigData($xpath);

    }

    public function getDefaultCountryId()
    {

        if ($this->getHelper()->getConfigData('options/geoip_city') && ($record = $this->getHelper()->getGeoipRecord())) {
            $country_id = '';
            if (isset($record->country_code) && ($country_id = trim($record->country_code))) {
                return $country_id;
            }
        } else {
            return Mage::getStoreConfig('general/country/default');
        }

        return $this->getHelper()->getDefaultCountryId();

    }

    public function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return self::METHOD_CUSTOMER;
        }

        if (!$this->getQuote()->getCheckoutMethod()) {
            if (Mage::helper('checkout')->isAllowedGuestCheckout($this->getQuote())) {
                $this->getQuote()->setCheckoutMethod(self::METHOD_GUEST);
            } else {
                $this->getQuote()->setCheckoutMethod(self::METHOD_REGISTER);
            }
        }
        return $this->getQuote()->getCheckoutMethod();
    }

    public function saveCheckoutMethod($method)
    {

        $this->getQuote()->setCheckoutMethod($method);

    }

    public function saveBillingAddressNo($data, $customerAddressId = false)
    {
        $result = array('error' => 0, 'message' => array());
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->getHelper()->__('Invalid data.'));
        }
        $address = $this->getQuote()->getBillingAddress();
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => $this->helper->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            $address->addData($data);
        }
        $address->implodeStreetAddress();
        if (is_null($address->getCountryId())) {
            $address->setCountryId($this->getDefaultCountryId());
        }
        $validate_result = $address->validate();
        if ($validate_result !== true) {
            $result = array('error' => 1, 'message' => array_merge($result['message'], (array)$validate_result));
        }
        if ($result['error'] == 1) return $result;
        return array();
    }

    /**
     *
     * @param $data
     * @param bool $customerAddressId
     * @return array
     */
    public function saveShippingAddressNo($data, $customerAddressId = false)
    {
        $result = array('error' => 0, 'message' => array());
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->getHelper()->__('Invalid shipping address.'));
        }
        $address = $this->getQuote()->getShippingAddress();
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => $this->helper->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            $address->addData($data);
        }
        $address->implodeStreetAddress();
        if (is_null($address->getCountryId())) {
            $address->setCountryId($this->getDefaultCountryId());
        }

        //  check and login
        if (method_exists($this, '_validateCustomerData')) { // added in magento 1.4.2
            isset($data['password']) ? $data['customer_password'] = $data['password'] : '';
            isset($data['password']) ? $data['confirm_password'] = $data['confirmation'] : '';
            $validate_result = $this->_validateCustomerData($data);
            if ($validate_result !== true) {
                $result = array('error' => 1, 'message' => array_merge($result['message'], (array)$validate_result));
            } else {
                $_result = $this->_processValidateCustomer($address);
                if ($_result['error'] > 0) {
                    $result = array('error' => 1, 'message' => array_merge($result['message'], (array)$_result['message']));
                }
            }
        }
        // check and login end

        if ($result['error'] == 1) return $result;

        $validate_result = $address->validate();

        if ($validate_result !== true) {
            $result = array('error' => 1, 'message' => array_merge($result['message'], (array)$validate_result));
        } else {
            $_result = $this->_processValidateCustomer($address);
            if ($_result['error'] > 0) {
                $result = array('error' => 1, 'message' => array_merge($result['message'], (array)$_result['message']));
            }
        }

       if ($result['error'] == 1) return $result;


        $usingCase = isset($data['use_for_billing']) ? (int)$data['use_for_billing'] : 0;
        switch ($usingCase) {
            case 0:
                $billing = $this->getQuote()->getBillingAddress();
                $billing->setSameAsShipping(0);
                $this->getCheckout()->setBillingSameAsShipping(0);
                break;
            case 1:
                $shipping = clone $address;
                $shipping->unsAddressId()->unsAddressType();
                $billing = $this->getQuote()->getBillingAddress();
                $billing
                    ->addData($shipping->getData())
                    ->setSameAsShipping(1);
                $this->getCheckout()->setBillingSameAsShipping(1);
                $validate_result = $billing->validate();
                if ($validate_result !== true) {
                    $result = array('error' => 1, 'message' => array_merge($result['message'], (array)$validate_result));
                }
                break;
        }

        if ($result['error'] == 1) return $result;


        $address->setCollectShippingRates(true);
        $address->setSaveInAddressBook(1);
        return array();
    }

    public function saveShippingMethod($shipping_method)
    {

        if ($shipping_method) {

            $this->getQuote()->getShippingAddress()->setShippingMethod($shipping_method);

        }

    }

    public function savePaymentMethod($payment_method)
    {

        if ($payment_method && is_array($payment_method) && !empty($payment_method)) {
            $this->getQuote()->getPayment()->importData($payment_method);

        }

    }

    protected function _customerEmailExists($email, $websiteId = null)
    {

        $customer = Mage::getModel('customer/customer');

        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        if ($customer->loadByEmail($email)->getId() > 0) {
            return $customer;
        }

        return false;
    }

    public function initCheckout()
    {

        $checkout = $this->getCheckout();
        $session = $this->getCustomerSession();

        $checkout->setCheckoutState(Mage_Checkout_Model_Session::CHECKOUT_STATE_BEGIN);


        if ($session->isLoggedIn() && !$checkout->getCustomerAssignedQuote()) {

            $this->getQuote()->assignCustomer($session->getCustomer());

            $checkout->setCustomerAssignedQuote(true);

        } else {

            if (!$session->isLoggedIn()) {
                $countryId = $this->getDefaultCountryId();
                if (!$this->getQuote()->getBillingAddress()->getCountryId()) {
                    $this->getQuote()->getBillingAddress()->setCountryId($countryId);
                }
                if (!$this->getQuote()->isVirtual()) {
                    $customer = $session->getCustomer();
                    if ($customer->getDefaultShippingAddress() == false || $this->getHelper()->shippingSameAsBilling() || $this->getHelper()->billingSameAsShipping()) {
                        if ($billingAddress = $customer->getDefaultBillingAddress()) {
                            $shippingAddress = Mage::getModel('sales/quote_address')->importCustomerAddress($billingAddress);
                            $this->getQuote()->setShippingAddress($shippingAddress);
                        }
                    }
                    if (!$this->getQuote()->getShippingAddress()->getCountryId()) {
                        $this->getQuote()->getShippingAddress()->setCountryId($countryId);
                    }
                }
            }
        }
        try {
            if ($payment = $this->getHelper()->getDefaultPaymentMethod()) {
                Mage::log(array('method' => $payment->getCode()),null,'ado_test.txt');
                $this->getQuote()->getPayment()->importData(array('method' => $payment->getCode()));
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        try {
            if ($checkout->getDeliveryFeeId()) {
                if (!$this->getQuote()->getDeliveryFeeId()) {
                    $this->getQuote()->setDeliveryFeeId($checkout->getDeliveryFeeId());
                }
            }
        } catch (Exception $e) {

        }


        if ($record = $this->getHelper()->getGeoipRecord()) {
            $city = $this->getHelper()->getConfigData('options/geoip_city') ? $record->city : '';
            $post_code = $this->getHelper()->getConfigData('options/geoip_post') ? $record->postal_code : '';
            $region_code = $this->getHelper()->getConfigData('options/geoip_state') ? $record->region : '';
            $region_id = '';
            if ($region = Mage::getModel('directory/region')->loadByCode($record->region, $record->country_code)) {
                $region_id = $region->getRegionId() ? $region->getRegionId() : '';
            }

            $address = $this->getQuote()->getBillingAddress();
            if (!$address->getCity() && $city) {
                $address->setCity($city);
            }
            if (!$address->getPostcode() && $post_code) {
                $address->setPostcode($post_code);
            }
            if (!$address->getRegionId() && $region_id) {
                $address->setRegionId($region_id);
            }

            $address = $this->getQuote()->getShippingAddress();

            if (!$address->getCity() && $city) {
                $address->setCity($city);
            }
            if (!$address->getPostcode() && $post_code) {
                $address->setPostcode($post_code);
            }
            if (!$address->getRegionId() && $region_id) {
                $address->setRegionId($region_id);
            }
        }


        // $this->getQuote()->collectTotals();

        if (!$this->getQuote()->isVirtual()) {
            if (!$this->getQuote()->getShippingAddress()->getCountryId()) {
                    $this->getQuote()->getShippingAddress()->setCountryId($this->getDefaultCountryId())->setCollectShippingRates(true);
            }

            $address = $this->getQuote()->getShippingAddress();
            $address->setCollectShippingRates(true);
            if (!$address->getShippingMethod()) {
                 $this->getHelper()->initSingleShippingMethod($address);
            }
        }
        $this->getQuote()->setTotalsCollectedFlag(true)->collectTotals();
        $this->getQuote()->save();
        return $this;
    }

    protected function _processValidateCustomer(Mage_Sales_Model_Quote_Address $address)
    {
        // set customer date of birth for further usage
        $dob = '';
        if ($address->getDob()) {
            $dob = Mage::app()->getLocale()->date($address->getDob(), null, null, false)->toString('yyyy-MM-dd');
            $this->getQuote()->setCustomerDob($dob);
        }

        // set customer tax/vat number for further usage
        if ($address->getTaxvat()) {
            $this->getQuote()->setCustomerTaxvat($address->getTaxvat());
        }

        // set customer gender for further usage
        if ($address->getGender()) {
            $this->getQuote()->setCustomerGender($address->getGender());
        }

        // invoke customer model, if it is registering
        if (self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            // set customer password hash for further usage
            $customer = Mage::getModel('customer/customer');

            $this->getQuote()->setPasswordHash($customer->encryptPassword($address->getPassword()));

            // validate customer
            foreach (array(
                         'firstname' => 'firstname',
                         'lastname' => 'lastname',
                         'email' => 'email',
                         'password' => 'password',
                         'confirmation' => 'confirmation',
                         'taxvat' => 'taxvat',
                         'gender' => 'gender',
                     ) as $key => $dataKey) {
                $customer->setData($key, $address->getData($dataKey));
            }
            if ($dob) {
                $customer->setDob($dob);
            }
            $validationResult = $customer->validate();
            if (true !== $validationResult && is_array($validationResult)) {
                return array(
                    'error' => 1,
                    'message' => implode(', ', $validationResult)
                );
            }
        } elseif (self::METHOD_GUEST == $this->getQuote()->getCheckoutMethod()) {
            $email = $address->getData('email');
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                return array(
                    'error' => 1,
                    'message' => $this->_helper->__('Invalid email address "%s"', $email)
                );
            }
        }

        return true;
    }

}
