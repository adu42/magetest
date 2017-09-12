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



abstract class Mirasvit_Email_Model_Event_Abstract
{
    /**
     * @var null|int
     */
    private $triggerId = null;

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
     * @return bool - true on success otherwise false
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

            if (($savedEvent = $this->saveEvent($eventCode, $uniqKey, $event))) {
                $savedEvents[$savedEvent->getId()] = $savedEvent->getId();
            }
        }

        Mage::helper('email')->setVar($timeVar, Mage::getSingleton('core/date')->gmtTimestamp());
        Mage::dispatchEvent('after_check_m_email_events', array(
                'event_model' => $this,
                'new_events' => $savedEvents,
                'event_code' => $eventCode,
            )
        );

        return true;
    }

    /**
     * default $args
     * ! customer_name
     * ! customer_email
     * ! store_id
     * ? customer_id
     * ? customer
     * ? order.
     *
     * @param string $code
     * @param string $uniqKey
     * @param array  $args
     *
     * @return Mirasvit_Email_Model_Event|null
     */
    public function saveEvent($code, $uniqKey, $args)
    {
        $args = $this->prepareArgs($args);
        $event = $this->checkSimilarEvent($code, $uniqKey, $args['time'] - $args['expire_after']);

        if ($this->getTriggerId()) { // Return existing event only for manual generation
            $args['time'] = time();
            if ($event) {
                // Re-save event with new scheduled at time
                $event->setArgs(array_merge($event->getArgs(), array('time' => $args['time'])))->save();

                return $event;
            }
        } elseif ($event) { // Return null if similar event exists only for auto generation
            return;
        }

        $event = Mage::getModel('email/event')->setCode($code)
            ->setUniqKey($uniqKey)
            ->setArgs($args)
            ->setStoreIds($args['store_id'])
            ->setCreatedAt(date('Y-m-d H:i:s', $args['time']))
            ->save();

        return $event;
    }

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
        );

        foreach ($args as $k => $v) {
            if (in_array($k, $uniqKeys)) {
                $key[] = $v;
            }
        }

        return implode('|', $key);
    }

    /**
     * Prepare event arguments.
     *
     * @param array $args
     *
     * @return array
     */
    protected function prepareArgs($args)
    {
        if (!isset($args['expire_after'])) {
            $args['expire_after'] = 3600;
        }

        if (!isset($args['time'])) {
            $args['time'] = time();
        }

        return $args;
    }

    /**
     * Check if not expired event with the same arguments exists yet.
     *
     * @param string     $code
     * @param string     $uniqKey
     * @param string|int $gmtExpireAt - timestamp, which indicates event expiration date
     *
     * @return Mirasvit_Email_Model_Event|void
     */
    protected function checkSimilarEvent($code, $uniqKey, $gmtExpireAt)
    {
        $event = Mage::getModel('email/event')->getCollection()
            ->addFieldToFilter('uniq_key', $uniqKey)
            ->addFieldToFilter('code', $code)
            ->addFieldToFilter('created_at', array('gt' => date('Y-m-d H:i:s', $gmtExpireAt)))
            ->getFirstItem();

        if ($event->getId()) {
            return $event;
        }
    }

    /**
     * Trigger ID is set only for manual generation of email queue.
     *
     * @return int|null
     */
    public function getTriggerId()
    {
        return $this->triggerId;
    }

    /**
     * @param int|null $triggerId
     *
     * @return $this
     */
    public function setTriggerId($triggerId)
    {
        $this->triggerId = $triggerId;

        return $this;
    }
}
