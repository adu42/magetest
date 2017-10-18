<?php

/**
 * Class Ado_Api_ProductsController
 */
class Ado_Api_ProductsController extends Ado_Api_BaseController {


    /**
     * 获取商品自定义属性
     */
    public function getCustomOptionAction() {
        $baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
        $currentCurrency = Mage::app ()->getStore ()->getCurrentCurrencyCode ();
        $product_id = $this->getRequest ()->getParam ( 'product_id' );
        $product = Mage::getModel ( "catalog/product" )->load ( $product_id );
        $selectid = 1;
        $select = array ();
        foreach ( $product->getOptions () as $o ) {
            if (($o->getType () == "field") || ($o->getType () =="file")) {
                $select [$selectid] = array (
                    'option_id' => $o->getId (),
                    'custom_option_type' => $o->getType (),
                    'custom_option_title' => $o->getTitle (),
                    'is_require' => $o->getIsRequire (),
                    'price' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $o->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
                    'price_type'=>$o->getPriceType(),
                    'sku'=>$o->getSku(),
                    'max_characters' => $o->getMaxCharacters (),
                );
            } else {
                $max_characters = $o->getMaxCharacters ();
                $optionid = 1;
                $options = array ();
                $values = $o->getValues ();
                foreach ( $values as $v ) {
                    $options [$optionid] = $v->getData ();
                    $optionid ++;
                }
                $select [$selectid] = array (
                    'option_id' => $o->getId (),
                    'custom_option_type' => $o->getType (),
                    'custom_option_title' => $o->getTitle (),
                    'is_require' => $o->getIsRequire (),
                    'price' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $o->getFormatedPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
                    'max_characters' => $max_characters,
                    'custom_option_value' => $options
                );
            }
            $selectid ++;
        }
        echo json_encode ( array('code'=>0, 'message'=>null, 'model'=>$select) );
    }

    /**
     * 根据sku获取商品信息
     */
    public function getProductDetailBySkuAction()
    {
        $sku = $this->getRequest()->getParam('sku');
        $sku= trim($sku);
        $productDetail=[];
        $id = Mage::getModel('catalog/product')->getResource()->getIdBySku($sku);
        if ($id)$productDetail = $this->_getProductDetail($id);
        if(empty($productDetail)){
            $this->responseError("cannot found product message by $sku.");
        }else{
            $this->responseResult($productDetail);
        }
    }
    /**
     * 获取商品详情
     */
    public function getProductDetailAction() {
        $product_id = $this->getRequest ()->getParam('product_id');
        $product_detail = $this->_getProductDetail($product_id);
        if($product_detail){
            $this->responseResult($product_detail);
           // echo json_encode(array('code'=>0, 'message'=>null, 'model'=>$product_detail));
        }else{
            $this->responseError('cannot found product message.');
           // echo json_encode(array('code'=>1, 'message'=>'cannot found product message.', 'model'=>null));
        }
    }

    /**
     * @param $product_id
     */
    protected function _getProductDetail($product_id){
        $baseCurrency = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $currentCurrency = Mage::app ()->getStore()->getCurrentCurrencyCode();
        $products_model = Mage::getModel('mapi/products');
        $product = Mage::getModel("catalog/product")->load($product_id);
        if(!$product || !$product->getId())return false;
        $store_id = Mage::app()->getStore()->getId();
        $_helper = Mage::helper('catalog/output');
        $product_detail = array();
        $options = array();
        $price = array();
        $product_type = $product->getTypeId();
        switch($product_type){
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE: {
                $product_detail['attribute_options'] = $products_model->getProductOptions($product);
                $price = Mage::getModel('mapi/currency')->getCurrencyPrice(($product->getSpecialPrice()) == null ? ($product->getPrice()) : ($product->getSpecialPrice()));
                $price = number_format($price, 2, '.', '' );
            }break;
            case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE: {
                $product_detail['custom_options'] = $products_model->getProductCustomOptionsOption($product);
                $product_detail['stock_level'] = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
                $price = $price = Mage::getModel('mapi/currency')->getCurrencyPrice(($product->getSpecialPrice()) == null ? ($product->getPrice()) : ($product->getSpecialPrice()));
                $price = number_format($price, 2, '.', '' );
            }break;
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE: {
                $price = $products_model->collectBundleProductPrices($product);
                $product_detail['bundle_option']  =  $products_model->getProductBundleOptions($product);
            }break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED: {
                $product_detail['grouped_option']  =  $products_model->getProductGroupedOptions($product);
            }break;
            case Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL:  {
                $price = $price = Mage::getModel('mapi/currency')->getCurrencyPrice(($product->getSpecialPrice()) == null ? ($product->getPrice()) : ($product->getSpecialPrice()));
                $price = number_format($price, 2, '.', '' );
            }break;
            default: {
                $price = $price = Mage::getModel('mapi/currency')->getCurrencyPrice(($product->getSpecialPrice()) == null ? ($product->getPrice()) : ($product->getSpecialPrice()));
                $price = number_format($price, 2, '.', '' );
            } break;
        }
        $product_detail['price'] = $price;
        $mediaGallery = array();
        foreach($product->getMediaGalleryImages()->getItems() as $image){
            $mediaGallery[] = $image['url'];
        }
        if(count($mediaGallery)<=0){
            array_push($mediaGallery,Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage()));
        };
        $product_detail['in_wishlist'] = false;
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer_id =  Mage::getSingleton('customer/session')->getCustomer ()->getId();
            $item_collection = Mage::getModel('wishlist/item')->getCollection()->addCustomerIdFilter($customer_id);
            foreach($item_collection as $item){
                if($item->getProductId()==$product->getId()){
                    $product_detail['in_wishlist'] = true;
                }
            }
        }
        $summaryData = Mage::getModel('review/review_summary')->setStoreId($store_id)->load($product->getId());
        $product_detail['entity_id'] = $product->getId();
        $product_detail['rating_summary'] = $summaryData->getRatingSummary();
        $product_detail['reviews_count'] = $summaryData->getReviewsCount();
        $product_detail['sku'] = $product->getSku();
        $product_detail['status'] = $product->getStatus();
        $product_detail['name'] = $product->getName();
        $product_detail['news_from_date'] = $product->getNewsFromDate();
        $product_detail['news_to_date'] = $product->getNewsToDate();
        $product_detail['product_type'] = $product->getTypeID();
        $product_detail['special_from_date'] = $product->getSpecialFromDate();
        $product_detail['special_to_date'] = $product->getSpecialToDate();
        $product_detail['image_url'] = $product->getImageUrl();
        $product_detail['url_key'] = $product->getProductUrl();
        $product_detail['regular_price_with_tax'] = number_format(Mage::helper('directory')->currencyConvert($product->getPrice(), $baseCurrency, $currentCurrency), 2, '.', '');
        $product_detail['final_price_with_tax'] = number_format( Mage::helper ('directory')->currencyConvert($product->getSpecialPrice(), $baseCurrency, $currentCurrency), 2, '.', '');
