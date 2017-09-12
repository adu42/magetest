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
 * Entity rating block
 *
 * @category   Mage
 * @package    Mage_Rating
 * @author      Magento Core Team <core@magentocommerce.com>
 *  直接使用_toHtml方法，不能继承以前的东西，否则parent::_toHtml();会被覆盖
 */
class Kush_Reviewimage_Block_Rating_Detailed extends Mage_Core_Block_Template //extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('rating/detailed.phtml');
    }

    protected function _toHtml()
    {
        $entityId = Mage::app()->getRequest()->getParam('id');
        if (intval($entityId) <= 0) {
            return '';
        }

        $reviewsCount = Mage::getModel('review/review')
            ->getTotalReviews($entityId, true);
        if ($reviewsCount == 0) {
            #return Mage::helper('rating')->__('Be the first to review this product');
            $this->setTemplate('rating/empty.phtml');
            return parent::_toHtml();
        }
        $ratingCollection = new Varien_Data_Collection();
        

        $ratingSummary = Mage::getModel('rating/rating')->getEntitySummary($entityId);
        if($ratingSummary){
            $rating = new Varien_Object();
            $rating->setSummary($ratingSummary->getPercent());
            $rating->setRatingCode(Mage::helper('reviewimage')->getRatingCode());
            $ratingCollection->addItem($rating);
        }
        $this->assign('collection', $ratingCollection);
        
        return parent::_toHtml();
    }
}
