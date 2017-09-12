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

namespace Mage\Cms\Test\Fixture\CmsBlock;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Mage\Adminhtml\Test\Fixture\Store;

/**
 * Source stores for Cms Block.
 */
class Stores extends InjectableFixture
{
    /**
     * Configuration settings.
     *
     * @var array
     */
    protected $params;

    /**
     * Stores data.
     * For example: ['0' => 'Main Website/Main Website Store/Default Store View']
     *
     * @var array
     */
    protected $data = [];

    /**
     * The created stores.
     *
     * @var Store[]
     */
    protected $stores = [];

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSets'])) {
            $dataSets = explode(',', $data['dataSets']);
            foreach ($dataSets as $dataSet) {
                /** @var Store $store */
                $store = $fixtureFactory->createByCode('store', ['dataSet' => $dataSet]);
                if (!$store->hasData('store_id')) {
                    $store->persist();
                }
                $this->stores[] = $store;
                $this->data[] = $store->getGroupId() . '/' . $store->getName();
            }
        }
    }

    /**
     * Persist data.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return id of the created entity.
     *
     * @param string|null $key [optional]
     * @return array
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Get stores.
     *
     * @return Store[]
     */
    public function getStores()
    {
        return $this->stores;
    }
}
