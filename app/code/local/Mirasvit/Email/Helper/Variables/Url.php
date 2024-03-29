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



class Mirasvit_Email_Helper_Variables_Url
{
    public function getAddToCartUrl($parent, $args)
    {
        $query = array();
        if (isset($args[0])) {
            $query['id'] = $args[0];
        }

        return $this->_getUrl($parent, 'eml/index/addToCart', $query);
    }

    public function getRestoreCartUrl($parent, $args)
    {
        return $this->_getUrl($parent, 'eml/index/restoreCart');
    }

    public function getViewInBrowserUrl($parent, $args)
    {
        $url = $this->_getUrl($parent, 'eml/index/view');
        if ($parent->getQueue()) {
            $url = $this->_getUrl($parent, 'eml/index/view', array('queue_id' => $parent->getQueue()->getId()));
        }

        return $url;
    }

    /**
     * Unsubscribe only from already scheduled emails.
     */
    public function getUnsubscribeUrl($parent, $args)
    {
        $query = array();
        if (isset($args[0])) {
            $query['to'] = base64_encode($args[0]);
        }

        return $this->_getUrl($parent, 'eml/index/unsubscribe', $query);
    }

    /**
     * Unsubscribe from all triggers at all time.
     */
    public function getUnsubscribeAllUrl($parent, $args)
    {
        $query = array();
        if (isset($args[0])) {
            $query['to'] = base64_encode($args[0]);
        }

        return $this->_getUrl($parent, 'eml/index/unsubscribeAll', $query);
    }

    /**
     * Unsubscribe from all triggers at all time + newsletter.
     */
    public function getUnsubscribeNewsletterUrl($parent, $args)
    {
        $query = array();
        if (isset($args[0])) {
            $query['to'] = base64_encode($args[0]);
        }

        return $this->_getUrl($parent, 'eml/index/unsubscribeNewsletter', $query);
    }

    /**
     * confirmation of newsletter subscription.
     */
    public function getSubscriberConfirmationUrl($parent, $args)
    {
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($parent->getData('customer_email'));

        return Mage::helper('newsletter')->getConfirmationUrl($subscriber);
    }

    public function getResumeUrl($parent, $args)
    {
        $query = array();
        if (isset($args[0])) {
            $query['to'] = base64_encode($args[0]);
        }

        return $this->_getUrl($parent, 'eml/index/resume', $query);
    }

    public function getFacebookUrl($parent, $args)
    {
        return Mage::getStoreConfig('trigger_email/info/facebook_url');
    }

    public function getTwitterUrl($parent, $args)
    {
        return Mage::getStoreConfig('trigger_email/info/twitter_url');
    }

    public function getReviewUrl($parent, $args)
    {
        $query = array();
        if (isset($args[0]) && ($product = $args[0]) && $product instanceof Mage_Catalog_Model_Product) {
            $query['to'] = base64_encode(Mage::getUrl('review/product/list/', array('id' => $product->getId(), '_fragment' => 'review-form')));
        }

        return $this->_getUrl($parent, 'eml/index/resume', $query);
    }

    public function getReorderUrl($parent, $args)
    {
        $query = array();
        if (isset($args[0]) && ($order = $args[0])) {
            $query['order_id'] = ($order instanceof Mage_Sales_Model_Order) ? $order->getId() : $order;
        }

        return $this->_getUrl($parent, 'sales/order/reorder', $query);
    }

    protected function _getUrl($parent, $path, $query = array())
    {
        if ($parent->getQueue() && $parent->getStore()) {
            $arQuery = array_merge(array('code' => $parent->getQueue()->getUniqKeyMd5()), $query);

            return $parent->getStore()->getUrl($path, $arQuery);
        } else {
            return Mage::helper('email')->__('Not available in preview mode');
        }
    }
}
