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
 * @package     Mage_Review
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Review summary resource model
 *
 * @category    Mage
 * @package     Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Kush_Reviewimage_Model_Resource_Summary extends Mage_Review_Model_Resource_Review_Summary //extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_reviewTable;

    protected $_reviewDetailTable;

    /**
     * Define module
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_reviewTable = $this->getTable('review/review');
        $this->_reviewDetailTable = $this->getTable('review/review_detail');
    }

    /**
     * Reaggregate all data by rating summary
     *
     * @param array $summary
     * @return Mage_Review_Model_Resource_Review_Summary
     */
    public function reAggregate($summary)
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(),
                array(
                    'primary_id' => new Zend_Db_Expr('MAX(primary_id)'),
                    'store_id',
                    'entity_pk_value'
                ))
            ->group(array('entity_pk_value', 'store_id'));
        foreach ($adapter->fetchAll($select) as $row) {
            $summary = $this->_getSummary($row);
            if (!empty($summary)) {
                $ratingSummary = round($summary['sum']*20 / $summary['count']);
            } else {
                $ratingSummary = 0;
            }



            $adapter->update(
                $this->getMainTable(),
                array('rating_summary' => $ratingSummary),
                $adapter->quoteInto('primary_id = ?', $row['primary_id'])
            );

            $this->_checkExsit($row);
        }
        return $this;
    }

    protected function _checkExsit($row){
        $stores = Mage::app()->getStores();
        $adapter = $this->_getWriteAdapter();
        foreach ($stores as $store){
            $storeId = $store->getId();
            if($storeId===$row['store_id'])
                continue;
                $select = $adapter->select()
                ->from($this->getMainTable(),
                    array(
                        'primary_id',
                        //'store_id',
                        //'entity_pk_value'
                    ))
               ->where('store_id = ?',$storeId)
               ->where('entity_pk_value = ?',$row['entity_pk_value'])
                ->limit(1);
            $rs = $adapter->fetchOne($select);
           // file_put_contents(dirname(__FILE__).'/aa.txt',print_r($rs,true),FILE_APPEND);
            if(empty($rs)){
                try{
                $adapter->query("insert into ".$this->getMainTable()." set entity_pk_value='${row['entity_pk_value']}',store_id='$storeId',reviews_count='${row['reviews_count']}',entity_type='${row['entity_type']}',rating_summary='${row['rating_summary']}'");
                }catch (Exception $e){
                    Mage::logException($e);
                }
            }
        }
    }

    /**
     * 汇总的时候，不管是哪个店铺的，全部汇总出来。
     * @param $row
     * @return array
     */
    protected function _getSummary($row)
    {
        $readAdapter = $this->_getReadAdapter();
        $sumCond = new Zend_Db_Expr("SUM(r.review_rating)");
        $countCond = new Zend_Db_Expr('COUNT(*)');

        $sumSelect = $readAdapter->select()
            ->from(array('r' => $this->_reviewDetailTable), array(
                'r.store_id',
                'sum' => $sumCond,
                'count' => $countCond,
            ))
            ->join(array('rd' => $this->_reviewTable), 'r.review_id=rd.review_id', array('rd.entity_pk_value', 'rd.entity_id'))
            ->where('rd.status_id = 1')
            ->where('rd.entity_pk_value = ?', $row['entity_pk_value'])
          //  ->where('r.store_id = ?', $row['store_id'])
            ->group(array('review.entity_pk_value'));

        return $readAdapter->fetchRow($sumSelect);
    }
}
