<?php
/**
 * Ado Auto Login
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Ado
 * @package    Ado_Login
 * @author     ado42 <114458573@qq.com>
 * @copyright  Copyright (c) 2012 ado42 s.p.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

class Ado_Guestcookies_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $w = isset($_GET['w'])?$_GET['w']:'';
        $h = isset($_GET['h'])?$_GET['h']:'';
        $token = isset($_GET['token'])?$_GET['token']:'';
        if(!$w || !$h)die();
        $canLogin = false;
        $canGetLogin = true;
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
                    try{
                      $session->setCustomerAsLoggedIn($customer);
                    }catch (Exception $e){
                        Mage::logException($e);
                    }
                    $newToken = $w.'|'.$h.'|'.$canLogin;
                    $newToken = Mage::helper('core')->encrypt($newToken);
                }
                $canGetLogin = false;
            }
        }
        if($session->isLoggedIn()){
            $canLogin =  $session->getCustomer()->getEmail();
            $newToken = $w.'|'.$h.'|'.$canLogin;
            $newToken = Mage::helper('core')->encrypt($newToken);
        }
        if($canGetLogin)$cookie->set('guest_token', $newToken ,60,'/',null,false,false);
    }
}