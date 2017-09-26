<?php

/**
 * @author @杜兵
 * @copyright 2015
 * magento时间处理
 * Mage::app()->getLocale()->date(strtotime($data['from_day']))  // 获得magento后台设置的时区的时间，我们以中国时区为例：
 * 展示时间UTC+8
 * 数据库时间UTC 这样在写记录的时候少了8小时
 * 读取的时候在用此工具，会把数据库少的时间补齐8小时
 * 查询的时候用当前时间中国时间+8+工具处理后+8，即跟UTC时间差16小时，所以要转换成UTC时间-16小时去查数据库
 * 展示数据库用此工具输出时间即可
 */
class quote{

    private $db;

    public function __construct($fileName=''){
        $this->setDb();
    }

    private function setDb(){
        if(is_object($this->db))return $this->db;
        $magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
        require_once$magento_bootstrap;
        Mage::app(); //加载……
        $this->db=Mage::getSingleton('core/resource')->getConnection('core_write');
        return $this->db;
    }

    //获得店铺及id
    public  function getStores(){
        $sql="select store_id,code from core_store order by store_id asc";
        $rs=$this->db->fetchAll($sql);
        foreach($rs as $r){
            $this->_storeIds[$r['code']]=$r['store_id'];
        }
        return $this->_storeIds;
    }

    public function query($data){
        // created_at updated_at
        $sql = "select *,
      (select method from sales_flat_quote_payment where quote_id=sales_flat_quote.entity_id limit 1) as payment,
      (select shipping_description from sales_flat_quote_address where address_type='shipping' and  quote_id=sales_flat_quote.entity_id limit 1) as shipping
       from sales_flat_quote";
        $where = '';
        $_where=array();
        if(isset($data['from_day']) && !empty($data['from_day']) && $data['from_day']!='起始日期'){
            $date = Mage::app()->getLocale()->date(strtotime($data['from_day'])-57600);
            $sTime = date('YmdHis',strtotime($date));
            // $sTime = date('YmdHis',strtotime($data['from_day']));
            $_where[]="updated_at >= $sTime";
        }
        if(isset($data['to_day']) && !empty($data['to_day']) && $data['to_day']!='截止日期'){
            $date = Mage::app()->getLocale()->date(strtotime($data['to_day'])+28800);
            $eTime = date('YmdHis',strtotime($date));
            //$eTime = date('YmdHis',strtotime($data['to_day'])+86400);
            $_where[]="updated_at <= $eTime";
        }
        if(isset($data['store']) && !empty($data['store'])){
            $_where[]="store_id = ${data['store']}";
        }
        if(isset($data['email']) && !empty($data['email']) && trim($data['email'])!='email'){
            $data['email']=addslashes($data['email']);
            $_where[]="customer_email = '${data['email']}'";
        }

        $_where[]="customer_email != ''";
        $where = !empty($_where)?(implode(' and ',$_where)):'';

        if(isset($data['repeat']) && !empty($data['repeat'])){
            $where.=" group by customer_email order by updated_at asc";
        }

        $where = ' where '.$where.' limit 500';
        $sql.=$where;

        return $this->db->fetchAll($sql);

    }
    public function _getQouteProductOptions($qouteId){
        $products =array();
        $quote =  Mage::getModel('sales/quote')->load($qouteId);
        foreach($quote->getAllItems() as $_item){
            if($_item){
                $products[$_item->getSku()]['name']=$_item->getName();
                $products[$_item->getSku()]['sku']=$_item->getSku();
                $options = $_item->getOptions();
               if($options){
                foreach($options as $option){
                    $_option_v=array();
                 /*  $_code = $option->getCode();
                   $_code=explode('_',$_code);
                   $_option_id = $_code[1];
                   if(is_numeric($_option_id)){
                       $_option = Mage::getModel('catalog/product_option')->load($_option_id);
                       $_options['title']=$_option->getTitle();
                       if($_option->getType()=='drop_down'||$_option->getType()=='radio'){
                           $_voption = Mage::getModel('catalog/product_option_value')->load($option->getValue());
                           $_options['value']=$_voption->getTitle();
                       }

                   }
*/
                   $_option = $this->getOptionTitleAndValue($option->getData());
                   if(isset($_option['title'])){
                       $_option_v['title']=$_option['title'];
                       $_option_v['value']=$_option['value'];
                       $products[$_item->getSku()]['selected_options'][]=$_option_v;
                   }

                }
               }


            }
        }
        return $products;
    }



