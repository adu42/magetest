<?php class Magebird_Popup_Model_Widget_Buttonsize
{
    public function toOptionArray()
    {
        for ($i = 1; $i <= 10; $i++) {
            $options[] = array('value' => $i, 'label' => $i);
        }
        return $options;
    }
} ?>