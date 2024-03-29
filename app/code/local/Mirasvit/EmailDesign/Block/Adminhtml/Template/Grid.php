<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Follow Up Email
 * @version   1.1.23
 * @build     800
 * @copyright Copyright (C) 2017 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_EmailDesign_Block_Adminhtml_Template_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('emaildesing_template_grid');
        $this->setDefaultSort('title');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('emaildesign/template')->getCollection();
        $collection->joinDesignTitle();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('template_id', array(
            'header' => Mage::helper('emaildesign')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'template_id',
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('emaildesign')->__('Title'),
            'align' => 'left',
            'index' => 'title',
            'renderer' => 'Mirasvit_EmailDesign_Block_Adminhtml_Design_Grid_Renderer_Title',
        ));

        $this->addColumn('design', array(
            'header' => Mage::helper('emaildesign')->__('Design'),
            'align' => 'left',
            'index' => 'design_title',
        ));

        $this->addColumn('action',
            array(
                'header' => Mage::helper('emaildesign')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('emaildesign')->__('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id',
                    ),
                    array(
                        'caption' => Mage::helper('emaildesign')->__('Export'),
                        'url' => array('base' => '*/*/export'),
                        'field' => 'id',
                    ),
                    array(
                        'caption' => Mage::helper('emaildesign')->__('Convert'),
                        'url' => array('base' => '*/*/convert'),
                        'field' => 'id',
                    ),
                    array(
                        'caption' => Mage::helper('emaildesign')->__('Remove'),
                        'url' => array('base' => '*/*/delete'),
                        'field' => 'id',
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