    public function getQoute($qouteId){
         $order =$this->_getOrders($qouteId);
                //===装配自定义的订单属性
        $order['payment_type_id'] = $this->_getPaymentMethods($qouteId);
        $order['shipping_method'] = $order['shipping_description']."|".$order['quote_currency_code'].':'.$order['shipping_amount'];//$shipping_num;
        $order['billingAddress']  = $this->_getOrderBillingAddress($qouteId);
        $order['shippingAddress'] = $this->_getOrderShippingAddress($qouteId);
                $orderProducts=$this->_getOrderProducts($qouteId); //订单商品处理
                $tempProducts=array(); //多条商品临时

                foreach($orderProducts as &$product){
                    if(empty($product['parent_item_id'])){   //不是组合商品资料
                        $productOptions=$this->_getOrderProductAttr($product['product_options'],$product['created_at']);
                        $productAttrs=$this->_getOrderProductOtherAttr($product['product_id']); //获得商品系统属性
                        if(!empty($productAttrs)){
                            //$exclude=array('color','length','size');
                            foreach($productAttrs as $key=>$value){
                                if(isset($productOptions[$key])&& $productOptions[$key]!=$value){  // && !in_array($key,$exclude)
                                    $productOptions[$key.' ']=$value;
                                }else{
                                    $productOptions[$key]=$value;
                                }
                            }
                        }

                        $_product_date_option=date('Y-m-d',strtotime("+20 days"));
                        foreach($productOptions as $key=>$productOption){
                            if(stripos($key,'date')!==false&&!empty($productOption)){
                                $productOptions['shipping_real_date']=$productOption;
                                $_product_date_option=$productOption;
                                $productOptions[$key]=$productOption;
                                $spot=0;
                            }else if($key=='spot' && !empty($productOption)){  //商品属性上带上现货标识
                                $spot=1;
                            }
                        }
                        if($spot)$productOptions['spot']=1;
                        $productOptions['shipping_date']=$_product_date_option;
                        //==结束处理发货日期==//
                        ksort($productOptions);
                        //分解多件为单件
                        $product['attributes'] = $productOptions;
                    }
                }
        $order['products']=$this->_getQouteProductOptions($qouteId);
        @file_put_contents(dirname(__FILE__).'/aa.txt',print_r($order,true),FILE_APPEND);
       // return $order;
      }


    public function _getOrders($qouteId){
        $qry = "select *,(SELECT `value` FROM core_config_data WHERE path = 'web/unsecure/base_url' limit 1) as domain,(select code from core_store where store_id=sales_flat_quote.store_id limit 1) as store_code FROM sales_flat_quote where entity_id='$qouteId' order by created_at desc";

        return $this->db->fetchRow($qry);
    }

    //return array BillingAddress    //获得支付单地址
    protected function _getOrderBillingAddress($order_id){
        return $this->_getOrderAddress($order_id,'billing');
    }


    //return array ShippingAddress  //获得发件单地址
    protected function _getOrderShippingAddress($order_id){
        return $this->_getOrderAddress($order_id,'shipping');
    }

    //return array Address   //获得地址 中间过程
    protected function _getOrderAddress($order_id,$address_type='shipping'){
        $qry = "select  fax as ".$address_type."_fax,
								 region as ".$address_type."_region,
								 postcode as ".$address_type."_postcode,
								 lastname as ".$address_type."_lastname,
								 street as ".$address_type."_street,
								 city as ".$address_type."_city,
								 email as ".$address_type."_email,
								 telephone as ".$address_type."_telephone,
								 country_id as ".$address_type."_country_id,
								 firstname as ".$address_type."_firstname,
								 middlename as ".$address_type."_middlename,
								 company as ".$address_type."_company
				 FROM sales_flat_quote_address  where quote_id='$order_id'  and address_type='$address_type'  limit 1";
        return $this->db->fetchAll($qry);
    }
    //return array Order products  //获得订单商品
    public function _getOrderProducts($order_id){
        //$qry = "select i.*,(select `value` from catalog_product_entity_varchar as p where p.entity_id=i.product_id and  attribute_id = 74 limit 1) as productImg FROM sales_flat_quote_item as i where i.order_id='$order_id'";
        $qry = "select * FROM sales_flat_quote_item where quote_id='$order_id'";
        $r=$this->db->fetchAll($qry);
        return $r;
    }
    //获得支付方式
    protected function _getPaymentMethods($order_id){
        $qry = "select method FROM sales_flat_quote_payment where quote_id='$order_id' limit 1";
        $r=$this->db->fetchRow($qry);
        if(!empty($r))
            return $r['method'];
        return 0;
    }




