<?php

class Dock_CloudZoom_Model_System_Config_Source_OpacityRange
{
    public function toOptionArray()
    {
        $result = array();
        for ($i = 0; $i <= 1; $i+=0.1) {
            $result[] = array(
                'value' => ($i*100),
                'label' => $i
            );
        }
        return $result;
    }
}
