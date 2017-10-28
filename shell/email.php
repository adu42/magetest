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



require_once 'abstract.php';

class Mirasvit_Shell_Email extends Mage_Shell_Abstract
{
    public function run()
    {
        if ($this->getArg('send-queue')) {
            Mage::getModel('email/observer')->sendQueue();
        } elseif ($this->getArg('check-events')) {
            Mage::getModel('email/observer')->checkEvents();
        } elseif ($this->getArg('check-event') && $this->getArg('event')) {
            $this->checkEvents($this->getArg('event'), $this->getArg('from'));
        } elseif ($this->getArg('reset-database')) {
            $this->resetDatabase();
        } elseif ($this->getArg('generate') && is_numeric($this->getArg('trigger'))) {
            $timestamp = strtotime($this->getArg('generate'));
            echo PHP_EOL;
            echo Mage::helper('email')->__('Generate emails from: "%s"', date('Y-m-d', $timestamp));
            echo PHP_EOL;
            Mage::getModel('email/trigger')->load($this->getArg('trigger'))->generate($timestamp);
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Check events for specified event and print them
     */
    public function checkEvents($eventCode, $from = null)
    {
        $event = Mage::helper('email/event')->getEventModel($eventCode);
        if (!$event) {
            echo PHP_EOL;
            echo Mage::helper('email')->__('Event "%s" does not exist', $eventCode);
            exit();
        }

        if (!$from) {
            $from = now();
        }

        $events = $event->findEvents($eventCode, strtotime($from));
        $count = count($events);

        if ($count) {
            echo PHP_EOL;
            print_r($events);
            echo PHP_EOL;
        }

        echo PHP_EOL;
        echo Mage::helper('email')->__('Found "%s" event(s) since "%s"', $count, $from);
        echo PHP_EOL;
    }

    public function resetDatabase()
    {
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');

        $queueTable = $resource->getTableName('email/queue');
        $eventTable = $resource->getTableName('email/event');
        $variableTable = $resource->getTableName('core/variable_value');

        $write->query("DELETE FROM $queueTable");
        $write->query("DELETE FROM $eventTable");
        $write->query("UPDATE $variableTable SET plain_value = 100");
    }

    public function _validate()
    {
    }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f email.php -- [options]
  send-queue        Send current queue
  check-events      Check new events
  check-event       Display events for specified event code found from date (e.g. --event "order_status|new" --from "Y-m-d H:i:s")
  reset-database    Clear extension tables (events and queue)
  generate          Generate email queue (e.g. --generate Y-m-d --trigger trigger_id)
  help              This help

USAGE;
    }
}

$shell = new Mirasvit_Shell_Email();
$shell->run();
