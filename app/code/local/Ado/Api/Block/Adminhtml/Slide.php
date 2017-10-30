<?php
class Ado_Api_Block_Adminhtml_Slide extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_slide';
    $this->_blockGroup = 'mapi';
    $this->_headerText = Mage::helper('mapi')->__('Slide Manager');
    $this->_addButtonLabel = Mage::helper('mapi')->__('Add Slide');
    parent::__construct();
  }
}