    //获得订单商品 属性  //颜色 尺寸
    protected function  _getOrderProductAttr($productOptions,$orderTime){
        $_productOptions= preg_replace('!s:(\d+):"(.*?)";!se',"'s:'.strlen('$2').':\"$2\";'",trim($productOptions));
        $_productOptions=@unserialize($_productOptions);
        if(!$_productOptions){
            $_productOptions=@unserialize($productOptions);
        }
        $productOptions=$_productOptions;

        $options=array();
        if(isset($productOptions['options'])){
            if(!isset($options['size']))$options['size']='';
            foreach($productOptions['options'] as $option){
                $option['label']=strtolower($option['label']);
                //$options[$option['label']]=str_replace('=','-',$option['value']);

                if(stristr($option['label'],'size')!==false&&$option['label']!='size'){
                    $options['size'].=' '.$option['label'].':  '.str_replace(array('=','/'),'-',$option['value']);
                }

                if(stristr($option['label'],'rush')!==false){
                    $_day_nums=(preg_match('|(\d+)|',$option['value'],$r))? $r[0]:0;
                    $options['rush_num']=$_day_nums;
                }

                if(stristr($option['label'],'jacket')!==false){
                    $_values=array(
                        'with jacket'=>'yes',
                        'without jacket'=>'',
                    );
                    if(strtolower($option['value'])=='with jacket'){
                        $option['value']='yes';
                    }else{
                        $option['value']='no';
                    }
                }

                if(isset($option['option_type'])&&$option['option_type']=='date'){
                    $options[$option['label']]= date('Y-m-d',strtotime($option['option_value']));
                }elseif(stristr($option['label'],'date')!==false){
                    if(is_numeric($option['value'])){
                        $options[$option['label']]=date('Y-m-d',strtotime($orderTime)+($option['value']+1)*86400);
                    }else{
                        $options[$option['label']]= date('Y-m-d',strtotime($option['option_value']));
                    }
                }elseif(!isset($options['color'])&&stristr($option['label'],'color')!==false){
                    $options['color']=$option['value'];
                }else{
                    $options[$option['label']]=str_replace(array('=','/'),'-',$option['value']);
                }
            }
        }

        if(isset($productOptions['attributes_info'])){
            foreach($productOptions['attributes_info'] as $option){
                $options[$option['label']]=str_replace('=','-',$option['value']);
            }
        }

        //name和sku
        foreach($productOptions as $key=>$attr){
            if(strpos($key,'simple_')!==false){
                $key=str_replace('simple_','',$key);
                $options[$key]=$attr;
            }
        }

        $options=array_change_key_case($options,CASE_LOWER);
        return $options;
    }

