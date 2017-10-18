<?php
class Ado_Api_Block_Adminhtml_Banneritem extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_banneritem';
    $this->_blockGroup = 'mapi';
    $this->_headerText = Mage::helper('mapi')->__('Banner Item Manager');
    $this->_addButton('save', array(
            'label'     => Mage::helper('mapi')->__('Save Banner Item Order'),
            'onclick'   => 'save_order()',
			'id'		=> 'save_cat',
        ));
    $this->_addButtonLabel = Mage::helper('mapi')->__('Add Banner Item');
    parent::__construct();
  }
  
	public function getSaveOrderUrl()
    {
        return $this->getUrl('*/*/setOrder');
    }
}