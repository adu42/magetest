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



class Mirasvit_Email_Model_Event_Email_FailedPayment extends Mirasvit_Email_Model_Event_Abstract
{
    const EVENT_CODE = 'email_failedPayment';

    public function getEventsGroup()
    {
        return Mage::helper('email')->__('Emails');
    }

    public function getEvents()
    {
        $result = array(
            self::EVENT_CODE => Mage::helper('email')->__('Payment Transaction Failed'),
        );

        return $result;
    }

    public function findEvents($eventCode, $timestamp)
    {
        return array();
    }

    protected function observe($eventCode, $observer)
    {
        $variables = $observer->getVariables();

        // Customer email is a required field
        if (!$variables['customerEmail']) {
            return array();
        }

        $event = array(
            'time' => time(),
            'customer_email' => $variables['customerEmail'],
            'customer_name' => $variables['customer'],
            'customer_id' => $variables['billingAddress']->getCustomerId(),
            'billing_address_id' => $variables['billingAddress']->getId(),
            'shipping_address_id' => $variables['shippingAddress']->getId(),
            'quote_id' => $variables['billingAddress']->getQuoteId(),
            'store_id' => $variables['store']->getStoreId(),
            'reason' => $variables['reason'],
        );

        return array($event);
    }

    public function isActive()
    {
        return (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_EmailSmtp')
                && get_class(Mage::getModel('core/email_template')) === 'Mirasvit_EmailSmtp_Model_Email_Template');
    }
}
