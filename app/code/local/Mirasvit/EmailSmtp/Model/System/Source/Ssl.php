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


class Mirasvit_EmailSmtp_Model_System_Source_Ssl
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'none',
                'label' => Mage::helper('emailsmtp')->__('None')
            ),
            array(
                'value' => 'SSL',
                'label' => Mage::helper('emailsmtp')->__('SSL')
            ),
            array(
                'value' => 'TLS',
                'label' => Mage::helper('emailsmtp')->__('TLS')
            )
        );
    }
}