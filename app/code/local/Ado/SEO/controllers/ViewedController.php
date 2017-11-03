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
    protected $_visited_key = 'visited_sku';
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

    protected function _getCatalogSession()
    {
        return Mage::getSingleton('catalog/session');
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
        $ajaxResponse = false;
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
            case('reviewlist'):
                if ($this->setProduct()) {
                    $html = $this->_getReviewListHtml();
                }
                break;
            case('reviewform'):
                if ($this->setProduct()) {
                    $html = $this->_getReviewFormHtml();
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
            case('user'):
                $ajaxResponse = true;
                $html = $this->userAction();
                break;
            case('wishlist'):
                $html = $this->wishlist();
                break;
             case('guest'):
                 $this->checkGuestLogin();
                 break;
            default:
        endswitch;
        if($ajaxResponse){
            $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($html));
        }else{
            $this->getResponse()->setBody($html);
        }
    }

    /**
     * 确认是否有商品数据
     * @return bool
     */
    public function setProduct(){
        $product = Mage::registry('product');
        if($product && $product->id())return true;
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
     * 获取评论数据
     * @return mixed
     */
    protected function _getReviewListHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('catalog_viewed_review_list_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }

    /**
     * 获取评论数据
     * @return mixed
     */
    protected function _getReviewFormHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('catalog_viewed_review_form_ajax');
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
					$customer->setPasswordConfirmation($this->getRequest()->getPost('confirmation'));
                    $customer->setEmailName();
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

    /**
     * 自动登陆
     */
    public function checkGuestLogin(){
        $w = isset($_GET['w'])?$_GET['w']:'';
        $h = isset($_GET['h'])?$_GET['h']:'';
        $token = isset($_GET['token'])?$_GET['token']:'';
        if(!$w || !$h)die();
        $canLogin = false;
        if(!empty($token)){
            $token = Mage::helper('core')->decrypt($token);
            $token = explode('|',$token);
            if(count($token)===3){
                if($w==$token[0] && $h==$token[1]){
                    $canLogin = $token[2];
                }
            }
        }
        $session = Mage::getSingleton('customer/session');
        $newToken = '';
        $cookie = Mage::getSingleton('core/cookie');
        if($canLogin){
            if(!$session->isLoggedIn()){
                $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($canLogin);
                if($customer && $customer->getId()){
                    $session->setCustomerAsLoggedIn($customer);
                    $newToken = $w.'|'.$h.'|'.$canLogin;
                    $newToken = Mage::helper('core')->encrypt($newToken);
                }
            }
        }
        if($session->isLoggedIn()){
            $canLogin =  $session->getCustomer()->getEmail();
            $newToken = $w.'|'.$h.'|'.$canLogin;
            $newToken = Mage::helper('core')->encrypt($newToken);
        }
        if(!empty($newToken)){
            $cookie->set('guest_token', $newToken ,60,'/',null,false,false);
        }
    }
    
    /**
     * wishlist
     */
    protected function wishlist()
    {
        if (!$this->_validateFormKey()) {
            return '0';
        }
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return ''; //$this->norouteAction();
        }

        $session = Mage::getSingleton('customer/session');

        $productId = (int)$this->getRequest()->getParam('product');
        if (!$productId) {
            return '-1';
            //  $this->_redirect('*/');
            //  return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            return '-2';
            //  $session->addError($this->__('Cannot specify product.'));
            //   $this->_redirect('*/');
            //   return;
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                return '-3';
                // Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            Mage::helper('wishlist')->calculate();

            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping.',
                $product->getName(), Mage::helper('core')->escapeUrl($referer));
            $session->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            return '-4';
            //  $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
        } catch (Exception $e) {
            return '-5';
            // $session->addError($this->__('An error occurred while adding item to wishlist.'));
        }
        return '1';
        // $this->_redirect('*', array('wishlist_id' => $wishlist->getId()));
    }

    protected function _getWishlist($wishlistId = null)
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }

        try {
            if (!$wishlistId) {
                $wishlistId = $this->getRequest()->getParam('wishlist_id');
            }
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            /* @var Mage_Wishlist_Model_Wishlist $wishlist */
            $wishlist = Mage::getModel('wishlist/wishlist');
            if ($wishlistId) {
                $wishlist->load($wishlistId);
            } else {
                $wishlist->loadByCustomer($customerId, true);
            }

            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                $wishlist = null;
                return false;//Mage::helper('wishlist')->__("Requested wishlist doesn't exist");
            }
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            // Mage::getSingleton('wishlist/session')->addError($e->getMessage());
            return false;
        } catch (Exception $e) {
            /*
            Mage::getSingleton('wishlist/session')->addException($e,
                Mage::helper('wishlist')->__('Wishlist could not be created.')
            );
            */
            return false;
        }

        return $wishlist;
    }

    /**
     * get client visited url|sku|times
     * 获取客户端访问数据
     * array unserialize $items;
     * $items = $this->_getCatalogSession()->getData($this->_visited_key);
     * 访问时间最长的10个商品id
     */
    public function visitAction(){
        if ($data =$this->getRequest()->getPost('data',false)) {
            $save_number = 10;
            try{
                $data = json_decode($data);
            }catch (Exception $e){}
            if($data && is_array($data)){
                $items = array();
                foreach ($data as $_item){
                    if($_item->sku){
                        $items[]= $_item->sku;
                    }
                }
                if(!empty($items)){
                    $count = count($items);
                    $diff = $save_number - $count;
                    if($diff>0){
                        $_items = $this->_getCatalogSession()->getData($this->_visited_key);
                        if($_items){
                            $_items=unserialize($_items);
                            if($_items){
                                $_items = array_diff($_items, $items);
                                $_items = array_slice($_items,0,$diff);
                                $items = array_merge($items,$_items);
                            }
                        }
                    }
                    $this->_getCatalogSession()->setData($this->_visited_key,serialize($items));
                }
            }
        }
    }

    /**
     * 用邮箱创建用户
     * use email create user
     * 用户名用邮箱的@前缀
     * 密码由系统注册密码
     * 其他不填，不数据验证
     *  逻辑验证：
     *  没有登陆
     *  没有注册过
     * 邮箱发送一封账户密码邮件，客户要确认
     */
    public function userAction()
    {
        $result=array('code'=>0,'message'=>'');
        $message = '';
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $result=array('code'=>1,'message'=>$this->__('You are logged in.'));
        }else{
            $session->setEscapeMessages(true); // prevent XSS injection in user input
            if ($this->getRequest()->isPost()) {
                $email = $this->getRequest()->getParam('email', false);
                $email=$this->checkEmail($email);
                if (!empty($email)) {
                    $customer = Mage::getModel('customer/customer')->loadByEmail($email);
                    if(!$customer && !$customer->getId()){
                        if (!$customer = Mage::registry('current_customer')) {
                            $customer = Mage::getModel('customer/customer')->setId(null);
                        }
                        //邮箱没有验证，不用订阅
                        $customer->setIsSubscribed(0);
                        $customer->getGroupId();
                        $newPassword = $customer->generatePassword();
                        $customer->setPassword($newPassword);
                        $customer->setConfirmation($newPassword);
                        $customer->setPasswordConfirmation($newPassword);
                        $customer->setEmailName();
                        /**
                         * Initialize customer group id
                         */
                        $customer->getGroupId();
                        $customer->save();

                        /**
                         * 这里必须要发邮件确认客户
                         * 这里登陆
                         */
                        try{
                            $customer->sendNewAccountEmail(
                                'confirmation',
                                $session->getBeforeAuthUrl(),
                                Mage::app()->getStore()->getId()
                            );
                            $message = $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail()));
                            $session->addSuccess($message);
                        }catch (Exception $e){
                            Mage::logException($e);
                        }

                        Mage::dispatchEvent('customer_register_success',
                            array('account_controller' => $this, 'customer' => $customer)
                        );
                        $session->setCustomerAsLoggedIn($customer);
                        Mage::getSingleton('core/cookie')->set('u_u_in', '1', 86400, '/', null, false, false);
                        $result['code']=0;
                    }else{
                        $result['code']=1;
                        $url = Mage::getUrl('customer/account/forgotpassword');
                        $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                    }
                }
            }
        }
        $result['message']=$message;
        return $result;
    }

    /**
     * 验证邮箱中的特殊单词，不要出现网址标识
     * @param $email
     * @return bool
     */
    protected function checkEmail($email){
        $email=trim($email);
        $isOk = true;
        if(empty($email))return false;
        $blackWords = array(
            'http','.co','.uk','www.','script','"',"'"
        );
        $prex = explode('@',$email);
        $prex = $prex[0];
        foreach ($blackWords as $word){
            if(stripos($prex,$word)!==false){
                $isOk=false;
                break;
            }
        }
        return $isOk?$email:$isOk;
    }
}