<?php

class Dock_Dock_Model_System_Config_Source_Js_Jquery_Easing
{
    public function toOptionArray()
    {
        return array(
			//Ease in-out
			array('value' => 'easeInOutSine',	'label' => Mage::helper('dock')->__('easeInOutSine')),
			array('value' => 'easeInOutQuad',	'label' => Mage::helper('dock')->__('easeInOutQuad')),
			array('value' => 'easeInOutCubic',	'label' => Mage::helper('dock')->__('easeInOutCubic')),
			array('value' => 'easeInOutQuart',	'label' => Mage::helper('dock')->__('easeInOutQuart')),
			array('value' => 'easeInOutQuint',	'label' => Mage::helper('dock')->__('easeInOutQuint')),
			array('value' => 'easeInOutExpo',	'label' => Mage::helper('dock')->__('easeInOutExpo')),
			array('value' => 'easeInOutCirc',	'label' => Mage::helper('dock')->__('easeInOutCirc')),
			array('value' => 'easeInOutElastic','label' => Mage::helper('dock')->__('easeInOutElastic')),
			array('value' => 'easeInOutBack',	'label' => Mage::helper('dock')->__('easeInOutBack')),
			array('value' => 'easeInOutBounce',	'label' => Mage::helper('dock')->__('easeInOutBounce')),
			//Ease out
			array('value' => 'easeOutSine',		'label' => Mage::helper('dock')->__('easeOutSine')),
			array('value' => 'easeOutQuad',		'label' => Mage::helper('dock')->__('easeOutQuad')),
			array('value' => 'easeOutCubic',	'label' => Mage::helper('dock')->__('easeOutCubic')),
			array('value' => 'easeOutQuart',	'label' => Mage::helper('dock')->__('easeOutQuart')),
			array('value' => 'easeOutQuint',	'label' => Mage::helper('dock')->__('easeOutQuint')),
			array('value' => 'easeOutExpo',		'label' => Mage::helper('dock')->__('easeOutExpo')),
			array('value' => 'easeOutCirc',		'label' => Mage::helper('dock')->__('easeOutCirc')),
			array('value' => 'easeOutElastic',	'label' => Mage::helper('dock')->__('easeOutElastic')),
			array('value' => 'easeOutBack',		'label' => Mage::helper('dock')->__('easeOutBack')),
			array('value' => 'easeOutBounce',	'label' => Mage::helper('dock')->__('easeOutBounce')),
			//Ease in
			array('value' => 'easeInSine',		'label' => Mage::helper('dock')->__('easeInSine')),
			array('value' => 'easeInQuad',		'label' => Mage::helper('dock')->__('easeInQuad')),
			array('value' => 'easeInCubic',		'label' => Mage::helper('dock')->__('easeInCubic')),
			array('value' => 'easeInQuart',		'label' => Mage::helper('dock')->__('easeInQuart')),
			array('value' => 'easeInQuint',		'label' => Mage::helper('dock')->__('easeInQuint')),
			array('value' => 'easeInExpo',		'label' => Mage::helper('dock')->__('easeInExpo')),
			array('value' => 'easeInCirc',		'label' => Mage::helper('dock')->__('easeInCirc')),
			array('value' => 'easeInElastic',	'label' => Mage::helper('dock')->__('easeInElastic')),
			array('value' => 'easeInBack',		'label' => Mage::helper('dock')->__('easeInBack')),
			array('value' => 'easeInBounce',	'label' => Mage::helper('dock')->__('easeInBounce')),
			//No easing
			array('value' => '',				'label' => Mage::helper('dock')->__('No easing'))
        );
    }
}
