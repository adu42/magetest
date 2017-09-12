<?php class Magebird_Popup_Block_Adminhtml_Popup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('popupGrid');
        $this->setDefaultSort('popup_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
    }

    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
/*
    protected function _prepareLayout()
    {
        $config = Mage::getModel('core/config_data');
        $extension_key = $config->load('magebird_popup/general/extension_key', 'path')->getData('value');
        if (empty($extension_key)) {
            $config = Mage::getModel('core/config_data');
            $trial_start = $config->load('magebird_popup/general/trial_start', 'path')->getData('value');
            if ($trial_start > strtotime('-7 days')) {
                $days = ceil((($trial_start + 60 * 60 * 24 * 7) - time()) / 60 / 60 / 24);
                $this->getMessagesBlock()->addError(Mage::helper('magebird_popup')->__("You are currently using free trial mode which will expire in %s days. If you purcached the extension go to System->Configuration->MAGEBIRD EXTENSIONS->Popup to activate your licence (if you get 404 error, logout from admin and login again). After the trial period is over your popups won't be displayed anymore until you submit your licence.", $days));
            } else {
                $this->getMessagesBlock()->addError(Mage::helper('magebird_popup')->__("You haven't submited your extension licence yet. Your popups won't be displayed anymore. Go to System->Configuration->MAGEBIRD EXTENSIONS->Popup to activate your licence."));
            }
        } else {
            if (Mage::getStoreConfig('magebird_popup/settings/requesttype') == 3) {
                $this->getMessagesBlock()->addError(Mage::helper('magebird_popup')->__("It seems that script magebirdpopup.php is not web accessible. Please read instructions <a href='%s' target='_blank'>here</a>.", "https://www.magebird.com/magento-extensions/popup.html?tab=faq#requestType"));
            }
        }
        return parent::_prepareLayout();
    }
*/

    protected function _prepareCollection()
    {
        $table_magebird_popup_day = Mage::getSingleton("core/resource")->getTableName('magebird_popup_day');
        $table = (boolean)Mage::getSingleton('core/resource')->getConnection('core_write')->showTableStatus(trim($table_magebird_popup_day, '`'));
        if (!$table) {
            $table_magebird_popup_day = Mage::getSingleton("core/resource")->getTableName('core_resource');
            $sql = "DELETE FROM $table_magebird_popup_day WHERE code = 'magebird_popup_setup';";
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            try {
                $connWrite->query($sql);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            echo 'Popup Extension hasn\'t installed successfully. Please temporary disable Magento cache (System->Cache Management), then refresh this page to try again. After that clear Magento cache and enabled it back. If the problem still exists, please <a href="http://www.magebird.com/contacts/">contact us</a> and we will solve the problem.';
            Mage::log('Magebird popup table does not exist');
            return;
        }
        $this->parseAllPopups();
        $collection = Mage::getModel('magebird_popup/popup')->getCollection();
        $collection->getSelect()->joinLeft(array('po' => Mage::getSingleton('core/resource')->getTableName('magebird_popup_orders')), 'po.popup_id=main_table.popup_id', array('couponSalesCount' => 'COUNT(order_id)'));
        $collection->getSelect()->joinLeft(array('o' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order')), 'po.order_id=o.entity_id', array('popupRevenue' => 'ROUND(SUM(base_total_paid),2)'));
        $collection->getSelect()->joinLeft(array('ps' => Mage::getSingleton('core/resource')->getTableName('magebird_popup_stats')), 'ps.popup_id=main_table.popup_id', array('popupSalesCount' => 'popup_purchases', 'popupVisitors' => 'popup_visitors', 'totalVisitors' => 'visitors', 'totalSalesCount' => 'purchases', 'totalCarts' => 'total_carts', 'popupCarts' => 'popup_carts'));
        $collection->getSelect()->group('main_table.popup_id');
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $baseCurrencyCode = Mage::app()->getLocale()->currency($baseCurrencyCode)->getSymbol();
        $collection->getSelect()->columns(array('currency' => new Zend_Db_Expr("'$baseCurrencyCode'")));
        $store = $this->_getStore();
        if ($store->getId()) {
            $collection->addStoreFilter($store);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('popup_id', array('header' => Mage::helper('magebird_popup')->__('Id'), 'align' => 'left', 'width' => '20px', 'index' => 'popup_id',));
        $this->addColumn('title', array('header' => Mage::helper('magebird_popup')->__('Title'), 'align' => 'left', 'index' => 'title',));
        $this->addColumn('status', array('header' => Mage::helper('magebird_popup')->__('Status'), 'align' => 'left', 'width' => '80px', 'index' => 'status', 'type' => 'options', 'options' => array(1 => 'Enabled', 2 => 'Disabled',), 'filter_index' => 'main_table.status'));
        $this->addColumn('views', array('header' => Mage::helper('magebird_popup')->__('Impressions'), 'align' => 'left', 'index' => 'views', 'filter' => false, 'width' => '80px',));
        $this->addColumn('avg_time', array('header' => Mage::helper('magebird_popup')->__('Time per <br />view') . "<span class='popupTooltip' title='" . Mage::helper('magebird_popup')->__("Time per view until any action is taken such as close popup, click inside popup, register, subscribe, ...") . ")'>(?)</span>", 'align' => 'left', 'width' => '84px', 'index' => 'total_time', 'filter' => false, 'renderer' => 'Magebird_Popup_Block_Adminhtml_Renderer_Avgtime',));
        $this->addColumn('popup_closed', array('header' => Mage::helper('magebird_popup')->__('Popup closed<br />without interaction'), 'align' => 'left', 'index' => 'popup_closed', 'filter' => false, 'width' => '80px', 'renderer' => 'Magebird_Popup_Block_Adminhtml_Renderer_Percent',));
        $this->addColumn('window_closed', array('header' => Mage::helper('magebird_popup')->__('Window <br />closed') . "<span class='popupTooltip' title='" . Mage::helper('magebird_popup')->__("Client closed browser window without popup interaction (e.g. subscribed newsletter) while popup was still opened.") . "'>(?)</span>", 'align' => 'left', 'index' => 'window_closed', 'filter' => false, 'width' => '80px', 'renderer' => 'Magebird_Popup_Block_Adminhtml_Renderer_Percent',));
        $this->addColumn('page_reloaded', array('header' => Mage::helper('magebird_popup')->__('Page <br /> reloaded') . "<span class='popupTooltip' title='" . Mage::helper('magebird_popup')->__("Client refreshed the browser window without popup interaction (e.g. subscribed newsletter) or pressed back browser button while popup was still opened.") . "'>(?)</span>", 'align' => 'left', 'index' => 'page_reloaded', 'filter' => false, 'width' => '80px', 'renderer' => 'Magebird_Popup_Block_Adminhtml_Renderer_Percent',));
        $this->addColumn('click_inside', array('header' => Mage::helper('magebird_popup')->__('Clicks inside <br /> popup'), 'align' => 'left', 'index' => 'click_inside', 'filter' => false, 'width' => '30px', 'renderer' => 'Magebird_Popup_Block_Adminhtml_Renderer_Percent',));
        $this->addColumn('goal_complition', array('header' => Mage::helper('magebird_popup')->__('Goal <br />completed') . "<span class='popupTooltip' title='" . Mage::helper('magebird_popup')->__("User signed up, clicked button, subscribed newletter or liked your page through your popup widget. If you do not use any popup widgets, this will be always 0.") . "'>(?)</span>", 'align' => 'left', 'index' => 'goal_complition', 'filter' => false, 'width' => '30px', 'renderer' => 'Magebird_Popup_Block_Adminhtml_Renderer_Percent',));
        $this->addColumn('sales_generated', array('header' => Mage::helper('magebird_popup')->__('Conversion') . "<span style='color:black;important;cursor:pointer;' class='popupTooltip' title='" . Mage::helper('magebird_popup')->__("<strong>Coupon Sales:</strong><br>How much revenue coupon code generated. Shows data only for popups with coupon code and orders with paid invoices. Shows only data for dynamic generated coupon codes.<br><strong>Coupon Orders:</strong><br>Number of placed orders assisted by coupon code from this popup. Shows only data for dynamic generated coupon codes.<br><strong>Conversion:</strong><br>How many users who have seen this popup placed order.<br><strong>Abonded cart:</strong><br>How many users who added product to cart AND have seen this popup left your site without completing the purchase.") . "'>(Details ?)</span>", 'align' => 'left', 'index' => 'sales_generated', 'filter' => false, 'sortable' => false, 'renderer' => 'Magebird_Popup_Block_Adminhtml_Renderer_Sales',));
        $this->addColumn('action', array('header' => Mage::helper('magebird_popup')->__('Action'), 'width' => '114', 'type' => 'text', 'filter' => false, 'sortable' => false, 'is_system' => true, 'renderer' => 'Magebird_Popup_Block_Adminhtml_Renderer_Actionlink',));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('popup_id');
        $this->getMassactionBlock()->setFormFieldName('popup');
        $this->getMassactionBlock()->addItem('delete', array('label' => Mage::helper('magebird_popup')->__('Delete'), 'url' => $this->getUrl('*/*/massDelete'), 'confirm' => Mage::helper('magebird_popup')->__('Are you sure?')));
        $this->getMassactionBlock()->addItem('reset', array('label' => Mage::helper('magebird_popup')->__('Reset statistics to 0'), 'url' => $this->getUrl('*/*/massReset'), 'confirm' => Mage::helper('magebird_popup')->__('Are you sure? All statistics data for selected popups will be deleted and reset to 0.')));
        $options = Mage::getSingleton('magebird_popup/status')->getOptionArray();
        array_unshift($options, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array('label' => Mage::helper('magebird_popup')->__('Change status'), 'url' => $this->getUrl('*/*/massStatus', array('_current' => true)), 'additional' => array('visibility' => array('name' => 'status', 'type' => 'select', 'class' => 'required-entry', 'label' => Mage::helper('magebird_popup')->__('Status'), 'values' => $options))));
        return $this;
    }

    protected function _filterStoreCondition($collection, $condition)
    {
        if (!$store = $condition->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($store);
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    public function getRowUrl($popup)
    {
        return $this->getUrl('*/*/edit', array('id' => $popup->getId()));
    }

    public function parseAllPopups()
    {
        $collection = Mage::getModel('magebird_popup/popup')->getCollection();
        $table = Mage::getSingleton('core/resource')->getTableName('magebird_popup_content');
        $collection->getSelect()->join(array('content' => $table), 'main_table.popup_id = content.popup_id AND content.is_template=0', array())->group('main_table.popup_id');
        if (count($collection) > 0) return;
        Mage::getModel('magebird_popup/popup')->parsePopupContent();
    }
} ?>