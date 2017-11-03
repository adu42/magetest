<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   Utilities
 * @package    Ado_Guestcookies
 * @author     ado <114458573@qq.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Ado_Guestcookies_Model_Viewed
 extends Ado_Guestcookies_Model_Cookie_Abstract
{

    protected $_ids=array();
	protected function _construct()
	{
		$this->setName(Mage::getStoreConfig('web/guestcookies/viewed_name'));
	}

	/**
	 * Update database with this visitor's recently viewed list
	 * 
	 * @param array $ids
	 * @return Ado_Guestcookies_Model_Viewed $this
	 */
	public function addProductIds($ids)
	{
		if ($ids) {
			foreach ($ids as $id) {
                $this->_ids[] = $id;
            }
            $this->_ids = array_unique($this->_ids);
			    /*
			    try {
    				Mage::getModel('reports/product_index_viewed')
    					->setProductId($id)
    					->save();
			    }
			    catch (Exception $e) {
			        // do nothing if product ID is missing
			    }
			}
			Mage::getModel('reports/product_index_viewed')
				->calculate();
			    */

		}
		return $this;
	}

	/**
	 * List recently viewed products from database for this visitor
	 * 
	 * @return array
	 */
	public function getProductIds()
	{
	    return $this->_ids;
		$viewed = Mage::getResourceModel('reports/product_index_viewed_collection');
		$viewed->addIndexFilter();
		return $viewed->getColumnValues('product_id');
	}

	/**
	 * Number of recently viewed products.
	 */
	public function getProductsCount()
	{
	    return count($this->_ids);
		return Mage::getModel('reports/product_index_viewed')->getCount();
	}

	public function readCookie()
	{

		if ($this->getProductsCount()) {
			// do not mix existing list with new data
			return $this;
		}

		$newProductIds = explode(' ', $this->_readCookie());
		$newProductIds = array_filter($newProductIds, 'is_numeric');
		// only proceed if there is data to work with
		if ($newProductIds) {
			$this->addProductIds($newProductIds);
		}

		return $this;
	}

	public function writeCookie()
	{
		if (Mage::getStoreConfigFlag('web/guestcookies/viewed')) {
			$this->_updateCookie(implode(' ', $this->getProductIds()));
		}
		return $this;
	}

}

