<?php

class Dock_Rural_Model_System_Config_Source_Category_AltImageColumn
{
    public function toOptionArray()
    {
        return array(
			array('value' => 'label',			'label' => Mage::helper('rural')->__('Label')),
            array('value' => 'position',		'label' => Mage::helper('rural')->__('Sort Order'))
        );
    }
}