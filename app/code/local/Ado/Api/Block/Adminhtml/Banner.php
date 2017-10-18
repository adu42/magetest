<?php
class Ado_Api_Block_Adminhtml_Banner extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_banner';
    $this->_blockGroup = 'mapi';
    $this->_headerText = Mage::helper('mapi')->__('Banner Manager');
    $this->_addButtonLabel = Mage::helper('mapi')->__('Add Banner');
    parent::__construct();
  }
}