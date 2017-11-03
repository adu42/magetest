<?php

/**
 * Ado Ciobanu
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Ado_Seo
 * @copyright   Copyright (c) 2013 Ado
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ado_SEO_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{

    /**
     * Add catalog router to front controller
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters(Varien_Event_Observer $observer)
    {
        $front = $observer->getEvent()->getFront();
        // Collect routes - needed for match()
        $this->collectRoutes('frontend', 'standard');
        $front->addRouter('ado_catalog', $this);
    }

    /**
     * Match the request
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        $helper = Mage::helper('ado_seo');
        if (!$helper->isEnabled()) {
            return false;
        }

        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        $urlRewrite = Mage::getModel('core/url_rewrite');
        list($cata, $param) = $urlRewrite->checkIsTags($request->getPathInfo());

        if (!$cata) return false;
        $catafull = $request->getPathInfo();
        /*========搜索强制设置mvc路由，默认的设置好像没有找到对应的路由，这里强制处理一下=========*/
        if ($cata == '/catalogsearch/result/index') {
            $modules = $this->getModuleByFrontName('catalogsearch');
            if($modules){
            foreach ($modules as $realModule) {
                $request->setRouteName($this->getRouteByFrontName('catalogsearch'));
                // Check if this place should be secure
                $this->_checkShouldBeSecure($request, '/catalogsearch/result/index');
                $controllerClassName = $this->_validateControllerClassName($realModule, 'result');
                if (!$controllerClassName) {
                    continue;
                }
                $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $this->getFront()->getResponse());
                if (!$controllerInstance->hasAction('index')) {
                    continue;
                }
                break;
            }
                $q = isset($param['q'])?$param['q']:'';
                $request->setModuleName('ado_seo')
                ->setControllerName('result')
                ->setActionName('index')
                ->setControllerModule('catalogsearch')
                ->setParam('q', $q);
            $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $this->getFront()->getResponse());
            $request->setDispatched(true);
            $controllerInstance->dispatch('index');
            return true;
            }
        }
        return false;
        //=== 强制搜索路由完毕 ===//


        //===下面的分类路由重写 ===//
        $urlRewrite->setStoreId(Mage::app()->getStore()->getId());
        //$cat = $urlSplit[0];

        $catPath = $cata . $suffix;
        $urlRewrite->loadByRequestPath($catPath);

        // Check if a valid category is found
        if ($urlRewrite->getId()) {
            $modules = $this->getModuleByFrontName('catalog');

            $found = false;

            // Find the controller to be executed
            // It takes in account rewrites
            foreach ($modules as $realModule) {
                $request->setRouteName($this->getRouteByFrontName('catalog'));

                // Check if this place should be secure
                $this->_checkShouldBeSecure($request, '/catalog/category/view');

                // Find controller class name
                $controllerClassName = $this->_validateControllerClassName($realModule, 'category');
                if (!$controllerClassName) {
                    continue;
                }

                // Instantiate controller class
                $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $this->getFront()->getResponse());

                // Check if controller has viewAction() method
                if (!$controllerInstance->hasAction('view')) {
                    continue;
                }
                $found = true;
                break;
            }

            // Check if we found a controller
            if (!$found) {
                return false;
            }

            // Set the required data on $request object
            $request->setPathInfo($urlRewrite->getTargetPath());
            $request->setRequestUri('/' . $urlRewrite->getTargetPath());
            $request->setModuleName('catalog')
                ->setControllerName('category')
                ->setActionName('view')
                ->setControllerModule($realModule)
                ->setParam('id', $urlRewrite->getCategoryId())
                ->setAlias(
                    Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $catPath
                );


            $request->setDispatched(true);
            $controllerInstance->dispatch('view');

            return true;
        }

        return false;
    }


}
