<?php class Magebird_Popup_Model_Config extends Mage_Core_Model_Config_Data
{
    /*
    public function save()
    {
        $oldValue = Mage::getStoreConfig($this->getPath());
        $value = $this->getValue();
        if (!$value) return;
        $field = $this->getData('field');
        $field_label = $this->getData('field_config')->label;
        if (!empty($value)) {
            if (empty($oldValue)) {
               $this->beforSave();
            }
        }
        return parent::save();
    }
*/
    /**
     * 判断是否可以写配置
     */
    /*
    public function beforSave(){
        $response = null;
        $query_string = http_build_query(array("licence_name" => "popup", "extension" => "popup", "licence_key" => $this->getValue(), "domain" => $_SERVER['HTTP_HOST'], "affId" => 0));
        if (function_exists('curl_version')) {
            $ch = @curl_init();
            @curl_setopt($ch, CURLOPT_URL, "https://www.magebird.com/licence/check.php?" . $query_string);
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = @curl_exec($ch);
            @curl_close($ch);
        }
        if ($response == null) {
            $header = "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($query_string) . "\r\n";
            $options = array("http" => array("method" => "POST", "header" => $header, "content" => $query_string));
            $context = stream_context_create($options);
            $response = @file_get_contents("https://www.magebird.com/licence/check.php", false, $context, 0, 100);
        }
        if ($response == null) {
            Mage::throwException(Mage::helper('magebird_popup')->__('Can not validate the licence key. Please <a href="http://www.magebird.com/contacts">contact us</a>.'));
        } elseif ($response != 1) {
            Mage::throwException($response);
        } else {
            Mage::getModel('core/config')->saveConfig('magebird_popup/general/extension_key', $this->getValue());
            if (Mage::getStoreConfig('magebird_popup/settings/requesttype') == 3) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magebird_popup')->__("The licence key has been submited, but it seems that script magebirdpopup.php is not web accessible. Please read instructions <a href='%s' target='_blank'>here</a>.", "https://www.magebird.com/magento-extensions/popup.html?tab=faq#requestType"));
            }
            Mage::getModel('core/config')->saveConfig('magebird_popup/settings/requestswitched', 1);
        }
    }
    */
} ?>