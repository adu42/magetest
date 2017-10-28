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



class Mirasvit_Email_Model_Observer_Event
{
    /**
     * @var Mirasvit_Email_Model_Service_EventGenerateInterface
     */
    private $eventGenerateService;

    public function __construct()
    {
        $this->eventGenerateService = Mage::getModel('email/service_eventGenerateService');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onWishlistShared(Varien_Event_Observer $observer)
    {
        $this->eventGenerateService->registerEvent('wishlist_wishlist|shared', false, $observer);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onNewsletterSubscriberSaveBefore(Varien_Event_Observer $observer)
    {
        $originalStatus = $observer->getEvent()->getDataObject()->getOrigData('subscriber_status');
        $status = $observer->getEvent()->getDataObject()->getSubscriberStatus();

        if ($originalStatus !== $status) {
            $this->eventGenerateService->registerEvent(
                'customer_newsletter|subscription_status_changed',
                false,
                $observer
            );
        }

        if ($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
            $this->eventGenerateService->registerEvent('customer_newsletter|subscribed', false, $observer);
        } elseif ($status == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
            $this->eventGenerateService->registerEvent('customer_newsletter|unsubscribed', false, $observer);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onCustomerSaveAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        // If a customer exists and associated with the frontend website
        if ($customer->getId() && $customer->getWebsiteId()) {
            // Process the event if a customer group was changed
            if ($customer->getOrigData('group_id') !== $customer->getGroupId()) {
                $this->eventGenerateService->registerEvent(
                    Mirasvit_Email_Model_Event_Customer_Groupchanged::EVENT_CODE,
                    false,
                    $observer
                );
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onReviewBeforeSave(Varien_Event_Observer $observer)
    {
        $originalReviewStatus = $observer->getEvent()->getDataObject()->getOrigData('status_id');
        $newReviewStatus = $observer->getEvent()->getDataObject()->getData('status_id');

        if ($newReviewStatus == Mage_Review_Model_Review::STATUS_APPROVED &&
            $originalReviewStatus !== $newReviewStatus) {
            $this->eventGenerateService->registerEvent('customer_review|approved', false, $observer);
        }
    }

    /**
     * Event dispatched by the overridden method Mirasvit_EmailSmtp_Model_Email_Template::send()
     * observer contains the following parameters:
     *  - Mirasvit_EmailSmtp_Model_Email_Template $object
     *  - array $variables - variables passed to the template
     *  - string $trace - result of function Varien_Debug::backtrace(true, false, false).
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterSendEmail(Varien_Event_Observer $observer)
    {
        if (strpos($observer->getEvent()->getTrace(), 'sendPaymentFailedEmail')) {
            $this->eventGenerateService->registerEvent(
                Mirasvit_Email_Model_Event_Email_FailedPayment::EVENT_CODE,
                false,
                $observer
            );
        }
    }
}
