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
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml detailed rating stars
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Kush_Reviewimage_Block_Adminhtml_Review_Rating_Detailed extends Mage_Adminhtml_Block_Review_Rating_Detailed // extends Mage_Adminhtml_Block_Template
{
    protected $_voteCollection = false;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('rating/detailed.phtml');
        if( Mage::registry('review_data') ) {
            $this->setReviewId(Mage::registry('review_data')->getReviewId());
        }
    }

    public function getRating()
    {
        if( !$this->getRatingCollection() ) {
            $ratingCollections = Mage::helper('reviewimage')->getCustomRatingCollection();
            if( $review = Mage::registry('review_data') ) {
                $ratingCollections->getFirstItem()->setSummary($review->getReviewRating());
            }
            $this->setRatingCollection($ratingCollections);
        }
        return $this->getRatingCollection();
    }

    public function setIndependentMode()
    {
        $this->setIsIndependentMode(true);
        return $this;
    }

    public function isSelected($option, $rating)
    {
            $ratings = $this->getRequest()->getParam('ratings');

            if(isset($ratings[$option->getRatingId()])) {
                return $option->getId() == $ratings[$option->getId()];
            }elseif ($rating->getSummary()){
                return $option->getId() == $rating->getSummary();
            }

        return false;
    }
}
