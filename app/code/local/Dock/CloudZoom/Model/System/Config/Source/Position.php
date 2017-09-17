<?php

class Dock_CloudZoom_Model_System_Config_Source_Position
{
    public function toOptionArray()
    {
        return array(
			array('value' => 'inside',		'label' => Mage::helper('dock_cloudzoom')->__('Inside')),
			array('value' => 'right',		'label' => Mage::helper('dock_cloudzoom')->__('Right')),
			array('value' => 'left',		'label' => Mage::helper('dock_cloudzoom')->__('Left')),
			array('value' => 'top',			'label' => Mage::helper('dock_cloudzoom')->__('Top')),
			array('value' => 'bottom',		'label' => Mage::helper('dock_cloudzoom')->__('Bottom'))
        );
    }
}
