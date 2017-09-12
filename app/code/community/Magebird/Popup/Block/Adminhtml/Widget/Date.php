<?php class Magebird_Popup_Block_Adminhtml_Widget_Date extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => 'ee', 'method' => 'post'));
        $form_date = new Varien_Data_Form_Element_Date(array('name' => 'date', 'label' => Mage::helper('magebird_popup')->__('Date'), 'after_element_html' => '<small>Click icon to select</small>', 'tabindex' => 1, 'type' => 'datetime', 'time' => true, 'image' => $this->getSkinUrl('images/grid-cal.gif'), 'format' => 'yyyy-MM-dd HH:mm:ss', 'value' => date('yyyy-MM-dd HH:mm:ss', strtotime('next weekday'))));
        $form_date->setForm($form);
        $form_date_id = 'date' . $element->getHtmlId();
        $form_date->setId($form_date_id);
        $form_date_html = $form_date->getElementHtml();
        $form_date_html .= "
        <style>
        .calendar{z-index:999999;}
        #$form_date_id{display:none}
        </style>
        <script>     
        var element = jQuery('#" . $element->getHtmlId() . "');
        element.attr('style', 'width: 150px !important');
        jQuery('body').on('change', '#$form_date_id', function(e) {
            element.val(jQuery(this).val())
        });          
        jQuery('#{$form_date_id}_trig').on('click', function() {                                       
          setTimeout(function(){                            
          jQuery('.calendar').css('top',(parseInt(element.offset().top)-50)+'px')
          jQuery('.calendar').css('left',(parseInt(element.offset().left)-150)+'px')      
          }, 100);   
        });    
        </script>";
        $element->setData('after_element_html', $form_date_html);
        return $element;
    }
} ?>