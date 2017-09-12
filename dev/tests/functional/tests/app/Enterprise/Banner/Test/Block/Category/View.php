<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
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
 * @category    Tests
 * @package     Tests_Functional
 * @copyright Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

namespace Enterprise\Banner\Test\Block\Category;

use Enterprise\Banner\Test\Fixture\Banner;
use Mage\Customer\Test\Fixture\Customer;
use Magento\Mtf\Client\Locator;

/**
 * Category view block on the category page.
 */
class View extends \Mage\Catalog\Test\Block\Category\View
{
    /**
     * Widget Banner CSS selector.
     *
     * @var string
     */
    protected $widgetBanner = './/div[contains(@class, "widget-banner")]/ul/li[contains(text(),"%s")]';

    /**
     * Check Widget Banners.
     *
     * @param Banner $banner
     * @return bool
     */
    public function checkWidgetBanners(Banner $banner)
    {
        return $this->_rootElement
            ->find(sprintf($this->widgetBanner, $banner->getStoreContents()['store_content']), Locator::SELECTOR_XPATH)
            ->isVisible();
    }
}
