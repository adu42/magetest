<?php class Magebird_Popup_Block_Adminhtml_Widget_Emailservices extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '';
        $actionMsgs = array();
        if (!Mage::getStoreConfig('magebird_popup/services/enableactivecampaign')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[ac_list_id\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>ActiveCampaign</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enablecampaignmonitor')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[cm_list_id\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>Campaignmonitor</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enablegetresponse')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[gr_campaign_token\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>GetResponse</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enablesendy')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[sendy_list_id\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>Sendy</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enable_phplist')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[phplist_list_id\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>phpList</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enable_klaviyo')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[klaviyo_list_id\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>Klaviyo</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enable_mailjet')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[mailjet_list_id\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>Mailjet</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enable_emma')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[emma_group_ids\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>Emma</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enable_iconneqt')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[iconneqt_list_id\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>iConneqt</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enable_nuevomailer')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[nuevomailer_list_ids\]']\").parent().parent().hide();";
            $html .= "jQuery(\"#widget_options input[name='parameters\[nuevomailer_newsletter\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>Nuevomailer</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enable_dotmailer')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[dotmailer_list_id\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>Dotmailer</strong>";
        }
        if (!Mage::getStoreConfig('magebird_popup/services/enable_cc')) {
            $html .= "jQuery(\"#widget_options input[name='parameters\[cc_list_id\]']\").parent().parent().hide();";
            $actionMsgs[] = "<strong>Constant Contact</strong>";
        }
        $html = '';
        if ($actionMsgs) {
            $html = Mage::helper('magebird_popup')->__("To insert also " . implode($actionMsgs, ", ") . " list id, enable it first inside System->Configuration->MAGEBIRD EXTENSIONS->Popup->Email services. After you enabled it new options will show up here.");
            $html = "<p style=\"padding-left:5px;margin-top:15px;margin-bottom:30px;\">Other e-mail services:<br>$html</p>";
        }
        $html = "<script>jQuery('#widget_options .hor-scroll').append('$html');</script>";
        return $html;
    }
} ?>