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
 * Review model
 *
 * @method Mage_Review_Model_Resource_Review _getResource()
 * @method Mage_Review_Model_Resource_Review getResource()
 * @method string getCreatedAt()
 * @method Mage_Review_Model_Review setCreatedAt(string $value)
 * @method Mage_Review_Model_Review setEntityId(int $value)
 * @method int getEntityPkValue()
 * @method Mage_Review_Model_Review setEntityPkValue(int $value)
 * @method int getStatusId()
 * @method Mage_Review_Model_Review setStatusId(int $value)
 *
 * @category    Mage
 * @package     Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Kush_Reviewimage_Model_Review extends Mage_Review_Model_Review
{

    /**
     * Event prefix for observer
     *
     * @var string
     */
    protected $_eventPrefix = 'review';
    protected $_images = null;

    /**
     * @deprecated after 1.3.2.4
     *
     */
    const ENTITY_PRODUCT = 1;

    /**
     * Review entity codes
     *
     */
    const ENTITY_PRODUCT_CODE = 'product';
    const ENTITY_CUSTOMER_CODE = 'customer';
    const ENTITY_CATEGORY_CODE = 'category';


    public function validate()
    {
        $errors = array();
        /*
                if (!Zend_Validate::is($this->getTitle(), 'NotEmpty')) {
                    $errors[] = Mage::helper('review')->__('Review Title can\'t be empty');
                }
        */

        if (!Zend_Validate::is($this->getNickname(), 'NotEmpty')) {
            $errors[] = Mage::helper('review')->__('Nickname can\'t be empty');
        }

        /*
        if (!Zend_Validate::is($this->getReviewImageA(), 'NotEmpty')) {
            $errors[] = Mage::helper('review')->__('Review Image can\'t be empty');
        }
        */

        if (!Zend_Validate::is($this->getDetail(), 'NotEmpty')) {
            $errors[] = Mage::helper('review')->__('Review can\'t be empty');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * 获得所有图片数组
     * @return array|null
     */
    public function getImages()
    {
        return $this->_images;
    }

    public function setImages($images)
    {
        $this->_images = $images;
        return $this;
    }

    public function getReviewInfo($review_id)
    {
        $table_name = 'review_detail';
        $store_id = Mage::app()->getStore()->getId();
        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        //SQL
        $results = $conn->fetchAll("SELECT * FROM  " . $table_name . " WHERE store_id=" . $store_id . " AND review_id = " . $review_id . " limit 1 ");

        return $results;
    }

    // 更新ReviewUrl
    public function _saveReviewUrl($data)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('core/url_rewrite');


        $select = $this->_getReadAdapter()
            ->select()
            ->from($table, 'url_rewrite_id')
            ->where('id_path=?', $data['id_path'])
            ->where('store_id=?', Mage::app()->getStore()->getId())
            ->limit(1);
        $urlRewriteId = $this->_getReadAdapter()->fetchOne($select);
        if (!$urlRewriteId) {
            $this->_getWriteAdapter()
                ->insert(
                    $table,
                    array(
                        'store_id' => Mage::app()->getStore()->getId(),
                        'id_path' => $data['id_path'],
                        'request_path' => $data['request_path'],
                        'target_path' => $data['target_path'],
                        'is_system' => 1,
                    )
                );
        }
    }


    // 设置读取适配器
    protected function _getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    // 设置写入适配器
    protected function _getWriteAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }


}
