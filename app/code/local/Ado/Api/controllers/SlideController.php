<?php
class Ado_Api_SlideController extends Mage_Core_Controller_Front_Action {
    /**
     * 其他内容列表的slide
     * get website info
     */
    public function getSlideAction() {
         $identifier  = Mage::app ()->getRequest ()->getParam('identifier');
        $page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
        $limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 3;
        $model  = Mage::getModel('mapi/slide')->load($identifier,'identifier');
        $helper = Mage::helper('mapi');
        if ($model->getId()) {
            $slide_items = Mage::getModel('mapi/slideitem')->getCollection()
                ->addFieldToFilter('status', true)
                ->addFieldToFilter('slide_id', $model->getSlideId())
                ->setOrder('slide_order','ASC');
            $pages = $slide_items->setPageSize ( $limit )->getLastPageNumber ();
            if ($page <= $pages) {
                $slide_items->setCurPage($page);
                $slide_items->setPageSize($limit);
            }else{
                $slide_items = array();
            }
            $slideList = array();
            foreach ($slide_items as $slide_item) {
                $temp_slide = array(
                    'slide_item_id' => $slide_item->getslideItemId(),
                    'title' => $slide_item->getTitle(),
                   // 'image' => $slide_item->getImage(),
                    'image_url' => $slide_item->getImageUrl()?$slide_item->getImageUrl():$helper->getImage($slide_item->getImage(),$model->getWidth(),$model->getHeight()) ,
//                    'thumb_image' => $slide_item->getThumbImage(),
//                    'thumb_image_url'=> $slide_item->getThumbImageUrl(),
                    'content' => $slide_item->getContent(),
                    'link_url' => $slide_item->getLinkUrl(),
                );
                array_push($slideList,$temp_slide);
            }
            echo json_encode(array(
                'code'=>0,
                'message'=>'get slides success!',
                'model'=>array(
                    'title'=> $model->getTitle(),
                    'content'=> $model->getContent(),
                    'width' => $model->getWidth(),
                    'height' => $model->getHeight(),
                    'delay'=> $model->getDelay(),
                    'status'=> $model->getStatus(),
                    'active_from'=> $model->getActiveFrom(),
                    'active_to' => $model->getActiveTo(),
                    'create_time'=> $model->getCreatedTime(),
                   // 'symbol'=> Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(),
                    'slide_items'=> $slideList
                )
            ));
        }else{
            echo json_encode ( array (
                'code'=>1,
                'message'=>'please send slide id!',
                'model'=>array ()
            ));
        }
    }
    /**
     * get products slide
     * 产品列表的slide
     */
    public function getItemListBySlideAction() {
        $baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
        $currentCurrency = Mage::app ()->getStore ()->getCurrentCurrencyCode ();
        $store_id = Mage::app()->getStore()->getId();
        $identifier  = Mage::app ()->getRequest ()->getParam('identifier');
        $page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
        $limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
        $model  = Mage::getModel('mapi/slide')->load($identifier,'identifier');
        $slide_item_list = array();
        if ($model->getId()) {
            $content = $model->getContent();
            $content = trim($content);
            if(stripos($content,'products:')!==false){
                $content = str_replace(array('products:','|',';','；','，'),',',$content);
                $paroductIds = explode(',',$content);
                $i = 0;
                foreach ($paroductIds as  $paroductId){
                    if($limit == $i) break;
                    if(empty($paroductId))continue;
                    $product = Mage::getModel ( "catalog/product" )->load ($paroductId);
                    if(!$product || !$product->getId())continue;
                    $i++;
                    $summaryData = Mage::getModel('review/review_summary')->setStoreId($store_id) ->load($product->getId());
                    $price =($product->getSpecialPrice()) == null ? ($product->getPrice()) : ($product->getSpecialPrice());
                    $regular_price_with_tax = $product->getPrice();
                    $final_price_with_tax = $product->getSpecialPrice();
                    $image = (string) Mage::helper('catalog/image')->init($product, 'small_image')->resize($model->getWidth(),$model->getHeight());
                    $temp_product = array(
                        'entity_id' => $product->getId(),
                        'sku' => $product->getSku(),
                        'name' => $product->getName(),
                        'rating_summary' => $summaryData->getRatingSummary(),
                        'reviews_count' => $summaryData->getReviewsCount(),
                        'news_from_date' => $product->getNewsFromDate (),
                        'news_to_date' => $product->getNewsToDate(),
                        'special_from_date' => $product->getSpecialFromDate(),
                        'special_to_date' => $product->getSpecialToDate(),
                        'image_url' =>  $image, //$product->getImageUrl(),
                        'url_key' => $product->getProductUrl(),
                        'price' =>  number_format(Mage::helper('directory')->currencyConvert($price, $baseCurrency, $currentCurrency), 2, '.', '' ),
                        'regular_price_with_tax' => number_format(Mage::helper('directory')->currencyConvert($regular_price_with_tax, $baseCurrency, $currentCurrency), 2, '.', '' ),
                        'final_price_with_tax' => number_format(Mage::helper('directory')->currencyConvert($final_price_with_tax, $baseCurrency, $currentCurrency), 2, '.', '' ),
                        'symbol' => Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(),
                        'stock_level' => (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty()
                    );
                    array_push($slide_item_list,$temp_product);
                }
            }

            echo json_encode(array(
                'code'=>0,
                'message'=>'get item list success!',
                'model'=> $slide_item_list
            ));
        }else{
            echo json_encode ( array (
                'code'=>1,
                'message'=>'please send slide identifier!',
                'model'=>array ()
            ));
        }
    }





    /**
     * get item by slide
     */
    public function getItemsBySlideItemAction() {
        $baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
        $currentCurrency = Mage::app ()->getStore ()->getCurrentCurrencyCode ();
        $store_id = Mage::app()->getStore()->getId();
        $return_result = array(
            'code' => 0,
            'message' => 'get products success!',
            'model' => null
        );
        $slide_id  = Mage::app ()->getRequest ()->getParam('slide_id');
        $model  = Mage::getModel('easyslide/slideitem')->load($slide_id,'slide_item_id');
        if ($model->getId()) {
            $content = $model->getContent();
            $product_list =  explode(',', $content);
            $return_products = array();
            $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('entity_id', array('in' => $product_list));
            $products->getSelect()->order("find_in_set(entity_id,'".implode(',',$product_list)."')");
            foreach($products as $product) {
                $product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );
                $summaryData = Mage::getModel('review/review_summary')->setStoreId($store_id)  ->load($product->getId());
                $price = ($product->getSpecialPrice()) == null ? ($product->getPrice()) : ($product->getSpecialPrice());
                $regular_price_with_tax = number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' );
                $final_price_with_tax = number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getSpecialPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' );
                $temp_product = array(
                    'entity_id' => $product->getId (),
                    'sku' => $product->getSku (),
                    'name' => $product->getName (),
                    'rating_summary' => $summaryData->getRatingSummary(),
                    'reviews_count' => $summaryData->getReviewsCount(),
                    'news_from_date' => $product->getNewsFromDate (),
                    'news_to_date' => $product->getNewsToDate (),
                    'special_from_date' => $product->getSpecialFromDate (),
                    'special_to_date' => $product->getSpecialToDate (),
                    'image_url' => $product->getImageUrl (),
                    'url_key' => $product->getProductUrl (),
                    'price' => number_format(Mage::getModel('mapi/currency')->getCurrencyPrice($price),2,'.',''),
                    'regular_price_with_tax' =>  number_format(Mage::getModel('mapi/currency')->getCurrencyPrice($regular_price_with_tax),2,'.',''),
                    'final_price_with_tax' =>  number_format(Mage::getModel('mapi/currency')->getCurrencyPrice($final_price_with_tax),2,'.',''),
                    'symbol'=> Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
                );
                array_push($return_products,$temp_product);
            }
            $return_result['model'] = $return_products;
        }else{
            $return_result['code'] = 1;
            $return_result['message'] = 'could not find this slide!';
        }
        echo json_encode($return_result);
    }

}
