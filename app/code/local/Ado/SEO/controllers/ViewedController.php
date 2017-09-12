<?php

/**
 * Ado Ciobanu
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Ado_Seo
 * @copyright   Copyright (c) 2013 Ado Ciobanu
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ado_Seo_ViewedController extends Mage_Core_Controller_Front_Action
{

    /**
     * Display search result
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * ajax 入口，获得一些静态数据，html内容的入口
     */
    public function helpAction()
    {
        $help = $this->getRequest()->getParam('what');
        $urlSuffix = Mage::helper('catalog/category')->getCategoryUrlSuffix();
        if ($urlSuffix !== '/') {
            $help = str_replace($urlSuffix, '', $help);
        } else {
            $help = trim($help, '/');
        }
        $html = '';
        switch ($help):
            case('color'):
                $html = $this->getLayout()->createBlock('cms/block')->setBlockId('color-chart-mobile')->toHtml();
                break;
            case('size'):
                $html = $this->getLayout()->createBlock('cms/block')->setBlockId('size-chart-mobile')->toHtml();
                break;
            case('menu'):
                $html = $this->getLayout()->createBlock('cms/block')->setBlockId('top-left-link-mobile')->toHtml();
                break;
            case('bottomlink'):
                $html = $this->getLayout()->createBlock('cms/block')->setBlockId('bottom-link-mobile')->toHtml();
                break;
            case('exchange'):
                $html = $this->getLayout()->createBlock('cms/block')->setBlockId('helper-exchange-refund-mobile')->toHtml();
                break;
            case('faq'):
                $html = $this->getLayout()->createBlock('cms/block')->setBlockId('helper-delivery-faq-mobile')->toHtml();
                break;
            case('review'):
                if ($this->setProduct()) {
                    $html = $this->_getReviewHtml();
                }
                break;
            case('history'):
                $html = $this->_getHistoryHtml();
                break;
            case('in'):
                $html = $this->login();
                break;
            case('nin'):
                $html = $this->createPostAction();
                break;
            default:
        endswitch;
        echo $html;
    }

    /**
     * 确认是否有商品数据
     * @return bool
     */
    public function setProduct()
    {
        $productId = $this->getRequest()->getParam('id');
        if ($productId) {
            $productId = (int)$productId;
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product && $product->getId()) {
                Mage::register('current_product', $product);
                Mage::register('product', $product);
                return true;
            }
        }
        return false;
    }

    /**
     * 获取评论数据
     * @return mixed
     */
    protected function _getReviewHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('catalog_viewed_review_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }

    /**
     * 访问历史记录
     */
    protected function _getHistoryHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('catalog_viewed_history_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }

    /**
     * login ajax
     * @return int|string
     */
    protected function login()
    {
        if ($this->_getSession()->isLoggedIn()) {
            return 1;
        }
        $session = $this->_getSession();
        $message = '';
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $logind = $session->login($login['username'], $login['password']);
                    if ($logind) {
                        Mage::getSingleton('core/cookie')->set('u_u_in', '1', 86400, '/', null, false, false);
                        return 1;
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $message = $this->__('Login and password are required.');
            }
        }
        return $message;
    }

    /**
     * Create customer account action
     */
    protected function createPostAction()
    {
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            return 1;
        }
        $message = '';
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if ($this->getRequest()->isPost()) {
            $errors = array();

            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_create')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }

            /**
             * Initialize customer group id
             */
            $customer->getGroupId();

            if ($this->getRequest()->getPost('create_address')) {
                /* @var $address Mage_Customer_Model_Address */
                $address = Mage::getModel('customer/address');
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('customer_register_address')
                    ->setEntity($address);

                $addressData = $addressForm->extractData($this->getRequest(), 'address', false);
                $addressErrors = $addressForm->validateData($addressData);
                if ($addressErrors === true) {
                    $address->setId(null)
                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                    $addressForm->compactData($addressData);
                    $customer->addAddress($address);

                    $addressErrors = $address->validate();
                    if (is_array($addressErrors)) {
                        $errors = array_merge($errors, $addressErrors);
                    }
                } else {
                    $errors = array_merge($errors, $addressErrors);
                }
            }

            try {
                $customerErrors = $customerForm->validateData($customerData);
                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {
                    $customerForm->compactData($customerData);
                    $customer->setPassword($this->getRequest()->getPost('password'));
                    $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                }

                $validationResult = count($errors) == 0;

                if (true === $validationResult) {
                    $customer->save();

                    Mage::dispatchEvent('customer_register_success',
                        array('account_controller' => $this, 'customer' => $customer)
                    );

                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                            'confirmation',
                            $session->getBeforeAuthUrl(),
                            Mage::app()->getStore()->getId()
                        );
                        $message = $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail()));
                        $session->addSuccess($message);
                        return $message;
                    } else {
                        $session->setCustomerAsLoggedIn($customer);
                        Mage::getSingleton('core/cookie')->set('u_u_in', '1', 86400, '/', null, false, false);
                        return 1;
                    }
                } else {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if (is_array($errors)) {
                        foreach ($errors as $errorMessage) {
                            $message .= $errorMessage . '<br/>';
                        }
                    } else {
                        $message .= $this->__('Invalid customer data');
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                    $session->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
            } catch (Exception $e) {
                $message = $e->getMessage() . $this->__('Cannot save the customer.');
            }
        }

        return $message;
    }


}