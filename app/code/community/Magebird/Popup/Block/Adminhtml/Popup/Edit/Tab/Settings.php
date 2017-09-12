<?php class Magebird_Popup_Block_Adminhtml_Popup_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('appearance_form', array('legend' => Mage::helper('magebird_popup')->__('Settings')));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("If 'Show only once', popup with the same id won't be shown again until cookie lifetime expires.") . "</small></p>";
        $fieldset->addField('showing_frequency', 'select', array('label' => Mage::helper('magebird_popup')->__("Showing Frequency"), 'name' => 'showing_frequency', 'values' => array(array('value' => 1, 'label' => Mage::helper('magebird_popup')->__("Show until the popup is closed"),), array('value' => 2, 'label' => Mage::helper('magebird_popup')->__("Show only once"),), array('value' => 3, 'label' => Mage::helper('magebird_popup')->__('Show every time'),), array('value' => 4, 'label' => Mage::helper('magebird_popup')->__("Show until user clicks inside popup"),), array('value' => 5, 'label' => Mage::helper('magebird_popup')->__("Show until user close it or clicks inside popup"),), array('value' => 6, 'label' => Mage::helper('magebird_popup')->__("Show until goal completed (e.g.: Subscribed newsletter)"),), array('value' => 7, 'label' => Mage::helper('magebird_popup')->__("Show once per session"),),), 'after_element_html' => $after_element_html,));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("You can use also decimal dotted number (e.g.: To expire cookie in 1 hour put 0.04 which means 1 day divided with 24 hours.)") . "</small></p>";
        $fieldset->addField('cookie_time', 'text', array('label' => Mage::helper('magebird_popup')->__('Cookie lifetime in days'), 'name' => 'cookie_time', 'class' => 'validate-number', 'required' => true, 'after_element_html' => $after_element_html,));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("Only alphabet and numbers are allowed. Recommended to leave auto generated value. If you are doing A B testing with duplicate popups with similar content, it is recommended to use the same cookie id. So once user close pop up, it wont show neither this popup or neither any duplicate again") . "</small></p>";
        $fieldset->addField('cookie_id', 'text', array('label' => Mage::helper('magebird_popup')->__('Cookie/popup id'), 'class' => 'required-entry', 'required' => true, 'name' => 'cookie_id', 'after_element_html' => $after_element_html,));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("If visitor leaves window open without beeing active (for example if the user have a phone call), this can confuse the statistic. That is why it is recommended to set max time per view.") . "</small></p>";
        $fieldset->addField('max_count_time', 'text', array('label' => Mage::helper('magebird_popup')->__('Max time per view to track statistics (in seconds)'), 'name' => 'max_count_time', 'class' => 'validate-number', 'required' => true, 'after_element_html' => $after_element_html,));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("Available for popups with background overlay.") . "</small></p>";
        $fieldset->addField('close_on_overlayclick', 'select', array('label' => Mage::helper('magebird_popup')->__('Close when click outside popup'), 'name' => 'close_on_overlayclick', 'values' => array(array('value' => 0, 'label' => Mage::helper('magebird_popup')->__('No'),), array('value' => 1, 'label' => Mage::helper('magebird_popup')->__('Yes'),),), 'after_element_html' => $after_element_html,));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("Leave 0 or empty if you don't want popup to be closed automatically") . "</small></p>";
        $fieldset->addField('close_on_timeout', 'text', array('label' => Mage::helper('magebird_popup')->__('Close automatically after x seconds'), 'name' => 'close_on_timeout', 'after_element_html' => $after_element_html,));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("If you added more popups for the same pages, you can stop further popups with less priority to be shown on the same page.") . "</small></p>";
        $fieldset->addField('stop_further', 'select', array('label' => Mage::helper('magebird_popup')->__('Stop further popups'), 'name' => 'stop_further', 'values' => array(array('value' => 1, 'label' => Mage::helper('magebird_popup')->__('Yes'),), array('value' => 2, 'label' => Mage::helper('magebird_popup')->__('No'),),), 'after_element_html' => $after_element_html,));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("If you added more popups for the same pages, select display priority") . "</small></p>";
        $fieldset->addField('priority', 'text', array('label' => Mage::helper('magebird_popup')->__('Priority'), 'name' => 'priority', 'after_element_html' => $after_element_html,));
        if (Mage::getSingleton('adminhtml/session')->getPopupData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPopupData());
            Mage::getSingleton('adminhtml/session')->setPopupData(null);
        } elseif (Mage::registry('popup_data')) {
            $form->setValues(Mage::registry('popup_data')->getData());
        }
        return parent::_prepareForm();
    }
} ?>