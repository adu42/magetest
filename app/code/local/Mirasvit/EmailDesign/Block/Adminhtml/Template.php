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


class Mirasvit_EmailDesign_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller     = 'adminhtml_template';
        $this->_blockGroup     = 'emaildesign';
        $this->_headerText     = Mage::helper('emaildesign')->__('Manage Templates');
        $this->_addButtonLabel = Mage::helper('emaildesign')->__('Add Template');

        return parent::__construct();
    }
}