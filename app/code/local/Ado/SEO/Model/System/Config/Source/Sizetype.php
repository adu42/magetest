<?php

/**
 * @author @¶Å±ø
 * @copyright 2015
 */


class Ado_SEO_Model_System_Config_Source_Sizetype
{

    const SIZETYPE_US = 1;
    const SIZETYPE_AU = 2;
    const SIZETYPE_UK = 3;
    const SIZETYPE_EUR = 4;
    const SIZETYPE_AUUK = 5;

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
                array('value'=>0,'label'=>$helper->__('No Change')),
                array('value'=>self::SIZETYPE_US,'label'=>$helper->__('US')),
                array('value'=>self::SIZETYPE_UK,'label'=>$helper->__('UK')),
                array('value'=>self::SIZETYPE_EUR,'label'=>$helper->__('EUR')),
                array('value'=>self::SIZETYPE_AU,'label'=>$helper->__('AU')),
                array('value'=>self::SIZETYPE_AUUK,'label'=>$helper->__('AU/UK')),
            );
        }
        return $this->_options;
    }

}
?>