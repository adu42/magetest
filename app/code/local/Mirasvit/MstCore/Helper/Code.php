<?php

/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.2
 * @build     1208
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */
class Mirasvit_MstCore_Helper_Code extends Mage_Core_Helper_Data
{
    const EE_EDITION = 'EE';
    const CE_EDITION = 'CE';
    protected static $_edition = false;
    protected $k;
    protected $s;
    protected $l;
    protected $v;
    protected $b;
    protected $d;

    public function getStatus($moduleCode = null)
    {
        return true;
        $ip = $this->getServerAddr();
        if (strpos($ip, '127.') !== false || strpos($ip, '192.') !== false) {
            return true;
        }
        if ($moduleCode) {
            $module = $this->getModuleName($moduleCode);
            $code = $this->checkCode($module);
            if ($code) {
                if (get_class($code) !== 'Mirasvit_MstCore_Helper_Code') {
                    return $code->getStatus(null);
                } else {
                    return true;
                }
            } else {
                return 'Wrong extension package!';
            }
        } else {
            return $this->checkExpired();
        }
        return true;
    }

    public function getOurExtensions()
    {
        $queryData = array();
        foreach (Mage::getConfig()->getNode('modules')->children() as $moduleName => $module) {
            if ($module->active != 'true') {
                continue;
            }
            if (strpos($moduleName, 'Mirasvit_') === 0) {
                if ($moduleName == 'Mirasvit_MstCore' || $moduleName == 'Mirasvit_MCore') {
                    continue;
                }
                $moduleNameArr = explode('_', $moduleName);
                if ($code = $this->checkCode($moduleNameArr[1])) {
                    if (method_exists($code, '_sku') && method_exists($code, '_version') && method_exists($code, '_build') && method_exists($code, '_key')) {
                        $queryData[] = array('s' => $code->_sku(), 'v' => $code->_version(), 'r' => $code->_build(), 'k' => $code->_key());
                    }
                }
            }
        }
        return $queryData;
    }

    private function checkCode($moduleCode)
    {
        $codeFile = Mage::getBaseDir() . '/app/code/local/Mirasvit/' . $moduleCode . '/Helper/Code.php';
        if (file_exists($codeFile)) {
            $code = Mage::helper(strtolower($moduleCode) . '/code');
            return $code;
        }
        return false;
    }

    private function getModuleName($moduleCode)
    {
        if (is_object($moduleCode)) {
            $moduleCode = get_class($moduleCode);
        }
        $moduleCode = explode('_', $moduleCode);
        if (isset($moduleCode[1])) {
            return $moduleCode[1];
        }
        return false;
    }

    private function checkExpired()
    {
        $result = true;
        $currentUrl = $this->getCurrentUrl();
        $flagData = $this->getFlagData();
        if (!$flagData) {
            $this->checkActived();
            $flagData = $this->getFlagData();
        }
        if ($flagData && isset($flagData['status'])) {
            if ($flagData['status'] === 'active') {
                if (abs(time() - $flagData['time']) > 24 * 60 * 60) {
                    $this->checkActived();
                }
                $result = true;
            } else {
                $this->checkActived();
                $result = $flagData['message'];
            }
        }
        return $result;
    }

    private function getFlagData()
    {
        $flag_code = 'mstcore_' . $this->getL();
        $flag = Mage::getModel('core/flag');
        $flag->load($flag_code, 'flag_code');
        if ($flag->getFlagData()) {
            $flagData = @unserialize(@base64_decode($flag->getFlagData()));
            if (is_array($flagData)) {
                return $flagData;
            }
        }
        return false;
    }

    private function setFlagData($flagData)
    {
        $flag_code = 'mstcore_' . $this->getL();
        $flag = Mage::getModel('core/flag');
        $flag->load($flag_code, 'flag_code');
        $flagData = base64_encode(serialize($flagData));
        $flag->setFlagCode($flag_code)->setFlagData($flagData);
        $flag->getResource()->save($flag);
        return $this;
    }

    private function checkActived()
    {
        $queryData = array();
        $queryData['v'] = 3;
        $queryData['d'] = $this->getCurrentUrl();
        $queryData['ip'] = $this->getServerAddr();
        $queryData['mv'] = Mage::getVersion();
        $queryData['me'] = $this->checkMageType();
        $queryData['l'] = $this->getL();
        $queryData['k'] = $this->_key();
        $queryData['uid'] = $this->getDb();
        $flagData = @unserialize($this->_request('http://mirasvit.com/lc/check/', $queryData));
        if (isset($flagData['status'])) {
            $flagData['time'] = time();
            $this->setFlagData($flagData);
        }
        return $this;
    }

    private function _request($url, $queryData)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->write(Zend_Http_Client::POST, $url, '1.1', array(), http_build_query($queryData, '', '&'));
        $flagData = $curl->read();
        $flagData = preg_split('/^\\r?$/m', $flagData, 2);
        $flagData = trim($flagData[1]);
        return $flagData;
    }

    private function getServerAddr()
    {
        return Mage::helper('core/http')->getServerAddr(false);
    }

    private function getCurrentUrl()
    {
        return Mage::helper('core/url')->getCurrentUrl();
    }

    private function checkMageType()
    {
        if (!self::$_edition) {
            $sp10ac20 = BP . DS . 'app' . DS . 'etc' . DS . 'modules' . DS . 'Enterprise' . '_' . 'Enterprise' . '.xml';
            $spce5fe6 = BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Enterprise' . DS . 'Enterprise' . DS . 'etc' . DS . 'config.xml';
            $sp37c0dc = !file_exists($sp10ac20) || !file_exists($spce5fe6);
            if ($sp37c0dc) {
                self::$_edition = self::CE_EDITION;
            } else {
                self::$_edition = self::EE_EDITION;
            }
        }
        return self::$_edition;
    }

    public function _key()
    {
        return $this->k;
    }

    public function _sku()
    {
        return $this->s;
    }

    private function getL()
    {
        return $this->l;
    }

    public function _version()
    {
        return $this->v;
    }

    public function _build()
    {
        return $this->b;
    }

    private function getD()
    {
        return $this->d;
    }

    private function getDb()
    {
        $connConfig = Mage::getConfig()->getResourceConnectionConfig('core_read');
        return md5($connConfig->dbname . $connConfig->dbhost);
    }

    public function onControllerActionPredispatch($layout)
    {
    }

    public function onModelSaveBefore($layout)
    {
    }

    public function onCoreBlockAbtractToHtmlAfter($layout)
    {
        $block = $layout->getBlock();
        if (is_object($block) && substr(get_class($block), 0, 9) == 'Mirasvit_') {
            $result = $this->getStatus(get_class($block));
            if ($result !== true) {
                $layout->getTransport()->setHtml("<ul class='messages'><li class='error-msg'><ul><li>{$result}</li></ul></li></ul>");
            }
        }
    }
}