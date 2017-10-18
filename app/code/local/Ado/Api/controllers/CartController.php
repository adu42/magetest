<?php
/**
 * Class Ado_Api_CartController
 */
class Ado_Api_CartController extends Mage_Core_Controller_Front_Action {
	///mobileapi/cart/add?product=421&qty=5&super_attribute[92]=21&super_attribute[180]=78
    /**
     * 添加商品到购物车
     */
	public function addAction() {
		$result = array (
			'code' => 1,
			'message' => null,
			'model' => null
		);
		if(Mage::getSingleton ( 'customer/session' )->isLoggedIn ()){
			try {
				$product_id = $this->getRequest ()->getParam ( 'product_id' );
				$params = $this->getRequest ()->getParams ();
				if (isset ( $params ['qty'] )) {
					$filter = new Zend_Filter_LocalizedToNormalized ( array (
							'locale' => Mage::app ()->getLocale ()->getLocaleCode ()
					) );
					$params ['qty'] = $filter->filter ( $params ['qty'] );
				} else
				// $params ['qty'] = 1; // 调试直接设为1
				// $param=$this->getRequest ()->getParam ( 'param' );
				// $qty = $this->getRequest ()->getParam ( 'qty' );
				if ($product_id == '') {
					$result['code'] = 1;
					$result['message'] = 'Product Not Added The SKU you entered" .$product_id. "was not found.';
				}
				$request = Mage::app ()->getRequest ();
				$product = Mage::getModel ( 'catalog/product' )->load ( $product_id );
				$session = Mage::getSingleton ( 'core/session', array (
						'name' => 'frontend'
				) );
				$cart = Mage::helper ( 'checkout/cart' )->getCart ();
				// $cart->addProduct ( $product, $qty );
				$cart->addProduct ( $product, $params );
				$session->setLastAddedProductId ( $product->getId () );
				$session->setCartWasUpdated ( true );
				$cart->save ();
				$items_qty = floor ( Mage::getModel ( 'checkout/cart' )->getQuote ()->getItemsQty () );
				$result = array("code"=>0, "msg"=> null, "model"=>array("items_qty"=>$items_qty));
				echo json_encode($result);
				return;
			} catch ( Exception $e ) {
				$result = array("code"=>1, "msg"=>$e->getMessage () , "model"=>null);
				echo json_encode($result);
				return;
			}
		}else{
			$result['code'] = 5;
			$result['message'] = 'not user login';
			echo json_encode($result);
		}
	}

    /**
     * @method removeCartItem
     */
    public function removeCartAction(){
    	$id = $this->getRequest ()->getParam ( 'cart_item_id' );
		$return_result = array(
			'code' => 0,
			'message'  => 'delete cart '.$id.' from carts success',
			'model' => null
		);
		if(Mage::getSingleton ( 'customer/session' )->isLoggedIn ()){
			Mage::getSingleton('checkout/cart')->removeItem($id)->save();
			Mage::getModel('checkout/cart')->save();
			$return_result['model'] = array(
				'items_qty' => $items_qty = floor (Mage::getModel('checkout/cart')->getQuote()->getItemsQty())
			);
		}else{
			$return_result['code'] = 5;
			$return_result['message'] = 'not user login';
		}
		echo json_encode($return_result);
    }



