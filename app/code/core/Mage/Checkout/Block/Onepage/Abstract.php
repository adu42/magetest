<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * One page common functionality block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Checkout_Block_Onepage_Abstract extends Mage_Core_Block_Template
{
    protected $_customer;
    protected $_checkout;
    protected $_quote;
    protected $_countryCollection;
    protected $_regionCollection;
    protected $_addressesCollection;

    /**
     * Get logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (empty($this->_customer)) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }

    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    /**
     * Retrieve sales quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }

    public function getRegionCollection()
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter($this->getAddress()->getCountryId())
                ->load();
        }
        return $this->_regionCollection;
    }

    public function customerHasAddresses()
    {
        return count($this->getCustomer()->getAddresses());
    }

/* */
    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }

    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::helper('core')->getDefaultCountry();
        }
        $countryOptions=$this->getSortOptions($this->getCountryOptions(),$countryId);
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($countryOptions);
        if ($type === 'shipping') {
            $select->setExtraParams('onchange="if(window.shipping)shipping.setSameAsBilling(false);"');
        }

        return $select->getHtml();
    }


    public function getRegionHtmlSelect($type)
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[region]')
            ->setId($type.':region')
            ->setTitle(Mage::helper('checkout')->__('State/Province'))
            ->setClass('required-entry validate-state')
            ->setValue($this->getAddress()->getRegionId())
            ->setOptions($this->getRegionCollection()->toOptionArray());

        return $select->getHtml();
    }

    public function getCountryOptions()
    {
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

    /**
     * Get checkout steps codes
     *
     * @return array
     */
    protected function _getStepCodes()
    {
        if(Mage::helper('checkout')->enableTheme()){
            return array('login', 'billing', 'shipping', 'shipping_method', 'payment', 'review');
        }
        return array('login', 'billing', 'shipping', 'shipping_method', 'payment', 'review');
    }


    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShow()
    {
        return true;
    }

    /**
     * @排序
     * by@ado
     */
    public function getSortOptions($options,$countryId){
        // @file_put_contents(dirname(__FILE__).'/aa.txt',$countryId.print_r($options,true)."\n",FILE_APPEND);
        $countryDefault=array(
            'SE' => array(
                'SE',
                'AU',
                'CA',
                'FR',
                'DE',
                'GB',
                'US',
            ),
            'DA' => array(
                'DA',
                'AU',
                'CA',
                'FR',
                'DE',
                'GB',
                'US',
            ),
            'DE' => array(
                'DE',
                'AT',
                'CH',
                'CZ',
                'DK',
                'NL',
                'NO',
                'PL',
                'SI',
                'SE',
            ),
            'AT' => array(
                'AT',
                'CZ',
                'DK',
                'DE',
                'NL',
                'NO',
                'PL',
                'SI',
                'SE',
                'CH',
            ),
            'US' => array(
                'US',
                'AU',
                'BR',
                'CA',
                'FR',
                'DE',
                'IT',
                'ES',
                'CH',
                'GB',
            ),
            'AU' => array(
                'AU',
                'BR',
                'CA',
                'FR',
                'DE',
                'IT',
                'ES',
                'CH',
                'GB',
                'US',
            ),
            'UK' => array(
                'GB',
                'AU',
                'BR',
                'CA',
                'FR',
                'DE',
                'IT',
                'ES',
                'CH',
                'US',
            ),
            'CA' => array(
                'CA',
                'AU',
                'BR',
                'FR',
                'DE',
                'IT',
                'ES',
                'CH',
                'GB',
                'US',
            ),
            'ES' => array(
                'ES',
                'AR',
                'CL',
                'CO',
                'EC',
                'MX',
                'PE',
                'PT',
                'PR',
                'VE',
            ),
            'FI' => array(
                'FI',
                'AU',
                'CA',
                'FR',
                'DE',
                'GB',
                'US',
            ),
            'FR' => array(
                'FR',
                'BE',
                'CA',
                'NC',
                'GF',
                'PF',
                'CH',
            ),
            'IT' => array(
                'IT',
                'CH',
            ),
            'NL' => array(
                'NL',
                'AN',
                'AU',
                'CA',
                'FR',
                'DE',
                'GB',
                'US',
            ),
            'NO' => array(
                'NO',
                'AU',
                'CA',
                'FR',
                'DE',
                'GB',
                'US',
            ),
            'PT' => array(
                'PT',
                'BR',
            ),
        );

        $find=false;
        $_options=array();
        if(isset($countryDefault[$countryId])){
            $countryDefault=$countryDefault[$countryId];
        }else{
            $countryDefault=array();
        }
        if(!empty($countryDefault)){
            foreach($countryDefault as $k=>$val){
                foreach($options as $key=>$option){
                    if($option['value']==$val){
                        $_options[$k]['value']=$val;
                        $_options[$k]['label']=$option['label'];
                        unset($options[$key]);
                        break;
                    }
                }
            }

            if(!empty($_options)){
                foreach($options as &$option){
                    if($option['value']==''){
                        $option['label']='------------';
                        break;
                    }
                }
                $options=  array_merge($_options,$options);
            }
        }
        return $options;
    }
/* */
}
