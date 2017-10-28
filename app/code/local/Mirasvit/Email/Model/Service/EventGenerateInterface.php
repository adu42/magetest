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



interface Mirasvit_Email_Model_Service_EventGenerateInterface
{
    /**
     * Register multiple events
     *
     * @param array $events - event codes
     * @param string|int|bool $timestamp - generate emails from given timestamp
     */
    public function generate(array $events, $timestamp = false);

    /**
     * Check and register specific event
     *
     * @param string $eventCode - observed event code
     * @param string|int|bool $timestamp - timestamp or false
     * @param object|null - object associated with the occurred event, only for events registered via observed
     */
    public function registerEvent($eventCode, $timestamp = false, $observer = null);
}