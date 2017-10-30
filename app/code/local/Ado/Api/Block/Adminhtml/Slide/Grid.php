<?php

class Ado_Api_Block_Adminhtml_Slide_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('adoslideGrid');
      $this->setDefaultSort('slide_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('mapi/slide')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('slide_id', array(
          'header'    => Mage::helper('mapi')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'slide_id',
      ));

      $this->addColumn('identifier', array(
          'header'    => Mage::helper('mapi')->__('Identifier'),
          'align'     =>'left',
          'index'     => 'identifier',
      ));

	  $this->addColumn('title', array(
          'header'    => Mage::helper('mapi')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

      $this->addColumn('show_title', array(
          'header'    => Mage::helper('mapi')->__('Show Title'),
          'align'     => 'left',
          'width'     => '40px',
          'index'     => 'show_title',
          'type'      => 'options',
          'options'   => array(
              1 => 'Yes',
              2 => 'No',
          ),
      ));
	$this->addColumn('active_from', array(
          'header'    => Mage::helper('mapi')->__('Active From'),
          'align'     =>'left',
          'index'     => 'active_from',
      ));
	  $this->addColumn('active_to', array(
          'header'    => Mage::helper('mapi')->__('Active To'),
          'align'     =>'left',
          'index'     => 'active_to',
      ));
	  $this->addColumn('width', array(
          'header'    => Mage::helper('mapi')->__('Width'),
          'align'     =>'right',
          'width'     => '40px',
          'index'     => 'width',
      ));

	  	  $this->addColumn('height', array(
          'header'    => Mage::helper('mapi')->__('Height'),
          'align'     =>'right',
          'width'     => '40px',
          'index'     => 'height',
      ));

	  $this->addColumn('delay', array(
          'header'    => Mage::helper('mapi')->__('Delay'),
          'align'     =>'right',
          'width'     => '40px',
          'index'     => 'delay',
      ));
	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('mapi')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('mapi')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('mapi')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('mapi')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('mapi')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('mapi')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('slide_id');
        $this->getMassactionBlock()->setFormFieldName('adoslide');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('mapi')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('mapi')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('mapi/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('mapi')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('mapi')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}