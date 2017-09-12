<?php class Magebird_Popup_Block_Adminhtml_Widget_Newsletter_Confirmneed extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $confirm = (Mage::getStoreConfig('newsletter/subscription/confirm') == 1) ? true : false;
        if (!$confirm) {
            $html = "<p class='note'><span>" . Mage::helper('magebird_popup')->__("Your current settings says that user doesn't need to confirm subscription (System->Configuration->CUSTOMERS->Newsletter->Need to Confirm).") . "</span></p>";
        } else {
            $html = '<p class="note"><span>' . Mage::helper('magebird_popup')->__('Your current settings says that user needs to confirm subscription (System->Configuration->CUSTOMERS->Newsletter->Need to Confirm). Would you like to give coupon to user once he subscribes or does he need also to confirm subscription?') . '</span></p>';
        }
        $element->setData('after_element_html', $html);
        return $element;
    }
} ?>