	/**
	 * Update customer's shopping cart
	 */
	public function updateAction(){
		$return_result = array(
			'code' => 0,
			'message'  => 'update carts success',
			'model' => null,
			'error' => null
		);
		try {
			$cartData = $this->getRequest()->getParam('cart');
			if (is_array($cartData)) {
				$filter = new Zend_Filter_LocalizedToNormalized(
					array('locale' => Mage::app()->getLocale()->getLocaleCode())
				);
				foreach ($cartData as $index => $data) {
					if (isset($data['qty'])) {
						$cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
					}
				}
				$cart = Mage::getSingleton('checkout/cart');
				if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
					$cart->getQuote()->setCustomerId(null);
				}
				$cartData = $cart->suggestItemsQty($cartData);
				$cart->updateItems($cartData)
					->save();
			}
			Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

		} catch (Mage_Core_Exception $e) {
			$return_result['code'] = 1;
			$return_result['error'] = $e->getMessage();
			echo json_encode($return_result);
			return false;
		} catch (Exception $e) {
			$return_result['code'] = 1;
			$return_result['error'] = 'Cannot update shopping cart.';
			Mage::logException($e);
			echo json_encode($return_result);
			return false;
		}
		return $this->getCartInfoAction ();
	}





	/**
	 * 1.24 取购物车中总金额及优惠券使用情况
	 */
	public function getCouponDetailAction() {
		if(Mage::getSingleton ( 'customer/session' )->isLoggedIn ()){
			$result = array (
				'code' => 0,
				'message' => 'get coupon detail success',
				'model' => $this->_getCartTotal()
			);
			echo json_encode ($result);
		}else{
			echo json_encode(array(
				'code' => 5,
				'message' => 'not user login',
				'model'=>array ()
			));
		}
	}

	/**
	 * 在购物车中使用和取消coupon 优惠券
	 * @return bool
	 */
	public function useCouponAction() {
		$couponCode = ( string ) Mage::app ()->getRequest ()->getParam ( 'coupon_code' );
		$cart = Mage::helper ( 'checkout/cart' )->getCart ();
		if (! $cart->getItemsCount ()) {
			echo json_encode ( array (
				'code' => 1,
				'message' => "You can't use coupon code with an empty shopping cart"
			) );
			return;
		}
		$oldCouponCode = $cart->getQuote()->getCouponCode();
		if (! strlen ( $couponCode ) && ! strlen ( $oldCouponCode )) {
			echo json_encode ( array (
				'code' => 2,
				'message' => "coupon code is Empty."
			));
			return;
		}
		try {
			$codeLength = strlen ( $couponCode );
			$isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;
			$cart->getQuote()->getShippingAddress()->setCollectShippingRates ( true );
			$cart->getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals()->save();
			if ($codeLength) {
				if ($isCodeLengthValid && $couponCode == $cart->getQuote ()->getCouponCode ()) {
					$messages = array (
						'code' => 0,
						'message' => $this->__ ( 'Coupon code "%s" was applied.', Mage::helper ( 'core' )->escapeHtml ( $couponCode ) )
					);
				} else {
					$messages = array (
						'code' => 1,
						'message' => $this->__ ( 'Coupon code "%s" is not valid.', Mage::helper ( 'core' )->escapeHtml ( $couponCode ) )
					);
				}
			} else {
				$messages = array (
					'code' => 0,
					'message' => $this->__ ( 'Coupon code was canceled.' )
				);
			}
		} catch ( Mage_Core_Exception $e ) {
			$messages = array (
				'code' => 3,
				'message' => $e->getMessage ()
			);
		} catch ( Exception $e ) {
			$messages = array (
				'code' => 4,
				'message' => $this->__ ( 'Cannot apply the coupon code.' ),
                'err' => $e
			);
		}
		$messages['model'] = $this->_getCartTotal();
		echo json_encode ($messages);
	}


    /**
     * 获取购物车信息
     */
	public function getCartInfoAction() {
		if(Mage::getSingleton ( 'customer/session' )->isLoggedIn ()){
			$cart = Mage::getSingleton ( 'checkout/cart' );
			if ($cart->getQuote ()->getItemsCount ()) {
				$cart->init ();
				$cart->save ();
			}
			$cart->getQuote ()->collectTotals ()->save ();
			$cartInfo = array ();
			$cartInfo ['is_virtual'] = Mage::helper ( 'checkout/cart' )->getIsVirtualQuote ();
			$cartInfo ['cart_items'] = $this->_getCartItems ();
			$message = sizeof ( $this->errors ) ? $this->errors : $this->_getMessage ();
			$cartInfo ['cart_items_count'] = Mage::helper ( 'checkout/cart' )->getSummaryCount ();
			$cartInfo ['payment_methods'] = $this->_getPaymentInfo ();
			$cartInfo ['allow_guest_checkout'] = Mage::helper ( 'checkout' )->isAllowedGuestCheckout ( $cart->getQuote () );
			$cartInfo ['symbol'] = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
			echo json_encode (array('code'=>0, 'message'=>$message, 'model'=>$cartInfo));
		}else{
			echo json_encode(array(
				'code' => 5,
				'message' => 'not user login',
				'model'=>array () 
			));
		}
	}

	/**
	 * 获取购物车详情（总金额以及优惠券使用情况）
	 * @return array
	 */
	protected function _getCartTotal() {
		$cart = Mage::getSingleton ( 'checkout/cart' );
		$totalItemsInCart = Mage::helper ( 'checkout/cart' )->getItemsCount (); // total items in cart
		$totals = Mage::getSingleton ( 'checkout/session' )->getQuote ()->getTotals (); // Total object
		$oldCouponCode = $cart->getQuote ()->getCouponCode ();
		$oCoupon = Mage::getModel ( 'salesrule/coupon' )->load ( $oldCouponCode, 'code' );
		$oRule = Mage::getModel ( 'salesrule/rule' )->load ( $oCoupon->getRuleId () );
		$subtotal = round ( $totals ["subtotal"]->getValue () ); // Subtotal value
		$grand_total = round ( $totals ["grand_total"]->getValue () ); // Grandtotal value
		if (isset ( $totals ['discount'] )) { // $totals['discount']->getValue()) {
			$discount = round ( $totals ['discount']->getValue () ); // Discount value if applied
		} else {
			$discount = '';
		}
		if (isset ( $totals ['tax'] )) { // $totals['tax']->getValue()) {
			$tax = round ( $totals ['tax']->getValue () ); // Tax value if present
		} else {
			$tax = '';
		}
		return array (
			'subtotal' => $subtotal,
			'grand_total' => $grand_total,
			'discount' => number_format(Mage::getModel('mapi/currency')->getCurrencyPrice($discount),2,'.',''),
			'tax' => $tax,
			'coupon_code' => $oldCouponCode,
			'symbol'=> Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(),
			'coupon_rule' => array(
				'rule_id' => $oRule->getData()['rule_id'],
				'name' => $oRule->getData()['name'],
				'description'=> $oRule->getData()['description'],
				'from_date'=> $oRule->getData()['from_date'],
				'to_date'=> $oRule->getData()['to_date'],
				'uses_per_customer'=> $oRule->getData()['uses_per_customer'],
				'is_active'=> $oRule->getData()['is_active'],
				'is_advanced'=> $oRule->getData()['is_advanced'],
				'product_ids'=> $oRule->getData()['product_ids'],
				'simple_action'=> $oRule->getData()['simple_action'],
				'discount_amount'=> $oRule->getData()['discount_amount'],
				'discount_qty'=> $oRule->getData()['discount_qty'],
				'discount_step'=> $oRule->getData()['discount_step'],
				'simple_free_shipping'=> $oRule->getData()['simple_free_shipping'],
				'apply_to_shipping'=> $oRule->getData()['apply_to_shipping'],
				'times_used'=> $oRule->getData()['times_used'],
				'is_rss'=> $oRule->getData()['is_rss'],
				'coupon_type'=> $oRule->getData()['coupon_type'],
				'use_auto_generation'=> $oRule->getData()['use_auto_generation'],
				'uses_per_coupon'=> $oRule->getData()['uses_per_coupon']
			)
		);
	}

    /**
     * @return array
     */
    //TODO:add method comment
	public function _getMessage() {
		$cart = Mage::getSingleton ( 'checkout/cart' );
		if (!Mage::getSingleton('checkout/type_onepage')->getQuote()->hasItems()) {
			$this->errors[] = 'Cart is empty!';
			return $this->errors;
		}
		if (!$cart->getQuote()->validateMinimumAmount()) {
			$warning = Mage::getStoreConfig('sales/minimum_order/description');
			$this->errors[] = $warning;
		}
	
		if (($messages = $cart->getQuote()->getErrors())) {
			foreach ($messages as $message) {
				if ($message) {
					$this->errors[] = $message->getText();
				}
			}
		}
		return $this->errors;
	}

    /**
     * 获取购物车支付信息
     * @return array
     */
	private function _getPaymentInfo() {
		$cart = Mage::getSingleton ( 'checkout/cart' );
		$methods = $cart->getAvailablePayment();
		foreach ($methods as $method) {
			if ($method->getCode() == 'paypal_express') {
				return array('paypalec');
			}
		}
		return array();
	}

    /**
     * 获取购物车商品
     * @return array
     */
	public function _getCartItems() {
		$cartItemsArr = array ();
		$cart = Mage::getSingleton ( 'checkout/cart' );
		$quote = $cart->getQuote();
		$currency = $quote->getquote_currency_code ();
		$displayCartPriceInclTax = Mage::helper ( 'tax' )->displayCartPriceInclTax ();
		$displayCartPriceExclTax = Mage::helper ( 'tax' )->displayCartPriceExclTax ();
		$displayCartBothPrices = Mage::helper ( 'tax' )->displayCartBothPrices ();
		$items=$quote->getAllVisibleItems();
		foreach ( $items as $item ) {
			$cartItemArr = array ();
			if($item->getProductType()=='bundle'){
				$cartItemArr['bundle_option'] =  Mage::getModel('mapi/cart')->getProductBundleOptions($item);
			}
			$cartItemArr ['cart_item_id'] = $item->getId ();
			$cartItemArr ['sku'] = $item->getSku ();
			$cartItemArr ['currency'] = $currency;
			$cartItemArr ['product_type'] = $item->getProductType ();
			$cartItemArr ['item_id'] = $item->getProduct ()->getId ();
			$cartItemArr ['item_title'] = strip_tags ( $item->getProduct ()->getName () );
			$cartItemArr ['qty'] = $item->getQty ();
			$cartItemArr ['thumbnail_pic_url'] = ( string ) Mage::helper('catalog/image')->init($item->getProduct(), 'thumbnail')->resize ( 250 );
			$cartItemArr ['custom_option']=$this->_getCustomOptions($item);
			if ($displayCartPriceExclTax || $displayCartBothPrices) {
				if (Mage::helper ( 'weee' )->typeOfDisplay ( $item, array (
						0,
						1,
						4 
				), 'sales' ) && $item->getWeeeTaxAppliedAmount ()) {
					$exclPrice = $item->getCalculationPrice () + $item->getWeeeTaxAppliedAmount () + $item->getWeeeTaxDisposition ();
				} else {
					$exclPrice = $item->getCalculationPrice ();
				}
			}
			if ($displayCartPriceInclTax || $displayCartBothPrices) {
				$_incl = Mage::helper ( 'checkout' )->getPriceInclTax ( $item );
				if (Mage::helper ( 'weee' )->typeOfDisplay ( $item, array (
						0,
						1,
						4 
				), 'sales' ) && $item->getWeeeTaxAppliedAmount ()) {
					$inclPrice = $_incl + $item->getWeeeTaxAppliedAmount ();
				} else {
					$inclPrice = $_incl - $item->getWeeeTaxDisposition ();
				}
			}
			$cartItemArr ['item_price'] = max ( $exclPrice, $inclPrice ); // only display one
			array_push ( $cartItemsArr, $cartItemArr );
		}
		return $cartItemsArr;
	}

	/**
	 * @param $item
	 * @return array
	 */
	public function _getCustomOptions($item){
		$options=$item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
		$result=array();
		if($options){
			if(isset($options['options'])){
				$result=array_merge($result,$options['options']);
			}
			if(isset($options['additional_options'])){
				$result=array_merge($result,$options['additional_options']);
			}
			if(!empty($options['attributes_info'])){
				$result=array_merge($result,$options['attributes_info']);
			}
		}
		return $result;
	}

    /**
     * 获取购物车商品数量
     */
	public function getQtyAction() {
		if(Mage::getSingleton ( 'customer/session' )->isLoggedIn ()){
			$items_qty = floor ( Mage::getModel ( 'checkout/cart' )->getQuote ()->getItemsQty () );
			echo json_encode(
                array(
                    'code'=>0,
                    'message'=>null,
                    'model'=>array(
                        'num'=>$items_qty
                    )
                )
            );
		}else{
			echo json_encode(array(
				'code' => 5,
				'message' => 'not user login',
				'model'=>array () 
			));
		}
	}



    /**
     * get shopping car url
     */
	public function getCartUrlAction() {
		if(Mage::getSingleton ( 'customer/session' )->isLoggedIn ()){
			$productid = $this->getRequest ()->getParam ( 'productid' );
			$product = Mage::getModel ( "catalog/product" )->load ( $productid );
			$url = Mage::helper ( 'checkout/cart' )->getAddUrl( $product );
			$cart = Mage::helper ( 'checkout/cart' )->getCart ();
			$item_qty = $cart->getItemsQty ();
			$summarycount = $cart->getSummaryCount();
			echo json_encode(array(
				'code' => 0,
				'url' => $url,
				'item_qty' => $item_qty,
				'summary_count' =>  $summarycount
			));
		}else{
			echo json_encode(array(
				'code' => 5,
				'message' => 'not user login',
				'model'=>array ()
			));
		}
	}

    /**
     * 获得商品定制属性
     * @param $product_id
     */
	public function optionsAction(){
        $result['code'] = 0;
        $result['message'] = '';
        $result['block'] = '';
       // $product_id = $this->getRequest()->getParam('pid');
        $item_id = $this->getRequest()->getParam('item_id');
        $cart = $this->_getCart();
        $item = $cart->getQuote()->getItemById($item_id);
        if($item){
            $selectedOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            if(!empty($selectedOptions['options'])){
                foreach ($selectedOptions['options'] as $option_id=> $option_value){
                    $item->getProduct()->getPreconfiguredValues()->setData('options/'. $option_id,$option_value);
                }
            }
           $block = $this->getLayout()->createBlock('mapi/product_options');
           if($block){
                $result['block'] =  $block->setProduct($item->getProduct())->getOptionsHtml();
           }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * 总价，重新计算了吗？
     */
    public function getTotals(){
        Mage::getSingleton('checkout/session')->getQuote()
            ->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();
        return $this->getLayout()->createBlock('checkout/cart_totals')->setTemplate("checkout/cart/totals.phtml")->tohtml();
        // $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * 变更后的尺码等属性
     */
    public function getProductOptions($item){
        return $this->getLayout()->createBlock('checkout/cart_item_renderer')
            ->setItem($item)
            ->setTemplate("ado_cart/product/view/options/item.phtml")->tohtml();
    }

    /**
     * 更新购物车
     * Update customer's shopping cart
     */
    public function updateCartAction()
    {
        $result['code']=0;
        $result['message']='';
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                //只做了个数量的变更，可以查看model对象
                //这里还需要改商品属性
                $cart->updateItems($cartData)
                    ->save();

               // $isMobile = Mage::helper('ado_seo')->isMobile();
                // 行总价计算
                foreach ($cartData as $id=>$data){
                    $item = $cart->getQuote()->getItemById($id);
                    if(!$item)continue;
                    /*
                    $row_total = $item->getRowTotal();
                    $_row_total = Mage::helper('checkout')->formatPrice($row_total);

                    if(!$isMobile){
                        $original = $item->getQty()*($item->getProduct()->getPrice());
                        if($original!=$row_total){
                            $off = round(($original-$row_total)/$original*100,0);
                            $_row_total .='<span class="off">('.$off.'%OFF)</span><span class="original-price">'. Mage::helper('checkout')->formatPrice($original).'</span>';
                        }
                    }
                    */
                    $result['item_row_total'][$id]= Mage::helper('checkout')->formatPrice($item->getRowTotal());
                }

            }
            $this->_getSession()->setCartWasUpdated(true);
            $result['message']='ok';
        } catch (Mage_Core_Exception $e) {
            $result['code']=1;
            $result['message']=Mage::helper('core')->escapeHtml($e->getMessage());
            // $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $result['code']=1;
            $result['message']=$this->__('Cannot update shopping cart.');
           // $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
        $result['block']= $this->getTotals();
        $result['promotion_free_shipping']= $this->getPromotionFreeShipping();
        $result['itemsQty']= Mage::helper('checkout/cart')->getItemsCount();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * 获得促销
     * @return string
     */
    private function getPromotionFreeShipping(){
        $html = '';
        $str = Mage::getStoreConfig('ado_seo/cart/promotion_free_shipping');
         if(!empty($str)){
           $html = '
             <div class="freeship-hint">
                 <p class="mshe-title5 wrapper">
               ';
                     $endPrice=0;
                     $_diffPrice = '';
                     if (preg_match('/(\d+)/',$str,$r))$_diffPrice =(int)( isset($r[0])?$r[0]:0 );
                     $endPrice = max($_diffPrice,80);
                     $subtotal = Mage::getSingleton('checkout/session')->getQuote()->getSubtotal(); //- Mage::getSingleton('checkout/session')->getQuote()->getShippingAmount();
                     $endPrice = $endPrice-$subtotal;
                     $endPrice = max($endPrice,0);
                     if($endPrice>0) {
                         $endPrice = '<span class="freeship-price">' . Mage::helper('checkout')->formatPrice($endPrice) . '</span>';
                         $str = $this->__($str);
                         $html .= str_replace($_diffPrice, $endPrice, $str);
                     }else{
                         $html .= $this->__('Your enjoy Free EXPRESS SHIPPING!');
                     }

             $html .= ' </p>
             </div>';
         }
         return $html;
    }



    /**
     * 更改商品属性
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $result['code']=0;
        $result['message']='';
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                $result['code']=1;
                $result['message']=$this->__('Quote item is not found.');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                die();
            }

            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }else{
                //自己重新获取一下数量
                $params['qty'] = $quoteItem->getQty();
            }


            //重新获取一下options，赋值一下
           $options =  $quoteItem->getProduct()->getTypeInstance(true)->getOrderOptions($quoteItem->getProduct());
         //  print_r($options);
           if(isset($options['info_buyRequest']) && isset($options['info_buyRequest']['options'])){
               foreach ($params['options'] as  $option_id => $value){
                    if(empty($value)){
                        if (isset($options['info_buyRequest']['options'][$option_id])){
                            $params['options'][$option_id] = $options['info_buyRequest']['options'][$option_id];
                        }
                    }
               }
            }

            // 必须传递qty，否组是后台的默认最小值1
            // 没有看到购物车模型这个方法，后面也确实没有获取到新的选中数据，这个方法失败。
            $item = $cart->updateItem($id,new Varien_Object($params));


            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }
            Varien_Profiler::start('cart->save');
            $cart->save();
            Varien_Profiler::stop('cart->save');
            $this->_getSession()->setCartWasUpdated(true);

            if($item && !is_string($item)){
                $result['items']= $this->getProductOptions($item);
                $result['item_id']= $item->getId();
                $result['item_row_total'][$item->getId()]= Mage::helper('checkout')->formatPrice($item->getRowTotal());
            }

            $result['message']='ok';
        } catch (Mage_Core_Exception $e) {
            $result['message']=Mage::helper('core')->escapeHtml($e->getMessage());
        } catch (Exception $e) {
            $result['message']=$this->__('Cannot update the item.');
            Mage::logException($e);
        }
        $result['block']= $this->getTotals();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

} 
