<?php class Magebird_Popup_Block_Contact extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    protected $_serializer = null;

    protected function _construct()
    {
        $this->_serializer = new Varien_Object();
        parent::_construct();
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }
} ?>