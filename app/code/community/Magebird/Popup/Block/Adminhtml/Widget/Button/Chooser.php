<?php class Magebird_Popup_Block_Adminhtml_Widget_Button_Chooser extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqHash = Mage::helper('core')->uniqHash($element->getId());
        $chooser_url = $this->getUrl('*/catalog_product_widget/chooser', array('uniq_id' => $uniqHash, 'use_massaction' => false,));
        $chooser_url = $this->getUrl('*/magebird_popup/buttonTemplate');
        $block = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')->setElement($element)->setTranslationHelper($this->getTranslationHelper())->setConfig($this->getConfig())->setFieldsetId($this->getFieldsetId())->setSourceUrl($chooser_url)->setUniqId($uniqHash);
        if ($element->getValue()) {
            $value = explode('/', $element->getValue());
            $buttonId = "Template " . str_replace(".phtml", "", $value[3]);
            $block->setLabel($buttonId);
        }
        $element->setData('after_element_html', $block->toHtml());
        return $element;
    }
} ?>