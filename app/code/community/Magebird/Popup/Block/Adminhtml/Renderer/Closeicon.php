<?php class Magebird_Popup_Block_Adminhtml_Renderer_Closeicon extends Varien_Data_Form_Element_Abstract
{
  //  protected $KHBuvitCgbu;

    public function getElementHtml()
    {
        $actionName = Mage::app()->getRequest()->getActionName();
        if ($actionName == "copy") {
            $copyid = Mage::app()->getRequest()->getParam('copyid');
            $close_style = Mage::getModel('magebird_popup/template')->load($copyid)->getData('close_style');
        } elseif ($actionName == "duplicate") {
            $copyid = Mage::app()->getRequest()->getParam('copyid');
            $close_style = Mage::getModel('magebird_popup/popup')->load($copyid)->getData('close_style');
        } else {
            $copyid = Mage::app()->getRequest()->getParam('id');
            $close_style = Mage::getModel('magebird_popup/popup')->load($copyid)->getData('close_style');
        }
        $baseImagePath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . "/frontend/base/default/images/magebird_popup/";
        $html = "<div class='closeIcons'>\r\n                 <input name='close_style' type='radio' value='5' /><span style='margin-right:10px;'>Don't show close icon</span>\r\n                 <input name='close_style' type='radio' value='2' /><img src='" . $baseImagePath . "close_big_preview.png' />\r\n                 <input name='close_style' type='radio' value='3' /><img src='" . $baseImagePath . "close_simple_dark_preview.png' />\r\n                 <input name='close_style' type='radio' value='4' /><img src='" . $baseImagePath . "close_simple_white_preview.png' />\r\n                 <input name='close_style' type='radio' value='8' /><img src='" . $baseImagePath . "close_big_x.png' /><br>\r\n                 <input name='close_style' type='radio' value='6' /><img src='" . $baseImagePath . "close_big_x_d.png' />\r\n                 <input name='close_style' type='radio' value='9' /><img src='" . $baseImagePath . "close_big_x_bold.png' />\r\n                 <input name='close_style' type='radio' value='10' /><img src='" . $baseImagePath . "close_big_x_bold_d.png' />\r\n                 <input name='close_style' type='radio' value='11' /><img src='" . $baseImagePath . "white_circle.png' />\r\n                 <input name='close_style' type='radio' value='1' /><img src='" . $baseImagePath . "close_dark.png' />\r\n                 <input name='close_style' type='radio' value='7' /><img src='" . $baseImagePath . "close_transparent.png' />\r\n                 </div>\r\n                  ";
        if ($close_style) {
            $html = str_replace("value='$close_style'", "value='$close_style' checked='checked'", $html);
        }
        return $html;
    }
} ?>