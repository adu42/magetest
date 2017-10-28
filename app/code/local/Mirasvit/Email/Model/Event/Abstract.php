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



abstract class Mirasvit_Email_Model_Event_Abstract
{
    /**
     * @var Mirasvit_Email_Model_Service_EventSaveProcessorInterface
     */
    protected $eventSaveProcessor;

    public function __construct()
    {
         $this->eventSaveProcessor = Mage::getModel('email/service_eventSaveProcessor_defaultSaveProcessor');
    }

    abstract public function getEvents();

    abstract public function findEvents($eventCode, $timestamp);

    /**
     * Return name of event group, like Customer, Cart, Base, Wishlist etc.
     *
     * @return string
     */
    public function getEventsGroup()
    {
        return Mage::helper('email')->__('Base');
    }

    /**
     * Register event by its code.
     *
     * @param string $eventCode - observed event code
     * @param string|bool - timestamp or false
     * @param object - object associated with the occured event - only for events observed by observer (not by cron)
     *
     * @return array - IDs of saved events
     */
    public function check($eventCode, $timestamp = false, $observer = null)
    {
        $timeVar = 'last_check_'.$eventCode;
        $events = array();
        $savedEvents = array();

        if (!$timestamp) {
            $timestamp = Mage::helper('email')->getVar($timeVar);
            if (!$timestamp) {
                $timestamp = Mage::getSingleton('core/date')->gmtTimestamp();
            }
        }

        if ($observer !== null) {
            if (!Mage::helper('email/event')->isEventObserved($eventCode)) {
                return;
            }

            $events = $this->observe($eventCode, $observer);
        } else {
            $events = $this->findEvents($eventCode, $timestamp);
        }

        foreach ($events as $event) {
            $uniqKey = $this->getEventUniqKey($event);
            $savedEvent = $this->eventSaveProcessor->saveEvent($eventCode, $uniqKey, $event);

            if ($savedEvent) {
                $savedEvents[$savedEvent->getId()] = $savedEvent->getId();
            }
        }

        Mage::helper('email')->setVar($timeVar, Mage::getSingleton('core/date')->gmtTimestamp());

        return $savedEvents;
    }

    /**
     * default $args
     * ! customer_name
     * ! customer_email
     * ! store_id
     * ? customer_id
     * ? customer
     * ? order
     * ? order_item_id
     * ? wishlist_id
     * ? registry_id
     * ? review_id
     *
     * @param array $args
     *
     * @return array
     */
    public function getEventUniqKey($args)
    {
        $key = array();
        $uniqKeys = array(
            'customer_email',
            'customer_id',
            'quote_id',
            'order_id',
            'store_id',
            'wishlist_id',
            'review_id',
            'order_item_id',
            'registry_id',
        );

        foreach ($args as $k => $v) {
            if (in_array($k, $uniqKeys)) {
                $key[] = $v;
            }
        }

        return implode('|', $key);
    }

    /**
     * Set event save processor
     *
     * @param Mirasvit_Email_Model_Service_EventSaveProcessorInterface $eventSaveProcessor
     *
     * @return $this
     */
    public function setEventSaveProcessor(Mirasvit_Email_Model_Service_EventSaveProcessorInterface $eventSaveProcessor)
    {
        $this->eventSaveProcessor = $eventSaveProcessor;

        return $this;
    }

    /**
     * Check can use event or not,
     * override this method in child class in order to validate specific event.
     *
     * @return bool
     */
    public function isActive()
    {
        return true;
    }
}
