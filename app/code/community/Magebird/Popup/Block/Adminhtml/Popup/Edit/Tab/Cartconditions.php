<?php class Magebird_Popup_Block_Adminhtml_Popup_Edit_Tab_Cartconditions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('appearance_form', array('legend' => Mage::helper('magebird_popup')->__('Cart conditions')));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__('Show popup only if current user has any pending payment order in history.') . "</small></p>";
        $fieldset->addField('if_pending_order', 'select', array('label' => Mage::helper('magebird_popup')->__('If pending payment order'), 'name' => 'if_pending_order', 'values' => array(array('value' => 0, 'label' => Mage::helper('magebird_popup')->__('Skip this condition'),), array('value' => 1, 'label' => Mage::helper('magebird_popup')->__('Yes, apply this condition'),),), 'after_element_html' => $after_element_html));
        $field = $fieldset->addField('product_in_cart', 'select', array('label' => Mage::helper('magebird_popup')->__('If product in cart'), 'name' => 'product_in_cart', 'values' => array(array('value' => 0, 'label' => Mage::helper('magebird_popup')->__("Skip this condition"),), array('value' => 1, 'label' => Mage::helper('magebird_popup')->__('Show only if there is any product in cart'),), array('value' => 2, 'label' => Mage::helper('magebird_popup')->__('Show only if product cart is empty'),)),));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("Leave empty or write 0 if you don't want to apply this condition.") . "</small></p>";
        $field = $fieldset->addField('cart_subtotal_min', 'text', array('label' => Mage::helper('magebird_popup')->__('Cart subtotal less than'), 'name' => 'cart_subtotal_min', 'after_element_html' => $after_element_html));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("Leave empty or write 0 if you don't want to apply this condition.") . "</small></p>";
        $field = $fieldset->addField('cart_subtotal_max', 'text', array('label' => Mage::helper('magebird_popup')->__('Cart subtotal more than'), 'name' => 'cart_subtotal_max', 'after_element_html' => $after_element_html));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__('Show popup only if there is at least 1 product with attribute value that matches your value (e.g. if color is green, ...). See instructions <a target="_blank" href="http://www.magebird.com/magento-extensions/popup.html?tab=faq#productAttributeCond">here</a>. Leave empty to skip this condition.') . "</small></p>";
        $field = $fieldset->addField('product_cart_attr', 'text', array('label' => Mage::helper('magebird_popup')->__('Product attribute in cart'), 'name' => 'product_cart_attr', 'after_element_html' => $after_element_html));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__('Show popup only if there is NO product with attribute value in cart (e.g. if NO products with green color in cart). See instructions <a target="_blank" href="http://www.magebird.com/magento-extensions/popup.html?tab=faq#productAttributeCond">here</a>. Leave empty to skip this condition.') . "</small></p>";
        $field = $fieldset->addField('not_product_cart_attr', 'text', array('label' => Mage::helper('magebird_popup')->__('Not product attribute in cart'), 'name' => 'not_product_cart_attr', 'after_element_html' => $after_element_html));
        $after_element_html = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__('Show popup only if there is any product in cart that belongs to selected categories. Write categories ids separated with comma (e.g.:1,12,31). Leave empty to skip this condition.') . "</small></p>";
        $field = $fieldset->addField('cart_product_categories', 'text', array('label' => Mage::helper('magebird_popup')->__('Product categories in cart'), 'name' => 'cart_product_categories', 'after_element_html' => $after_element_html));
        if (Mage::getSingleton('adminhtml/session')->getPopupData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPopupData());
            Mage::getSingleton('adminhtml/session')->setPopupData(null);
        } elseif (Mage::registry('popup_data')) {
            $form->setValues(Mage::registry('popup_data')->getData());
        }
        return parent::_prepareForm();
    }
} ?>