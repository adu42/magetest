<?php class Magebird_Popup_Block_Timer extends Magebird_Popup_Block_Widget_Abstract
{
    public function getTimer()
    {
        if ($this->getToDate()) {
            $time = strtotime($this->getToDate());
        } else {
            $time = $this->getData('minutes') * 60;
        }
        return $time;
    }

    public function getFontSize()
    {
        return $this->getTimerSize();
    }

    public function getLabelFontSize()
    {
        $font_size = intval($this->getTimerSize() / 2);
        if ($font_size < 10) $font_size = 10;
        return $font_size;
    }
} ?>