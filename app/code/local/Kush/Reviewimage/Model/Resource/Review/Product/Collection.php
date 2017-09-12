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
 *  启用
 * Review Product Collection
 *
 * @category    Mage
 * @package     Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Kush_Reviewimage_Model_Resource_Review_Product_Collection extends Mage_Review_Model_Resource_Review_Product_Collection // extends Mage_Catalog_Model_Resource_Product_Collection
{

    /**
     * Define module
     *
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Add review summary
     *
     * @return Mage_Review_Model_Resource_Review_Product_Collection
     */
    public function addReviewSummary()
    {
        foreach ($this->getItems() as $item) {
            $data = array();
            $data['review_summary']=$item->getReviewRating();
            $data['rating_code']='rating';
            
          //  $model = Mage::getModel('rating/rating');
          //  $model->getReviewSummary($item->getReviewId());
            $item->addData($data);
        }
        return $this;
    }

    /**
     *  //== 这个不用了
     * Add rote votes
     *
     * @return Mage_Review_Model_Resource_Review_Product_Collection
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
    

}
