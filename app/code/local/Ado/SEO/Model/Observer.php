<?php
/**
 *  2017-9-20 by@ado
 *  修改
 *  增加方法---货币切换方法，重新设置折扣价格
 *  增加方法---折扣时间限制在多少分钟内有效
 * 
 *   结论：
 *  不要在原商品上设置值，保留原有的价格
 *  这里只计算购物车中的价格，
 *  setCustomPrice 用基础货币单位
 *  setOriginalCustomPrice 非基础货币单位
 *
 *
 */


class Ado_SEO_Model_Observer
{
    /**
     * 单个商品加入购物车式应用本方法，应用一个折扣
     * @param Varien_Event_Observer $observer
     */
    public function applyDiscount(Varien_Event_Observer $observer)
    {
        /*  $item Mage_Sales_Model_Quote_Item  */
        $percentDiscount = (float)Mage::getStoreConfig('ado_seo/cart/add_cart_discount');
        $items = $observer->getItems();
        foreach ($items as $item){
            if($item->getAdoDiscount())continue;
            if ($item->getParentItem()) {
                $item = $item->getParentItem();
            }
            // Discounted 25% off
            $exclude = $this->excludeCategories($item->getProduct()->getCategoryIds());
            if($exclude)continue;
            if($percentDiscount<1 && $percentDiscount>0){
                $specialPrice = $item->getProduct()->getFinalPrice() - ($item->getProduct()->getFinalPrice() * $percentDiscount);
            }elseif ($percentDiscount>=1){
                $specialPrice = $item->getProduct()->getFinalPrice() - $percentDiscount;
            }else{
                break;
            }

            $_specialPrice =  Mage::app()->getStore()->convertPrice(
                $specialPrice
            );

            // Make sure we don’t have a negative
            if ($specialPrice > 0 && !$item->getIsPromo()) {
                $item->setAdoDiscount(true);
                $item->setCustomPrice($specialPrice);
                $item->setOriginalCustomPrice($_specialPrice);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
    }


    /**
     * 整体修正购物车中多个商品的折扣
     * 更改购物车、在购物车切换货币的时候触发
     * 定义位置：
     * \app\code\local\Ado\SEO\etc\config.xml
     * @param Varien_Event_Observer $observer
     */
    public function applyDiscounts(Varien_Event_Observer $observer)
    {
        $percentDiscount = (float)Mage::getStoreConfig('ado_seo/cart/add_cart_discount');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote && $quote->hasItems()) {
            foreach ($quote->getAllVisibleItems() as $item/* @var $item Mage_Sales_Model_Quote_Item */) {
                if ($item->getAdoDiscount()) continue;
                if ($item->getParentItem()) {
                    $item = $item->getParentItem();
                }
                $exclude = $this->excludeCategories($item->getProduct()->getCategoryIds());
                if ($exclude) continue;
                $outTime = $this->discountOutTime($item->getCreatedAt());
                if($outTime) continue;
                // Discounted 25% off
                if ($percentDiscount < 1 && $percentDiscount > 0) {
                    $specialPrice = $item->getProduct()->getFinalPrice() - ($item->getProduct()->getFinalPrice() * $percentDiscount);
                } elseif ($percentDiscount >= 1) {
                    $specialPrice = $item->getProduct()->getFinalPrice() - $percentDiscount;
                } else {
                    break;
                    //return ;
                }

                // This makes sure the discount isn’t applied over and over when refreshing
                // $specialPrice = $item->getOriginalPrice() - ($item->getOriginalPrice() * $percentDiscount);
                // $specialPrice = $item->getOriginalPrice() - 10;
                $_specialPrice = Mage::app()->getStore()->convertPrice(
                    $specialPrice
                );
               // 不要在原商品上设置值，保留原有的价格
                //这里只计算购物车中的价格，
                //setCustomPrice 用基础货币单位
                //setOriginalCustomPrice 非基础货币单位
              //  $item->getProduct()->setCalculatedFinalPrice($specialPrice);
              //  $item->getProduct()->setFinalPrice($specialPrice);

                // Make sure we don’t have a negative
                if ($specialPrice > 0 && !$item->getIsPromo()) {
                    $item->setCustomPrice($specialPrice);
                    $item->setOriginalCustomPrice($_specialPrice);
                    $item->getProduct()->setIsSuperMode(true);
                    $item->setAdoDiscount(true);
                }
            }
            $quote->collectTotals()->save();
        }
    }

    /**
     * 切换货币后处理汇率
     * @param $observer
     */
    public function hookCurrencyChangeDispatch($observer){
         $this->applyDiscounts($observer);
    }

    /**
     * 时间多少分钟内有效
     * @param $addedTime
     * @return bool
     */
    protected function discountOutTime($addedTime){
        $time = (int)Mage::getStoreConfig('ado_seo/checkout_coupon/checkout_count_down_time');
        if($time){
            $time = $time*60;
            try{
                $now = Mage::getSingleton('core/date')->gmtDate();
                $now = strtotime($now);
                $createdAt = $addedTime;
                $createdAt = strtotime($createdAt);
            }catch (Exception $e){}
             return (($now-$createdAt)>=$time);
        }
        return false;
    }
    /**
     * 排除一些分类
     * @param $productCategories
     * @return bool|int
     */
    protected function excludeCategories($productCategories){
        $match = false;
        $categories = Mage::getStoreConfig('ado_seo/cart/add_cart_discount_categories');
        if(!empty($categories)){
            $categories=explode(',',$categories);
            $productCategories = (array)$productCategories;
            $match = count(array_intersect($productCategories, $categories));
        }
        return $match;
    }

    public function refreshCache($observer) {
        try {
            $types = Mage::app()->getCacheInstance()->getInvalidatedTypes();
            foreach($types as $type) {
                Mage::app()->getCacheInstance()->cleanType($type->getId());
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
        foreach ($pCollection as $process) {
            if ($process->getStatus() == Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX && $process->getMode() != Mage_Index_Model_Process::MODE_MANUAL) {
                try {
                    // $process->indexEvents();
                    $process->reindexEverything();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
        $sessionSave = Mage::getConfig()->getNode('global/session_save');
        if($sessionSave=='db'){ //clear expired sessions
            $dbwrite =  Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql = 'DELETE FROM core_session WHERE session_expires <= UNIX_TIMESTAMP()';
            $dbwrite->query($sql);
        }
    }
}

?>