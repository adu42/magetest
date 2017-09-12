<?php class Magebird_Popup_Block_Adminhtml_Popup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'magebird_popup';
        $this->_controller = 'adminhtml_popup';
        $this->_updateButton('save', 'label', Mage::helper('magebird_popup')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('magebird_popup')->__('Delete Item'));
        $this->_addButton('saveandcontinue', array('label' => Mage::helper('adminhtml')->__('Save And Continue Edit'), 'onclick' => 'saveAndContinueEdit(this)', 'class' => 'save',), -100);
        $this->_formScripts[] = "\r\n            function toggleEditor() { \r\n                if (tinyMCE.getInstanceById('static_content') == null) {\r\n                    tinyMCE.execCommand('mceAddControl', false, 'static_content');\r\n                } else {\r\n                    tinyMCE.execCommand('mceRemoveControl', false, 'static_content');\r\n                }\r\n            }\r\n\r\n            function save(){\r\n                jQuery('#popup_content_parsed').val(parseContent());            \r\n                editForm.submit();\r\n            }  \r\n            \r\n            function saveAndContinueEdit(el){\r\n                //jQuery(el).text('Working...')\r\n                //jQuery('#popup_content_parsed').val(parseContent());\r\n                editForm.submit($('edit_form').action+'back/edit/');\r\n            }  \r\n            \r\n            //we may need this later if file_get_contents wont work for everyone\r\n            function parseContent(){            \r\n              if(typeof(tinyMCE) !== 'undefined'){\r\n                tinyMCE.triggerSave();\r\n              }\r\n              if (location.protocol === 'https:') {\r\n                  var ajaxUrl =  document.location.origin+'magebird_popup/index/parsePopup';\r\n              }else{\r\n                  var ajaxUrl = document.location.origin+'magebird_popup/index/parsePopup';\r\n              }    \r\n              var parsed = '';          \r\n              jQuery.ajax({\r\n                type: 'POST',\r\n                url: ajaxUrl,\r\n                data:'content='+jQuery('#popup_content').val(),\r\n                async:false,\r\n                success: function(response){  \r\n                  parsed = response;              \r\n                }, \r\n                error: function(response){  \r\n                  //alert(response)            \r\n                }         \r\n              });  \r\n               \r\n              return parsed;          \r\n            }\r\n        ";
    }

    protected function _prepareLayout()
    {
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        }
        parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        if (Mage::registry('popup_data') && Mage::registry('popup_data')->getId()) {
            return Mage::helper('magebird_popup')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('popup_data')->getTitle()));
        } else {
            return Mage::helper('magebird_popup')->__('Add Item');
        }
    }
} ?>