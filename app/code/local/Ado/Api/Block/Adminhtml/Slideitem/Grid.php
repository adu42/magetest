<?php

class Ado_Api_Block_Adminhtml_Slideitem_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('adoslideitemGrid');
      $this->setDefaultSort('slide_item_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('mapi/slideitem')->getCollection()->setOrder('slide_id','DESC')->setOrder('slide_order','ASC');
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
  	 // $this->setTemplate('adoslide/grid.phtml');
      $this->addColumn('slide_item_id', array(
          'header'    => Mage::helper('mapi')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'slide_item_id',
      ));

	  $slides = array();
	  $collection = Mage::getModel('mapi/slide')->getCollection();
	  foreach ($collection as $slide) {
		 $slides[$slide->getId()] = $slide->getTitle();
	  }
	  
	  $this->addColumn('slide_id', array(
          'header'    => Mage::helper('mapi')->__('Slide'),
          'align'     =>'left',
          'index'     => 'slide_id',
		  'type'      => 'options',
          'options'   => $slides,
      ));

	  $this->addColumn('image',
			array(
				'header'=> Mage::helper('mapi')->__('Image'),
				'type'  => 'image',
				'width' => 64,
				'index' => 'image',
		));

      $this->addColumn('title', array(
          'header'    => Mage::helper('mapi')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

      $this->addColumn('link_url', array(
          'header'    => Mage::helper('mapi')->__('Url'),
          'align'     =>'left',
          'index'     => 'link_url',
      ));
      
 	  $this->addColumn('slide_order', array(
          'header'    => Mage::helper('mapi')->__('Order'),
          'align'     =>'left',
 	  	  'width' 	  => 64,
          'index'     => 'slide_order',
      ));
	  
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
        $this->setMassactionIdField('slide_item_id');
        $this->getMassactionBlock()->setFormFieldName('adoslideitem');

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