    //获得商品的其他属性
    protected function  _getOrderProductOtherAttr($productId){
        $_texture=array('fabric','material');
        $_exclude=array('product_default_length','product_default_color','color','length','size');
        $sql="select * from catalog_product_index_eav where entity_id='$productId' and `value`!=0";
        $r=$this->db->fetchAll($sql);
        $attributes=array();
        if(!empty($r)){
            foreach($r as $option){
                $sql="select frontend_label as value from eav_attribute where attribute_id='".$option['attribute_id']."' limit 1";
                $attribute=$this->db->fetchRow($sql);
                if(!empty($attribute)){
                    $sql="select `value` from eav_attribute_option_value where option_id='".$option['value']."' order by store_id desc limit 1";
                    $_option=$this->db->fetchRow($sql);
                    if(!empty($_option)){
                        $_option['value']=str_replace(',','.',$_option['value']);
                        $attribute['value']=trim($attribute['value']);
                        if(in_array(strtolower($attribute['value']),$_exclude))continue;
                        $attributes[$attribute['value']]=$_option['value'];
                        if(in_array(strtolower($attribute['value']),$_texture)){
                            $attributes['texture']=$_option['value'];
                        }
                    }
                }
            }
        }
        $hairsAttributes=array('filter_style',
            'filter_weight',
            'hair_pack',
            'hair_fiber',
            'hair_set_contents',
            'hair_number_of_clips',);
        $_hairsAttributes="'".implode("','",$hairsAttributes)."'";
        $sql="select frontend_label,attribute_id,backend_type,frontend_input from eav_attribute where attribute_id>155 and entity_type_id=4 and attribute_code in ($_hairsAttributes)";
        $r=$this->db->fetchAll($sql);
        if(!empty($r)){
            foreach($r as $option){
                if($option['frontend_input']=='multiselect'||$option['frontend_input']=='select'){
                    $sql="select `value` from catalog_product_entity_{$option['backend_type']} where attribute_id='{$option['attribute_id']}' and entity_id='{$productId}' and `value`!='' limit 1";
                    $r=$this->db->fetchRow($sql);
                    if(!empty($r)){
                        $sql="select `value` from eav_attribute_option_value where option_id in ({$r['value']})";
                        $rs=$this->db->fetchAll($sql);
                        if(!empty($rs)){
                            $_val=array();
                            foreach($rs as $vals){
                                $_val[]=$vals['value'];
                            }
                            $attributes[$option['frontend_label']]=implode(',',$_val);
                        }
                    }
                }else{
                    $sql="select `value` from catalog_product_entity_{$option['backend_type']} where attribute_id='{$option['attribute_id']}' and entity_id='{$productId}' and `value`!='' limit 1";
                    $rs=$this->db->fetchRow($sql);
                    if(!empty($rs)){
                        $attributes[$option['frontend_label']]=$rs['value'];
                    }
                }
            }
        }
        if(!empty($attributes)){
            $attributes=array_change_key_case($attributes,CASE_LOWER);
        }
        return $attributes;
    }

    protected function getOptionTitleAndValue($option){
        $optionCode = $option['code'];
        $_optionCode = explode('_',$optionCode);
        $optionId = $_optionCode[1];
        if(is_numeric($optionId)){
        $optionValue = $option['value'];
         $sql = "select a.title,b.type from catalog_product_option_title a INNER JOIN catalog_product_option b on a.option_id=b.option_id where a.option_id='$optionId' limit 1";
        $rs=$this->db->fetchRow($sql);
            $option['title']=$rs['title'];
        if($rs['type']=='drop_down'||$rs['type']=='radio'){
            $sql = "select a.title from catalog_product_option_type_title a INNER JOIN catalog_product_option_type_value b on a.option_type_id=b.option_type_id where b.option_type_id='$optionValue' limit 1";
            $optionValue = $this->db->fetchOne($sql);
        }

        $option['value']=$optionValue;
        }
        return $option;
    }
}
$rows=$stores=array();
$quote = new quote();

if($_POST[data]){
    $data=$_POST[data];

    foreach($data as $key=>$val){
        $val=trim($val);
        $data[$key]=mysql_escape_string($val);
    }
    $rows = $quote->query($data);
}
$stores = $quote->getStores();
if(!empty($rows)){
    if($data['down']){
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=quote.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        echo iconv('UTF-8','GB2312',"创建日期,\temail,\tf.name,\tl.name,\tqty,\ttotal,\tcurrency,\tpayment,\tshipping,\tOrderId,\t手机端\n");
        foreach($rows as $row){
            $row['customer_firstname'] = iconv('UTF-8','GB2312',$row['customer_firstname']);
            $row['customer_lastname'] = iconv('UTF-8','GB2312',$row['customer_lastname']);
            $row['payment'] = iconv('UTF-8','GB2312',$row['payment']);
            $row['shipping'] = iconv('UTF-8','GB2312',$row['shipping']);
            $row['payment'] = strip_tags($row['payment']);
            $row['shipping'] = strip_tags($row['shipping']);
            if(isset($row['is_mobile'])&& $row['is_mobile']){$row['is_mobile']='mobile';}else{$row['is_mobile']='';}

            $date = Mage::app()->getLocale()->date(strtotime($row['updated_at']));
            $row['updated_at'] = date('Y-m-d H:i:s',strtotime($date));


            echo "${row['updated_at']},\t${row['customer_email']},\t${row['customer_firstname']},\t${row['customer_lastname']},\t${row['items_count']},\t${row['grand_total']},\t${row['quote_currency_code']},\t${row['payment']},\t${row['shipping']},\t${row['reserved_order_id']},\t${row['is_mobile']}\n";
        }
        exit;
    }
}
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
    <script src="webcalendar.js"></script>
