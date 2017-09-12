<?php class Magebird_Popup_Block_Adminhtml_Widget_Follow_Successmsg extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setValue(urldecode($element->getValue()));
        return $element;
    }
} ?>