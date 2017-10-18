<?php

/**
 * Class Ado_Api_IndexController
 */
class Ado_Api_IndexController extends Mage_Core_Controller_Front_Action {
    protected $_debug = true;
	public function indexAction() {

        $result = $this->getCachedData();
	    if(!empty($result)){
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            exit();
        }

        $result['code']=0;
        $result['message']='';
        $result['model']=array();

		$cmd = ($this->getRequest ()->getParam ( 'cmd' )) ? ($this->getRequest ()->getParam ( 'cmd' )) : 'daily_sale';
		switch ($cmd) {
			case 'menu' : // OK
				// ---------------------------------列出产品目录-BEGIN-------------------------------------//
				$tree = ($this->getRequest ()->getParam ( 'mode'))?true:false;
                $_helper = Mage::helper ( 'catalog/category' );
				$_categories = $_helper->getStoreCategories();
				$_categorylist = array ();
				if (count ( $_categories ) > 0) {
					foreach ( $_categories as $_category ) {
						if(Mage::getModel('mapi/menu')->_hasProducts($_category->getId())) {
                            $_category =  Mage::getModel('catalog/category')->load($_category->getId());
                            $_categorylist_sample = array(
                                'category_id' => $_category->getId(),
                                'name' => $_category->getName(),
                                'is_active' => $_category->getIsActive(),
                                'position ' => $_category->getPosition(),
                                'level ' => $_category->getLevel(),
                                'url_key' => $_category->getUrlPath(),
                                'thumbnail_url' => $_category->getThumbnailUrl(),
                                'image_url' => $_category->getImageUrl(),
                                // 'children' => Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getAllChildren (),
                               // 'child' => $this->getChildCatalog($_category)
                            );
                            $_categorylist_child = $this->getChildCatalog($_category,$tree);
                            if(!$tree){
                                $_categorylist_sample['child'] = $_categorylist_child;
                            }else{
                                $_categorylist[] = $_categorylist_sample;
                                $_categorylist =array_merge($_categorylist,$_categorylist_child);
                            }
						}
					}
				}
                $result['model'] = $_categorylist;
				// ---------------------------------列出产品目录 END----------------------------------------//
				break;
            // ---------------------------------指定一些分类数据，可以进行缓存 ----------------------------------------//
            case 'catalog_tabnames':
               $_categorylist = Mage::getModel('mapi/category')->getHomeCategories();
               if(!empty($_categorylist)){
                    $result['model']=$_categorylist;
                }else{
                    $result['code']=1;
                    $result['message']='Not Found Data.';
                }
                break;
            case 'history_views':
                $skus = $this->getVisitedSkus();
                $page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
                $limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 10;
                $collection = Mage::getModel ( 'catalog/product' )->getCollection ();
                $collection->addAttributeToSelect ( '*' )
                    ->addAttributeToFilter('sku in (?)',$skus)
                    ->addAttributeToSort ( 'created_at', 'desc');
                $pages = $collection->setPageSize( $limit )->getLastPageNumber();
                // $count=$collection->getSize();
                if ($page <= $pages) {
                    $collection->setPage ( $page, $limit );
                    $products = $collection->getItems ();
                    $productlist = $this->getProductList ( $products );
                }
                $result['model'] = $productlist;
                break;
			case 'catalog' :
//				Mage::app()->getStore()->setCurrentCurrencyCode('CNY');
				$category_id = $this->getRequest ()->getParam ( 'cat_id' );
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 10;
				$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'position';
				$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';
				// ----------------------------------取某个分类下的产品-BEGIN------------------------------//
				$category = Mage::getModel ( 'catalog/category' )->load ( $category_id );
				$collection = $category->getProductCollection ()->addAttributeToFilter ( 'status', 1 )->addAttributeToFilter ( 'visibility',array('neq' => 1))->addAttributeToSort ( $order, $dir );
				Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
				$pages = $collection->setPageSize ( $limit )->getLastPageNumber ();
				if ($page <= $pages) {
					$collection->setPage ( $page, $limit );
					$product_list = $this->getProductList ( $collection, 'catalog' );
				}else{
					$product_list = array();
				}
                $result['message'] = 'get '.count($product_list).' product success!';
                $result['model'] = $product_list;
				// ------------------------------取某个分类下的产品-END-----------------------------------//
				break;
			case 'coming_soon' : // 数据ok
				// ------------------------------首页 促销商品 BEGIN-------------------------------------//
				// 初始化产品 Collection 对象
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				// $todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
				$tomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 1, date ( 'y' ) );
				$dateTomorrow = date ( 'm/d/y', $tomorrow );
				$tdatomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 3, date ( 'y' ) );
				$tdaTomorrow = date ( 'm/d/y', $tdatomorrow );
				$_productCollection = Mage::getModel ( 'catalog/product' )->getCollection ();
				$_productCollection->addAttributeToSelect ( '*' )->addAttributeToFilter ( 'visibility', array (
					'neq' => 1
				) )->addAttributeToFilter ( 'status', 1 )->addAttributeToFilter ( 'special_price', array (
					'neq' => 0
				) )->addAttributeToFilter ( 'special_from_date', array (
					'date' => true,
					'to' => $dateTomorrow
				) )->addAttributeToFilter ( array (
					array (
						'attribute' => 'special_to_date',
						'date' => true,
						'from' => $tdaTomorrow
					),
					array (
						'attribute' => 'special_to_date',
						'null' => 1
					)
				) )/* ->setPage ( $page, $limit ) */;
				$pages = $_productCollection->setPageSize ( $limit )->getLastPageNumber ();
				// $count=$collection->getSize();
				if ($page <= $pages) {
					$_productCollection->setPage ( $page, $limit );
					$products = $_productCollection->getItems ();
					$productlist = $this->getProductList ( $products );
				}
                $result['model'] = $productlist;
				// ------------------------------首页 促销商品 END-------------------------------------//
				break;
			case 'best_seller' : // OK
				// ------------------------------首页 预特价商品 BEGIN------------------------------//
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
				$_products = Mage::getModel ( 'catalog/product' )->getCollection ()->addAttributeToSelect ( '*'
                    /*
                    array (
                        'name',
                        'special_price',
                        'news_from_date'
                    )
                    */
                )->addAttributeToFilter ( 'news_from_date', array (
					'or' => array (
						0 => array (
							'date' => true,
							'to' => $todayDate
						),
						1 => array (
							'is' => new Zend_Db_Expr ( 'null' )
						)
					)
				), 'left' )->addAttributeToFilter ( 'news_to_date', array (
					'or' => array (
						0 => array (
							'date' => true,
							'from' => $todayDate
						),
						1 => array (
							'is' => new Zend_Db_Expr ( 'null' )
						)
					)

				), 'left' )->addAttributeToFilter ( array (
					array (
						'attribute' => 'news_from_date',
						'is' => new Zend_Db_Expr ( 'not null' )
					),
					array (
						'attribute' => 'news_to_date',
						'is' => new Zend_Db_Expr ( 'not null' )
					)
				) )->addAttributeToFilter ( 'visibility', array (
					'in' => array (
						2,
						4
					)
				) )->addAttributeToSort ( 'news_from_date', 'desc' )/* ->setPage ( $page, $limit ) */;
				$pages = $_products->setPageSize ( $limit )->getLastPageNumber ();
				// $count=$collection->getSize();
				if ($page <= $pages) {
					$_products->setPage ( $page, $limit );
					$products = $_products->getItems ();
					$product_list = $this->getProductList ( $products );
				}else{
					$product_list = array();
				}
                $result['model'] = $product_list;
				// ------------------------------首页 预特价商品 END--------------------------------//
				break;
			case 'daily_sale' : // 数据OK
				// -------------------------------首页 特卖商品 BEGIN------------------------------//
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
				$tomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 1, date ( 'y' ) );
				$dateTomorrow = date ( 'm/d/y', $tomorrow );
				// $collection = Mage::getResourceModel ( 'catalog/product_collection' );
				$collection = Mage::getModel ( 'catalog/product' )->getCollection ();
				$collection->/* addStoreFilter ()-> */addAttributeToSelect ( '*' )->addAttributeToFilter ( 'special_price', array (
					'neq' => "0"
				) )->addAttributeToFilter ( 'special_from_date', array (
					'date' => true,
					'to' => $todayDate
				) )->addAttributeToFilter ( array (
					array (
						'attribute' => 'special_to_date',
						'date' => true,
						'from' => $dateTomorrow
					),
					array (
						'attribute' => 'special_to_date',
						'null' => 1
					)
				) );
				$pages = $collection->setPageSize ( $limit )->getLastPageNumber ();
				// $count=$collection->getSize();
				if ($page <= $pages) {
					$collection->setPage ( $page, $limit );
					$products = $collection->getItems ();
					$productlist = $this->getProductList ( $products );
				}
                $result['model'] = $productlist;

				// -------------------------------首页 特卖商品 END------------------------------//
				break;
			case 'new_products' : // 数据OK
				// -------------------------------首页 获取新品 BEGIN------------------------------//
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
//				$tomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 1, date ( 'y' ) );
//				$dateTomorrow = date ( 'm/d/y', $tomorrow );
				// $collection = Mage::getResourceModel ( 'catalog/product_collection' );
				$collection = Mage::getModel ( 'catalog/product' )->getCollection ();
				$collection->/* addStoreFilter ()-> */addAttributeToSelect ( '*' )->addAttributeToSort ( 'created_at', 'desc');
				$pages = $collection->setPageSize ( $limit )->getLastPageNumber ();
				// $count=$collection->getSize();
				if ($page <= $pages) {
					$collection->setPage ( $page, $limit );
					$products = $collection->getItems ();
					$productlist = $this->getProductList ( $products );
				}

                $result['model'] = $productlist;
				// -------------------------------首页 特卖商品 END------------------------------//
				break;
			default :
                $result['code'] =1;
                $result['message'] ='Your request was wrong.';
				// echo 'Your request was wrong.';
			//echo json_encode(array('code'=>1, 'message'=>'Your request was wrong.', 'model'=>array()));
				// echo $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
				// echo Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
				break;
		}
        $this->setCachedData($result);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

	/**
	 * @param $products
	 * @param string $mod
	 * @return array
	 *
	 *
	 */
	public function getProductList($products, $mod = 'product') {
		$store_id = Mage::app()->getStore()->getId();
		$product_list = array();
		$image_width = (int)Mage::getStoreConfig('mapi/info/image_width');
        $image_height = (int)Mage::getStoreConfig('mapi/info/image_height');
        $image_width = max($image_width,267);
        $image_height=max($image_height,356);
		foreach ( $products as $product ){
			if ($mod == 'catalog') {
				$product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );
			}
			$summaryData = Mage::getModel('review/review_summary')->setStoreId($store_id)  ->load($product->getId());
			$price = ($product->getSpecialPrice()) == null ? ($product->getPrice()) : ($product->getSpecialPrice());
            $oldPrice = ($product->getSpecialPrice()) ? ($product->getPrice()) : '';
            $discount = ($product->getIsoff() && $oldPrice)?(number_format(($oldPrice - $price)*100/$oldPrice,0)):0;
			$price = Mage::helper('core')->currency($price, true, false);
            $oldPrice = Mage::helper('core')->currency($oldPrice, true, false);
            $image = Mage::helper('catalog/image')->init($product,'image')->resize($image_width,$image_height);
		    $temp_product = array(
				'product_id' => $product->getId (),
				'sku' => $product->getSku (),
				'name' => $product->getName (),
				'rating_summary' => $summaryData->getRatingSummary(),
				'reviews_count' => $summaryData->getReviewsCount(),
				'news_from_date' => $product->getNewsFromDate (),
				'news_to_date' => $product->getNewsToDate (),
				'special_from_date' => $product->getSpecialFromDate (),
				'special_to_date' => $product->getSpecialToDate (),
				'image_url' =>  (string)$image, //$product->getImageUrl (), //
				'url_key' => $product->getProductUrl (),
				'price' => $price,
                'old_price' => $oldPrice,
                'discount'=> $discount,
				'symbol'=> Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(),
				'stock_level' => (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty(),
                'wishlist' => Mage::helper('wishlist')->getAddUrl($product),
			);
			array_push($product_list,$temp_product);
		}
		return $product_list;
	}

    /**
     * 获得分类的子分类数据
     * @param $_category
     * @return array
     */
	public function getChildCatalog($_category,$tree= false){
        $child = array();
        if(Mage::getModel('mapi/menu')->_hasProducts($_category->getId())) {
            $childMenu = Mage::getModel('catalog/category')->load($_category->getId())->getAllChildren();
            $childMenu = explode(',', $childMenu);
            array_shift($childMenu);
            if(count($childMenu)){
            foreach ($childMenu as $childSec) {
                //判断子级类目是否有商品
                if (Mage::getModel('mapi/menu')->_hasProducts($childSec)) {
                    $childCatalog =  Mage::getModel('catalog/category')->load($childSec);
                    $child_child = $this->getChildCatalog($childCatalog,$tree);
                    $child[$childSec] =  array(
                        'category_id' => $childCatalog->getId(),
                        'name' => $childCatalog->getName(),
                        'is_active' => $childCatalog->getIsActive(),
                        'position ' => $childCatalog->getPosition(),
                        'level ' => $childCatalog->getLevel(),
                        'url_key' => $childCatalog->getUrlPath(),
                        'thumbnail_url' => $childCatalog->getThumbnailUrl(),
                        'image_url' => $childCatalog->getImageUrl(),
                        // 'children' => Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getAllChildren (),
                        // 'child' => $this->getChildCatalog($childCatalog),
                    );
                    if($tree){
                         $child = array_merge($child,$child_child);
                    }else{
                        $child[$childSec]['child'] = $child_child;
                    }
                }
              }
            }
        }
        return $child;
    }

    /**
     * 由参数决定是否缓存，如果不缓存，就不出key
     * @return bool|string
     */
    protected function getCacheKey(){
	    if($this->getRequest()->getParams('live',false) && !$this->_debug){
	        $params = $this->getRequest()->getParams();
	        return md5(serialize($params));
        }else{
            Mage::app ()->cleanCache ();
        }
        return false;
    }

    /**
     * 从缓存中取数据
     * @return bool|mixed
     */
    protected function getCachedData(){
        if($key = $this->getCacheKey())
            return Mage::app()->loadCache($key);
        return false;
    }

    /**
     * 数据缓存
     * @return bool|mixed
     */
    protected function setCachedData($data){
        if($key = $this->getCacheKey())
            return Mage::app()->saveCache($data,$key);
        return false;
    }

    /**
     * 分类会话
     * @return Mage_Core_Model_Abstract
     */
    protected function _getCatalogSession()
    {
        return Mage::getSingleton('catalog/session');
    }

    /**
     * visited_sku 来源于
     * app\code\local\Ado\SEO\controllers\ViewedController.php
     *
     */
    protected function getVisitedSkus(){
        $_items = $this->_getCatalogSession()->getData('visited_sku');
        if($_items){
            $_items=unserialize($_items);
        }
        return $_items;
    }

}