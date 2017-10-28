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



class Mirasvit_Email_Helper_Event extends Mage_Core_Helper_Abstract
{
    /**
     * @param $eventCode
     *
     * @return bool|Mirasvit_Email_Model_Event_Abstract
     */
    public function getEventModel($eventCode)
    {
        $arr = explode('|', $eventCode);

        try {
            $model = Mage::getModel('email/event_'.$arr[0]);
        } catch (Exception $e) {
            Mage::logException($e);
            $model = false;
        }

        return $model;
    }

    public function getEventCodes()
    {
        $events = Mage::getSingleton('email/system_source_event')->toArray();

        $result = array();
        foreach ($events as $group => $sub) {
            foreach (array_keys($sub) as $value) {
                $result[] = $value;
            }
        }

        return $result;
    }

    public function getRandomEventArgs($storeId = null)
    {
        $args = array();
        $customerCollection = Mage::getModel('customer/customer')->getCollection();
        $customerCollection->getSelect()->limit(1, rand(0, $customerCollection->getSize() - 1));
        $customer = Mage::getModel('customer/customer')->load($customerCollection->getFirstItem()->getId());

        // Retrieve quote ID
        $quoteCollection = Mage::getModel('sales/quote')->getCollection()
            ->addFieldToFilter('items_qty', array('gt' => 0));
        $quoteCollection->getSelect()
            ->joinLeft(
                array('quote_item' => $quoteCollection->getResource()->getTable('sales/quote_item')),
                'main_table.entity_id = quote_item.quote_id',
                array()
            )
            ->where('quote_item.item_id IS NOT NULL')
            ->limit(1, rand(0, $quoteCollection->getSize() - 1));

        $quote = Mage::getModel('sales/quote')->setSharedStoreIds(array_keys(Mage::app()->getStores()))
            ->load($quoteCollection->getFirstItem()->getId());

        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->getSelect()->limit(1, rand(0, $orderCollection->getSize() - 1));
        $order = Mage::getModel('sales/order')->load($orderCollection->getFirstItem()->getId());

        $wishlistCollection = Mage::getModel('wishlist/wishlist')->getCollection();
        $wishlistCollection->getSelect()
            ->joinLeft(
                array('w_item' => $wishlistCollection->getResource()->getTable('wishlist/item')),
                'main_table.wishlist_id = w_item.wishlist_id',
                array('wishlist_item_id')
            )
            ->where('wishlist_item_id IS NOT NULL')
            ->order('RAND()')
            ->limit(1);
        $wishlist = Mage::getModel('wishlist/wishlist');
        if ($wishlistCollection->getSize() > 0) {
            $wishlist->load($wishlistCollection->getFirstItem()->getId());
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Giftr')) {
            $registryCollection = Mage::getModel('giftr/registry')->getCollection();
            $registryCollection->getSelect()->order('RAND()')->limit(1);
            if ($registryCollection->getSize() > 0) {
                $args['registry_id'] = $registryCollection->getFirstItem()->getId();
            }
        }

        $testEmail = Mage::getSingleton('email/config')->getTestEmail();

        $store = $defaultStoreId = Mage::app()->getWebsite(true)
            ->getDefaultGroup()
            ->getDefaultStore();

        $args = array_merge($args, array(
            'customer_id' => $customer->getId(),
            'customer_email' => $testEmail,
            'customer_name' => $customer->getName(),
            'quote_id' => $quote->getId(),
            'order_id' => $order->getId(),
            'wishlist_id' => $wishlist->getId(),
            'time' => time(),
            'store_id' => $store->getId(),
        ));

        return $args;
    }

    /**
     * Check whether the event code is observed.
     *
     * @param string $eventCode
     *
     * @return bool
     */
    public function isEventObserved($eventCode)
    {
        $events = $this->getActiveEvents();

        return in_array($eventCode, $events);
    }

    /**
     * Return all events observed by active triggers.
     *
     * @return array
     */
    public function getActiveEvents()
    {
        $events = array();
        $triggers = Mage::getModel('email/trigger')->getCollection()
            ->addActiveFilter();

        foreach ($triggers as $trigger) {
            $events = array_merge($events, $trigger->getEvents());
        }

        return array_unique($events);
    }

    /**
     * Return array of trigger IDs associated with the given event code.
     *
     * @param string $eventCode - event code
     *
     * @return array - array of trigger IDs associated with given event code
     */
    public function getAssociatedTriggers($eventCode)
    {
        $triggers = array();
        foreach (Mage::getModel('email/trigger')->getCollection()->addActiveFilter() as $trigger) {
            foreach ($trigger->getEvents() as $event) {
                if ($eventCode === $event) {
                    $triggers[] = $trigger->getId();
                }
            }
        }

        return $triggers;
    }
}
