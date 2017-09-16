<?php

class Infortis_UltraSlideshow_Model_Source_Navigation_Pagination_Position
{
	public function toOptionArray()
	{
		return array(
			array('value' => 'bottom-centered',			'label' => Mage::helper('ultraslideshow')->__('Bottom, centered')),
			array('value' => 'bottom-left',				'label' => Mage::helper('ultraslideshow')->__('Bottom, left')),
			array('value' => 'bottom-right',			'label' => Mage::helper('ultraslideshow')->__('Bottom, right')),
			array('value' => 'over-bottom-centered',	'label' => Mage::helper('ultraslideshow')->__('Bottom, centered, over the slides')),
			array('value' => 'over-bottom-left',		'label' => Mage::helper('ultraslideshow')->__('Bottom, left, over the slides')),
			array('value' => 'over-bottom-right',		'label' => Mage::helper('ultraslideshow')->__('Bottom, right, over the slides')),
		);
	}
}
