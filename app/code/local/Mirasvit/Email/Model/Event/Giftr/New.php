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



class Mirasvit_Email_Model_Event_Giftr_New extends Mirasvit_Email_Model_Event_Abstract
{
    const EVENT_CODE = 'giftr_new';

    public function getEventsGroup()
    {
        return Mage::helper('email')->__('Gift Registry');
    }

    public function getEvents()
    {
        $result = array(
            self::EVENT_CODE => Mage::helper('email')->__('New Gift Registry'),
        );

        return $result;
    }

    protected function observe($eventCode, $observer)
    {
        return array();
    }

    public function findEvents($eventCode, $timestamp)
    {
        $events   = array();
        $fromDate = date('Y-m-d H:i:s', $timestamp);

        $registryCollection = Mage::getModel('giftr/registry')->getCollection()
            ->addFieldToFilter('main_table.created_at', array('gt' => $fromDate))
            ->setOrder('main_table.created_at', 'asc');

        foreach ($registryCollection as $registry) {
            $customer = Mage::getModel('customer/customer')->load($registry->getCustomerId());
            $events[] = array(
                'time'              => strtotime($registry->getCreatedAt()),
                'customer_email'    => $registry->getEmail(),
                'customer_name'     => $registry->getFirstname() . ' ' . $registry->getLastname(),
                'customer_id'       => $registry->getCustomerId(),
                'registry_id'       => $registry->getId(),
                'store_id'          => $customer->getStoreId()
            );
        }

        return $events;
    }

    public function isActive()
    {
        return Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Giftr');
    }
}
