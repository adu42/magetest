<?php

/*
 * Magento EsayCheckout Extension
 *
 * @copyright:	EcommerceTeam (http://www.ecommerce-team.com)
 * @version:	1.2
 *
 */

class EcommerceTeam_EasyCheckout_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Abstract{
    protected $_rates;
    protected $_address;

    protected function _construct()
    {
        parent::_construct();
        $this->addData(array(
            'cache_lifetime'=> false,
            'cache_tags'    => array(Mage_Core_Model_Store::CACHE_TAG, 'EcommerceTeam_EasyCheckout_Block_Onepage_Shipping_Method_Available')
        ));
    }

    public function getShippingRates()
    {

        if (empty($this->_rates)) {

            $this->getAddress()->collectShippingRates()->save();
            return $this->_rates = $this->getAddress()->getGroupedAllShippingRates();
        }

        return $this->_rates;
    }

    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }

    public function getAddressShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
    }
}
