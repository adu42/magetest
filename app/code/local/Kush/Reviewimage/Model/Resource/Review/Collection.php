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
 * Review collection resource model
 *
 * @category    Mage
 * @package     Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Kush_Reviewimage_Model_Resource_Review_Collection extends Mage_Review_Model_Resource_Review_Collection
{
    
   /**
     * Review table
     *
     * @var string
     */
    protected $_reviewTable;

    /**
     * Review detail table
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
     * Add store data flag
     * @var bool
     */
    protected $_addStoreDataFlag   = false;

    /**
     * Define module
     *
     */
    protected function _construct()
    {
        $this->_init('review/review');
        $this->_reviewTable         = $this->getTable('review/review');
        $this->_reviewDetailTable   = $this->getTable('review/review_detail');
        $this->_reviewStatusTable   = $this->getTable('review/review_status');
        $this->_reviewEntityTable   = $this->getTable('review/review_entity');
        $this->_reviewStoreTable    = $this->getTable('review/review_store');

    }

    /**
     * init select
     *
     * @return Mage_Review_Model_Resource_Review_Product_Collection
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(array('main_table' => $this->getMainTable()));
        $this->getSelect()
            ->join(array('detail' => $this->_reviewDetailTable),
                'main_table.review_id = detail.review_id',
                array('detail_id',
                    'title',
                    'detail',
                    'nickname',
                    'customer_id',
                    'review_image_a',
                    'review_image_b',
                    'review_image_c',
                    'review_image_d',
                    'review_image_e',
                    'review_image_f',
                    'review_image_g',
                    'review_likes',
                    'review_catalog',
                    'review_position',
                    'review_rating',
                    'review_video',
                    'review_video_thumb',
                ));
        return $this;
    }

    /**
     * 根据分类id过滤
     * @param int $catalogId
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addCategoryFilter($catalogId)
    {
        if($catalogId>2){
            $this->addFieldToFilter('detail.review_catalog',$catalogId);
        }
        return $this;
    }

    /**
     * 过滤是否定位在首页的
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addHomeFilter()
    {
        $this->addFieldToFilter('detail.review_home',1);
        return $this;
    }
    /**
     * 过滤是否定位在侧边的
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addSidebarFilter()
    {
        $this->addFieldToFilter('detail.review_sidebar',1);
        return $this;
    }


    /**
     * 过滤有图的
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addHasImageFilter()
    {
        $this->addFieldToFilter('detail.review_image_a',array('neq'=>null));
        return $this;
    }



    /**
     * review_position
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function setPositionOrder($dir = 'DESC')
    {
        $this->getSelect()->order("detail.review_position $dir");
        return $this;
    }
    /**
     * Enter description here ...
     *
     * @param unknown_type $customerId
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addCustomerFilter($customerId)
    {
        $this->addFilter('customer',
            $this->getConnection()->quoteInto('detail.customer_id=?', $customerId),
            'string');
        return $this;
    }

    /**
     * Add store filter
     *
     * @param int|array $storeId
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addStoreFilter($storeId)
    {
        $inCond = $this->getConnection()->prepareSqlCondition('store.store_id', array('in' => $storeId));
        $this->getSelect()->join(array('store'=>$this->_reviewStoreTable),
            'main_table.review_id=store.review_id',
            array());
        $this->getSelect()->where($inCond);
        return $this;
    }

    /**
     * Add stores data
     *
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addStoreData()
    {
        $this->_addStoreDataFlag = true;
        return $this;
    }

    /**
     * Add entity filter
     *
     * @param int|string $entity
     * @param int $pkValue
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addEntityFilter($entity, $pkValue)
    {
        if (is_numeric($entity)) {
            $this->addFilter('entity',
                $this->getConnection()->quoteInto('main_table.entity_id=?', $entity),
                'string');
        } elseif (is_string($entity)) {
            $this->_select->join($this->_reviewEntityTable,
                'main_table.entity_id='.$this->_reviewEntityTable.'.entity_id',
                array('entity_code'));

            $this->addFilter('entity',
                $this->getConnection()->quoteInto($this->_reviewEntityTable.'.entity_code=?', $entity),
                'string');
        }

        $this->addFilter('entity_pk_value',
            $this->getConnection()->quoteInto('main_table.entity_pk_value=?', $pkValue),
            'string');

        return $this;
    }

    /**
     * Add status filter
     *
     * @param int|string $status
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addStatusFilter($status)
    {
        if (is_string($status)) {
            $statuses = array_flip(Mage::helper('review')->getReviewStatuses());
            $status = isset($statuses[$status]) ? $statuses[$status] : 0;
        }
        if (is_numeric($status)) {
            $this->addFilter('status',
                $this->getConnection()->quoteInto('main_table.status_id=?', $status),
                'string');
        }
        return $this;
    }

    /**
     * Set date order
     *
     * @param string $dir
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function setDateOrder($dir = 'DESC')
    {
        $this->getSelect()->order("main_table.created_at $dir");
        //$this->setOrder('main_table.created_at', $dir);
        return $this;
    }

    /**
     *  在用
     *  =========
     * Add rate votes
     *
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addRateVotes()
    {
        foreach ($this->getItems() as $item) {
            $votesCollection = new Varien_Data_Collection();
            $rating = new Varien_Object();
            $rating->setRatingCode(Mage::helper('reviewimage')->getRatingCode());
            $rating->setPercent($item->getReviewRating() * 20);
            $votesCollection->addItem($rating);
            $item->setRatingVotes($votesCollection);
        }
        return $this;
    }

    /**
     * 这段干什么用的，看不懂了
     * Add reviews total count
     *
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function addReviewsTotalCount()
    {
        $this->_select->joinLeft(
            array('r' => $this->_reviewTable),
            'main_table.entity_pk_value = r.entity_pk_value',
            array('total_reviews' => new Zend_Db_Expr('COUNT(r.review_id)'))
        )
        ->group('main_table.review_id');

        /*
         * Allow analytic functions usage
         */
        $this->_useAnalyticFunction = true;

        return $this;
    }

    /**
     * Load data
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        Mage::dispatchEvent('review_review_collection_load_before', array('collection' => $this));
        parent::load($printQuery, $logQuery);
        if ($this->_addStoreDataFlag) {
            $this->_addStoreData();
        }
        return $this;
    }

    /**
     * Add store data
     *
     */
    protected function _addStoreData()
    {
        $adapter = $this->getConnection();

        $reviewsIds = $this->getColumnValues('review_id');
        $storesToReviews = array();
        if (count($reviewsIds)>0) {
            $inCond = $adapter->prepareSqlCondition('review_id', array('in' => $reviewsIds));
            $select = $adapter->select()
                ->from($this->_reviewStoreTable)
                ->where($inCond);
            $result = $adapter->fetchAll($select);
            foreach ($result as $row) {
                if (!isset($storesToReviews[$row['review_id']])) {
                    $storesToReviews[$row['review_id']] = array();
                }
                $storesToReviews[$row['review_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $item) {
            if (isset($storesToReviews[$item->getId()])) {
                $item->setStores($storesToReviews[$item->getId()]);
            } else {
                $item->setStores(array());
            }
        }
    }
}