<?
class Magebird_Popup_Block_Widget_Wysiwyg extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setValue(urldecode($element->getValue()));
        $element->setType("textarea");
        return parent::render($element);

        $editor = new Varien_Data_Form_Element_Editor($element->getData());
        $editor->getConfig()->setPlugins(array());
        $wysiwygConfig = Mage::getSingleton("cms/wysiwyg_config")->getConfig(array("add_variables" => false, "add_widgets" => false, "files_browser_window_url" => Mage::getSingleton("adminhtml/url")->getUrl("adminhtml/cms_wysiwyg_images/index"), "theme" => "advanced", "force_br_newlines" => "false",));
        $editor->setConfig($wysiwygConfig);
        $editor->setId($element->getId());
        $editor->setForm($element->getForm());
        $editor->setWysiwyg(true);
        $editor->setForceLoad(true);
        return parent::render($editor);
    }
} ?>

