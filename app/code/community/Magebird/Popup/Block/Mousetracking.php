<?php class Magebird_Popup_Block_Mousetracking extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    protected function getTrackingData()
    {
        $mousetrackingpopup = Mage::getModel('magebird_popup/mousetrackingpopup')->load($this->getId());
        $mousetracking = Mage::getModel('magebird_popup/mousetracking')->load($mousetrackingpopup->getData('mousetracking_id'));
        $popup_id = Mage::getModel('magebird_popup/popup')->load($mousetrackingpopup->getData('popup_id'));
        return array("mousetrackingpopup" => $mousetrackingpopup, "mousetracking" => $mousetracking, "popup" => $popup_id);
    }

    public function getWindow()
    {
        $mousetrackingpopup = Mage::getModel('magebird_popup/mousetrackingpopup')->load($this->getId());
        $mousetracking = Mage::getModel('magebird_popup/mousetracking')->load($mousetrackingpopup->getData('mousetracking_id'));
        return array('width' => $mousetracking->getData('window_width'), 'height' => $mousetracking->getData('window_height'));
    }

    public function getId()
    {
        return $this->getRequest()->getParam('id');
    }

    public function getPrefixedCss($css, $prefix)
    {
        $css_array = explode('}', $css);
        foreach ($css_array as &$part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }
            $css_per_array = explode('{', $part);
            if (substr_count($part, "{") == 2) {
                $mediaQuery = $css_per_array[0] . "{";
                $css_per_array[0] = $css_per_array[1];
                $css_nested = true;
            }
            $css_selectors = explode(',', $css_per_array[0]);
            foreach ($css_selectors as &$subPart) {
                if (trim($subPart) == "@font-face" || strpos($subPart, ".dialog ") !== false || strpos($subPart, " .dialog") !== false || strpos($subPart, ".dialog#") !== false || strpos($subPart, ".dialog.") !== false || strpos($subPart, "dialogBg") !== false || (strpos($subPart, ".dialog") !== false && strlen($subPart) == 7)) continue;
                if (strpos($subPart, $prefix) !== false) {
                    $subPart = trim($subPart);
                } elseif (strpos($subPart, ".mbdialog") !== false) {
                    $subPart = str_replace(".mbdialog", $prefix, $subPart);
                } else {
                    $subPart = $prefix . ' ' . trim($subPart);
                }
            }
            if (substr_count($part, "{") == 2) {
                $part = $mediaQuery . "\n" . implode(', ', $css_selectors) . "{" . $css_per_array[2];
            } elseif (empty($part[0]) && $css_nested) {
                $css_nested = false;
                $part = implode(', ', $css_selectors) . "{" . $css_per_array[2] . "}\n";
            } else {
                $part = implode(', ', $css_selectors) . "{" . $css_per_array[1];
            }
        }
        $css = implode("}\n", $css_array);
        return $css;
    }
} ?>