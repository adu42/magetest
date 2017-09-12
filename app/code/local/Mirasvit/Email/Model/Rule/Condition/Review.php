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
 * @version   1.0.34
 * @build     705
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Email_Model_Rule_Condition_Review extends  Mage_SalesRule_Model_Rule_Condition_Product
{
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes = array(
            'sku' => Mage::helper('email')->__('Reviewed product'),
        );
    }
}
