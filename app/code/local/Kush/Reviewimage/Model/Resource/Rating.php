<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Rating
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Rating resource model
 *
 * @category    Mage
 * @package     Mage_Rating
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Kush_Reviewimage_Model_Resource_Rating extends Mage_Rating_Model_Resource_Rating // extends Mage_Core_Model_Resource_Db_Abstract
{
    
    
    /**
     *
     * Resource initialization
     */
    protected function _construct()
    {
        parent::_construct();
    }

    public function getEntitySummary($object, $onlyForCurrentStore = true)
    {
        $data = $this->_getEntitySummaryData($object);
        if(isset($data[0])){
         $object->addData($data[0]);
        }
        return $object;
    }
    /**
     * 不分店铺汇总的rating数据
     * @param Mage_Rating_Model_Rating $object
     * @return array
     */
    protected function _getEntitySummaryData($object)
    {
        $adapter    = $this->_getReadAdapter();

        $sumColumn      = new Zend_Db_Expr("SUM(review_detail.review_rating)");
        $countColumn    = new Zend_Db_Expr("COUNT(*)");

        $select = $adapter->select()
            ->from(array('review_detail' => $this->getTable('review/review_detail')),
                array(
                    'entity_pk_value' => 'review.entity_pk_value',
                    'sum'             => $sumColumn,
                    'count'           => $countColumn))
            ->join(array('review' => $this->getTable('review/review')),
                'review_detail.review_id=review.review_id',
                array('review.entity_pk_value'))
          /*  ->joinLeft(array('review_store' => $this->getTable('review/review_store')),
                'review_detail.review_id=review_store.review_id',
                array('review_store.store_id'))
          */
            ->join(array('review_status' => $this->getTable('review/review_status')),
                'review.status_id = review_status.status_id',
                array())
            ->where('review_status.status_code = :status_code')
            ->group('review.entity_pk_value');
            //->group('review_store.store_id');
        $bind = array(':status_code' => self::RATING_STATUS_APPROVED);

        $entityPkValue = $object->getEntityPkValue();
        if ($entityPkValue) {
            $select->where('review.entity_pk_value = :pk_value');
            $bind[':pk_value'] = $entityPkValue;
        }
        
        $rows = $adapter->fetchAll($select, $bind);
        
        if(!empty($rows)){
            foreach($rows as $key=>$row){
                $rows[$key]['percent']= round($row['sum']*20/$row['count']);
            }
        }
        
        return $rows;
    }

    public function getReviewSummary($object, $onlyForCurrentStore = true)
    {
        $adapter = $this->_getReadAdapter();

        $sumColumn      = new Zend_Db_Expr("SUM(review_detail.review_rating)");
        $countColumn    = new Zend_Db_Expr('COUNT(*)');
        $select = $adapter->select()
            ->from(array('review_detail' => $this->getTable('review/review_detail')),
                array(
                    'sum'   => $sumColumn,
                    'count' => $countColumn
                ))
            ->join(array('review' => $this->getTable('review/review')),
                'review_detail.review_id=review.review_id',
                array('review.entity_pk_value'))
            ->joinLeft(array('review_store' => $this->getTable('review/review_store')),
                'review_detail.review_id = review_store.review_id',
                array('review_store.store_id'))
            ->where('review_detail.review_id = :review_id')
            ->group('review_detail.review_id')
            ->order('review_store.store_id');
           // ->group('review_store.store_id');

        $data = $adapter->fetchAll($select, array(':review_id' => $object->getReviewId()));

        if ($onlyForCurrentStore) {
            foreach ($data as $row) {
                if ($row['store_id'] == Mage::app()->getStore()->getId()) {
                    $row['percent'] = round($row['sum']*20/$row['count']);
                    $object->addData($row);
                }
            }
            return $object;
        }

        $result = array();

    
        $stores = Mage::app()->getStore()->getResourceCollection()->load();

        foreach ($data as $row) {
            $clone = clone $object;
            $row['percent'] = round($row['sum']*20/$row['count']);
            $clone->addData($row);
            $result[$clone->getStoreId()] = $clone;
        }

        $usedStoresId = array_keys($result);

        foreach ($stores as $store) {
            if (!in_array($store->getId(), $usedStoresId)) {
                $clone = clone $object;
                $clone->setCount(0);
                $clone->setSum(0);
                $clone->setStoreId($store->getId());
                $result[$store->getId()] = $clone;
            }
        }

        return array_values($result);
    }
}
