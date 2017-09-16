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
        $this->_helper = Mage::helper("da\x74\141\x70o\x72\x74\x65\162");
        $this->_hc = Mage::helper("d\141\x74\141po\x72\x74\x65\162\057c\x66\x67\x70\157r\164\145\x72\137\x64\141\x74\x61");
        $this->_ep = "d\x61\164a\160o\162\x74\145\x72\137\x63\146\147p\x6frt\145\162";
    }

    public function indexAction()
    {
        $x0e = $this->getRequest()->getParam("\x61\x63\164\x69o\156_\x74\x79\160\x65");
        if ($x0e === "ex\160\157\x72t") {
            $x0f = $this->getLayout()->createBlock("\x64\141\164\x61\x70ort\x65r\057a\x64m\x69\x6eht\155\154\137\143f\147\160\157\x72\164\145r_\x65\170\x70\157r\164\x5fed\x69t");
        } elseif ($x0e === "\x69\x6d\160o\162\x74") {
            $x0f = $this->getLayout()->createBlock("\144\141t\x61\160\157\x72\x74\x65r\x2f\141\x64\x6d\x69\156ht\x6d\154_c\146g\160\157r\164\x65r_\151\155p\x6f\x72\164\x5f\x65\144\x69\x74");
        } elseif ($x0e === NULL) {
            $this->getResponse()->setRedirect($this->getUrl("\x61d\155\x69\x6eh\x74\x6d\154\057das\x68\x62\x6f\x61\162\x64"));
            return;
        }
        $this->loadLayout();
        $this->_setActiveMenu('infortis');
        $this->_addBreadcrumb($this->_helper->__("\103o\x6ef\151\147 I\155\160\x6f\x72\x74\040an\x64 \105\170\x70\157\x72\x74"), $this->_helper->__("\103\x6f\156\x66\151\x67\040I\155\160\x6f\162\x74\040\141\156d\x20E\170p\x6f\162t"));
        $this->_addContent($x0f);
        $this->renderLayout();
    } /* */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('dataporter');
    }  /* */
    public function exportAction()
    {
        $this->loadLayout();
        $x10 = $this->x0d();
        if (!empty($x10)) {
            try {
                $x11 = $this->x0b();
                $this->x0c($x11);
                $x12 = $this->getRequest()->getParam("\x73\x74\157\x72\145\x73");
                if (is_array($x12)) {
                    throw new Exception("\x57\x65\x62\x73it\145\057\123\x74\157re\x20\x49D\040\x72e\x74\x72\151\x65\x76e\144 \x61\163\x20\x61\x72r\141y\056\040\105x\160e\143\164\x65\x64\040s\164\162\151\x6e\x67\x2e");
                }
                $this->x11($x10, "\144\x65\x66a\x75\x6c\x74", $x12, $x11);
                Mage::getSingleton("a\x64m\151\x6eh\x74\155l\x2fse\x73si\157\156")->addSuccess($this->_helper->__("\x53\x75\143\x63es\x73ful\x6c\171 e\x78p\157\x72\164\x65\x64\x20\164\157\x20\x66il\x65\040\x25\163", $x11));
            } catch (Exception $x13) {
                Mage::logException($x13);
                Mage::getSingleton("\x61dm\151\x6ehtm\154/\x73\x65\163\x73\151o\x6e")->addError($this->_helper->__("\101n\x20er\x72\x6fr\x20\x6f\143cur\162\145\144\x20d\x75\x72\x69n\147\040\145x\160ort \164\157\x20\146\151\154\145 \045\163", $x11) . "\x3c\x62r/>" . $this->_helper->__("E\170\143\x65pt\151on\072\x20\045\163", $x13->getMessage()));
            }
        } else {
            Mage::getSingleton("\141dm\151\156\150\164m\154\x2f\163e\x73s\151o\156")->addError($this->_helper->__("\101n\x20\x65r\x72\x6f\x72\x20\x6f\x63\143u\162re\x64\x3a \x6e\x6f\x20s\x6f\x75r\143e\040\x6d\157\x64\x75\x6c\x65\x20se\154e\x63\x74\145\x64 \x66\157\162 \145\x78po\x72\164\056"));
        }
        $this->renderLayout();
        $this->getResponse()->setRedirect($this->getUrl("\x2a\057\x2a/", $this->x0f()));
    }

    protected function x0b()
    {
        return $this->_hc->getPresetFilepath($this->getRequest()->getParam("\160\x72\145\x73\x65t_\156\141m\145"), $this->getRequest()->getParam("p\x61c\153\x61\x67e"));
    }

    protected function x0c($x14)
    {
        $x15 = 0777;
        $x16 = dirname($x14);
        if (is_dir($x16)) {
            if (!is_writable($x16)) {
                chmod($x16, $x15);
            }
        } else {
            if (!mkdir($x16, $x15, true)) {
                return FALSE;
            }
        }
        return TRUE;
    }

    protected function x0d()
    {
        $x17 = $this->getRequest()->getParam("m\157\x64\165\154\145s");
        $x10 = array();
        if (!empty($x17)) {
            foreach ($x17 as $x18) {
                if (!empty($x18)) {
                    $x10[] = $x18;
                }
            }
        }
        return $x10;
    }

    public function importAction()
    {
        $this->loadLayout();
        $x11 = $this->x0e();
        if (file_exists($x11)) {
            try {
                $x12 = $this->getRequest()->getParam("\163t\157\x72\145\x73");
                $x19 = Mage::getSingleton("i\156\146\157r\164\x69\163/c\x6f\156fig_sco\x70e")->decodeScope($x12);
                $x1a = "\x76\x61s\142\145\x67\x76\x66";
                $this->x14($x19["\163\143op\145"], $x19["s\143\157\160\x65\111\x64"], $x11);
                Mage::getSingleton("\x61\144min\x68\x74\155\x6c\057s\x65s\163i\x6fn")->addSuccess($this->_helper->__("\123\165\x63\143e\163\163\146u\x6c\x6cy \151mp\x6fr\164\145\144\040\146\162\x6fm \x66\x69\x6c\x65 %\163", $x11));
                $x1b = array("\160\x6f\x72\164Sc\157p\145" => $x19["\x73cop\145"], "\160\157\x72\164\x53\x63\157p\x65\111\144" => $x19["\163\143\x6f\x70\145\111d"]);
                Mage::dispatchEvent($this->_ep . "\137im\x70\157\162\164\137a\x66\x74e\162", $x1b);
            } catch (Exception $x13) {
                Mage::logException($x13);
                Mage::getSingleton("a\x64\155\x69\156\x68\x74ml/\163\145\163si\157n")->addError($this->_helper->__("\101n e\162\162\157\162 o\143c\165\162\x72\145\x64 \144u\162i\156g\040i\155\160\157\x72t \x66\162o\x6d\x20\146\x69\154\145\040%s", $x11) . "<\142r\057>" . $this->_helper->__("\x45x\143\x65\x70\x74io\156\x3a\040\x25\x73", $x13->getMessage()));
            }
        } else {
            Mage::getSingleton("ad\155\151\x6eh\164ml/\163e\163s\x69o\156")->addError($this->_helper->__("\x41\x6e e\x72\162\x6f\162 \x6fc\143\165\162\162\x65\144\x3a \165\x6e\141\142\x6ce\x20t\157\x20\x72\145\x61d\x20f\151\x6ce\x20%\x73", $x11));
        }
        $this->renderLayout();
        $this->getResponse()->setRedirect($this->getUrl("*\x2f\x2a\057", $this->x0f()));
    }

    protected function x0e()
    {
        $x1c = $this->_helper->getTmpFileBaseDir();
        $x1d = '';
        $x1e = '';
        if (!empty($x1f["d\x61\164a\x5f\151\x6d\160\x6f\x72\x74_\x66\x69\154e"]["\x6e\x61\x6de"])) {
            if (file_exists($x1f["\144\x61\x74\x61\137i\x6d\x70o\x72\x74\x5f\146i\x6ce"]["\164m\160\137n\x61\x6d\145"])) {
                try {
                    $x20 = new Varien_File_Uploader("\x64\141\164a\x5f\151m\160\157rt\137\146i\x6c\145");
                    $x20->setAllowedExtensions(array('xml'));
                    $x20->setAllowCreateFolders(true);
                    $x20->setAllowRenameFiles(false);
                    $x20->setFilesDispersion(false);
                    $x20->save($x1c, $x1f["d\141ta\137\x69\x6d\x70\x6frt_f\151l\145"]["\156a\155\x65"]);
                    $x1e = $x1c . $x1f["d\x61\x74a\137\151mp\157\x72\164_\x66\x69\154\x65"]["\156a\155\145"];
                } catch (Exception $x13) {
                    Mage::getSingleton("a\x64m\151\156h\164\x6dl/se\x73s\x69on")->addError($this->_helper->__("A\156 \145\x72\x72\157r\x20\157\143\143u\162r\145\x64\040\x64\x75\x72in\x67 \x75\160\154\157\x61d \x6f\146 \x66il\x65 \x25\x73", $x1f["\x64\141t\141\137\151mp\157\162\164_f\151\x6ce"]["\156\x61\x6de"]) . "\x3c\142\162\057\076" . $this->_helper->__("\x45\170c\x65pti\157n\x3a\x20\x25\163", $x13->getMessage()));
                }
            }
        } else {
            $x1e = $this->_hc->getPresetFilepath($this->getRequest()->getParam("\160\x72es\x65t\x5f\156a\x6d\145"), $this->getRequest()->getParam("p\x61\x63\x6b\x61\x67\x65"));
        }
        if (!empty($x1e)) {
            return $x1e;
        } else {
            return '';
        }
    }

    protected function x0f()
    {
        $x21 = array();
        $x21["\x61\x63\x74\x69\x6fn_t\x79p\x65"] = $this->getRequest()->getParam("a\x63\164\151\157\156\x5f\164y\160\145");
        $x21["\x70\141ckage"] = $this->getRequest()->getParam("\x70a\143\x6b\x61\147\x65");
        return $x21;
    }

    protected function x10($x22)
    {
        $x23 = print_r($this->getRequest()->getParams(), 1);
        Mage::log("\x48\x65r\x65\x3a\040" . $x22 . "\x20\072\n" . $x23, null, "\144\x61\x74\x61p\x6fr\164\x65\x72.\x6c\157\147");
        $x24 = "\x3c\160r\x65\x3e" . $x22 . "\x20:\n";
        $x24 .= $x23;
        $x24 .= "\x3c\057\160\162\x65\076";
        $x0f = $this->getLayout()->createBlock("\x63\157\x72\145\057\x74\145x\164", "d\145bug-\x64\x61\164\141-\x70\x72\x69n\164")->setText($x24);
        $this->_addContent($x0f);
    }

    protected function x11($x10, $x25, $x12, $x14, $x26 = TRUE, $x27 = FALSE)
    {
        $x28 = $this->x12($x10, $x25);
        $x29 = array();
        foreach ($x28->children() as $x2a) {
            foreach ($x2a->children() as $x2b) {
                foreach ($x2b->children() as $x2c) {
                    if ($x2c->hasChildren()) {
                        continue;
                    }
                    $x2d = $x2a->getName() . '/' . $x2b->getName() . '/' . $x2c->getName();
                    $x2e = Mage::getStoreConfig($x2d, $x12);
                    if ($x12 > 0 && '' === $x2e) {
                        if ($x26) {
                            $x2f = Mage::getStoreConfig($x2d, 0);
                            $x2e = $x2f;
                            if ($x27) {
                                if ('' === $x2f) {
                                    $x29[] = $x2d;
                                    continue;
                                }
                            }
                        }
                    }
                    $x2b->{$x2c->getName()} = $x2e;
                }
            }
        }
        foreach ($x29 as $x30) {
            $x31 = $x28->xpath($x30);
            unset($x31[0][0]);
        }
        $x32 = $x28->asNiceXml();
        if (!file_put_contents($x14, $x32)) {
            throw new Exception("\125n\x61b\154\145\040\164o \167\x72\151\164e\x20fi\154e\056");
        }
    }

    protected function x12($x10, $x25)
    {
        $x33 = simplexml_load_string("<de\x66a\165\x6c\076\074/\144\145\x66au\154>", "V\x61\162\x69\145\156\x5f\123\151mpl\145\x78\155\154_E\154e\x6d\145\156\164");
        foreach ($x10 as $x34) {
            $x31 = $this->x13($x34, $x25);
            if ($x31 && ($x31 instanceof SimpleXMLElement)) {
                foreach ($x31->children() as $x35) {
                    $x33->appendChild($x35);
                }
            }
        }
        return $x33;
    }

    protected function x13($x34, $x25)
    {
        $x36 = Mage::getConfig()->getModuleDir("\x65\164c", $x34) . DS . "\x63\157n\146\x69g\056\170ml";
        if (file_exists($x36)) {
            $x37 = file_get_contents($x36);
            if ($x37 !== FALSE) {
                $x38 = simplexml_load_string($x37, "\126a\x72\x69e\x6e\x5f\x53im\x70\x6ce\170\155l\137\x45\x6c\145\x6d\x65\156\164");
                $x31 = $x38->descend($x25);
                return $x31;
            }
        }
        return NULL;
    }

    protected function x14($x19, $x39, $x14)
    {
        $x28 = $this->x15($x14);
        if (!$x28) {
            throw new Exception("U\x6ea\x62\x6c\x65 t\157\040\x72e\x61d\040\130\x4d\114 d\x61t\141\040fr\157\155\040fil\x65 \x2d e\155\x70t\171\040f\x69\x6c\x65 \x6f\162\040\x69\156\166\141l\151d fo\x72\x6da\x74.");
        }
        foreach ($x28->children() as $x2a) {
            foreach ($x2a->children() as $x2b) {
                foreach ($x2b->children() as $x2c) {
                    if ($x2c->hasChildren()) {
                        continue;
                    }
                    $x3a = (string)$x2c;
                    if ('' === $x3a) {
                        $x3a = NULL;
                    }
                    Mage::getConfig()->saveConfig($x2a->getName() . '/' . $x2b->getName() . '/' . $x2c->getName(), $x3a, $x19, $x39);
                }
            }
        }
    }

    protected function x15($x11)
    {
        $x37 = file_get_contents($x11);
        return simplexml_load_string($x37, "\126\x61ri\x65\x6e_\x53\x69\155\x70le\170m\154_\105\x6c\x65\155\x65n\164");
    }
}