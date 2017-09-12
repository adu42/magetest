<?php class Magebird_Popup_Block_Adminhtml_Widget_Couponexpiration extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = "<input type='checkbox' class='inheritTimer' /> Iherit from countdown timer (<a target='_blank' href='http://www.magebird.com/magento-extensions/popup.html?tab=faq#timelimitedCoupons'>What is that?</a>)\r\n        <script>\r\n        var element = jQuery(\"#widget_options input[name='parameters\[coupon_expiration\]']\");\r\n        element.attr('style', 'width: 70px !important');\r\n        jQuery(\".inheritTimer\").click(function(){\r\n          if(element.attr('readonly')){\r\n            element.attr('readonly', false);      \r\n            element.val('');        \r\n          }else{\r\n            element.val('inherit');\r\n            element.attr('readonly', true);      \r\n          }\r\n        });\r\n        </script>";
        $element->setData('after_element_html', $html);
        return $element;
    }
} ?>