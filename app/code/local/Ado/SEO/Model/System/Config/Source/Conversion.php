<?php

/**
 * @author @¶Å±ø
 * @copyright 2015
 */


class Ado_SEO_Model_System_Config_Source_Conversion
{

    const CONVERSION_PAGE_CART = 1;
    const CONVERSION_PAGE_ONEPAGE = 2;
    const CONVERSION_PAGE_PAYPAL_REVIEW = 3;
    const CONVERSION_PAGE_SUCCESS = 4;
    const CONVERSION_PAGE_FAILURE = 5;

    protected $_options;

    /**
     * Retrieve types of submit for price slider filter
     * 
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $helper = Mage::helper('ado_seo');
            $this->_options = array(
                array('value'=>self::CONVERSION_PAGE_CART,'label'=>$helper->__('cart')),
                array('value'=>self::CONVERSION_PAGE_ONEPAGE,'label'=>$helper->__('onpage')),
                array('value'=>self::CONVERSION_PAGE_PAYPAL_REVIEW,'label'=>$helper->__('paypal review')),
                array('value'=>self::CONVERSION_PAGE_SUCCESS,'label'=>$helper->__('success')),
                array('value'=>self::CONVERSION_PAGE_FAILURE,'label'=>$helper->__('failure')),
            );
        }
        return $this->_options;
    }

}
?>