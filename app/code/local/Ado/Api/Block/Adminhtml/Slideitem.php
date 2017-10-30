<?php
class Ado_Api_Block_Adminhtml_Slideitem extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_slideitem';
    $this->_blockGroup = 'mapi';
    $this->_headerText = Mage::helper('mapi')->__('Slide Item Manager');
    $this->_addButton('save', array(
            'label'     => Mage::helper('mapi')->__('Save Slide Item Order'),
            'onclick'   => 'save_order()',
			'id'		=> 'save_cat',
        ));
    $this->_addButtonLabel = Mage::helper('mapi')->__('Add Slide Item');
    parent::__construct();
  }
  
	public function getSaveOrderUrl()
    {
        return $this->getUrl('*/*/setOrder');
    }
}