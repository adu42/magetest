<?php class Magebird_Popup_Block_List extends Magebird_Popup_Block_Widget_Abstract
{
    public function getScript($name)
    {
        $html = "<script type=\"text/javascript\">\n";
        $html .= "if(!jQuery(\"link[href*='/css/magebird_popup/widget/newsletter/" . $name . ".css']\").length){\n";
        $html .= "jQuery('head').append('<link rel=\"stylesheet\" href=\"" . $this->getSkinUrl("css/magebird_popup/widget/newsletter/" . $name . ".css?v=1.4.8") . "\" type=\"text/css\" />');";
        $html .= "}\n";
        $html .= "newslPopup['" . $this->getWidgetId() . "'] = {};\n";
        $html .= "newslPopup['" . $this->getWidgetId() . "'].successMsg = decodeURIComponent(('" . urlencode(Mage::helper('cms')->getBlockTemplateProcessor()->filter(urldecode($this->getData('success_msg')))) . "'+'').replace(/\+/g, '%20'));";
        $on_success = $this->getData('on_success') ? $this->getData('on_success') : 1;
        $html .= "newslPopup['" . $this->getWidgetId() . "'].successAction = '" . $on_success . "';";
        $html .= "newslPopup['" . $this->getWidgetId() . "'].successUrl = '" . $this->getData('success_url') . "';";
        $html .= "newslPopup['" . $this->getWidgetId() . "'].errorText = '" . $this->__('Write a valid Email address') . "';";
        $delay = $this->getDelay() * 1000;
        $html .= "newslPopup['" . $this->getWidgetId() . "'].actionDelay = '" . $delay . "';";
        $html .= "</script>\n";
        return $html;
    }
} ?>