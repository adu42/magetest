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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Review
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Review resource model
 *
 * @category    Mage
 * @package     Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Kush_Reviewimage_Model_Resource_Review extends Mage_Review_Model_Resource_Review
{
   
    /**
     * Review table
     *
     * @var string
     */
    protected $_reviewTable;

    /**
     * Review Detail table
     *
     * @var string
     */
    protected $_reviewDetailTable;

    /**
     * Review status table
     *
     * @var string
     */
    protected $_reviewStatusTable;

    /**
     * Review entity table
     *
     * @var string
     */
    protected $_reviewEntityTable;

    /**
     * Review store table
     *
     * @var string
     */
    protected $_reviewStoreTable;

    /**
     * Review aggregate table
     *
     * @var string
     */
    protected $_aggregateTable;

    /**
     * Cache of deleted rating data
     *
     * @var array
     */
    private $_deleteCache   = array();

    /**
     * Define main table. Define other tables name
     *
     */
    protected function _construct()
    {
        $this->_init('review/review', 'review_id');
        $this->_reviewTable         = $this->getTable('review/review');
        $this->_reviewDetailTable   = $this->getTable('review/review_detail');
        $this->_reviewStatusTable   = $this->getTable('review/review_status');
        $this->_reviewEntityTable   = $this->getTable('review/review_entity');
        $this->_reviewStoreTable    = $this->getTable('review/review_store');
        $this->_aggregateTable      = $this->getTable('review/review_aggregate');
    }

    /**
     * 写入的时候,不是0号店铺就是1号店铺的详情表
     * 并且保存review_store表里全部店铺关联，让它在所有店铺里展示
     * @param Mage_Core_Model_Abstract $object
     * @return $this
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getWriteAdapter();
        /**
         * save detail
         */
        $detail = array(
            'title'     => $object->getTitle(),
            'detail'    => $object->getDetail(),
            'nickname'  => $object->getNickname(),
            'review_rating'  => $object->getReviewRating(),
            'review_catalog'  => $object->getReviewCatalog(),
            'review_image_a'  => $object->getReviewImageA(),
            'review_image_b'  => $object->getReviewImageB(),
            'review_image_c'  => $object->getReviewImageC(),
            'review_image_d'  => $object->getReviewImageD(),
            'review_image_e'  => $object->getReviewImageE(),
            'review_image_f'  => $object->getReviewImageF(),
            'review_image_g'  => $object->getReviewImageG(),
            'review_position'  => $object->getReviewPosition(),
            'review_home'  => $object->getReviewHome(),
            'review_sidebar'  => $object->getReviewSidebar(),
            'review_likes' => $object->getReviewLikes(),
            'review_video' => $object->getReviewVideo(),
            'review_video_thumb' => $object->getReviewVideoThumb(),
        );

        if($object->getEnable()){
            $adapter->update($this->_reviewTable,array('status_id'=>1) ,array('review_id = ?' => $object->getId()));
        }



        $select = $adapter->select()
            ->from($this->_reviewDetailTable, 'detail_id')
            ->where('review_id = :review_id');
        $detailId = $adapter->fetchOne($select, array(':review_id' => $object->getId()));

        if ($detailId) {
            $condition = array("detail_id = ?" => $detailId);
            $adapter->update($this->_reviewDetailTable, $detail, $condition);
        } else {
            $storeId = ($object->getStoreId()>=1)?1:0;
            $detail['store_id']   = $storeId;
            $detail['customer_id']= $object->getCustomerId();
            $detail['review_id']  = $object->getId();
            $adapter->insert($this->_reviewDetailTable, $detail);
        }


        /**
         * save stores
         * 
         */
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = array('review_id = ?' => $object->getId());
            $adapter->delete($this->_reviewStoreTable, $condition);

            $insertedStoreIds = array();
            
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = array(
                    'store_id' => $storeId,
                    'review_id'=> $object->getId()
                );
                $adapter->insert($this->_reviewStoreTable, $storeInsert);
            }
        }

        // reaggregate ratings, that depend on this review
        $this->_aggregateRatings(
            $this->_loadVotedRatingIds($object->getId()),
            $object->getEntityPkValue()
        );

        return $this;
    }

    /**
     * Perform actions after object load
     *
     * @param Varien_Object $object
     * @return Mage_Review_Model_Resource_Review
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_reviewStoreTable, array('store_id'))
            ->where('review_id = :review_id');
        $stores = $adapter->fetchCol($select, array(':review_id' => $object->getId()));
        if (empty($stores) && Mage::app()->isSingleStoreMode()) {
            $object->setStores(array(Mage::app()->getStore(true)->getId()));
        } else {
            $object->setStores($stores);
        }
        return $this;
    }

    /**
     * Action before delete
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Review_Model_Resource_Review
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        // prepare rating ids, that depend on review
        $this->_deleteCache = array(
            'ratingIds'     => $this->_loadVotedRatingIds($object->getId()),
            'entityPkValue' => $object->getEntityPkValue()
        );
        return $this;
    }

    /**
     * Perform actions after object delete
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Review_Model_Resource_Review
     */
    public function afterDeleteCommit(Mage_Core_Model_Abstract $object)
    {
        $this->aggregate($object);

        // reaggregate ratings, that depended on this review
        $this->_aggregateRatings(
            $this->_deleteCache['ratingIds'],
            $this->_deleteCache['entityPkValue']
        );
        $this->_deleteCache = array();

        return $this;
    }

    /**
     * 汇总所有reviews，根据review_store表中的关系来汇总
     * Retrieves total reviews
     *
     * @param int $entityPkValue
     * @param bool $approvedOnly
     * @param int $storeId
     * @return int
     */
    public function getTotalReviews($entityPkValue, $approvedOnly = false, $storeId = 0)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_reviewTable,
                array(
                    'review_count' => new Zend_Db_Expr('COUNT(*)')
                ))
            ->where("{$this->_reviewTable}.entity_pk_value = :pk_value");
        $bind = array(':pk_value' => $entityPkValue);
        if ($storeId > 0) {
            $select->join(array('store'=>$this->_reviewStoreTable),
                $this->_reviewTable.'.review_id=store.review_id AND store.store_id = :store_id',
                array());
            $bind[':store_id'] = (int)$storeId;
        }
        if ($approvedOnly) {
            $select->where("{$this->_reviewTable}.status_id = :status_id");
            $bind[':status_id'] = Mage_Review_Model_Review::STATUS_APPROVED;
        }
        return $adapter->fetchOne($select, $bind);
    }




    /**
     * 统计打分，不分店铺统计，然后分店铺写进review_entity_summary表里的
     * 只在删除评论的时候重新计算三项打分rating综合值
     * Aggregate
     *
     * @param Mage_Core_Model_Abstract $object
     */
    public function aggregate($object)
    {
        $readAdapter    = $this->_getReadAdapter();
        $writeAdapter   = $this->_getWriteAdapter();
        if (!$object->getEntityPkValue() && $object->getId()) {
            $object->load($object->getReviewId());
        }
        $writeAdapter->query('update '.$this->_reviewDetailTable.' set review_rating=4.5 where review_rating IS NULL or review_rating=0');
        $sumCond = new Zend_Db_Expr("SUM(r.review_rating)");
        $countCond = new Zend_Db_Expr('COUNT(*)');

        //不分店铺统计
        $sumSelect = $readAdapter->select()
            ->from(array('r' => $this->_reviewDetailTable), array(
                'r.store_id',
                'sum'         => $sumCond,
                'count'       => $countCond,
            ))
            ->join(array('rd'=>$this->_reviewTable) ,'r.review_id=rd.review_id',array('rd.entity_pk_value','rd.entity_id'))
            ->where('rd.status_id = 1')
            ->where('rd.entity_pk_value = ?',$object->getEntityPkValue())
            ->group(array('rd.entity_pk_value'));

        foreach ($writeAdapter->fetchAll($sumSelect) as $row) {
            $ratingSummary = round($row['sum'] * 20 / $row['count'] );
            $select = $readAdapter->select()
                ->from($this->_aggregateTable)
                ->where('entity_pk_value = :pk_value')
                ->where('entity_type = :entity_type');
                //->where('store_id = :store_id');
            $bind = array(
                ':pk_value'    => $row['entity_pk_value'],
                ':entity_type' => $row['entity_id'],
              //  ':store_id'    => 0,
            );
            $oldDatas = $readAdapter->fetchAll($select, $bind);
            $data = new Varien_Object();

            $data->setReviewsCount($row['count'])
                ->setEntityPkValue($row['entity_pk_value'])
                ->setEntityType($row['entity_id'])
                ->setRatingSummary(($ratingSummary > 0) ? $ratingSummary : 0);

            if(count($oldDatas)>0){
            foreach($oldDatas as $oldData){
            $writeAdapter->beginTransaction();
            try {
                if ($oldData['primary_id'] > 0) {
                    $condition = array("{$this->_aggregateTable}.primary_id = ?" => $oldData['primary_id']);
                    $writeAdapter->update($this->_aggregateTable, $data->getData(), $condition);
                    ///跟踪一下sql日志看看，为什么店铺更新失败？？？//==========
                }
                $writeAdapter->commit();
            } catch (Exception $e) {
                $writeAdapter->rollBack();
            }
            }
            }else{
                $writeAdapter->beginTransaction();
                try {
                    $data->setStoreId(0);
                    $writeAdapter->insert($this->_aggregateTable, $data->getData());
                    $data->setStoreId(1);
                    $writeAdapter->insert($this->_aggregateTable, $data->getData());
                    $writeAdapter->commit();
                } catch (Exception $e) {
                    $writeAdapter->rollBack();
                }
            }
        }
    }

    /**
     * Get rating IDs from review votes
     *
     * @param int $reviewId
     * @return array
     */
    protected function _loadVotedRatingIds($reviewId)
    {
        $adapter = $this->_getReadAdapter();
        if (empty($reviewId)) {
            return array();
        }
        /*  
        $select = $adapter->select()
            ->from(array('v' => $this->getTable('rating/rating_option_vote')), 'r.rating_id')
            ->joinInner(array('r' => $this->getTable('rating/rating')), 'v.rating_id=r.rating_id')
            ->where('v.review_id = :revire_id');
         return $adapter->fetchCol($select, array(':revire_id' => $reviewId));
        */
        return $ratingIds = array(array('rating_id'=>1));
       
    }

    /**
     * 不用了
     * //===============
     * Aggregate this review's ratings.
     * Useful, when changing the review.
     *
     * @param array $ratingIds
     * @param int $entityPkValue
     * @return Mage_Review_Model_Resource_Review
     */
    protected function _aggregateRatings($ratingIds, $entityPkValue)
    {
        return $this;
        if ($ratingIds && !is_array($ratingIds)) {
            $ratingIds = array((int)$ratingIds);
        }
        if ($ratingIds && $entityPkValue && ($resource = Mage::getResourceSingleton('rating/rating_option'))) {
            foreach ($ratingIds as $ratingId) {
                $resource->aggregateEntityByRatingId(
                    $ratingId, $entityPkValue
                );
            }
        }
        return $this;
    }

    /**
     * 不用了
     *  //===========
     * Reaggregate this review's ratings.
     *
     * @param int $reviewId
     * @param int $entityPkValue
     */
    public function reAggregateReview($reviewId, $entityPkValue)
    {
        return false;
        $this->_aggregateRatings($this->_loadVotedRatingIds($reviewId), $entityPkValue);
    }

    /**
     * Get review entity type id by code
     *
     * @param string $entityCode
     * @return int|bool
     */
    public function getEntityIdByCode($entityCode)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_reviewEntityTable, array('entity_id'))
            ->where('entity_code = :entity_code');
        return $adapter->fetchOne($select, array(':entity_code' => $entityCode));
    }

    /**
     * Delete reviews by product id.
     * Better to call this method in transaction, because operation performed on two separated tables
     *
     * @param int $productId
     * @return Mage_Review_Model_Resource_Review
     */
    public function deleteReviewsByProductId($productId)
    {
        $this->_getWriteAdapter()->delete($this->_reviewTable, array(
            'entity_pk_value=?' => $productId,
            'entity_id=?' => $this->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE)
        ));
        $this->_getWriteAdapter()->delete($this->getTable('review/review_aggregate'), array(
            'entity_pk_value=?' => $productId,
            'entity_type=?' => $this->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE)
        ));
        return $this;
    }
    
    
}
