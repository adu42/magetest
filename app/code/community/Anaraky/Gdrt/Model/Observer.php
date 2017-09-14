<?php
class Anaraky_Gdrt_Model_Observer {
    
    public function addGdrtBlock(Varien_Event_Observer $observer) {
        if (Mage::getStoreConfig('gdrt/general/gdrt_enable', Mage::app()->getStore()->getId()) === "1") 
        {
            $gdrtPages = Mage::getStoreConfig('gdrt/pages');
            $mName = Mage::app()->getRequest()->getModuleName();
            $cName = Mage::app()->getRequest()->getControllerName();
            $aName = Mage::app()->getRequest()->getActionName();
            $pageType = 'other';

            $pathA =  $mName . '/' . $cName . '/' . $aName;
            $pathB =  $mName . '/' . $cName;
            foreach ($gdrtPages as $k => $v)
            {
                $v = rtrim($v, '/');
                if ( stripos($pathA,$v)!==false ||  stripos($pathB,$v)!==false)
                {
                    $pageType = $k;
                }
            }

            $referece =($pageType=='purchase')? 'after_body_start':'before_body_end';
            $layout = $observer->getEvent()->getLayout();
            $block = '<reference name="'.$referece.'">
                          <block type="gdrt/script" name="gdrt_block">
                              <action method="setData">
                                  <key>pageType</key>
                                  <value>' . $pageType . '</value>
                              </action>
                              <action method="setData">
                                  <key>pagePath</key>
                                  <value>' .$pathA. '</value>
                              </action>
                          </block>
                      </reference>';
            $layout->getUpdate()->addUpdate($block);

            return $this;
        }
    }
}