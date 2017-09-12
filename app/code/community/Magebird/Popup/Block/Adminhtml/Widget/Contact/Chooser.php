<?php class Magebird_Popup_Block_Adminhtml_Widget_Contact_Chooser extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniq_id = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('*/catalog_product_widget/chooser', array('uniq_id' => $uniq_id, 'use_massaction' => false,));
        $sourceUrl = $this->getUrl('*/magebird_popup/contactTemplate');
        $block = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')->setElement($element)->setTranslationHelper($this->getTranslationHelper())->setConfig($this->getConfig())->setFieldsetId($this->getFieldsetId())->setSourceUrl($sourceUrl)->setUniqId($uniq_id);
        if ($element->getValue()) {
            $value = explode('/', $element->getValue());
            $chooser_id = "Template " . str_replace(".phtml", "", $value[3]);
            $block->setLabel($chooser_id);
        }
        $element->setData('after_element_html', $block->toHtml());
        return $element;
    }
} ?>