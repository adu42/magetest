<?php class Magebird_Popup_Block_Adminhtml_Widget_Follow_Rules extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $collection = Mage::getModel('salesrule/rule')->getCollection();
        $collection->addFieldToFilter('coupon_type', 2);
        $collection->addFieldToFilter('use_auto_generation', 1);
        if (count($collection) == 0) {
            $html = "<p class='note'><span style='color:red'>ERROR:</span><span>" . Mage::helper('magebird_popup')->__('Only rules with field &quot;<a target="_blank" href="%s">Use Auto Generation</a>&quot; checked on can be used. Also make sure you set &quot;Coupon&quot; field to &quot;Specific coupon&quot; value.', "http://www.magebird.com/magento-extensions/popup.html?tab=faq#dynamicCoupon") . " <a target='_blank' href='http://www.magebird.com/magento-extensions/popup.html?tab=faq#dynamicCoupon'>" . Mage::helper('magebird_popup')->__("See instructions here") . "</a></span></p>";
        } else {
            $html = '<p class="note"><span>' . Mage::helper('magebird_popup')->__('Choose your shoping cart rule you want to be used to generate coupons. Only rules with field &quot;<a target="_blank" href="%s">Use Auto Generation</a>&quot; checked on can be used. If you want to use static coupon code change &quot;Coupon type&quot; above.', 'http://www.magebird.com/magento-extensions/popup.html?tab=faq#dynamicCoupon') . '</span></p>';
        }
        $element->setData('after_element_html', $html);
        return $element;
    }
} ?>