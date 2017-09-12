<?php class Magebird_Popup_Block_Register extends Magebird_Popup_Block_Widget_Abstract
{
    public function getRegisterUrl()
    {
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $url = Mage::getUrl('magebird_popup/user/register', array('_forced_secure' => true));
        } else {
            $url = Mage::getUrl('magebird_popup/user/register');
        }
        return $url;
    }

    public function getLoginUrl()
    {
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $url = Mage::getUrl('magebird_popup/user/login', array('_forced_secure' => true));
        } else {
            $url = Mage::getUrl('magebird_popup/user/login');
        }
        return $url;
    }
} ?>