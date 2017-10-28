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



class Mirasvit_Email_Model_Service_EventSaveProcessor_ManualSaveProcessor
    extends Mirasvit_Email_Model_Service_EventSaveProcessor_AbstractSaveProcessor
{
    private $scheduleStrategy;

    public function __construct(
        $scheduleStrategy = Mirasvit_Email_Model_Service_EventGenerateService::SCHEDULE_STRATEGY_DEFAULT
    ) {
        $this->scheduleStrategy = $scheduleStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function saveEvent($eventCode, $uniqueKey, $args)
    {
        $args = $this->prepareArgs($args);
        $event = $this->checkSimilarEvent($eventCode, $uniqueKey, $args['time'] - $args['expire_after']);

        if ($this->scheduleStrategy == Mirasvit_Email_Model_Service_EventGenerateService::SCHEDULE_STRATEGY_DEFAULT) {
            $args['time'] = time();
        }

        if ($event) {
            // Re-save event with new scheduled at time
            $event->setArgs(array_merge($event->getArgs(), array('time' => $args['time'])))->save();

            return $event; // Return existing event only for manual generation
        }

        return $this->save($eventCode, $uniqueKey, $args);
    }
}