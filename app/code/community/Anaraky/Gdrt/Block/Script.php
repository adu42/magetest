<?php
class Anaraky_Gdrt_Block_Script extends Mage_Core_Block_Abstract {
    
    private $_storeId = 0;
    private $_pid = false;
    private $_pid_prefix = "";
    private $_pid_prefix_ofcp = 0;
    private $_pid_ending = "";
    private $_pid_ending_ofcp = 0;
	private $_use_base_currency = false;
    
    private function getEcommProdid($product)
    {
        $ecomm_prodid = (string)($this->_pid ? $product->getId() : $product->getSku());
        $ofcp = false;
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
            $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
        {
            $ofcp = true;
        }
        
        if (!empty($this->_pid_prefix) && (($this->_pid_prefix_ofcp === 1 && $ofcp) ||
            $this->_pid_prefix_ofcp === 0))
        {
                $ecomm_prodid = $this->_pid_prefix . $ecomm_prodid;
        }
        
        if (!empty($this->_pid_ending) && (($this->_pid_ending_ofcp === 1 && $ofcp) ||
            $this->_pid_ending_ofcp === 0))
        {
                $ecomm_prodid .= $this->_pid_ending;
        }
        
        return $ecomm_prodid;
    }
    
    private function getParams()
    {
        if ((int)Mage::getStoreConfig('gdrt/general/gdrt_product_id', $this->_storeId) === 0)
            $this->_pid = true;
        
        $this->_pid_prefix = Mage::getStoreConfig('gdrt/general/gdrt_product_id_prefix', $this->_storeId);
        $this->_pid_prefix_ofcp = (int)Mage::getStoreConfig('gdrt/general/gdrt_product_id_prefix_ofcp', $this->_storeId);
        $this->_pid_ending = Mage::getStoreConfig('gdrt/general/gdrt_product_id_ending', $this->_storeId);
        $this->_pid_ending_ofcp = (int)Mage::getStoreConfig('gdrt/general/gdrt_product_id_ending_ofcp', $this->_storeId);
		$this->_use_base_currency = Mage::getStoreConfig('gdrt/general/gdrt_use_base_currency', $this->_storeId);
        
        $inclTax = false;
        if ((int)Mage::getStoreConfig('gdrt/general/gdrt_tax', $this->_storeId) === 1)
            $inclTax = true;
                
        $type = $this->getData('pageType');
        $params = array('ecomm_pagetype' => 'other');
        $fbParams = array('content_type'=>'product');
        switch ($type) {
            case 'home':
                $params = array( 'ecomm_pagetype' => 'home');
              //  $fbParams['track']='PageView';
                unset($fbParams['content_type']);
                break;
            
            case 'searchresults':
                $params = array( 'ecomm_pagetype' => 'searchresults');
                $fbParams['track']='ViewContent';
                $fbParams['content_type']='product_group';
                $fbParams['content_name']='searchresults';
                $fbParams['content_ids']='2';
                break;
            
            case 'category':
                $category = Mage::registry('current_category');

                $params = array(
                    'ecomm_pagetype' => 'category',
                    'ecomm_category' => (string)$category->getName()
                );
                $fbParams['track']='ViewContent';
                $fbParams['content_type']='product_group';
                $fbParams['content_name']=(string)$category->getName();
                $fbParams['content_ids']="'".$category->getId()."'";

                $storeCode = Mage::app()->getStore()->getCode();
                if($storeCode!='en' && $storeCode!='admin' && $storeCode!='default'){
                    try{
                    $storeId=1;
                    $defaultStoreCategory = Mage::getModel('catalog/category');
                    $defaultStoreCategory->setStoreId($storeId);
                    $defaultStoreCategory->load($category->getId());
                    $enCatalog =(string)$defaultStoreCategory->getName();
                    $params['category']=$enCatalog;
                    unset($defaultStoreCategory);
                    }catch(exception $e){
                        
                   }
                }
                unset($category);
                break;
            
            case 'product':
                $product = Mage::registry('current_product');
				
				if($this->_use_base_currency){
					$totalvalue = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), $inclTax);
					$currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
				}else{
					$currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
					$totalvalue = Mage::app()->getStore()->convertPrice(
								Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), $inclTax)
							);

				}
                        
                $params = array(
                    'ecomm_prodid' => $this->getEcommProdid($product),
                    'ecomm_pagetype' => 'product',
                    'ecomm_totalvalue' =>  (float)number_format($totalvalue, '2', '.', '')
                );

                $category = $product->getCategory();
                $categoryName = '';
                if($category && $category->getId()){
                    $categoryName=$category->getName();
                }
                
                if(empty($categoryName)){
                    $categoryIds = $product->getCategoryIds();
                    array_splice($categoryIds,5);
				    $categoryId=end($categoryIds);
                    $_category = Mage::getModel('catalog/category')->load($categoryId);
                    if($_category && $_category->getId()){
                    $categoryName = $_category->getName();
                    }
                }
                
                
                
                $fbParams['track']='ViewContent';
                $fbParams['content_type']='product';
                $fbParams['content_category']=$categoryName;
                $fbParams['content_name']=(string)$product->getName();
                $_sku = $product->getSku();
                $_sku = explode('-',$_sku);
                $sku = $_sku[0];
                $fbParams['content_ids']="'".$sku."'";
                $fbParams['value']=(float)number_format($totalvalue, '2', '.', '');
                $fbParams['currency']=$currencyCode;
                unset($categoryName);
                unset($product);
                break;
            
            case 'cart':
                $cart = Mage::getSingleton('checkout/session')->getQuote();
                $items = $cart->getAllVisibleItems();

				if($this->_use_base_currency){
					$totalvalue = $cart->getBaseGrandTotal();
					$currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
				}else{
					$currencyCode = $cart->getQuoteCurrencyCode();
					$totalvalue = $cart->getGrandTotal();
				}

                $sku = array();
                $fbParams['track']='AddToCart';
                $fbParams['content_name']='Shopping Cart';
                $fbParams['content_type']='product';
                $fbParams['value']=(float)number_format($totalvalue, '2', '.', '');
                $fbParams['currency']=$currencyCode;

                if (count($items) > 0) {
                    $data  = array();
                    $totalvalue = 0;
                    foreach ($items as $item)
                    {
                        $data[0][] = $this->getEcommProdid($item->getProduct());
                        $data[1][] = (int)$item->getQty();
						
						if($this->_use_base_currency){
						$totalvalue += $inclTax ? $item->getBaseTaxAmount() : $item->getBaseRowTotal();
						}else{
                        $totalvalue += $inclTax ? $item->getTaxAmount() : $item->getRowTotal();
						}

                        $_sku =  $item->getProduct()->getSku();
                        $_sku = explode('-',$_sku);
                        $sku[] = $_sku[0];

                    }

                    $params = array(
                        'ecomm_prodid' => $data[0],
                        'ecomm_pagetype' => 'cart',
                        'ecomm_quantity' => $data[1],
                        'ecomm_totalvalue' => (float)number_format($totalvalue, '2', '.', '')
                    );
                }

                $fbParams['content_ids']= "['".implode("','",$sku)."']";

                unset($cart, $items, $item, $data);
                break;
            
            case 'purchase':
                $isOrder = false;
                $sku = array();
                $fbParams['track']='Purchase';
                // $fbParams['content_name']='Shopping Cart';
                $fbParams['content_type']='product';
                $orderid =  Mage::getSingleton('checkout/session')->getLastOrderId();
                if($orderid){
                    $order = Mage::getModel('sales/order')->load($orderid);
                    if($order && $order->getId()){
                        $isOrder = true;
						
						if($this->_use_base_currency){
							$totalvalue = $order->getBaseGrandTotal();
							$currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
						}else{
							$currencyCode = $order->getOrderCurrencyCode();
							$totalvalue = $order->getGrandTotal();
						}


                        $fbParams['value']=(float)number_format($totalvalue, '2', '.', '');
                        $fbParams['currency']=$currencyCode;
                        $items = $order->getAllVisibleItems();
                        if(count($items)>0){
                            $data  = array();
                            $totalvalue = 0;
                        foreach($items as $item){
                            if($item->getId()){
                                 $data[0][] = $this->getEcommProdid($item->getProduct());
                                    $data[1][] = (int)$item->getQty();

									if($this->_use_base_currency){
										$totalvalue += $inclTax ? $item->getBaseTaxAmount() : $item->getBaseRowTotal();
									}else{
										$totalvalue += $inclTax ? $item->getTaxAmount() : $item->getRowTotal();
									}

                                    $_sku =  $item->getProduct()->getSku();
                                    $_sku = explode('-',$_sku);
                                    $sku[] = $_sku[0];
                            }
                        }
                            $params = array(
                                'ecomm_prodid' => $data[0],
                                'ecomm_pagetype' => 'purchase',
                                'ecomm_quantity' => $data[1],
                                'ecomm_totalvalue' => (float)number_format($totalvalue, '2', '.', '')
                            );
                        }
                    }
                }

                if(!$isOrder){
                $cart = Mage::getSingleton('checkout/session')->getQuote();
                $items = $cart->getAllVisibleItems();

				if($this->_use_base_currency){
					$totalvalue = $cart->getBaseGrandTotal();
					$currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
				}else{
					$currencyCode = $cart->getQuoteCurrencyCode();
					$totalvalue = $cart->getGrandTotal();
				}

                $fbParams['value']=(float)number_format($totalvalue, '2', '.', '');
                $fbParams['currency']=$currencyCode;
                if (count($items) > 0) {
                    $data  = array();
                    $totalvalue = 0;
                    foreach ($items as $item)
                    {
                        $data[0][] = $this->getEcommProdid($item->getProduct());
                        $data[1][] = (int)$item->getQty();

						if($this->_use_base_currency){
							$totalvalue += $inclTax ? $item->getBaseTaxAmount() : $item->getBaseRowTotal();
						}else{
							$totalvalue += $inclTax ? $item->getTaxAmount() : $item->getRowTotal();
						}

                        $_sku =  $item->getProduct()->getSku();
                        $_sku = explode('-',$_sku);
                        $sku[] = $_sku[0];
                    }

                    $params = array(
                        'ecomm_prodid' => $data[0],
                        'ecomm_pagetype' => 'purchase',
                        'ecomm_quantity' => $data[1],
                        'ecomm_totalvalue' => (float)number_format($totalvalue, '2', '.', '')
                    );
                }
                }
                $fbParams['content_ids']= "['".implode("','",$sku)."']";
                unset($cart, $items, $item, $data,$sku);

                break;
            
            default:
				unset($fbParams['content_type']);
               break;
        }

        return array($params,$fbParams);
    }

    protected function paramsToFbJS($fbParams){
        //if(!isset($fbParams['track']))$fbParams['track']='PageView';
		$js = "fbq('track','PageView');\n";
		if(isset($fbParams['track'])){
        $js .= "fbq('track','${fbParams['track']}',\n";
        unset($fbParams['track']);
        $result = array();
		if(!empty($fbParams)){
        foreach($fbParams as $key=>$val){ 
			if($key == 'value' || $key=='content_ids'){
				$result[] = "$key:$val";	
			}else{
				$result[] = "$key:'$val'";
			}
        }}
        $js .="{".implode(",\n", $result)."});";
		}
      return  $js;
    }
    
    private function paramsToJS($params)
    {
        $result = array();
        
        foreach ($params as $key => $value)
        {
            if (is_array($value) && count($value) == 1)
                $value = $value[0];
            
            if (is_array($value))
            {
                if (is_string($value[0]))
                    $value = '["' . implode('","', $value) . '"]';
                else
                    $value = '[' . implode(',', $value) . ']';
            }
            elseif (is_string($value))
                $value = '"' . $value . '"';

            $result[] = $key . ': ' . $value;
        }
        
        return PHP_EOL . "\t" . implode(',' . PHP_EOL . "\t", $result) . PHP_EOL;
    }
    
    private function paramsToURL($params)
    {
        $result = array();
        
        foreach ($params as $key => $value)
        {
            if (is_array($value))
                $value = implode(',', $value);

            $result[] = $key . '=' . $value;
        }
        
        return urlencode(implode(';', $result));
    }
    
    private function paramsToDebug($params)
    {
        $result = '';
        
        foreach ($params as $key => $value)
        {
            if (is_array($value) && count($value) == 1)
                $value = $value[0];
            
            if (is_array($value))
            {
                if (is_string($value[0]))
                    $value = '["' . implode('","', $value) . '"]';
                else
                    $value = '[' . implode(',', $value) . ']';
            }
            elseif (is_string($value))
                $value = '"' . $value . '"';

            $result .= '<tr>' .
                '           <td style="text-align:right;font-weight:bold;">' . $key . ': &nbsp;</td>' .
                '           <td style="text-align:left;"> ' . $value . '</td>' . 
                '        </tr>';
        }
        
        return $result;
    }
    
    protected function _toHtml()
    {
        $this->_storeId = Mage::app()->getStore()->getId();
        $gcId = (int)Mage::getStoreConfig('gdrt/general/gc_id', $this->_storeId);
        list($gcParams,$fbParams)  =  $this->getParams();
        $fbId = (int)Mage::getStoreConfig('gdrt/general/fb_id');
        $s = '';
        if($gcId){
            $gcLabel = trim(Mage::getStoreConfig('gdrt/general/gc_label', $this->_storeId));
            $version = (string)Mage::getConfig()->getNode()->modules->Anaraky_Gdrt->version;
            $s = PHP_EOL .
            '<!-- Anaraky GDRT v.' . $version . ' script begin -->' . PHP_EOL .
            '<script type="text/javascript">' . PHP_EOL .
            '/* <![CDATA[ */' . PHP_EOL .
            'var google_tag_params = {' . $this->paramsToJS($gcParams) . '};' . PHP_EOL .
            'var google_conversion_id = ' . $gcId . ';' . PHP_EOL .
            (!empty($gcLabel) ? 'var google_conversion_label = "' . $gcLabel . '";' . PHP_EOL : '') .
            'var google_custom_params = google_tag_params;' . PHP_EOL .
            'var google_remarketing_only = true;' . PHP_EOL .
            '/* ]]> */' . PHP_EOL .
            '</script>' . PHP_EOL .
            '<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">' . PHP_EOL .
            '</script>' . PHP_EOL .
            '<noscript>' . PHP_EOL .
            '<div style="display:inline;">' . PHP_EOL .
            '<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/' . $gcId . '/?value=0' . (!empty($gcLabel) ? '&amp;label=' . $gcLabel : '') . '&amp;guid=ON&amp;script=0&amp;data=' . $this->paramsToURL($gcParams) . '"/>' . PHP_EOL .
            '</div>' . PHP_EOL .
            '</noscript>' . PHP_EOL .
            '<!-- Anaraky GDRT script end -->' . PHP_EOL;
		}
        if(!empty($fbId)){
            $str = $this->paramsToFbJS($fbParams);
            if(!empty($str)){
                $s .= PHP_EOL.
                    '<!-- Facebook Audience Pixel Code -->' . PHP_EOL .
                    '<script type="text/javascript">' . PHP_EOL .
                    '/* <![CDATA[ */' . PHP_EOL .
                    "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','//connect.facebook.net/en_US/fbevents.js');".PHP_EOL.
                    "fbq('init', '$fbId');". PHP_EOL .
                    $str. PHP_EOL .
                    '/* ]]> */' . PHP_EOL .
                    '</script>'. PHP_EOL .
                    '<noscript><img height="1" width="1" style="display:none" src="//www.facebook.com/tr?id='.$fbId.'&ev=PageView&noscript=1"/></noscript>'. PHP_EOL .
                    '<!-- End Facebook Audience Pixel Code -->'. PHP_EOL;
            }
        }
        
        if ((int)Mage::getStoreConfig('gdrt/debug/show_info', $this->_storeId) === 1)
        {
            $lk = str_replace(' ', '', Mage::getStoreConfig('dev/restrict/allow_ips', $this->_storeId));
            $ips = explode(',', $lk);
            if (empty($ips[0]) || in_array(Mage::helper('core/http')->getRemoteAddr(), $ips))
            {
                $s .= PHP_EOL .
                    '<div style="position:fixed; left:0; right:0; bottom:0; padding:5px 0; background:rgba(255, 208, 202, 0.8); border:1px solid #f92104;">' . PHP_EOL .
                    '    <table style="margin:0 auto;font-size:13px;color:#222;">' .
                    '        <tr>' .
                    '           <td rowspan="' . (count($gcParams) + 1) . '" style="vertical-align:middle;padding-right:40px;"><h3 style="margin:0;">Anaraky GDRT debug v.' . $version . '</h3></td>' .
                    '           <td style="text-align:right;font-weight:bold;">Model/Controller/Action: &nbsp;</td>' .
                    '           <td style="text-align:left;"> ' . $this->getData('pagePath') . '</td>' . 
                    '        </tr>' .
                    $this->paramsToDebug($gcParams) .
                    '    </table>' .
                    '</div>';
            }
        }
        
        return $s;
    }
}
