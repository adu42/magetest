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



class Mirasvit_Email_Model_Event_Customer_Update extends Mirasvit_Email_Model_Event_Abstract
{
    const EVENT_CODE = 'customer_update';

    public function getEventsGroup()
    {
        return __('Customer');
    }

    public function getEvents()
    {
        $result = array();

        $result[self::EVENT_CODE] = __('Customer Account Update');

        return $result;
    }

    public function findEvents($eventCode, $from)
    {
        $events = array();
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('customer/customer')->getCollection();

        $collection->getSelect()->where('updated_at >= ?', date('Y-m-d H:i:s', $from));
        foreach ($collection as $customer) {
            $customer = Mage::getModel('customer/customer')->load($customer->getId());
            $events[] = array(
                'time' => strtotime($customer->getUpdatedAt()),
                'customer_email' => $customer->getEmail(),
                'customer_name' => $customer->getFirstname().' '.$customer->getLastname(),
                'customer_id' => $customer->getId(),
                'store_id' => $customer->getStoreId(),
                'expire_after' => 60 * 5,
            );
        }

        return $events;
    }
}
