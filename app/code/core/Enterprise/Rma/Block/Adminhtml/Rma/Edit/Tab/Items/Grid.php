<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Admin RMA create order grid block
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 */

class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_Items_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Default limit collection
     *
     * @var int
     */
    protected $_defaultLimit = 0;

    /**
     * Variable to store store-depended string values of attributes
     *
     * @var null|array
     */
    protected $_attributeOptionValues = null;

    /**
     * Block constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('enterprise_rma_item_edit_grid');
        $this->setDefaultSort('entity_id');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setSortable(false);
        $this->_gatherOrderItemsData();
    }

    /**
     * Gather items quantity data from Order item collection
     *
     * @return void
     */
    protected function _gatherOrderItemsData()
    {
        $itemsData = array();
        foreach (Mage::registry('current_order')->getItemsCollection() as $item) {
            $itemsData[$item->getId()] = array(
                'qty_shipped' => $item->getQtyShipped(),
                'qty_returned' => $item->getQtyReturned()
            );
        }
        $this->setOrderItemsData($itemsData);
    }

    /**
     * Prepare grid collection object
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_Items_Grid
     */
    protected function _prepareCollection()
    {
        $rma = Mage::registry('current_rma');

        /** @var $collection Enterprise_Rma_Model_Resource_Item_Collection */
        $collection = $rma->getItemsForDisplay();

        if ($this->getItemFilter()) {
            $collection->addFilter('entity_id', $this->getItemFilter());
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $statusManager = Mage::getSingleton('enterprise_rma/item_status');
        $rma = Mage::registry('current_rma');
        if ($rma
            && (($rma->getStatus() === Enterprise_Rma_Model_Rma_Source_Status::STATE_CLOSED)
                || ($rma->getStatus() === Enterprise_Rma_Model_Rma_Source_Status::STATE_PROCESSED_CLOSED))
        ) {
            $statusManager->setOrderIsClosed();
        }

        $this->addColumn('product_admin_name', array(
            'header' => Mage::helper('enterprise_rma')->__('Product Name'),
            'width'  => '80px',
            'type'   => 'text',
            'index'  => 'product_admin_name',
            'escape' => true,
        ));

        $this->addColumn('product_admin_sku', array(
            'header'=> Mage::helper('enterprise_rma')->__('SKU'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'product_admin_sku',
        ));

        //Renderer puts available quantity instead of order_item_id
        $this->addColumn('qty_ordered', array(
            'header'=> Mage::helper('enterprise_rma')->__('Remaining Qty'),
            'width' => '80px',
            'getter'   => array($this, 'getQtyOrdered'),
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_quantity',
            'index' => 'qty_ordered',
            'order_data' => $this->getOrderItemsData(),
        ));

        $this->addColumn('qty_requested', array(
            'header'=> Mage::helper('enterprise_rma')->__('Requested Qty'),
            'width' => '80px',
            'index' => 'qty_requested',
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_textinput',
            'validate_class' => 'validate-greater-than-zero'
        ));

        $this->addColumn('qty_authorized', array(
            'header'=> Mage::helper('enterprise_rma')->__('Authorized Qty'),
            'width' => '80px',
            'index' => 'qty_authorized',
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_textinput',
            'validate_class' => 'validate-greater-than-zero'
        ));

        $this->addColumn('qty_returned', array(
            'header'=> Mage::helper('enterprise_rma')->__('Returned Qty'),
            'width' => '80px',
            'index' => 'qty_returned',
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_textinput',
            'validate_class' => 'validate-greater-than-zero'
        ));

        $this->addColumn('qty_approved', array(
            'header'=> Mage::helper('enterprise_rma')->__('Approved Qty'),
            'width' => '80px',
            'index' => 'qty_approved',
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_textinput',
            'validate_class' => 'validate-greater-than-zero'
        ));

        $this->addColumn('reason', array(
            'header'=> Mage::helper('enterprise_rma')->__('Reason to Return'),
            'width' => '80px',
            'getter'   => array($this, 'getReasonOptionStringValue'),
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_reasonselect',
            'options' => Mage::helper('enterprise_rma/eav')->getAttributeOptionValues('reason'),
            'index' => 'reason',
        ));

        $this->addColumn('condition', array(
            'header'=> Mage::helper('enterprise_rma')->__('Item Condition'),
            'width' => '80px',
            'getter'   => array($this, 'getConditionOptionStringValue'),
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_textselect',
            'options' => Mage::helper('enterprise_rma/eav')->getAttributeOptionValues('condition'),
            'index' => 'condition',
        ));

        $this->addColumn('resolution', array(
            'header'=> Mage::helper('enterprise_rma')->__('Resolution'),
            'width' => '80px',
            'index' => 'resolution',
            'getter'   => array($this, 'getResolutionOptionStringValue'),
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_textselect',
            'options' => Mage::helper('enterprise_rma/eav')->getAttributeOptionValues('resolution'),
        ));

        $this->addColumn('status', array(
            'header'=> Mage::helper('enterprise_rma')->__('Status'),
            'width' => '80px',
            'index' => 'status',
            'getter'=> array($this, 'getStatusOptionStringValue'),
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_status',
        ));

        $actionsArray = array(
            array(
                'caption'   => Mage::helper('enterprise_rma')->__('Details'),
                'class'     => 'item_details',
            ),
        );
        if (!($rma
            && (($rma->getStatus() === Enterprise_Rma_Model_Rma_Source_Status::STATE_CLOSED)
                || ($rma->getStatus() === Enterprise_Rma_Model_Rma_Source_Status::STATE_PROCESSED_CLOSED))
        )) {
                $actionsArray[] = array(
                'caption'   => Mage::helper('enterprise_rma')->__('Split'),
                'class'     => 'item_split_line',
                'status_depended' => '1'
            );
        }

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('enterprise_rma')->__('Action'),
                'width'     => '100',
                'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_action',
                'actions'   => $actionsArray,
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get available for return item quantity
     *
     * @param Varien_Object $row
     * @return int
     */
    public function getQtyOrdered($row)
    {
        $orderItemsData = $this->getOrderItemsData();
        if (is_array($orderItemsData)
                && isset($orderItemsData[$row->getOrderItemId()])
                && isset($orderItemsData[$row->getOrderItemId()]['qty_shipped'])
                && isset($orderItemsData[$row->getOrderItemId()]['qty_returned'])) {
            $return = $orderItemsData[$row->getOrderItemId()]['qty_shipped'] -
                    $orderItemsData[$row->getOrderItemId()]['qty_returned'];
        } else {
            $return = 0;
        }
        return $return;
    }

    /**
     * Get string value of "Reason to Return" Attribute
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getReasonOptionStringValue($row)
    {
        return $this->_getAttributeOptionStringValue($row->getReason());
    }

    /**
     * Get string value of "Reason to Return" Attribute
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getResolutionOptionStringValue($row)
    {
        return $this->_getAttributeOptionStringValue($row->getResolution());
    }

    /**
     * Get string value of "Reason to Return" Attribute
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getConditionOptionStringValue($row)
    {
        return $this->_getAttributeOptionStringValue($row->getCondition());
    }

    /**
     * Get string value of "Status" Attribute
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getStatusOptionStringValue($row)
    {
        return $row->getStatusLabel();
    }

    /**
     * Get string value option-type attribute by it's unique int value
     *
     * @param int $value
     * @return string
     */
    protected function _getAttributeOptionStringValue($value)
    {
        if (is_null($this->_attributeOptionValues)) {
            $this->_attributeOptionValues = Mage::helper('enterprise_rma/eav')->getAttributeOptionStringValues();
        }
        if (isset($this->_attributeOptionValues[$value])) {
            return $this->escapeHtml($this->_attributeOptionValues[$value]);
        } else {
            return $this->escapeHtml($value);
        }
    }

    /**
     * Sets all available fields in editable state
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_Items_Grid
     */
    public function setAllFieldsEditable()
    {
        Mage::getSingleton('enterprise_rma/item_status')->setAllEditable();
        return $this;
    }

}