//			'description' => nl2br ( $product->getDescription()),
        $product_detail['short_description'] = $_helper->productAttribute($product, nl2br($product->getShortDescription()), 'short_description');
//        $product_detail['description'] = $product->getDescription();
        $product_detail['description'] = $_helper->productAttribute($product, nl2br($product->getDescription()), 'description');
        //$product_detail['description'] = $product->getDescription();                   //add by wayne    /*long description*/
        $product_detail['additional'] = $products_model->getAdditionalFront($product); //add by wayne    /*additional information Visible on Product View Page on Front-end*/
        $product_detail['symbol'] = Mage::app ()->getLocale ()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        $product_detail['options'] = $options;
        $product_detail['mediaGallery'] = $mediaGallery;
        return $product_detail;
      //  echo json_encode(array('code'=>0, 'message'=>null, 'model'=>$product_detail));
    }

    /**
     * 获得相关产品
     */
    public function getRelatedProductAction()
    {
        $product_id = (int)$this->getRequest()->getParam('product_id');
        $_product = Mage::getModel('catalog/product')->load($product_id);
        if(!$_product || !$_product->getId()){
            $this->responseError('cannot found product data.');
        }
        $productdetail = array();
        $relArray = array();
        $relProductIds = $_product->getRelatedProductIds();
        if(!$relProductIds || empty($relProductIds)){
            $this->responseError('cannot found related product data.');
        }
        $baseCurrency = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $currentCurrency = $this->currency;
        foreach ($relProductIds as $id) {
            $product = Mage::getModel('catalog/product')->load($id);
            if(!$product || !$product->getId())continue;
            $productData = $product->getData();
            if ($product->getTypeId() == "configurable")
                $qty = Mage::helper('connector')->getProductStockInfoById($product->getId());
            else
                $qty = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();

            $relArray['entity_id'] = $productData['entity_id'];
            $relArray['sku'] = $productData['sku'];
            $relArray['name'] = $productData['name'];
            $relArray['image'] = Mage::helper('connector')->Imageresize($product->getImage(), 'product', '300', '300');
            $relArray['url_key'] = $product->getProductUrl();
            $relArray['regular_price_with_tax'] = number_format(Mage::helper('directory')->currencyConvert($product->getPrice(), $baseCurrency, $currentCurrency), 2, '.', '');
            $relArray['final_price_with_tax'] = number_format(Mage::helper('directory')->currencyConvert(
                Mage::helper('tax')->getPrice($product, $product->getFinalPrice(),
                    true, null, null, null, null, false),
                $baseCurrency, $currentCurrency), 2, '.', '');
            $relArray['symbol'] = Mage::helper('connector')->getCurrencysymbolByCode($this->currency);
            $relArray['qty'] = $qty;
            $relArray['wishlist'] = Mage::helper('connector')->check_wishlist($product->getId());
            $relArray['specialprice'] = number_format(Mage::helper('connector')->getSpecialPriceProduct($product->getId()), 2, '.', '');

            array_push($productdetail, $relArray);
        }
        echo json_encode($productdetail);
    }





} 