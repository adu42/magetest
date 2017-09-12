<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Regular License.
 * You may not use any part of the code in whole or part in any other software
 * or product or website.
 *
 * @author        Infortis
 * @copyright    Copyright (c) 2014 Infortis
 * @license        Regular License http://themeforest.net/licenses/regular
 */
class Infortis_Dataporter_Adminhtml_CfgporterController extends Mage_Adminhtml_Controller_Action
{
    protected $_helper;
    protected $_hc;
    protected $_ep;

    protected function _construct()
    {
        $this->_helper = Mage::helper("dataporter");
        $this->_hc = Mage::helper("dataporter/cfgporter_data");
        $this->_ep = "dataporter_cfgporter";
    }

    public function indexAction()
    {
        $action_type = $this->getRequest()->getParam("action_type");
        if ($action_type === "export") {
            $block = $this->getLayout()->createBlock("dataporter/adminhtml_cfgporter_export_edit");
        } elseif ($action_type === "import") {
            $block = $this->getLayout()->createBlock("dataporter/adminhtml_cfgporter_import_edit");
        } elseif ($action_type === NULL) {
            $this->getResponse()->setRedirect($this->getUrl("adminhtml/dashboard"));
            return;
        }
        $this->loadLayout();
        $this->_setActiveMenu('infortis');
        $this->_addBreadcrumb($this->_helper->__("Config Import and Export"), $this->_helper->__("Config Import and Export"));
        $this->_addContent($block);
        $this->renderLayout();
    } /* */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('dataporter');
    }  /* */
    public function exportAction()
    {
        $this->loadLayout();
        $modules = $this->getModules();
        if (!empty($modules)) {
            try {
                $presetFilepath = $this->getPresetFilepath();
                $this->makeDir($presetFilepath);
                $stores = $this->getRequest()->getParam("stores");
                if (is_array($stores)) {
                    throw new Exception("Website/Store ID retrieved as array. Expected string.");
                }
                $this->presetFilepath($modules, "default", $stores, $presetFilepath);
                Mage::getSingleton("adminhtml/session")->addSuccess($this->_helper->__("Successfully exported to file %s", $presetFilepath));
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton("adminhtml/session")->addError($this->_helper->__("An error occurred during export to file %s", $presetFilepath) . "\x3c\x62r/>" . $this->_helper->__("Exception: %s", $e->getMessage()));
            }
        } else {
            Mage::getSingleton("adminhtml/session")->addError($this->_helper->__("An error occurred: no source module selected for export."));
        }
        $this->renderLayout();
        $this->getResponse()->setRedirect($this->getUrl("*/*/", $this->params()));
    }

    protected function getPresetFilepath()
    {
        return $this->_hc->getPresetFilepath($this->getRequest()->getParam("preset_name"), $this->getRequest()->getParam("package"));
    }

    protected function makeDir($filename)
    {
        $mode = 0777;
        $pathname = dirname($filename);
        if (is_dir($pathname)) {
            if (!is_writable($pathname)) {
                chmod($pathname, $mode);
            }
        } else {
            if (!mkdir($pathname, $mode, true)) {
                return FALSE;
            }
        }
        return TRUE;
    }

    protected function getModules()
    {
        $modules = $this->getRequest()->getParam("modules");
        $_modules = array();
        if (!empty($modules)) {
            foreach ($modules as $module) {
                if (!empty($module)) {
                    $_modules[] = $module;
                }
            }
        }
        return $_modules;
    }

    public function importAction()
    {
        $this->loadLayout();
        $presetFilepath = $this->uploaderFile();
        if (file_exists($presetFilepath)) {
            try {
                $stores = $this->getRequest()->getParam("stores");
                $scope = Mage::getSingleton("infortis/config_scope")->decodeScope($stores);
                $x1a = "vasbegvf";
                $this->filename($scope["scope"], $scope["scopeId"], $presetFilepath);
                Mage::getSingleton("adminhtml/session")->addSuccess($this->_helper->__("Successfully imported from file %s", $presetFilepath));
                $port = array("portScope" => $scope["scope"], "portScopeId" => $scope["scopeId"]);
                Mage::dispatchEvent($this->_ep . "_import_after", $port);
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton("adminhtml/session")->addError($this->_helper->__("An error occurred during import from file %s", $presetFilepath) . "<br/>" . $this->_helper->__("Exception: %s", $e->getMessage()));
            }
        } else {
            Mage::getSingleton("adminhtml/session")->addError($this->_helper->__("An error occurred: unable to read file %s", $presetFilepath));
        }
        $this->renderLayout();
        $this->getResponse()->setRedirect($this->getUrl("*/*/", $this->params()));
    }

    protected function uploaderFile()
    {
        $tmpFileBaseDir = $this->_helper->getTmpFileBaseDir();
        $x1d = '';
        $file = '';
        if (!empty($_FILE["data_import_file"]["name"])) {
            if (file_exists($_FILE["data_import_file"]["tmp_name"])) {
                try {
                    $uploader = new Varien_File_Uploader("data_import_file");
                    $uploader->setAllowedExtensions(array('xml'));
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $uploader->save($tmpFileBaseDir, $_FILE["data_import_file"]["name"]);
                    $file = $tmpFileBaseDir . $_FILE["data_import_file"]["name"];
                } catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($this->_helper->__("An error occurred during upload of file %s", $_FILE["data_import_file"]["name"]) . "<br/>" . $this->_helper->__("Exception: %s", $e->getMessage()));
                }
            }
        } else {
            $file = $this->_hc->getPresetFilepath($this->getRequest()->getParam("preset_name"), $this->getRequest()->getParam("package"));
        }
        if (!empty($file)) {
            return $file;
        } else {
            return '';
        }
    }

    protected function params()
    {
        $params = array();
        $params["action_type"] = $this->getRequest()->getParam("action_type");
        $params["package"] = $this->getRequest()->getParam("package");
        return $params;
    }

    protected function modules($x22)
    {
        $params = print_r($this->getRequest()->getParams(), 1);
        Mage::log("Here: " . $x22 . " :\n" . $params, null, "dataporter.log");
        $str = "<pre>" . $x22 . " :\n";
        $str .= $params;
        $str .= "</pre>";
        $block = $this->getLayout()->createBlock("core/text", "debug-data-print")->setText($str);
        $this->_addContent($block);
    }

    protected function presetFilepath($modules, $path, $stores, $filename, $useDefault = TRUE, $save = FALSE)
    {
        $defaultConfig = $this->stores($modules, $path);
        $paths = array();
        foreach ($defaultConfig->children() as $node) {
            foreach ($node->children() as $child) {
                foreach ($child->children() as $children) {
                    if ($children->hasChildren()) {
                        continue;
                    }
                    $path = $node->getName() . '/' . $child->getName() . '/' . $children->getName();
                    $pathValue = Mage::getStoreConfig($path, $stores);
                    if ($stores > 0 && '' === $pathValue) {
                        if ($useDefault) {
                            $defaultValue = Mage::getStoreConfig($path, 0);
                            $pathValue = $defaultValue;
                            if ($save) {
                                if ('' === $defaultValue) {
                                    $paths[] = $path;
                                    continue;
                                }
                            }
                        }
                    }
                    $child->{$children->getName()} = $pathValue;
                }
            }
        }
        foreach ($paths as $_path) {
            $_node = $path->xpath($_path);
            unset($_node[0][0]);
        }
        $xml = $path->asNiceXml();
        if (!file_put_contents($filename, $xml)) {
            throw new Exception("Unable to write file.");
        }
    }

    protected function stores($modules, $path)
    {
        $node = simplexml_load_string("<defaul></defaul>", "Varien_Simplexml_Element");
        foreach ($modules as $moduleName) {
            $child = $this->e($moduleName, $path);
            if ($child && ($child instanceof SimpleXMLElement)) {
                foreach ($child->children() as $children) {
                    $node->appendChild($children);
                }
            }
        }
        return $node;
    }

    protected function e($moduleName, $path)
    {
        $config = Mage::getConfig()->getModuleDir("etc", $moduleName) . DS . "config.xml";
        if (file_exists($config)) {
            $data = file_get_contents($config);
            if ($data !== FALSE) {
                $node = simplexml_load_string($data, "Varien_Simplexml_Element");
                $child = $node->descend($path);
                return $child;
            }
        }
        return NULL;
    }

    protected function filename($scope, $scopeId, $filename)
    {
        $xml = $this->loadXml($filename);
        if (!$xml) {
            throw new Exception("Unable to read XML data from file - empty file or invalid format.");
        }
        foreach ($xml->children() as $node) {
            foreach ($node->children() as $child) {
                foreach ($child->children() as $children) {
                    if ($children->hasChildren()) {
                        continue;
                    }
                    $value = (string)$children;
                    if ('' === $value) {
                        $value = NULL;
                    }
                    Mage::getConfig()->saveConfig($node->getName() . '/' . $child->getName() . '/' . $children->getName(), $value, $scope, $scopeId);
                }
            }
        }
    }

    protected function loadXml($presetFilepath)
    {
        $data = file_get_contents($presetFilepath);
        return simplexml_load_string($data, "Varien_Simplexml_Element");
    }
}