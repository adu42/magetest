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
 * Review detailed view block
 *
 * @category   Mage
 * @package    Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Kush_Reviewimage_Block_Review_Review extends Mage_Review_Block_View // extends Mage_Catalog_Block_Product_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Retrieve collection of ratings
     *
     * @return Mage_Rating_Model_Mysql4_Rating_Option_Vote_Collection
     */
    public function getRating()
    {
        if( !$this->getRatingCollection() ) { 
            $review =  $this->getReviewData();
            $ratingCollection = new Varien_Data_Collection();
            if(is_object($review)){
                $rating = new Varien_Object();
                $rating->setRatingCode(Mage::helper('reviewimage')->getRatingCode());
                $rating->setPercent($review->getReviewRating());
                $ratingCollection->addItem($rating);
            }
            $this->setRatingCollection( ( $ratingCollection->getSize() ) ? $ratingCollection : false );
        }
        return $this->getRatingCollection();
    }

    /**
     * Retrieve rating summary for current product
     *
     * @return string
     */
    public function getRatingSummary()
    {
        if( !$this->getRatingSummaryCache() ) {
            $this->setRatingSummaryCache(Mage::getModel('rating/rating')->getEntitySummary($this->getProductData()->getId()));
        }
        return $this->getRatingSummaryCache();
    }

    /**
     * Retrieve total review count for current product
     *
     * @return string
     */
    public function getTotalReviews()
    {
        if( !$this->getTotalReviewsCache() ) {
            $this->setTotalReviewsCache(Mage::getModel('review/review')->getTotalReviews($this->getProductData()->getId(), false, Mage::app()->getStore()->getId()));
        }
        return $this->getTotalReviewsCache();
    }

    /**
     * Format date in long format
     *
     * @param string $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_LONG);
    }
}
