<?php

/**
 * Class Ado_Api_SearchController
 */
class Ado_Api_SearchController extends Mage_Core_Controller_Front_Action {

    /**
     * get current user session
     * @return mixed
     */
	protected function _getSession(){
		return Mage::getSingleton ('catalog/session');
	}

    /**
     * search
     */
	public function indexAction() {
        $msg = '';
		$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
		$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
		$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'relevance';
		$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';
		$query = Mage::helper ( 'catalogsearch' )->getQuery();
        $msg = $query->getQueryText();
		$query->setStoreId ( Mage::app ()->getStore ()->getId () );
		if ($query->getQueryText () != '') {
			if (Mage::helper ( 'catalogsearch' )->isMinQueryLength ()){
				$query->setId( 0 )->setIsActive( 1 )->setIsProcessed( 1 );
			}else{
				if ($query->getId ()) {
					$query->setPopularity ( $query->getPopularity () + 1 );
				} else {
					$query->setPopularity ( 1 );
				}
				if ($query->getRedirect ()) {
					$query->save ();
					$this->getResponse ()->setRedirect ( $query->getRedirect () );
					return;
				} else {
					$query->prepare ();
				}
			}
			Mage::helper('catalogsearch')->checkNotes();
			$collection = $query->getResultCollection();
			$collection->setCurPage($page)->setPageSize($limit)->addAttributeToFilter('visibility', array('in' => array(
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
			)))->addAttributeToSort($order, $dir);
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
			$pages = $collection->setPageSize($limit)->getLastPageNumber();

			if($page <= $pages){
				$i = 1;
				$baseCurrency = Mage::app()->getStore()->getBaseCurrency()->getCode();
				$currentCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
				$store_id = Mage::app()->getStore()->getId();
				try{
				foreach($collection as $product){
					$product = Mage::getModel('catalog/product')->load($product->getId());
					$summaryData = Mage::getModel('review/review_summary')->setStoreId($store_id) ->load($product->getId());
					$price =($product->getSpecialPrice()) == null ? ($product->getPrice()) : ($product->getSpecialPrice());
					$regular_price_with_tax = $product->getPrice();
					$final_price_with_tax = $product->getSpecialPrice();
         			$product_list [] = array(
						'entity_id' => $product->getId(),
						'sku' => $product->getSku(),
						'name' => $product->getName(),
						'rating_summary' => $summaryData->getRatingSummary(),
						'reviews_count' => $summaryData->getReviewsCount(),
						'news_from_date' => $product->getNewsFromDate (),
						'news_to_date' => $product->getNewsToDate(),
						'special_from_date' => $product->getSpecialFromDate(),
						'special_to_date' => $product->getSpecialToDate(),
						'image_url' => $product->getImageUrl(),
						'url_key' => $product->getProductUrl(),
						'price' =>  number_format(Mage::helper('directory')->currencyConvert($price, $baseCurrency, $currentCurrency), 2, '.', '' ),
						'regular_price_with_tax' => number_format(Mage::helper('directory')->currencyConvert($regular_price_with_tax, $baseCurrency, $currentCurrency), 2, '.', '' ),
						'final_price_with_tax' => number_format(Mage::helper('directory')->currencyConvert($final_price_with_tax, $baseCurrency, $currentCurrency), 2, '.', '' ),
						'symbol' => Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(),
						'stock_level' => (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty()
					);
					$i ++;
				}}catch (exception $e){
                    $msg .= $e->getMessage();
                }
			}else{
                $msg .='nnnnnn';
                $product_list = array();
			}
			echo json_encode(
				array(
					'code'=>0,
					'message'=>'search '.count($collection).' product success!'.$msg.$page.'==='.$pages,
					'model'=> array(
						'items'=> $product_list,
                        'page'=>$page,
                        'pages'=>$pages,
                        'limit'=>$limit,
                        'sort_order'=>$order,
                        'dir'=>$dir
					)
				)
			);
			if(!Mage::helper('catalogsearch')->isMinQueryLength() && count($product_list)){
				$query->save();
			}
		} else {
			echo json_encode(
				array(
					'code'=>0,
					'message'=>null,
					'model'=>null,
					'error'=>'search keyword can not null!'
				)
			);
		}
	}



	/**
	 * get search number
	 */
	public function getSearchNumAction() {
		$query = Mage::helper ( 'catalogsearch' )->getQuery();
		$query->setStoreId ( Mage::app ()->getStore ()->getId () );
		if ($query->getQueryText () != '') {
			if (Mage::helper ( 'catalogsearch' )->isMinQueryLength ()){
				$query->setId( 0 )->setIsActive( 1 )->setIsProcessed( 1 );
			}else{
				if ($query->getId ()) {
					$query->setPopularity ( $query->getPopularity () + 1 );
				} else {
					$query->setPopularity ( 1 );
				}
				if ($query->getRedirect ()) {
					$query->save ();
					$this->getResponse ()->setRedirect ( $query->getRedirect () );
					return;
				} else {
					$query->prepare ();
				}
			}
			Mage::helper('catalogsearch')->checkNotes();
			$collection = $query->getResultCollection();
			$collection->addAttributeToFilter('visibility', array('in' => array(
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
			)));
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
			echo json_encode(
				array(
					'code'=>0,
					'message'=>'search '.count($collection).' product success!',
					'model'=> count($collection)
				)
			);
			if(!Mage::helper('catalogsearch')->isMinQueryLength()){
				$query->save();
			}
		} else {
			echo json_encode(
				array(
					'code'=>0,
					'message'=>null,
					'model'=>null,
					'error'=>'search keyword can not null!'
				)
			);
		}
	}

	/**
     * suggest 搜素提示
     */
	public function suggestAction(){

        $query = Mage::helper('catalogsearch')->getQuery();
        $query->setStoreId(Mage::app()->getStore()->getId());

        $queryText = urlencode(Mage::app()->getRequest()->getParam('q'));
        $query->setQueryText($queryText);

        $result = array();

        if ($query->getQueryText()) {
            if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            } else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity() + 1);
                } else {
                    $query->setPopularity(1);
                }
                $query->prepare();
            }
            Mage::helper('catalogsearch')->getQuery()->save();
            $data = $this->getSuggestData();

            if(count($data)){
                echo json_encode(
                    array(
                        'code'=>0,
                        'message'=>null,
                        'model'=>$data,
                    )
                );
            }else{
                echo json_encode(
                    array(
                        'code'=>1,
                        'message'=>'search keyword no suggest!',
                        'model'=>$data,
                    )
                );
            }

        } else {
            echo json_encode(
                array(
                    'code'=>1,
                    'message'=>'search keyword invalid!',
                    'model'=>null,
                )
            );
        }
    }


    protected function getSuggestData()
    {
        if (!$this->_suggestData) {
            $collection = Mage::helper('catalogsearch')->getSuggestCollection();
            $query = Mage::helper('catalogsearch')->getQueryText();
            $counter = 0;
            $data = array();
            foreach ($collection as $item) {
                $_data = array(
                    'title' => $item->getQueryText(),
                    'row_class' => (++$counter)%2?'odd':'even',
                    'num_of_results' => $item->getNumResults()
                );

                if ($item->getQueryText() == $query) {
                    array_unshift($data, $_data);
                }
                else {
                    $data[] = $_data;
                }
            }
            $this->_suggestData = $data;
        }
        return $this->_suggestData;
    }


}

