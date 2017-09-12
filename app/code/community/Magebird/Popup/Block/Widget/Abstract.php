<?php class Magebird_Popup_Block_Widget_Abstract extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
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

    public function brightness($color, $inc)
    {
        $color = str_replace('#', '', $color);
        $r = substr($color, 0, 2);
        $g = substr($color, 2, 2);
        $b = substr($color, 4, 2);
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        $r = max(0, min(255, $r + $inc));
        $g = max(0, min(255, $g + $inc));
        $b = max(0, min(255, $b + $inc));
        $dr = dechex($r);
        if (strlen($dr) == 1) $dr = "0" . $dr;
        $dg = dechex($g);
        if (strlen($dg) == 1) $dg = "0" . $dg;
        $db = dechex($b);
        if (strlen($db) == 1) $db = "0" . $db;
        return '#' . $dr . $dg . $db;
    }

    public function getButtonTextColor()
    {
        $button_text_color = $this->getData('button_text_color');
        if (!$button_text_color) $button_text_color = $this->getData('buttontext_color');
        if (!$button_text_color) $button_text_color = "#FFFFFF";
        if (strpos($button_text_color, '#') === false) $button_text_color = "#" . $button_text_color;
        return $button_text_color;
    }

    public function getButtonColor()
    {
        $button_color = $this->getData('button_color');
        if (!$button_color) $button_color = '#d83c3c';
        if (strpos($button_color, '#') === false) $button_color = "#" . $button_color;
        return $button_color;
    }

    public function getDelay()
    {
        $delay = 0;
        if ($this->getData('on_success') == 2) {
            $delay = $this->getData('close_delay');
        } elseif ($this->getData('on_success') == 3) {
            $delay = $this->getData('redirect_delay');
        }
        return $delay;
    }
} ?>