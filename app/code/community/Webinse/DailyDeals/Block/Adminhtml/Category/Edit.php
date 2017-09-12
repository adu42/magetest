<?php

/**
 * Webinse_DailyDeals_Block_Adminhtml_Category_Edit
 *
 * PHP Version 5.5.9
 *
 * @category  Webinse
 * @package   Webinse_DailyDeals
 * @author    Webinse Team <info@webinse.com>
 * @copyright 2015 Webinse Ltd. (https://www.webinse.com)
 * @license   The Open Software License 3.0
 * @link      http://opensource.org/licenses/OSL-3.0
 */

/**
 * Webinse_DailyDeals_Block_Adminhtml_Category_Edit.
 * .
 *
 * @category  Webinse
 * @package   Webinse_DailyDeals
 * @author    Webinse Team <info@webinse.com>
 * @copyright 2015 Webinse Ltd. (https://www.webinse.com)
 * @license   The Open Software License 3.0
 * @link      http://opensource.org/licenses/OSL-3.0
 */
class Webinse_DailyDeals_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_blockGroup = 'dailydeals';

    /**
     * Simple constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_category';
        $this->_updateButton('save', 'label', Mage::helper('dailydeals')->__('Save'));
        $this->_removeButton('delete', 'label', Mage::helper('dailydeals')->__('Delete User'));
        $this->_updateButton('back', 'label', Mage::helper('dailydeals')->__('Back'));
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/*/newcategory') . '\')');
        $this->_addButton(
            'back_grid', array(
                'label' => Mage::helper('dailydeals')->__('Back to deals'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/categoryGrid') . '\')'
            )
        );

    }

    /**
     * Header text
     *
     * @return mixed
     */
    public function getHeaderText()
    {
        return Mage::helper('dailydeals')->__('Edit Deal Category');
    }

    /**
     * Css
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-products';
    }
}