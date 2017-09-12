<?php

class Infortis_Ultimo_Model_System_Config_Source_Category_Shopbyposition
{
    public function toOptionArray()
    {
        return array(
			array('value' => 'top','label' => Mage::helper('ultimo')->__('Main Top')),
            array('value' => 'left','label' => Mage::helper('ultimo')->__('Main Left')),
            array('value' => 'right','label' => Mage::helper('ultimo')->__('Main Right')),
        );
    }
}