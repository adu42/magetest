<?php

/**
 * Created by PhpStorm.
 * User: 杜兵
 * Date: 2017/3/15
 * Time: 11:08
 */

class Ado_Api_BaseController extends Mage_Core_Controller_Front_Action
{
    public $storeId = "1";
    public $viewId = "";
    public $currency = "";
    /**
     * 输出格式在这里定义
     */
    public function _construct()
    {
        header('content-type: application/json; charset=utf-8');
        header("access-control-allow-origin: *");
        $this->storeId = Mage::app()->getFrontController()->getRequest()->getHeader('storeId');
        $this->viewId = Mage::app()->getFrontController()->getRequest()->getHeader('viewId');
        $this->currency = Mage::app()->getFrontController()->getRequest()->getHeader('currency');
        Mage::app()->setCurrentStore($this->storeId);
        Mage::app()->getStore($this->storeId)->setCurrentCurrency($this->currency);
        parent::_construct();
    }

    /**
     * 获取post过来的值
     * @return array|mixed|string
     */
    public function postJsonParams(){
        $params = @file_get_contents("php://input");
        if(!empty($params)){
            $params = json_decode($params,true);
        }else{
            $params = $this->getRequest ()->getParams();
        }
        if(empty($params)){
            echo json_encode ( array (
                'code'=>1,
                'message'=>'Nothing Posted.',
                'model'=> null
            ) );
            die();
        }
        return $params;
    }

    /**
     * 输出错误
     */
    public function responseError($message=''){
        $result = array(
            'code'=>1,
            'message'=>$message,
            'model'=>null,
        );
        echo json_encode($result);
        die();
    }

    /**
     * 输出正常结果
     * @param array $result
     * @param string $message
     */
    public function responseResult($result=array(),$message=null){
        $result = array(
            'code'=>1,
            'message'=>$message,
            'model'=>$result,
        );
        echo json_encode($result);
        die();
    }

    /**
     * 检查商品是否加入了wishlist
     * @param $productId
     * @return bool
     */
    public function check_wishlist($productId)
    {
        $customer = Mage::getSingleton("customer/session");
        if ($customer->isLoggedIn()):
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer->getId(), true);
            $wishListItemCollection = $wishlist->getItemCollection();
            $wishlist_product_id = array();
            foreach ($wishListItemCollection as $item) {
                $wishlist_product_id[] = $item->getProductId();
            }
            if (in_array($productId, $wishlist_product_id))
                return true;
            else
                return false;

        else:
            return false;
        endif;
    }


    /**
     * check cache according to request
     */
    public function checkcache($key, $store = 1)
    {
        try {
            $cache = Mage::app()->getCache();
            $cache_key = "adoapi_" . $key . "_store" . $store;
            if ($cache->load($cache_key)):
                echo $cache->load($cache_key);
                exit;
            endif;
            return false;
        } catch (exception $e) {
            return false;
        }
    }
    /**
     * create cache according to request
     */
    public function createNewcache($key, $store = 1, $data)
    {
        try {
            $CACHE_EXPIRY = 300;
            $cache = Mage::app()->getCache();
            $cache_key = "adoapi_" . $key . "_store" . $store;
            $cache->save($data, $cache_key, array("adoapi"), $CACHE_EXPIRY);
            return true;
        } catch (exception $e) {
            return false;
        }
    }
}