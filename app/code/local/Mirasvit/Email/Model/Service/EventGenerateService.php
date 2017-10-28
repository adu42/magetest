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



class Mirasvit_Email_Model_Service_EventGenerateService implements Mirasvit_Email_Model_Service_EventGenerateInterface
{
	/**
	 * Default strategy - schedule the emails starting from email queue generation date
	 */
	const SCHEDULE_STRATEGY_DEFAULT = 0;

	/**
	 * Event strategy - schedule the emails starting from event creation date
	 */
	const SCHEDULE_STRATEGY_EVENT	= 1;

	/**
	 * @var Mirasvit_Email_Model_Event
     */
	private $eventModel;

	/**
	 * @var Mirasvit_Email_Helper_Event
     */
	private $eventHelper;

	private $scheduleStrategy;

	private $triggerId = null;

	public function __construct()
	{
		$this->eventHelper = Mage::helper('email/event');
		$this->eventModel = Mage::getModel('email/event');
		$this->scheduleStrategy = self::SCHEDULE_STRATEGY_DEFAULT;
	}

	/**
	 * Set email schedule strategy, default or event
	 *
	 * @param int|null
	 *
	 * @return $this
	 */
	public function setScheduleStrategy($scheduleStrategy)
	{
		$this->scheduleStrategy = $scheduleStrategy;

		return $this;
	}

	/**
	 * Set associated trigger ID
	 *
	 * @param int
	 *
	 * @return $this
	 */
	public function setTriggerId($triggerId)
	{
		$this->triggerId = $triggerId;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function generate(array $events, $timestamp = false)
	{
		foreach ($events as $eventCode) {
			$this->registerEvent($eventCode, $timestamp);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerEvent($eventCode, $timestamp = false, $observer = null)
	{
		$event = $this->eventHelper->getEventModel($eventCode);
		// triggerId exists only in manual generation
		if ($this->triggerId) {
			$triggers = array($this->triggerId);
			/* @var $manualSaveProcessor Mirasvit_Email_Model_Service_EventSaveProcessor_ManualSaveProcessor */
			$manualSaveProcessor = Mage::getModel(
				'email/service_eventSaveProcessor_manualSaveProcessor',
				$this->scheduleStrategy
			);
			$event->setEventSaveProcessor($manualSaveProcessor);
		} else {
			$triggers = $this->eventHelper->getAssociatedTriggers($eventCode);
		}

		$events = $event->check($eventCode, $timestamp, $observer);

		if ($events) {
			$this->eventModel->addTriggerEvents($events, $triggers);
		}
	}
}