</head>
<div>
    <form method="post" action="qoutev2.php" target="_self" enctype="application/x-www-form-urlencoded">
        <input type="text" id="email" name="data[email]" size="30" value="<?php echo !empty($data[email]) ? $data[email]:"email"; ?>" onfocus="if(this.value=='email')this.value='';"  onblur="if(this.value=='')this.value='email';" style="width:200px;"/>
        <input type="text" id="sTime" name="data[from_day]" size="30" value="<?php echo !empty($data[from_day]) ? $data[from_day]:"起始日期"; ?>" onfocus="if(this.value=='起始日期')this.value='';"  onblur="if(this.value=='')this.value='起始日期';" onclick="SelectDate(this,'yyyy-MM-dd')" readonly="true" style="width:78px;cursor:pointer"/>
        <input type="text" id="eTime" name="data[to_day]" size="30" onblur="if(this.value=='')this.value='截止日期';" onfocus="if(this.value=='截止日期')this.value='';" value="<?php echo !empty($data[to_day]) ? $data[to_day]:"截止日期"; ?>" onclick="SelectDate(this,'yyyy-MM-dd')" readonly="true" style="width:78px;cursor:pointer"/>
        <select name="data[store]">
            <?php
            foreach($stores as $code=>$storeId){
                $select = '';
                if($code==$data[store])$select=' selected="selected"';
                if($code=='admin')$code='all';
                echo '<option value="'.$storeId.'"'.$select.'>'.$code.'</option>';
            }
            ?>
        </select>
        <!--<input type="checkbox" name="data[repeat]" value="1" checked="checked" />排除重复-->
        <input type="checkbox" name="data[down]" value="1" />下载
        <input type="submit" value="查询" />
    </form>
</div>
<?php
if(!empty($rows)){
    echo count($rows).' Items<br/>';
    ?>
<?php if(false): ?>
    <div style="border-bottom: 2px solid #999;">
        <?php
        echo "创建日期,&emsp;\temail,&emsp;\tf.name,&emsp;\tl.name,&emsp;\tqty,&emsp;\ttotal,&emsp;\tcurrency,&emsp;\tpayment,&emsp;\tshipping,&emsp;\tOrderId,&emsp;\tisMobile<br/>";
        foreach($rows as $row){
            $date = Mage::app()->getLocale()->date(strtotime($row['updated_at']));
            $row['updated_at'] = date('Y-m-d H:i:s',strtotime($date));
            if(isset($row['is_mobile'])&& $row['is_mobile']){$row['is_mobile']='mobile';}else{$row['is_mobile']='pc';}
            echo "${row['updated_at']},&emsp;\t${row['customer_email']},&emsp;\t${row['customer_firstname']},&emsp;\t${row['customer_lastname']},&emsp;\t${row['items_count']},&emsp;\t${row['grand_total']},&emsp;\t${row['quote_currency_code']},&emsp;\t${row['payment']},&emsp;\t${row['shipping']},&emsp;\t${row['reserved_order_id']},&emsp;${row['is_mobile']}<br/>";

        }
        ?>
    </div>
        <?php endif; ?>

    <div style="border-bottom: 2px solid #999;">
        <?php
        @unlink(dirname(__FILE__).'/aa.txt');
        foreach($rows as $row){
            $quote->getQoute($row['entity_id']);
        }
        ?>
        <a href="aa.txt" target="_blank">打开或下载</a>
    </div>

    <?php
}
?>
</html>