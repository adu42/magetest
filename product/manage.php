<?php
@header('Content-Type:text/html;charset=utf-8');
@header('Access-Control-Allow-Origin:*');
$pwd = 'doit';
@Error_Reporting(E_ALL);
@session_start();
$pfile=isset($_GET["pfile"])?$_GET["pfile"]:'';
$cfile=isset($_GET["cfile"])?$_GET["cfile"]:'';
$do=isset($_GET["do"])?$_GET["do"]:'';
$store=isset($_GET["store"])?$_GET["store"]:'';
$catalog=isset($_GET["c"])?$_GET["c"]:'';
if(!empty($pfile)){
    $product=new appendProduct($pfile);
    $product->run();
    $obj = new cleanDbCacheCatalogAndProduct();
    $obj->run('p'); //清空 flat 表
    //_processAll();
}
if(!empty($cfile)){
    $catalog=new appendCatalog($cfile);
    $catalog->run();
    $obj = new cleanDbCacheCatalogAndProduct();
    $obj->run('c'); //清空 flat 表
    //_processAll();
}

//检查密码，提示密码明文
function chkcls(){
    global  $pwd ;
    $pass =  isset($_POST['pwd'])?$_POST['pwd']:'';
    $validated = ($pass == $pwd);

    if (!$validated && (!isset($_SESSION['auth']) ||  !$_SESSION['auth'])) {
        echo "<form action='{$_SERVER['REQUEST_URI']}' METHOD='post'>\n";
        echo "<input type='text' name='pwd' value='' />\n";
        echo "<input type='submit' value='Re Authenticate' />\n";
        echo "</form></p>\n";
        die();
    }else{
        $_SESSION['auth']=true;
    }
}

if(!empty($do)){
    if($do=='clsp'){
        chkcls();
        $obj=new clsCataOrProduct();
        $obj->runClsProduct();
        $obj = new cleanDbCacheCatalogAndProduct();
        $obj->run('p'); //清空 flat 表
       // _processAll();
    }else if($do=='clsc'){
        chkcls();
        $obj=new clsCataOrProduct();
        $obj->runClsCatalog();
        $obj = new cleanDbCacheCatalogAndProduct();
        $obj->run('c'); //清空 flat 表
      //  _processAll();
    }else if($do=='clso'){
        chkcls();
        $obj=new clsCataOrProduct();
        $obj->runClsOrder();
      // _processAll();
    }else if($do=='clscu'){
        chkcls();
        $obj=new clsCataOrProduct();
        $obj->runClsCustomer();
       // _processAll();
    }else if($do=='clslog'){
        chkcls();
        $obj=new clsCataOrProduct();
        $obj->runClsLog();
        $obj = new cleanDbCacheCatalogAndProduct();
        $obj->run('c'); //清空 flat 表
       // _processAll();
    }else if($do=='clsemaillog'){
        chkcls();
        $obj=new clsCataOrProduct();
        $obj->runEmailClsLog();
       // $obj = new cleanDbCacheCatalogAndProduct();
       // $obj->run('c'); //清空 flat 表
        // _processAll();
    }else if($do=='clsr'){
        chkcls();
        $obj=new clsCataOrProduct();
        $obj->runClsReview();
        //_processAll();
    }else if($do=='maxid'){
        $obj=new clsCataOrProduct();
        $obj->runGetCatalogMaxId();
        die();
    }else if($do=='expro'){
        $obj=new exportProduct();
        $obj->run($catalog,$store);
        die();
	 }else if($do=='clspro'){
	    chkcls();
        $obj=new clsCataOrProduct();
        $obj->runClsProduct();
		$obj->runClsOrder();
		$obj->runClsCustomer();
		$obj->runClsReview();
		$obj->runClsLog();
        $obj = new cleanDbCacheCatalogAndProduct();
        $obj->run('p'); //清空 flat 表
        die();
    }else if($do=='init'){
	    chkcls();
        $obj=new clsCataOrProduct();
        //$obj->runClsProduct();
		$obj->runClsOrder();
		$obj->runClsCustomer();
		//$obj->runClsReview();
		$obj->runClsLog();
        $obj->runEmailClsLog();
        $id=(isset($_GET['id'])&&!empty($_GET['id']))?$_GET['id']:'';
        $obj->initOrderId($id);
        $obj = new cleanDbCacheCatalogAndProduct();
        $obj->run('all'); //清空 flat 表，url 表
        die();
    }else if($do=='impview'){
        $code=isset($_GET['code'])?substr($_GET['code'],0,2):'';
        $del=isset($_GET['notdel'])?false:true;
        $sku=isset($_GET['sku'])?trim($_GET['sku']):'';
        $obj=new importReview();
        if($sku)$obj->setSku($sku);
        $obj->run($code,$del);
        die();
    }else if($do=='copyview'){
        $code=isset($_GET['code'])?substr($_GET['code'],0,2):'';
        $obj=new importReview();
        if(isset($_GET['vote'])&&$_GET['vote']==1){
            $obj->setEnReviewVoteToOtherStore($code);
        }else{
            $obj->setEnReviewToOtherStore($code);
        }
        die();
    }else if($do=='clsreview'){
        chkcls();
        $obj=new importReview();
        $obj->runcls();
        die();
    }else if($do=='copycatalogproduct'){
        chkcls();
        $obj=new copyCatalogAndProduct();
        $obj->run($_GET['ctc']); // 24-36 src_catalog_id-dst_catalog_id
        die();
    }else if($do=='upsell'){
        $obj = new appendUpSell();
        $obj->run();
        die();
    }else if($do=='clsDbCache'){
        $obj = new cleanDbCacheCatalogAndProduct();
        $obj->run($_GET['ctc']); //清空 flat 表
        die();
    }else if($do=='help'){
        $obj=new clsCataOrProduct();
        $obj->runGetHelp();
        die();
    }
}
/**
 * ====================================================
 * @增改magento商品数据类
 * @动态attribute_id模式
 * @copyright by ado
 * @114458573@qq.com
 * ====================================================
 */
class appendProduct{
   private $_file=''; 
   private $_defaultProduct=array(
        'has_options'=>1,
        'store_id'=>0,
        'website_id'=>1,
        'has_options'=>0,
        'status'=>1,
        'visibility'=>4,
        'tax_class_id'=>0,
        'qty'=>1000,
        'is_recurring'=>0,
        'is_in_stock'=>1,
        'weight'=>500,
        'entity_type_id'=>4,
   );
  
  private $_productHead=array(); //记录商品csv头
  private $_productOptions=array();//记录自定义属性列名 
  private $visibilitys=array(
    'catalog,search'=>4,
    'catalog'=>2,
    'search'=>3,
    'not_visible'=>1,
  );
  
  private $attrs;
  private $_subAttributeIds;  //商品属性id集
  private $_subAttributeIdsSimple;  //商品属性id集
  private $_subAttributeBackendTypes;  //商品属性写入方式类别集
  
  private $_checkColumnExist;
  private $_subAttributeValues; //商品属性id集值
  
  private $db;
  private function setDb(){
    if(is_object($this->db))return $this->db;
    $magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
	require_once$magento_bootstrap;
	Mage::app(); //加载……
	$this->db=Mage::getSingleton('core/resource')->getConnection('core_read');
    return $this->db;
  }

    /**
     * 检查列是否存在
     * @param $table
     * @param $column
     * @return bool
     */
  protected function checkColumnExist($table,$column){
      if($this->_checkColumnExist && isset($this->_checkColumnExist[$table]) && isset($this->_checkColumnExist[$table][$column]))return $this->_checkColumnExist[$table][$column];
      $r = $this->db->fetchOne("show COLUMNS from $table like '$column'");
      return $this->_checkColumnExist[$table][$column] = !empty($r);
  }

  
  public function __construct($fileName=''){
    if(!empty($fileName)&&is_file($fileName))$this->_file=$fileName;
  }
  
  public function setCsvfile($fileName){
     if(!empty($fileName)&&is_file($fileName))$this->_file=$fileName;
  }
  
  public function getCsvfile(){
    return $this->_file;
  }
  /**
   * 主入口
   */
  public function run(){
     if(!empty($this->_file)){
         $this->setDb();
         $this->_setDefaultProduct();
         $this->_setCsvData($this->_file);
     }
  }
   /**
    * @读取文件
    */
   private function _setCsvData($file){
        $row=0;
	    $handle=fopen($file,"r");
        while($data=fgetcsv($handle,100000,",")){
            if(empty($data))continue;
            $row++;
            $_data=@iconv('gb2312','utf-8',$data);
            if(!empty($_data))$data=$_data;
             if($row==1){
                $productHead=array();
                foreach($data as $key=>$val){
                    $val=trim($val);
                    $val=iconv('gbk','utf-8',$val);
                    if(!empty($val) && stripos($val,':')!==false){
                        $temp_arr=explode(':',$val);
                        if(count($temp_arr)>=3){
                            $this->_productOptions[]=$temp_arr[0];
                             $_isRequired=isset($temp_arr[3])?($temp_arr[3]>0?1:0):0;
                            $productHead[$key]=array(
                                'optionLabel'=>$temp_arr[0],
                                'optionType'=>$temp_arr[1],
                                'sort'=>$temp_arr[2],
                                'isRequired'=>$_isRequired,
                                //'is_option'=>1,
                            );
                        }
                    }else{
                        $productHead[$key]=$val;
                    }
                } 
                $this->_productHead=$productHead;          
             }else{
                $dataRow=$this->_getRow($this->_productHead,$data);
                // print_r($dataRow);
                $dataRow=$this->_setRowData($dataRow);
                $this->checkCatalogIds($dataRow);
                $this->initAttributeIds();
                $this->setProduct($dataRow);
                //die('end');
             }
        }
        fclose($handle);
   }
   /**
    * @设置单行的默认数据
    */
   protected function _setRowData($dataRow){
        if(!isset($dataRow['attribute_set']))$dataRow['attribute_set']='default';
        $dataRow['visibility']=$this->_getVisibility($dataRow);
        $dataRow['attribute_set_id']=$this->_getAttributeSetId($dataRow);
        unset($dataRow['attribute_set']);
        if(!isset($dataRow['type'])||empty($dataRow['type'])){
            $dataRow['type']='simple';   
        }else if(!in_array(strtolower($dataRow['type']),array('simple','configurable','grouped','virtual','bundle','downloadable'))){
            $dataRow['type']='simple';
        }else{
           $dataRow['type']=strtolower($dataRow['type']);  
        }
        $dataRow['entity_type_id']=4;
        if(!isset($dataRow['special_price'])&&isset($dataRow['special_from_date']))unset($dataRow['special_from_date']);
        if(isset($dataRow['special_price'])&&($dataRow['special_price'] <=0 )){
            unset($dataRow['special_price']);
            unset($dataRow['special_from_date']);   
        }else if($dataRow['special_price'] > 0){
            if(!isset($dataRow['special_from_date'])||empty($dataRow['special_from_date']))$dataRow['special_from_date']=date('Y-m-d',strtotime('-1 day'));
            if(!isset($dataRow['special_to_date'])||empty($dataRow['special_to_date'])){
                $dataRow['special_to_date']=NULL;   
            }
        }
        if(isset($dataRow['price'])&&($dataRow['price'] <=0 )){
            $dataRow['price']=10000;  
        }
        if(!isset($dataRow['news_from_date'])||empty($dataRow['news_from_date']))$dataRow['news_from_date']=date('Y-m-d',strtotime('-1 day'));
        if(!isset($dataRow['news_to_date'])||empty($dataRow['news_to_date']))$dataRow['news_to_date']=NULL;
        if(!isset($dataRow['is_in_stock']))$dataRow['is_in_stock']=1;
        if(isset($dataRow['status'])&& strtolower($dataRow['status'])=='disable'){
            $dataRow['status']=0;
        }else{
            $dataRow['status']=1;
        }
        if(!isset($dataRow['url_key'])||empty($dataRow['url_key'])){
            if(!empty($dataRow['name'])){
                $dataRow['url_key']=str_replace(array('  ','&','+','#','_',' ',"'",'"'),'-',$dataRow['name']);
                $dataRow['url_path']=isset($dataRow['url_path'])?$dataRow['url_path']:$dataRow['url_key'].'.html';
            }  
        }
        $dataRow['url_key']=strtolower($dataRow['url_key']);
        $dataRow['url_path']=strtolower($dataRow['url_path']);
        if(isset($dataRow['websites'])){
           $dataRow['website_id']=$this->_setWebsiteId($dataRow['websites']);
            unset($dataRow['websites']);
        }
        if(isset($dataRow['store'])){
           $dataRow['store_id']=$this->_setStoreId($dataRow['store']);
            unset($dataRow['store']);
        }
        return $dataRow;
   }    
     /**
   * @在哪可见
   */
  private function _getVisibility($row){
     if(isset($row['visibility'])){
        $row['visibility']=trim($row['visibility']);
        $row['visibility']=strtolower($row['visibility']);
        $row['visibility']=str_replace(' ','',$row['visibility']);
        if(isset($this->visibilitys[$row['visibility']]))return $this->visibilitys[$row['visibility']];
     }
     return 4;
  }  
/**
 * @获得属性集ID
 */
private function _getAttributeSetId($row){
    if(isset($row['attribute_set'])&&strtolower($row['attribute_set'])=='default')return 4;
    $attributeSetId=false;
    $sql="select attribute_set_id from `eav_attribute_set` where attribute_set_name='".$row['attribute_set']."' limit 1";
    $attributeSetId=$this->db->fetchOne($sql);
	if($attributeSetId)return $attributeSetId;
    die("Attribute Set购物站里未找到".$row['attribute_set']." 请在后台手工建立或者修改csv的Attribute_Set ".$row['attribute_set']."");
}
/**
 * @获得购物系统内的一些默认值，并加在$_defaultProduct里
 */
 private function _setDefaultProduct(){
    $sql="select attribute_code,default_value from eav_attribute where attribute_code='options_container'";
    $rs=$this->db->fetchAll($sql);
    if(!empty($rs)){
        foreach($rs as $attr){
            $this->_defaultProduct[$attr['attribute_code']]=$attr['default_value'];
        }
    }
 }

/**
 * @获得'website_id'=>1,
 */
private function _setWebsiteId($webSiteCode){
    if(!empty($webSiteCode)){
        $websites=array(
            'admin'=>0,
            'base'=>1,
        );
        if(isset($websites[$webSiteCode]))return $websites[$webSiteCode];
        $sql="select website_id from core_website where code ='$webSiteCode' limit 1";
        $rs=$this->db->fetchOne($sql);
        if(!empty($rs))return $rs;
    }
    return 1;
}
/**
 * @获得store_id
 */
private function _setStoreId($storeCode){
    if(!empty($storeCode)){
        $stores=array(
            'admin'=>0,
            'default'=>1,
        );
        if(isset($stores[$storeCode]))return $stores[$storeCode];
        $sql="select store_id from core_store where code ='$storeCode' limit 1";
        $rs=$this->db->fetchOne($sql);
        if(!empty($rs))return $rs;
    }
    return 0;
}

private function isMustAttribute($attr){
    $mustAttributes=array(
    'store',
    'websites',
    'attribute_set',
    'entity_type_id',
    'has_options',
    'store_id',
    'website_id',
    'status',
    'visibility',
    'tax_class_id',
    'qty',
    'is_recurring',
    'is_in_stock',
    'weight',
    'options_container',
    'image',
    'gallimg',
    'gallimg_label',
    'gallimg_color',
    'typecsvname',
    'type',
    'category_ids',
    'sku',
    'is_imported',
    'name',
    'url_key',
    'meta_title',
    'meta_description',
    'small_image',
    'thumbnail',
    'thumbimg',
    'url_path',
    'price',
    'special_price',
    'meta_keyword',
    'description',
    'short_description',
    'special_from_date',
    'news_from_date',
    'news_to_date',
    'attributes',
    'attribute_set_id',
    'special_to_date',
    'product_id',);
    return in_array($attr,$mustAttributes);
}
/**
 * @获得一行数据
 */
private function _getRow($rowHead,$row){
    $product=$this->_defaultProduct;
    if(is_array($row)&&!empty($row)){
        foreach($row as $key=>$val){
            if(isset($rowHead[$key])){
                if(!is_array($rowHead[$key])){
                    if($this->isMustAttribute($rowHead[$key])){
                        $product[$rowHead[$key]]=$this->strCheck($val);
                    }else{
                        if(!empty($val))
                        $product['other_attributes'][]=$rowHead[$key].':'.$this->strCheck($val);
                    }
                }else{
                    $val=trim($val);
                    $_options=explode('||',$val);
                    $product[$rowHead[$key]['optionLabel']]=$rowHead[$key];
                    $product[$rowHead[$key]['optionLabel']]['store_id']=$product['store_id'];
                    if(!empty($_options)){
                     foreach($_options as $k=>$_option){
                        $_optionArr=explode(':',$_option,7);
                        if(count($_optionArr)>=1){
                            $_title = isset($_optionArr[0])?$_optionArr[0]:'';
                            $_priceType=isset($_optionArr[1])?$_optionArr[1]:'fixed';
                            $_price=isset($_optionArr[2])?$_optionArr[2]:'0.00';
                            $_sort=isset($_optionArr[3])?$_optionArr[3]:($k+1);
                            $_sku=isset($_optionArr[4])?$_optionArr[4]:'';
                            $_isDefault=isset($_optionArr[5])?$_optionArr[5]:'';
                            $_note=isset($_optionArr[6])?$_optionArr[6]:'';
                            //$_file_ext=isset($_optionArr[5])?$_optionArr[5]:'';
                            // $_image_x=isset($_optionArr[6])?$_optionArr[6]:'';
                            // $_image_y=isset($_optionArr[7])?$_optionArr[7]:'';
                           $product['has_options']=1;
                           $product[$rowHead[$key]['optionLabel']]['options'][]=array(
                                'title'=>$_title,
                                'price'=>$_price,
                                'priceType'=>$_priceType,
                                'sort'=>$_sort,
                                'sku'=>$_sku,
                                'is_default'=>$_isDefault,
                                'note'=>$_note
                                //  'file_extension'=>$_file_ext,
                                //  'image_size_x'=>$_image_x,
                                //  'image_size_y'=>$_image_y,
                            );
                        }
                    }
                    }else if($rowHead[$key]['optionType']=='field'||$rowHead[$key]['optionType']=='area'){
                        $product['has_options']=1;
                        $product[$rowHead[$key]['optionLabel']]['options'][]=array(
                            'title'=>'20',
                            'price'=>'0.00',
                            'priceType'=>'fixed',
                            'sort'=>0,
                            'sku'=>'',
                            //  'file_extension'=>$_file_ext,
                            //  'image_size_x'=>$_image_x,
                            //  'image_size_y'=>$_image_y,
                        );
                    }
                }   
            }
        }
    }
    if(count($product)<18){
        return array();
    } 
    return $product;
}
private function strCheck($str)
{
	$str=addslashes(trim($str));
	return $str;
}
private function checkCatalogIds($product){
    if(!isset($product['category_ids'])||empty($product['category_ids'])){
        $product['category_ids']='2';
        //die('<p>category_ids 没有填写，请填写完整!</p>');   
    }else{
        $sql="select entity_id from catalog_category_entity where entity_id in (".$product['category_ids'].")";
        $rs=$this->db->fetchCol($sql);
        $_ids=explode(',',$product['category_ids']);
        if(count($_ids)!=count($rs)){
            die('<p>category_ids 购物站后台不存在，请重新填写准确!</p>');
        }
    }
}
/**
 * @获取属性id
 */
private function initAttributeIds(){
    $sql="select attribute_id,attribute_code from eav_attribute where entity_type_id=4";
    $attrs=$this->db->fetchAll($sql);
    $_attrs=array();
    if(!empty($attrs)){
        foreach($attrs as $attr){
            $_attrs[$attr['attribute_code']]=$attr['attribute_id'];
        }
    }
    $this->attrs=$_attrs;
}
/**
 * @写进一条商品数据，返回商品实体ID
 */
private function setProduct($product){
    $sql="SET FOREIGN_KEY_CHECKS=0";
    $this->db->query($sql);
    $sql = "select entity_id from `catalog_product_entity`  where sku ='".$product['sku']."' limit 1";
    $productId=$this->db->fetchOne($sql);
    if($productId===false){
       $sql= "insert into catalog_product_entity(entity_id,entity_type_id,attribute_set_id,type_id,sku,has_options,required_options,created_at,updated_at) values (NULL,4,'".$product['attribute_set_id']."','".$product['type']."','".$product['sku']."','".$product['has_options']."','".$product['has_options']."','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')";
	  
       $this->db->query($sql);
       $productId=$this->db->lastInsertId();					
    }
    $product['product_id']=$productId;
    $psql = "replace into catalog_product_entity_datetime(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['news_from_date']."','".$product['store_id']."',".$productId.",'".$product['news_from_date']."')";
    $this->db->query($psql);
   
    if(isset($product['news_to_date'])){
    $psql = "replace into catalog_product_entity_datetime(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['news_to_date']."','".$product['store_id']."',".$productId.",'".$product['news_to_date']."')";
    $this->db->query($psql);
    }
    
    $psql = "replace into catalog_product_entity_datetime(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['special_from_date']."','".$product['store_id']."',".$productId.",'".$product['special_from_date']."')";
    $this->db->query($psql);
    
    /**
    *设置表catalog_product_entity_decimal 
    *attribute_id 
    *69是weight
    *64是原价
    *65是现价
    */
    
    $psql = "replace into catalog_product_entity_decimal(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['weight']."','".$product['store_id']."',".$productId.",'".$product['weight']."')";
    $this->db->query($psql);
    
     $psql = "replace into catalog_product_entity_decimal(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['price']."','".$product['store_id']."',".$productId.",'".$product['price']."')";
     $this->db->query($psql);
    
    if(isset($product['special_price'])){
    $psql = "replace into catalog_product_entity_decimal(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['special_price']."','".$product['store_id']."',".$productId.",'".$product['special_price']."')";
    $this->db->query($psql);
    
    }
    
    /**
    *设置表catalog_product_entity_int  
    */
    $psql = "replace into catalog_product_entity_int(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['status']."','".$product['store_id']."',".$productId.",'".$product['status']."')";
    $this->db->query($psql);
    
     $psql = "replace into catalog_product_entity_int(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['tax_class_id']."','".$product['store_id']."',".$productId.",'".$product['tax_class_id']."')";
    $this->db->query($psql);
    
    $psql = "replace into catalog_product_entity_int(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['visibility']."','".$product['store_id']."',".$productId.",'".$product['visibility']."')";
    $this->db->query($psql);
    
  //  $psql = "replace into catalog_product_entity_int(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['enable_googlecheckout']."','".$product['store_id']."',".$productId.",1)";
   // $this->db->query($psql);
        
   // $psql = "replace into catalog_product_entity_int(entity_type_id,attribute_id,store_id,entity_id,value) values (4,109,'".$product['store_id']."',".$productId.",1)";
   // $this->db->query($psql);
    
    $psql = "replace into catalog_product_entity_int(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['is_recurring']."','".$product['store_id']."',".$productId.",0)";
    $this->db->query($psql);
    
    //插入颜色属性的值 查询eav_attribute_option_value v,eav_attribute_option 这两张表获取color的值的ID，和COLOR的ID插入到catalog_product_entity_int中来
    //$this->clsAttribute($productId,$product['entity_type_id']);    
    if(isset($product['attributes'])&&!empty($product['attributes'])){
        $this->setAttribute($productId,$product['entity_type_id'],$product['attributes'],$product['store_id']);
        if($product['type']=='configurable'){
            //如果是可配置产中品，color或者style选中  catalog_product_super_attribute
            $this->setConfigurableAttribute($productId,$product['attributes']);
        }      
    }
    
    
    
    /**
    *设置表catalog_product_entity_media_gallery  
    *这张表是图片表
    */
    //拷贝图
    $targetpath=dirname(dirname(__FILE__))."/media/catalog/product/";
    $images=$product['image'].';'.$product['gallimg'];
    $nofile=$this->copeFile($images,$targetpath,$product['typecsvname']);
    if(!empty($nofile))
    {
        echo $product['sku']."缺图:".$nofile."<br/>";
    }
    //写主图
    $imagename=$this->imgname($product['image']);
    if($imagename<>"")
    {
        $psql = "replace into catalog_product_entity_media_gallery(attribute_id,entity_id,value) values ('".$this->attrs['media_gallery']."',".$productId.",'".$imagename."')";
        $this->db->query($psql);
        
    }
    
    //写附属图
    if(!empty($product['gallimg']))
    {
        $gimg=explode(";",$product['gallimg']);
        $gimg_labels = array();
        if(isset($product['gallimg_label']) && !empty($product['gallimg_label'])){
            $gimg_labels=explode(";",$product['gallimg_label']);
        }

        if(isset($product['gallimg_color']) && !empty($product['gallimg_color'])){
            $gimg_colors=explode(";",$product['gallimg_color']);
        }

        for($i=0;$i<count($gimg);$i++)
        {
            $gimg[$i]=$this->imgname($gimg[$i]);
            if(empty($gimg[$i]))continue;
            $psql = "replace into catalog_product_entity_media_gallery(attribute_id,entity_id,value) values ('".$this->attrs['media_gallery']."',".$productId.",'".$gimg[$i]."')";
            $this->db->query($psql);
            
            $position=$i+1;
            $value_id = $this->db->lastInsertId();
            $gimg_label=isset($gimg_labels[$i])?$gimg_labels[$i]:$product['name'];
            if($this->checkColumnExist('catalog_product_entity_media_gallery_value','title')){
                $gimg_color=isset($gimg_colors[$i])?$gimg_colors[$i]:'';
                $_gimg_color = explode(':',$gimg_color);
                $gimg_color = $_gimg_color[0];
                $_gimg_color_show = isset($_gimg_color[1])?1:0;
                $psql = "replace into catalog_product_entity_media_gallery_value(value_id,store_id,label,title,position,`show`) value (".$value_id.",0,'$gimg_label','$gimg_color',".$position.",'".$_gimg_color_show."')";
            }else{
                $psql = "replace into catalog_product_entity_media_gallery_value(value_id,store_id,label,position) value (".$value_id.",0,'$gimg_label',".$position.")";
            }
            $this->db->query($psql);
            
        }
    }
    //删除多余的空格
    $psql="delete from catalog_product_entity_media_gallery where value='\n'";
    $this->db->query($psql);

    /**
    *设置表catalog_product_entity_text  
    *这张表是产品的属性表， 61为长描述  62为短描述 72的keywords
    */
    $psql = "replace into catalog_product_entity_text(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['description']."','".$product['store_id']."',".$productId.",'".$product['description']."')";//description
    $this->db->query($psql);
    
    $psql = "replace into catalog_product_entity_text(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['short_description']."','".$product['store_id']."',".$productId.",'".$product['short_description']."')";//short_description
    $this->db->query($psql);
    
    $psql = "replace into catalog_product_entity_text(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['meta_keyword']."','".$product['store_id']."',".$productId.",'".$product['meta_keyword']."')";
    $this->db->query($psql);
    
    
    /**
    *设置表catalog_product_entity_varchar  
    *这张表是产品的属性表， 60为名称，86为URLKEY,71为产品的title,73为产品的descript
    *74,75,76 为三张默认图片 97为container2   ,87 为urlkey.html  store_id 为 1 和0
    */
    $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['name']."','".$product['store_id']."',".$productId.",'".$product['name']."')";
    $this->db->query($psql);
    
    $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['url_key']."','".$product['store_id']."',".$productId.",'".$product['url_key']."')";
    $this->db->query($psql);

    $psql = "replace into catalog_product_entity_url_key (entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['url_key']."','".$product['store_id']."',".$productId.",'".$product['url_key']."')";
    $this->db->query($psql);

    $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['meta_title']."','".$product['store_id']."',".$productId.",'".$product['meta_title']."')";
    $this->db->query($psql);
    $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['meta_description']."','".$product['store_id']."',".$productId.",'".$product['meta_description']."')";
    $this->db->query($psql);
    $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['image']."','".$product['store_id']."',".$productId.",'".$imagename."')";
    $this->db->query($psql);
    $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['small_image']."','".$product['store_id']."',".$productId.",'".$imagename."')";
    $this->db->query($psql);
    $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['thumbnail']."','".$product['store_id']."',".$productId.",'".$imagename."')";
    $this->db->query($psql);

    if(isset($product['thumbimg']) && !empty($product['thumbimg'])){
        $product['thumbimg']=$this->imgname($product['thumbimg']);
        $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['thumbimg']."','".$product['store_id']."',".$productId.",'".$product['thumbimg']."')";
        $this->db->query($psql);
    }
    
    
    $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['options_container']."','".$product['store_id']."',".$productId.",'container1')";
    $this->db->query($psql);
    $psql = "replace into catalog_product_entity_varchar(entity_type_id,attribute_id,store_id,entity_id,value) values (4,'".$this->attrs['url_path']."','".$product['store_id']."',".$productId.",'".$product['url_path']."')";
    $this->db->query($psql);
    /**
    *设置表catalog_product_flat_1  
    *产品的相关属性表
    */
   // $psql = "replace into catalog_product_flat_1(entity_id,attribute_set_id,type_id,cost,created_at,enable_googlecheckout,gift_message_available,has_options,image_label,is_recurring,links_exist,links_purchased_separately,links_title,name,news_from_date,news_to_date,price,price_type,price_view,recurring_profile,required_options,shipment_type,short_description,sku,sku_type,small_image,small_image_label,special_from_date,special_price,special_to_date,tax_class_id,thumbnail,thumbnail_label,updated_at,url_key,url_path,visibility,weight,weight_type) values (".$productId.",'".$product['attribute_set_id']."','".$product['type']."','','".date("Y-m-d H:i:s",time())."','1','0','".$product['has_options']."','','0','','','','".$product['name']."','".$product['news_from_date']."','".$product['news_to_date']."','".$product['price']."','','','','".$product['has_options']."','','".$product['short_description']."','".$product['sku']."','','".$imagename."','','".$product['special_from_date']."','".$product['special_price']."','".$product['special_to_date']."',0,'".$imagename."','','".date("Y-m-d H:i:s",time())."','".$product['url_key']."','".$product['url_path']."','".$product['visibility']."','".$product['weight']."','')";
    
   // $this->db->query($psql);
     
    /**
    *设置表catalog_product_index_price  
    *产品的价格表
    */
    if($product['special_price']==0)$product['special_price']=$product['price'];
    $psql = "replace into catalog_product_index_price(entity_id,customer_group_id,website_id,tax_class_id,price,final_price,min_price,max_price,tier_price) values (".$productId.",0,'".$product['website_id']."',0,'".$product['price']."','".$product['special_price']."','".$product['special_price']."','".$product['special_price']."','')";
    $this->db->query($psql);
    $psql = "replace into catalog_product_index_price(entity_id,customer_group_id,website_id,tax_class_id,price,final_price,min_price,max_price,tier_price) values (".$productId.",1,'".$product['website_id']."',0,'".$product['price']."','".$product['special_price']."','".$product['special_price']."','".$product['special_price']."','')";
    $this->db->query($psql);
    $psql = "replace into catalog_product_index_price(entity_id,customer_group_id,website_id,tax_class_id,price,final_price,min_price,max_price,tier_price) values (".$productId.",2,'".$product['website_id']."',0,'".$product['price']."','".$product['special_price']."','".$product['special_price']."','".$product['special_price']."','')";
    $this->db->query($psql);
    $psql = "replace into catalog_product_index_price(entity_id,customer_group_id,website_id,tax_class_id,price,final_price,min_price,max_price,tier_price) values (".$productId.",3,'".$product['website_id']."',0,'".$product['price']."','".$product['special_price']."','".$product['special_price']."','".$product['special_price']."','')";
    $this->db->query($psql);
    
    
    	/**
		*	设置表`cataloginventory_stock_item`    
		*	产品的的库存选项
		*/

		$psql = "replace into cataloginventory_stock_item(product_id,stock_id,qty,min_qty,use_config_min_qty,is_qty_decimal,backorders,use_config_backorders,min_sale_qty,use_config_min_sale_qty,max_sale_qty,use_config_max_sale_qty,is_in_stock,low_stock_date,notify_stock_qty,use_config_notify_stock_qty,manage_stock,use_config_manage_stock,use_config_qty_increments,qty_increments,enable_qty_increments) values (".$productId.",'1','".$product['qty']."','0.00',1,0,0,1,'1.0000',1,'0.00',1,'".$product['is_in_stock']."','','',1,0,1,1,'0.0000',0)";
		$this->db->query($psql);
    

		$psql = "replace into cataloginventory_stock_status(product_id,website_id,stock_id,qty,stock_status) values (".$productId.",'".$product['website_id']."',1,'".$product['qty']."','1')";
    	$this->db->query($psql);
    
		$psql = "replace into `cataloginventory_stock_status_idx` (product_id,website_id,stock_id,qty,stock_status) values (".$productId.",'".$product['website_id']."',1,'".$product['qty']."','1')";
		$this->db->query($psql);
    
				
		/**
		*	设置表`core_url_rewrite`    
		*	产品的URL
		*/		
		$psql = "replace into `core_url_rewrite` (`url_rewrite_id` ,`store_id` ,`category_id` ,`product_id` ,`id_path` ,`request_path` ,`target_path` ,`is_system` ,`options` ,`description` )VALUES (NULL ,'".$product['store_id']."', NULL , '".$productId."', 'product/".$productId."', '".strtolower($product['url_path'])."', 'catalog/product/view/id/".$productId."', '1', '', NULL );";
		$this->db->query($psql);
    
						
						
		/**
		*	设置表`catalog_category_product`    
		*	产品的的库存选项
		*/
		
		$catearr=explode(",",$product['category_ids']);
		for($c=0;$c<count($catearr);$c++)
		{ 	if(empty($catearr[$c]))continue;
			$psql = "replace into catalog_category_product (category_id,product_id,position) VALUES ('".$catearr[$c]."','".$productId."',1);";
			$this->db->query($psql);
            $psql = "replace into catalog_category_product_index(category_id,product_id,position,is_parent,store_id,visibility) VALUES ('".$catearr[$c]."','".$productId."',1,1,'".$product['store_id']."','".$product['visibility']."')";
			$this->db->query($psql);
            
            if($catearr[$c]<>2)
			{
				//这里是添加core_url_rewrite索引
				
				$pathCate="";
				$csql="select path from `catalog_category_entity` where entity_id=".$catearr[$c];
                $rs=$this->db->fetchAll($csql);
                if(!empty($rs)){
                    foreach($rs as $cata){
                    $cags=explode("/",$cata['path']);
					if(count($cags)>1)
					{
						for($i=2;$i<count($cags);$i++)
						{
							$csql1="select `value` from `catalog_category_entity_varchar` where entity_id =".$cags[$i]." and attribute_id=(select attribute_id from eav_attribute where attribute_code='url_path' and entity_type_id=3 limit 1)";
							$rs1 = $this->db->fetchAll($csql1);
                            if(!empty($rs1)){
                                foreach($rs1 as $cat){
                                    $pathCate.=$cat['value']."/";
                                }
                            }
						}
					}
                    }
                }
				$pathCate=$pathCate.strtolower($product['url_path']);
				$ssql="select request_path from `core_url_rewrite` where request_path='".$pathCate."' and product_id<>'".$productId."'";
				$rs = $this->db->fetchOne($ssql);
				if(empty($rs)){
				    if(!isset($product['store_id']))$product['store_id']=1;
				   $psql = "replace into `core_url_rewrite` (`url_rewrite_id` ,`store_id` ,`category_id` ,`product_id` ,`id_path` ,`request_path` ,`target_path` ,`is_system` ,`options` ,`description` )VALUES (NULL , '".$product['store_id']."', '".$catearr[$c]."' , '".$productId."', 'product/".$productId."/".$catearr[$c]."', '".$pathCate."', 'catalog/product/view/id/".$productId."/category/".$catearr[$c]."', '1', '', NULL );";
                    $this->db->query($psql);
				}
			}
		}				
		/**
		*	设置表catalog_product_website   
		*/
        if(!isset($product['website_id']))$product['website_id']=1;
		$psql = "replace into catalog_product_website(product_id,website_id) values (".$productId.",'".$product['website_id']."')";
		$this->db->query($psql);
        if(!empty($this->_productOptions)){
            foreach($this->_productOptions as $_productOption){
                if(isset($product[$_productOption])&&!empty($product[$_productOption])){
                    $this->setCustomOptions($productId,$product[$_productOption],$product['has_options']);
                }
            }    
        }
        if(!empty($product['other_attributes'])){
            $_other_attributes=implode(';',$product['other_attributes']);
            $this->setAttribute($productId,$product['entity_type_id'],$_other_attributes,$product['store_id']);
        }
    $sql="SET FOREIGN_KEY_CHECKS=1";
    $this->db->query($sql);
    return $productId;
}




/**
 * @获得图片名
 */
private function imgname($file)
{
    $file=ltrim($file,'/');
	if(!empty($file))
	{
		$strlen=strlen($file);
		if($strlen>5){
		    if(stripos($file,'/')!==false){
                $filenew = '/'.$file;
            }else{
                $file='/'.$file;
                $first_character = substr($file,1,1);
                $second_character = substr($file,2,1);
                $filenew="/".$first_character."/".$second_character.$file;
            }
		}else{
			$first_character = substr($file,0,1);
			$filenew="/".$first_character."/".$file;
		}
		return $filenew;
	}
	return "";
}
/**
 * @拷贝图片
 */
private function copeFile($path,$targetpath,$typecsvname){
	$nofile="";//没有上传成功的产品图片
	$fileArray=explode(";",$path);
	foreach($fileArray as $k=>$file){
	$pathtmp = "";
    if(empty($file))continue;
	$strlen=strlen($file);
		if($strlen>5){
			$first_character = substr($file,1,1);
			$dir1 = $targetpath.$first_character;
			if(!is_dir($dir1)){
				@mkdir($dir1, 0777);
			}
			$second_character = substr($file,2,1);
			$dir2 = $dir1."/".$second_character;
			if(!is_dir($dir2)){
				@mkdir($dir2, 0777);
			}
			$pathtmp .= $first_character."/".$second_character."/";
			$dir = $targetpath.$pathtmp;
			if(is_dir($dir)){
				if (@!copy("/data0/web/image/$typecsvname".$file,$dir.$file)) {
					$nofile.=$file.";";
				}
			}else{
				@mkdir($dir, 0777);
				if (@!copy("/data0/web/image/$typecsvname".$file,$dir.$file)) {
					$nofile.=$file.";";
				}
			}	
		}else{
			$first_character = substr($file,0,1);
			$dir1 = $targetpath.$first_character;
			if(!is_dir($dir1)){
				@mkdir($dir1, 0777);
			}
			$pathtmp .= $first_character."/";
			$dir = $targetpath.$pathtmp;
			if(is_dir($dir)){
				if (@!copy("/data0/web/image/$typecsvname".$file,$dir.$file)) {
					$nofile.=$file.";";
				}
			}else{
				@mkdir($dir, 0777);
				if (@!copy("/data0/web/image/$typecsvname".$file,$dir.$file)) {
					$nofile.=$file.";";
				}
			}
		}
	}
	$nofile=substr($nofile,0,-1);
	return $nofile;
}

/**
 * @清除系统属性
 */
private function clsAttribute($productId,$entityTypeId){
    $attributeIdsSimple=$this->_getSubAttributeIdsSimple();
    if(!empty($attributeIdsSimple)){
        foreach($attributeIdsSimple as $attr=>$attribute_id){
            if(isset($this->_subAttributeBackendTypes[$attribute_id])){
                $_type=$this->_subAttributeBackendTypes[$attribute_id];
                $sql="delete from catalog_product_entity_$_type where attribute_id='{$attribute_id}' and entity_id='$productId' and entity_type_id='$entityTypeId' limit 1";
                $this->db->query($sql);
            }
        }
    }
}
/**
 * @写系统属性
 * @attrNames是逗号分隔的字符串
 */
private function setAttribute($productId,$entityTypeId,$attrNames,$storeId=0){
    if(!empty($attrNames)){
        $_attrNames=$this->_getAttributesAndValues($attrNames);
        if(!empty($_attrNames)){
            $_storeId=1;
            foreach($_attrNames as $attribute_id=>$value){
                if(isset($this->_subAttributeBackendTypes[$attribute_id])){
                   $_type=$this->_subAttributeBackendTypes[$attribute_id];
                   if(in_array($_type,array('varchar','int','text','decimal','datetime'))){
                        $value=mysql_escape_string($value);
                        $psql = "replace into catalog_product_entity_$_type(entity_type_id,attribute_id,store_id,entity_id,value) values ('$entityTypeId','".$attribute_id."','$storeId',".$productId.",'".$value."')";
                        $this->db->query($psql); 
                   }
                }
            }
        }
        return true;
    }
}

private function setConfigurableAttribute($db,$productId,$attrName){
    $sql="SELECT attribute_id FROM `eav_attribute` WHERE attribute_code='$attrName' limit 1";
    $attributeId = $this->db->fetchOne($sql);
    if(!empty($attributeId)){
        $sql = "replace into catalog_product_super_attribute(product_id,attribute_id,position) values ('".$productId."','$attributeId',0)";
        $this->db->query($sql);
    }else{
        echo "<br>购物站缺少$attrName 属性，请在后台手工添加\n";
    }
}

protected function _getAttributesAndValues($attrNames){
    $_attrs=array();
    //$attrNames=strtolower($attrNames);
    $_attrNames=$this->_getSubAttributes($attrNames);
    $_titles=$this->_getSubAttributeIds();
    $_values=$this->_getSubAttributeValues();  
    if(!empty($_attrNames)){
        foreach($_attrNames as $title=>$_attrNameArr){
            $title=trim($title);
            $uctitle=$title;
            $title=strtolower($title);
            if(isset($_titles[$title])&&isset($_values[$_titles[$title]])){
                $_attrs[$_titles[$title]]=$this->_getAttributesValuesByValue($_attrNameArr,$_values[$_titles[$title]]);   
            }else{
                $rs=$this->_getAttributeOtherType($title,$uctitle);
                if(!empty($rs)){
                    $_attrs[$rs['attribute_id']]=$_attrNameArr[0];
                    $this->_subAttributeBackendTypes[$rs['attribute_id']]=$rs['backend_type'];
                }
            }
        }
    }
    return $_attrs;
}

private function _getAttributeOtherType($title,$uctitle){
    $sql="SELECT attribute_id,backend_type FROM `eav_attribute` WHERE (attribute_code='$title' or frontend_label='$uctitle') and backend_type!='static' and entity_type_id=4 limit 1";
    return $this->db->fetchRow($sql);
}

private function _getAttributesValuesByValue($_attrNameArr,$_values){
    $_value=array();
    if(is_array($_attrNameArr)){
        foreach($_attrNameArr as $_attr){
            $_attr=trim($_attr);
            $_attr=strtolower($_attr);
            if(isset($_values[$_attr]))$_value[]=$_values[$_attr];
        }  
    }
    return implode(',',$_value);    
}

private function _getSubAttributes($attrNames){
    $_attrNames=array();
    $_attrs=explode(';',$attrNames);
    foreach($_attrs as $_attr){
        $_subAttrs=explode(':',$_attr);
        if(count($_subAttrs)==2){
            $_attrNames[$_subAttrs[0]]=explode(',',$_subAttrs[1]);
        }
    }
    return $_attrNames;
}

private function _getSubAttributeIds(){
    if(empty($this->_subAttributeIds)){
    $_subAttributeBackendTypes=$_subAttributeIds=$_subAttributeIdsSimple=array();
    $sql="select attribute_id,attribute_code,`frontend_label`,backend_type  from eav_attribute where entity_type_id=4";
    $rs=$this->db->fetchAll($sql);
    if(!empty($rs)){
        foreach($rs as $row){
            $row['frontend_label']=strtolower($row['frontend_label']);
            $_subAttributeIds[$row['frontend_label']]=$row['attribute_id'];
            $_subAttributeIds[$row['attribute_code']]=$row['attribute_id'];
            if(!$this->isMustAttribute($row['attribute_code'])){
                $_subAttributeIdsSimple[$row['attribute_code']]=$row['attribute_id'];
            }
            $_subAttributeBackendTypes[$row['attribute_id']]=$row['backend_type'];
        }
    }
    $this->_subAttributeIdsSimple=$_subAttributeIdsSimple;
    $this->_subAttributeBackendTypes=$_subAttributeBackendTypes;
    $this->_subAttributeIds=$_subAttributeIds;
    }
    return $this->_subAttributeIds;
}

private function _getSubAttributeIdsSimple(){
    if(empty($this->_subAttributeIdsSimple)){
        $this->_getSubAttributeIds();
    }
    return $this->_subAttributeIdsSimple;
}

private function _getSubAttributeValues(){
    if(empty($this->_subAttributeValues)){
    $_subAttributeValues=array();
    $sql="select o.attribute_id,v.option_id,v.`value` from eav_attribute_option o left join eav_attribute_option_value v on o.option_id=v.option_id ORDER BY o.attribute_id";
    $rs=$this->db->fetchAll($sql);
    if(!empty($rs)){
        foreach($rs as $row){
            if(!isset($_subAttributeValues[$row['attribute_id']][$row['value']])){
            $row['value']=strtolower($row['value']);
            $_row_value=explode('_',$row['value']);
            $row['value']=$_row_value[0];
            $_subAttributeValues[$row['attribute_id']][$row['value']]=$row['option_id'];
            }
        }
    }
    $this->_subAttributeValues=$_subAttributeValues;
    }
   return $this->_subAttributeValues;
}

/**
 * @写商品自定义属性
 */
private function setCustomOptions($productId,$options,$has_options){
  /*
    $options=array(
        'optionLabel'=>'Size',
        'optionType'=>'drop_down',//'field'
        'store_id'=>'0',//'store_id'
        'options'=>array(
            array(
                'title'=>'S',
                'price'=>'0.00',
                'priceType'=>'fixed', //<option value="percent">Percent</option>
                'sku'=>'1',
                'sort'=>'1',
                'is_default'=>'1',
                'note'=>'',
            ),
            array(
                'title'=>'M',
                'price'=>'0.00',
                'priceType'=>'fixed', 
                'sku'=>'1',
                'sort'=>'1',
                'is_default'=>'1',
                'note'=>'',
            ),
        ),
    );
    
    $options=array(
        'optionLabel'=>'Size',
        'optionType'=>'field',//'text'
        'store_id'=>'0',//'store_id'
        'options'=>array(
            array(
                'title'=>'12',//==maxchar
                'price'=>'0.00',
                'priceType'=>'fixed', //<option value="percent">Percent</option>
                'sku'=>'1',
                'sort'=>'1',
            ),
        ),
    );
    */
    //<option value="field">Field</option>
    //S:fixed:0.00:1:1||M:fixed:0.00:2:2||L:fixed:0.00:3:3||XL:fixed:0.00:4:4||XXL:fixed:0.00:5:5

      $sql = "SELECT a.option_id from catalog_product_option a,catalog_product_option_title b where a.option_id=b.option_id and b.title='".$options['optionLabel']."' and a.product_id='$productId' limit 1"; //option_id
      $rs = $this->db->fetchRow($sql);
      if(!empty($rs)){ //如果不为空，删除，在下面重写
        $sql="delete from catalog_product_option where option_id='".$rs['option_id']."'";
        $this->db->query($sql);
        $sql="delete from catalog_product_option_title where option_id='".$rs['option_id']."'";
        $this->db->query($sql);
        $sql="delete from catalog_product_option_price where option_id='".$rs['option_id']."'";
        $this->db->query($sql);
        $sql="select option_type_id from catalog_product_option_type_value where option_id='".$rs['option_id']."'";
        $rs01=$this->db->fetchAll($sql);
        if(!empty($rs01)){
            foreach($rs01 as $optyid){
                $sql="delete from catalog_product_option_type_title where option_type_id='".$optyid['option_type_id']."'";
                $this->db->query($sql);
                $sql="delete from catalog_product_option_type_price where option_type_id='".$optyid['option_type_id']."'";
                $this->db->query($sql);
                $sql="delete from catalog_product_option_type_value where option_type_id='".$optyid['option_type_id']."'";
                $this->db->query($sql);
            }
        }
      }
      if($options['optionType']=='delete'||!$has_options)return true;
       // if(empty($rs)){  //如果已写，则不再写
            $sql = "insert into catalog_product_option(product_id,type,is_require,sku,max_characters,file_extension,image_size_x,image_size_y,sort_order) values ('$productId','".$options['optionType']."','".$options['isRequired']."','','','','0','0','".$options['sort']."')";
            $this->db->query($sql);
            $option_id=$this->db->lastInsertId();
            if($option_id){
				$sql = "insert into catalog_product_option_title(option_id,store_id,title) values ('$option_id','".$options['store_id']."','".$options['optionLabel']."')";
				$this->db->query($sql);
                if(!empty($options['options'])){
               	foreach($options['options'] as $i=>$option) {
               	    if(in_array($options['optionType'],array('field','area'))){
               	        $sql = "update catalog_product_option set max_characters='".$option['title']."' where option_id='$option_id' limit 1"; //新建option_type_id
                        $this->db->query($sql);
                        $sql = "insert into catalog_product_option_price(option_id,store_id,price,price_type) values ('$option_id','".$options['store_id']."','".$option['price']."','".$option['priceType']."')"; //新建option_type_id
                        $this->db->query($sql);
                        break; //只需要写一条就行
               	    }else if(in_array($options['optionType'],array('drop_down','radio','checkbox','multiple'))){
               	    $sql = "insert into catalog_product_option_type_value (option_id,sku,sort_order) VALUES ('$option_id','".$option['sku']."','".$option['sort']."')"; //新建option_type_id
                    $this->db->query($sql);
			        $option_type_id=$this->db->lastInsertId();
        			if($option_type_id){
                        if($this->checkColumnExist('catalog_product_option_type_title','note')) {
                            $sql = "insert into catalog_product_option_type_title (option_type_id,store_id,title,note,is_default) values ('$option_type_id'," . $options['store_id'] . ",'" . $option['title'] . "','" . $option['note'] . "','" . $option['is_default'] . "')"; //根据option_type_id添加title和price
                        }else{
                            $sql = "insert into catalog_product_option_type_title (option_type_id,store_id,title) values ('$option_type_id'," . $options['store_id'] . ",'" . $option['title'] . "')"; //根据option_type_id添加title和price
                        }
                        $this->db->query($sql);
            			$sql = "insert into catalog_product_option_type_price (option_type_id,store_id,price,price_type) values ('$option_type_id',".$options['store_id'].",'".$option['price']."','".$option['priceType']."')";
            			$this->db->query($sql);
        			}
        			}else if(in_array($options['optionType'],array('date','date_time','time'))){
        			     $sql = "insert into catalog_product_option_type_value (option_id,sku,sort_order) VALUES ('$option_id','".$option['sku']."','".$option['sort']."')"; //新建option_type_id
                         $this->db->query($sql);
			             $option_type_id=$this->db->lastInsertId();
                         if($option_type_id){
        			    $sql = "insert into catalog_product_option_type_price (option_type_id,store_id,price,price_type) values ('$option_type_id',".$options['store_id'].",'".$option['price']."','".$option['priceType']."')";
            			$this->db->query($sql);
                        }
        			}
                    /*文件列暂时不开启
                    else if(in_array($options['optionType'],array('file'))){
        			     $sql = "update catalog_product_option set file_extension='".$option['file_extension']."',image_size_x='".$option['image_size_x']."',image_size_y='".$option['image_size_y']."', where option_id='$option_id' limit 1"; //新建option_type_id
                            $this->db->query($sql);
        			     $sql = "insert into catalog_product_option_type_value (option_id,sku,sort_order) VALUES ('$option_id','".$option['sku']."','".$option['sort']."')"; //新建option_type_id
                         $this->db->query($sql);
			             $option_type_id=$this->db->lastInsertId();
                         if($option_type_id){
        			    $sql = "insert into catalog_product_option_type_price (option_type_id,store_id,price,price_type) values ('$option_type_id',".$options['store_id'].",'".$option['price']."','".$option['priceType']."')";
            			$this->db->query($sql);
                        }
        			}
                    */
        		} 
            }
           
        }
}
/**
 * 重建产品之间的关系
 */
private function setProductRelation(){
	$delsql="TRUNCATE `catalog_product_super_link` ";
    $this->db->query($delsql);
	$delsql="TRUNCATE `catalog_product_relation` ";
	$this->db->query($delsql);
    $sql = "select entity_id,sku from catalog_product_entity where type_id='configurable'";
	$rs = $this->db->fetchAll($sql);
    if(!empty($rs)){
        foreach($rs as $product){
            $sql3 = "select entity_id,sku from catalog_product_entity where sku like '".$product['sku']."-%'";
            $rs1 = $this->db->fetchAll($sql3);
            if(empty($rs1)){
                foreach($rs1 as $productB){
                if($productB['entity_id']!=$product['entity_id']){
                    $sql ="replace into `catalog_product_super_link` (product_id,parent_id) values('".$productB['entity_id']."','".$product['entity_id']."')";
					$this->db->query($sql);
					$sql ="replace into `catalog_product_relation` (parent_id,child_id) values('".$product['entity_id']."','".$productB['entity_id']."')";
					$this->db->query($sql);
                }
               }
            }
            	
        }
    }
}
}
/**
 * ====================================================
 * @清除magento分类和商品数据类
 * @copyright by ado
 * @114458573@qq.com
 * ====================================================
 */
class clsCataOrProduct{
  private $db;
  private function setDb(){
    if(is_object($this->db))return $this->db;
    $magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
	require_once$magento_bootstrap;
	Mage::app(); //加载……
	$this->db=Mage::getSingleton('core/resource')->getConnection('core_read');
    return $this->db;
  }
  public function __construct(){
     $this->setDb();
  }
  private $clsProductSqls=array(
	"SET FOREIGN_KEY_CHECKS=0",
    "truncate table cataloginventory_stock_item",
    "truncate table cataloginventory_stock_status",
    "truncate table cataloginventory_stock_status_idx",
    "truncate table catalog_category_product",
    "truncate table catalog_category_product_index",
    "truncate table catalog_product_entity",
    "truncate table catalog_product_entity_datetime",
    "truncate table catalog_product_entity_decimal",
    "truncate table catalog_product_entity_gallery",
    "truncate table catalog_product_entity_int",
    "truncate table catalog_product_entity_media_gallery",
    "truncate table catalog_product_entity_media_gallery_value",
    "truncate table catalog_product_entity_text",
    "truncate table catalog_product_entity_tier_price",
    "truncate table catalog_product_entity_varchar",
    "truncate table catalog_product_option",
    "truncate table catalog_product_option_price",
    "truncate table catalog_product_option_title",
    "truncate table catalog_product_option_type_price",
    "truncate table catalog_product_option_type_title",
    "truncate table catalog_product_option_type_value",
    "truncate table catalog_product_website",
     "delete from core_url_rewrite where product_id>0",
    "SET FOREIGN_KEY_CHECKS=1",
  );
  private $clsCatalogSqls=array(
    "delete FROM `catalog_category_entity` WHERE `entity_id`>2",
    "delete FROM `catalog_category_entity` WHERE `entity_id`>2",
    "delete FROM `catalog_category_entity_datetime` WHERE `entity_id`>2",
    "delete FROM `catalog_category_entity_decimal` WHERE `entity_id`>2",
    "delete FROM `catalog_category_entity_int` WHERE `entity_id`>2",
    "delete FROM `catalog_category_entity_text` WHERE `entity_id`>2",
    "delete FROM `catalog_category_entity_varchar` WHERE `entity_id`>2",
    "delete FROM `catalog_category_entity` WHERE `entity_id`>2",
  );
  
  private $clsOrderSqls=array(
    "SET FOREIGN_KEY_CHECKS=0",
    "TRUNCATE table `sales_flat_creditmemo`",
    "TRUNCATE table `sales_flat_creditmemo_comment`",
    "TRUNCATE table `sales_flat_creditmemo_grid`",
    "TRUNCATE table `sales_flat_creditmemo_item`",
    "TRUNCATE table `sales_flat_invoice`",
    "TRUNCATE table `sales_flat_invoice_comment`",
    "TRUNCATE table `sales_flat_invoice_grid`",
    "TRUNCATE table `sales_flat_invoice_item`",
    "TRUNCATE table `sales_flat_order`",
    "TRUNCATE table `sales_flat_order_address`",
    "TRUNCATE table `sales_flat_order_grid`",
    "TRUNCATE table `sales_flat_order_item`",
    "TRUNCATE table `sales_flat_order_payment`",
    "TRUNCATE table `sales_flat_order_status_history`",
    "TRUNCATE table `sales_flat_quote`",
    "TRUNCATE table `sales_flat_quote_address`",
    "TRUNCATE table `sales_flat_quote_address_item`",
    "TRUNCATE table `sales_flat_quote_item`",
    "TRUNCATE table `sales_flat_quote_item_option`",
    "TRUNCATE table `sales_flat_quote_payment`",
    "TRUNCATE table `sales_flat_quote_shipping_rate`",
    "TRUNCATE table `sales_flat_shipment`",
    "TRUNCATE table `sales_flat_shipment_comment`",
    "TRUNCATE table `sales_flat_shipment_grid`",
    "TRUNCATE table `sales_flat_shipment_item`",
    "TRUNCATE table `sales_flat_shipment_track`",
    "TRUNCATE table `sales_invoiced_aggregated`",
    "TRUNCATE table `sales_invoiced_aggregated_order`",
    "TRUNCATE table `sales_order_aggregated_created`",
      "TRUNCATE table `sales_ddc_order`",
      "TRUNCATE table `sales_ddc_quote`",
    "SET FOREIGN_KEY_CHECKS=1",
  );
  
  private $clsCustomerSqls=array(
    "SET FOREIGN_KEY_CHECKS=0",
    "TRUNCATE TABLE `customer_address_entity`",
    "TRUNCATE TABLE `customer_address_entity_decimal`",
    "TRUNCATE TABLE `customer_address_entity_int`",
    "TRUNCATE TABLE `customer_address_entity_text`",
    "TRUNCATE TABLE `customer_address_entity_varchar`",
    "TRUNCATE TABLE `customer_entity`",
    "TRUNCATE TABLE `customer_entity_datetime`",
    "TRUNCATE TABLE `customer_entity_decimal`",
    "TRUNCATE TABLE `customer_entity_int`",
    "TRUNCATE TABLE `customer_entity_text`",
    "TRUNCATE TABLE `customer_entity_varchar`",
    "SET FOREIGN_KEY_CHECKS=1",
  );
  
   private $clsReviewSqls=array(
        "SET FOREIGN_KEY_CHECKS=0",
        "TRUNCATE TABLE `review`",
        "TRUNCATE TABLE `review_detail`",
        "TRUNCATE TABLE `review_entity_summary`",
        "TRUNCATE TABLE `rating_option_vote`",
        "TRUNCATE TABLE `rating_option_vote_aggregated`",
        "SET FOREIGN_KEY_CHECKS=1", 
   );
  
  private $clsLogSqls=array( 
    "SET FOREIGN_KEY_CHECKS=0",
    "truncate table dataflow_batch_export",
    "truncate table dataflow_batch_import",
    "TRUNCATE TABLE `log_quote`",
    "TRUNCATE TABLE `log_url_info`",
    "TRUNCATE TABLE `log_url`",
    "TRUNCATE TABLE `log_visitor_info`",
    "TRUNCATE TABLE `log_visitor`",
    "TRUNCATE TABLE `log_customer`",
    "TRUNCATE TABLE `log_summary`",
    "TRUNCATE TABLE report_event",
	"TRUNCATE TABLE report_viewed_product_index",
	"TRUNCATE TABLE report_compared_product_index",
	"TRUNCATE TABLE index_process_event",
	"TRUNCATE TABLE index_event",
	"TRUNCATE TABLE index_process_event",
	"TRUNCATE TABLE index_event",
    "TRUNCATE TABLE adminnotification_inbox",
    "SET FOREIGN_KEY_CHECKS=1", 
  );
   // email logs
    private $clsEmailLogSqls=array(
        "SET FOREIGN_KEY_CHECKS=0",
        "TRUNCATE TABLE `m_emailreport_aggregated`",
        "TRUNCATE TABLE `m_emailreport_click`",
        "TRUNCATE TABLE `m_emailreport_open`",
        "TRUNCATE TABLE `m_emailreport_order`",
        "TRUNCATE TABLE `m_emailreport_review`",
        "TRUNCATE TABLE `m_emailsmtp_mail`",
        "TRUNCATE TABLE `m_email_event`",
        "TRUNCATE TABLE `m_email_event_trigger`",
        "TRUNCATE TABLE `m_email_queue`",
        "TRUNCATE TABLE `m_email_unsubscription`",
        "TRUNCATE TABLE `m_mstcore_logger",
        "SET FOREIGN_KEY_CHECKS=1",
    );
  
  public function initOrderId($id=''){
     if(empty($id)){
        $feed='ABCDEFGHIGKLMNOPQRSTUVWXYZ';
        $numfeed='0123456789';
        $max = strlen($feed)-1;
        $prefix=$feed[rand(0,$max)];
        $lastid=$numfeed[rand(0,9)].$numfeed[rand(0,9)];
        $lastid.='00000000';
     }else{
        $prefix=substr($id,0,1);
        $lastid=substr($id,1);
     }
     $sql="update eav_entity_store
inner join eav_entity_type
on eav_entity_type.entity_type_id = eav_entity_store.entity_type_id
set eav_entity_store.increment_last_id='$lastid', eav_entity_store.increment_prefix = '$prefix'
where eav_entity_type.entity_type_code='order'";
    $this->db->query($sql);
  }
  /**
   * 清除商品
   */
  public function runClsProduct(){
        foreach($this->clsProductSqls as $sql){
            $this->db->query($sql);    
        }
        echo '执行清除产品完毕<br>';
    }/**
     * 清除分类
     */
    public function runClsCatalog(){
        foreach($this->clsCatalogSqls as $sql){
            $this->db->query($sql);    
        }
        echo '执行清除分类完毕<br>';
    }
    /**
     * 清除订单
     */
    public function runClsOrder(){
        foreach($this->clsOrderSqls as $sql){
            $this->db->query($sql);    
        }
        echo '执行清除订单完毕<br>';
    }
    /**
     * 清除客户
     */
    public function runClsCustomer(){
        foreach($this->clsCustomerSqls as $sql){
            $this->db->query($sql);    
        }
        echo '执行清除客户资料完毕<br>';
    }
    /**
     * 清除商品留言
     */
    public function runClsReview(){
        foreach($this->clsReviewSqls as $sql){
            $this->db->query($sql);    
        }
        echo '执行清除商品留言完毕<br>';
    }
    
    /**
     * 清除日志
     */
    public function runClsLog(){
        foreach($this->clsLogSqls as $sql){
            $this->db->query($sql);    
        }
        echo '执行清除日志完毕<br>';
    }
    /**
     * 清除邮件日志
     */
    public function runEmailClsLog(){
        foreach($this->clsEmailLogSqls as $sql){
            $this->db->query($sql);
        }
        echo '执行清除邮件日志完毕<br>';
    }
    /**
     * @获得最大的分类id号
     */
     public function runGetCatalogMaxId(){
        $sql="select max(entity_id) from catalog_category_entity limit 1";
        echo "当前分类的最大ID是:".$this->db->fetchOne($sql);
     }
     
     /**
     * @获得帮助提示
     */
    public function runGetHelp(){
        $site=Mage::getBaseUrl();
        $site=str_replace('://','||||',$site);
        $_site=explode('/',$site);
        $site=$_site[0];
        $site=str_replace('||||','://',$site);
        $str="<style>body{font-size:12px;} p{padding-bottom: 2px;}</style>";
        $str.="<p>清除分类：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clsc\">$site/product/manage.php?do=clsc</a> </p>";
		$str.="<p>清除商品订单评论客户留言日志：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clspro\">$site/product/manage.php?do=clspro</a> </p>";
        $str.="<p>清除商品：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clsp\">$site/product/manage.php?do=clsp</a></p>";
        $str.="<p>清除订单：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clso\">$site/product/manage.php?do=clso</a></p>";
        $str.="<p>清除客户资料：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clscu\">$site/product/manage.php?do=clscu</a></p>";
        $str.="<p>清除商品留言：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clsr\">$site/product/manage.php?do=clsr</a></p>";
        $str.="<p>清除日志：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clslog\">$site/product/manage.php?do=clslog</a></p>";
        $str.="<p>清除邮件日志：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clsemaillog\">$site/product/manage.php?do=clslog</a></p>";
        $str.="<p>初始化：清除订单，装起始订单号，清除客户、清除日志、邮件日志<br><a target=\"_blank\" href=\"$site/product/manage.php?do=init\">$site/product/manage.php?do=init</a></p>";
        $str.="<p>获得最大的分类ID：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=maxid\">$site/product/manage.php?do=maxid</a></p>";
        $str.="<p>导出商品：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=expro&c=2&store=\">$site/product/manage.php?do=expro&c=2&store=en</a>(c=分类id或url，默认全部)</p>";
        $str.="<p>导入分类：<br>$site/product/manage.php?cfile=xxxx.csv</p>";
        $str.="<p>导入商品：<br>$site/product/manage.php?pfile=xxxx.csv</p>";
        $str.="<p>商品upsell：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=upsell\">$site/product/manage.php?do=upsell</a></p>";
        $str.="<p>导评论(已清理带url,错误标题的,不删则重复利用)：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=impview&code=fr&notdel=1&sku=xxx\">$site/product/manage.php?do=impview&code=fr&amp;notdel=1&sku=xxx</a><br>";
        $str.="<p>导评论：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=impview&code=fr\">$site/product/manage.php?do=impview&code=fr</a><br>复制英文评论到店(vote=1只复制评分)：<a target=\"_blank\" href=\"$site/product/manage.php?do=copyview&code=fr\">$site/product/manage.php?do=copyview&code=fr&vote=1</a></p>";
        $str.="<p>清除评论：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clsreview\">$site/product/manage.php?do=clsreview</a></p>";
        $str.="<p>分类产品对拷：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=copycatalogproduct&ctc=\">$site/product/manage.php?do=copycatalogproduct&ctc=sTd</a></p>";
        $str.="<p>清除数据库缓存表（url|flat）：<br><a target=\"_blank\" href=\"$site/product/manage.php?do=clsDbCache&ctc=all\">$site/product/manage.php?do=clsDbCache&ctc=all(cat|pro|url|)</a></p>";
        $str.="<p>帮助 for magentoEE1.4：<br>$site/product/manage.php?do=help</p>";
        echo $str;
    }
    
}
/**
 * ====================================================
 * @magento分类增改类
 * @copyright ado 
 * @114458573@qq.com
 * ====================================================
 */
class appendCatalog{
   private $_file=''; 
   private $_cataHead=array(); //记录分类csv头
   private $_defaultCatalog=array(
      'is_active'=>1,
      'in_menu'=>1,
      'parent_id'=>'',
      'catalog_id'=>'',
      'name'=>'',
      'meta_title'=>'',
      'description'=>'',
      'meta_description'=>'',
      'meta_keywords'=>'',
      'override'=>0, //当有id相同时是否覆盖，1是0否
   ); //记录分类csv头
   
   private $_catalog_colums=array(
    'name'=>'',
    'is_active'=>'',
    'url_key'=>'',
    'description'=>'',
    'image'=>'',
    'meta_title'=>'',
    'meta_keywords'=>'',
    'meta_description'=>'',
    'display_mode'=>'',
    'landing_page'=>'',
    'is_anchor'=>'',
    'path'=>'',
    'position'=>'',
    'all_children'=>'',
    'path_in_store'=>'',
    'children'=>'',
    'url_path'=>'',
    'custom_design'=>'',
    //'custom_design_from'=>'',
    //'custom_design_to'=>'',
    'page_layout'=>'',
    'custom_layout_update'=>'',
    'level'=>'',
    'children_count'=>'',
    'available_sort_by'=>'',
    'default_sort_by'=>'',
    'include_in_menu'=>'',
    'custom_use_parent_settings'=>'',
    'custom_apply_to_products'=>'',
    'filter_price_range'=>'',
    'thumbnail'=>'',
   );
   
   /**
    * parent_id,catalog_id,name,meta_title,description,meta_description,meta_keywords,is_active,in_menu,override
    */
   private $db;
   
   public function __construct($fileName=''){
    if(!empty($fileName)&&is_file($fileName))$this->_file=$fileName;
  }
  public function setCsvfile($fileName){
     if(!empty($fileName)&&is_file($fileName))$this->_file=$fileName;
  }
  
  public function getCsvfile(){
    return $this->_file;
  }
  /**
   * 主入口
   */
  public function run(){
    if(!empty($this->_file)){
     $this->setDb();
     $this->_resetCatalogColums();
     $this->_setCsvData($this->_file);
     }
  }
  /**
   * @step1
   */
  private function setDb(){
    if(is_object($this->db))return $this->db;
    $magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
	require_once$magento_bootstrap;
	Mage::app(); //加载……
	$this->db=Mage::getSingleton('core/resource')->getConnection('core_read');
    return $this->db;
  }
  
  /**
   * @step2
   * @获得当前站系统对应的列ID,取一次
   */
  private function _resetCatalogColums(){
        $_colums=array_keys($this->_catalog_colums);
        $_columstr="'".implode("','",$_colums)."'";
        $sql="select attribute_id,attribute_code,backend_type from eav_attribute where entity_type_id=3 and attribute_code in ($_columstr)";
        $rs=$this->db->fetchAll($sql);
        if(!empty($rs)){
            foreach($rs as $_colum){
                $_colum['value']='';
                if(isset($this->_catalog_colums[$_colum['attribute_code']]))
                $this->_catalog_colums[$_colum['attribute_code']]=$_colum; 
            }    
        }
  }
   /**
    * @step3
    * @读取文件
    */
   private function _setCsvData($file){
        $row=0;
	    $handle=fopen($file,"r");
        $_rows=array();
        while($data=fgetcsv($handle,100000,",")){
            if(empty($data))continue;
            $row++;
            $_data=iconv('gb2312','utf-8',$data);
            if(!empty($_data))$data=$_data;
             if($row==1){
                $head=array();
                foreach($data as $key=>$val){
                    $val=trim($val);
                    if(!empty($val)){
                        $val=iconv('gbk','utf-8',$val);
                        $head[$key]=strtolower($val);
                    }
                } 
                $this->_cataHead=$head;          
             }else{
                $row=$this->_getRow($this->_cataHead,$data);
                $row=$this->setUrlKey($row);
                $_rows[]=$row;
             }
        }
        fclose($handle);
        $_rows=$this->_reOrderRows($_rows);
        $this->setCatalogs($_rows);
   }
   /**
    * 获得一行
    */
   private function _getRow($rowHead,$row){
    $catalog=$this->_defaultCatalog;
    if(is_array($row)&&!empty($row)){
        foreach($row as $key=>$val){
            if(isset($rowHead[$key])){
                $catalog[$rowHead[$key]]=$this->strCheck($val);
            }
        }
    }
    if(count($catalog)<5){
        return array();
    } 
    return $catalog;
   }
   /**
    * step 3.5
    * @排序，必须在写入之前全体排序（catalog_id和parent_id）
    * @先有父后有子，（不排除有子无父，在插入数据之前会再查父）
    */
   private function _reOrderRows($rows){
        if(!empty($rows)&&is_array($rows)){
            $_catalogIds=array();
            $_parentIds=array();
            foreach($rows as $key=>$row){
                $_catalogIds[$key]=$row['catalog_id'];
                $_parentIds[$key]=$row['parent_id'];
            }
            array_multisort($_parentIds, SORT_ASC,$_catalogIds, SORT_ASC,$rows);
        }
        return $rows;
   }
   
   /**
    * @step4
    * @赋值
    */
   protected function _setRowData($row){
        $rowData=$this->_catalog_colums;
        if(!empty($row)){
            foreach($row as $key=>$val){
                if($key=='in_menu')$key='include_in_menu';
                if($key=='url_key'){
                    $rowData['url_path']['value']=$val.'/';
                }
                if(isset($rowData[$key])){
                    $rowData[$key]['value']=$val;
                }  
            }
        }
        return $rowData;
   }
   /**
    * @装配数据，写表
    */
   private function setCatalogs($rows){
        if(!empty($rows)){
            foreach($rows as $row){
                $this->setCatalog($row);
            }
        }
   } 
   /**
    * @写一行数据进去
    */
   private function setCatalog($row){
        if(!empty($row)){
            $rs=$this->isExist($row);
            if(!empty($rs)){
                $row['path']=$rs['path'];
                $row['catalog_id']=$rs['entity_id'];
                $this->modifyCatalog($row);
            }else{
                $this->addCatalog($row);
            }
        }
   } 
   /**
    * @检查Id是否被占用
    */
    private function isExist($row){
        $sql="select entity_id,path from `catalog_category_entity` where entity_id ='".$row['catalog_id']."' limit 1";
        $rs = $this->db->fetchRow($sql);
        if($rs)return $rs;
        $sql="select entity_id from catalog_category_entity_varchar where `value`='".$row['name']."' limit 1";
        $rs = $this->db->fetchOne($sql);
        if($rs){
            echo "<br>分类名称有重复，请更改：".$row['name'];
            $sql="select entity_id,path from `catalog_category_entity` where entity_id ='".$rs."' limit 1";
            return $this->db->fetchRow($sql);
        }
        return false;
    }
   /**
    * @修改
    */
    private function modifyCatalog($row){
        //print_r($row);
        if(!$row['override'])return false;
        $dataRow=$this->_catalog_colums;
        $entity_varchar3 = "update catalog_category_entity_varchar set value='".$row['name']."' where attribute_id='".$dataRow['name']['attribute_id']."' and entity_id='".$row['catalog_id']."'";
        $entity_varchar2 = "update catalog_category_entity_varchar set value='".$row['url_key']."' where attribute_id='".$dataRow['url_key']['attribute_id']."' and entity_id='".$row['catalog_id']."'";
        $entity_varchar2_1 = "update catalog_category_entity_url_key set value='".$row['url_key']."' where attribute_id='".$dataRow['url_key']['attribute_id']."' and entity_id='".$row['catalog_id']."'";
        $entity_varchar4 = "update catalog_category_entity_varchar set value='PRODUCTS' where attribute_id='".$dataRow['display_mode']['attribute_id']."' and entity_id='".$row['catalog_id']."'";			
        $entity_varchar1 = "update catalog_category_entity_varchar set value='".$row['meta_title']."' where entity_id='".$row['catalog_id']."' and attribute_id='".$dataRow['meta_title']['attribute_id']."'";
        $entity_text1="update catalog_category_entity_text set value='".$row['description']."' where entity_id='".$row['catalog_id']."' and attribute_id='".$dataRow['description']['attribute_id']."'";
        $entity_text2="update catalog_category_entity_text set value='".$row['meta_keywords']."' where entity_id='".$row['catalog_id']."' and attribute_id='".$dataRow['meta_keywords']['attribute_id']."'";
        $entity_text3="update catalog_category_entity_text set value='".$row['meta_description']."' where entity_id='".$row['catalog_id']."' and attribute_id='".$dataRow['meta_description']['attribute_id']."'";
        $entity_int1="update catalog_category_entity_int set value='".$row['in_menu']."' where entity_id='".$row['catalog_id']."' and attribute_id='".$dataRow['include_in_menu']['attribute_id']."'";
		$entity_int2="update catalog_category_entity_int set value='".$row['is_active']."' where entity_id='".$row['catalog_id']."' and attribute_id='".$dataRow['is_active']['attribute_id']."'";  // and entity_type_id=3
		
        $this->db->query($entity_varchar1);
        $this->db->query($entity_varchar2);
        $this->db->query($entity_varchar2_1);
        $this->db->query($entity_varchar3);
        $this->db->query($entity_varchar4);
        $this->db->query($entity_int1);
        $this->db->query($entity_int2);
        $this->db->query($entity_text1);
        $this->db->query($entity_text2);
        $this->db->query($entity_text3);
        //写url
        $this->setUrlReWrite($row);
    }
    /**
     * @写url
     */
    private function setUrlReWrite($row){
        $pathCate='';
        $cags=explode("/",$row['path']);
		if(count($cags)>1)
		{
			for($i=2;$i<count($cags)-1;$i++)
			{
				$sql="select `value` from `catalog_category_entity_varchar` where entity_id =".$cags[$i]." and attribute_id=(select attribute_id from eav_attribute where attribute_code='url_path' and entity_type_id=3 limit 1) limit 1";
				$rs=$this->db->fetchOne($sql);
                if(!empty($rs)){
                    $pathCate.=$rs."/";
                }
			}
		}
	    $sql = "replace into `core_url_rewrite` (`url_rewrite_id` ,`store_id` ,`category_id` ,`product_id` ,`id_path` ,`request_path` ,`target_path` ,`is_system` ,`options` ,`description` )VALUES (NULL , '1',  '".$row['catalog_id']."', NULL , 'category/".$row['catalog_id']."', '".$pathCate.$row['url_key']."/', 'catalog/category/view/id/".$row['catalog_id']."', '1', '', NULL );";

	    $this->db->query($sql);
        $dataRow=$this->_catalog_colums;
        $sql = "insert into catalog_category_entity_url_key set value='".$row['url_key']."',attribute_id='".$dataRow['url_key']['attribute_id']."' ,store_id='".$row['0']."', entity_id='".$row['catalog_id']."',entity_type_id = 3";
        $this->db->query($sql);
    }
    
    /**
     * @新增加
     */
     private function addCatalog($row){
            $row['level']=0;
            $row['path']='';

     		     $sql = "select `path` from catalog_category_entity where entity_id ='".$row['parent_id']."' limit 1";
                 $row['path']=$this->db->fetchOne($sql);
                 if(!empty($row['path'])){ //必须有
                   $array_level = explode("/",$row['path']);
				   $row['level'] = count($array_level);
                   $row['path'].='/'.$row['catalog_id'];
                 }else{
                    echo '出错了，找不到父目录,parent_id:'.$row['parent_id'].'<br>';
                    //@file_put_contents(dirname(__FILE__).'/aa.txt',print_r($row,true)."\n===出错了，找不到父目录===\n",FILE_APPEND);
                    return false;
                 }  
                 $sql = "select count(*) as num from catalog_category_entity where parent_id ='".$row['parent_id']."'  limit 1";
                 $row['position']=$this->db->fetchOne($sql);
                 $row['position']+=1;
                 $sql = "select count(*) as num from catalog_category_entity where parent_id ='".$row['catalog_id']."'  limit 1";
                 $row['children_count']=$this->db->fetchOne($sql);
                 $sql = "insert into catalog_category_entity(entity_id,entity_type_id,attribute_set_id,parent_id,created_at,updated_at,path,position,level,children_count) values ('".$row['catalog_id']."',3,3,'".$row['parent_id']."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','".$row['path']."','".$row['position']."','".$row['level']."','".$row['children_count']."')";
                 $this->db->query($sql);
			
            $dataRow=$this->_setRowData($row); //赋值
            
            foreach($dataRow as $colum=>$data){
                if(isset($data['backend_type'])&&$data['backend_type']!='static'){
                    $sqlTpl="insert into catalog_category_entity_%s(entity_type_id,attribute_id,store_id,entity_id,value) values (3,'%s',0,'%s','%s');";
                    $_data=array();
                    $_data[]=$data['backend_type'];
                    $_data[]=$data['attribute_id'];
                    $_data[]=$row['catalog_id'];
                    $_data[]=$data['value'];
                   
                    $sql=vsprintf($sqlTpl,$_data);
                    $r= $this->db->query($sql);
                    if(!$r){
                        @file_put_contents(dirname(__FILE__).'/aa.txt',$sql."\n",FILE_APPEND);
                    }
                }
            }
            //写url
            $this->setUrlReWrite($row);
     }
    /**
     * @获得url_path
     */
    private function setUrlKey($row){
        $row['url_key']=str_replace(array('  ','&','+','#','_',' '),'-',strtolower($row['name']));
        return $row;
    }
   /**
    * @
    */
   private function strCheck($str)
    {
    	$str=addslashes(trim($str));
    	return $str;
    }
}

/**
 * @导出商品csv类
 */
class exportProduct{
  private $db;
  private $defaultStoreId='0';
  private $storeId='1';
  private $catalog='';
  private $fileName='';
  private $head=array();
  private $data=array();

  
  private function setDb(){
    if(is_object($this->db))return $this->db;
    $magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
	require_once$magento_bootstrap;
	Mage::app(); //加载……
	$this->db=Mage::getSingleton('core/resource')->getConnection('core_read');
    return $this->db;
  }
  /**
   * 主入口
   */
  public function run($catalog,$storeCode=0){
     $this->setDb();
     $this->setStoreId($storeCode);
     $this->setCatalog($catalog);
     if(!$this->exportHasFile()){
         $this->dataExport();
         $this->exportData();
         $this->exportHasFile();
     }
  }
  //if(isset($data['backend_type'])&&$data['backend_type']!='static'){

    /**
     * 设置店铺
     * @param $storeCode
     * @return int|string
     */
  protected function setStoreId($storeCode){
     if(empty($storeCode))return $this->storeId=0;
     $sql="select store_id from core_store where code='$storeCode' limit 1";
     $r=$this->db->fetchOne($sql);
     $this->storeId = (int)$r;
     return $this->storeId;
  }

    /**
     * 设置分类
     * @param $catalog
     * @return int|string
     */
  protected function setCatalog($catalog){
      if(empty($catalog))return $this->catalog=2;
      $sql="select entity_id from catalog_category_entity_varchar where `value`='$catalog' or entity_id='$catalog' limit 1";
      $r=$this->db->fetchOne($sql);
      $this->catalog=(int)$r;
      return $this->catalog;
  }
  
  private $exportNotStaticAttributes=array();
  
  
  private $defaultValues=array(
    'sku'=>'',
    'type'=>'simple',
    'category_ids'=>'2,',
    'is_imported'=>'YES',
    'has_options'=>'0',
    'news_from_date'=>'',
    'status'=>'Enabled',
    'visibility'=>'Catalog, Search',
    'tax_class_id'=>'None',
    'qty'=>1000,
    'is_in_stock'=>1,
    'store_id'=>'0',
    'websites'=>'base',
    'attribute_set'=>'',
  );
  
  protected function setDefaultValues(){
     $date=date('Y-m-d H:i:s');
     $this->defaultValues['news_from_date']=$date;
     //$this->defaultValues['store_id']=$this->storeId;
     return $this->defaultValues;
  }
  
  protected function getDefaultValues($row){
      $this->defaultValues['sku']=$row['sku'];
      $this->defaultValues['attribute_set']=$row['attribute_set'];
      return $this->defaultValues;
  }
  
  protected function getAttributeIdAndType(){
     if(empty($this->exportNotStaticAttributes)){
     $exportNotStaticAttributes=array('name','url_key','options_container',
      'special_price','price','short_description',
      'description','weight','meta_title',
      'meta_keyword','meta_description','url_key',
      'media_gallery','image','small_image','thumbnail','thumbimg','url_path'
      );
      foreach($exportNotStaticAttributes as $attr){
            $sql=vsprintf($this->attrsSql,array($attr));
            $rs = $this->db->fetchRow($sql);
            if(!empty($rs)){
                $this->exportNotStaticAttributes[$attr]['attribute_id']=$rs['attribute_id'];
                $this->exportNotStaticAttributes[$attr]['backend_type']=$rs['backend_type'];
            }
      }
      }
      return $this->exportNotStaticAttributes;
  }
  
 // private $needQuoteAttr=array('short_description','description','meta_title','meta_keyword','meta_description','name');
  
  protected function getAttributeValues($row){
        $data=array();
        if(!empty($this->exportNotStaticAttributes)){
            foreach($this->exportNotStaticAttributes as $attrName=>$attr){
                $args=array();
                $attrValue='';
                if($attrName=='media_gallery'){
                    $args[]=$attr['attribute_id'];
                    $args[]=$row['entity_id'];
                    $sql=vsprintf($this->imageSql,$args);
                    $rs=$this->db->fetchAll($sql);
                    if(!empty($rs)){
                        foreach($rs as $r){
                            $attrValue.=';'.$r['value'];
                        }
                        $attrValue=substr($attrValue,1);
                    }
                    $data[$attrName]=$attrValue;
                    continue;
                }
                
                $args[]=$attr['backend_type'];
                $args[]=$attr['attribute_id'];
                $args[]=$row['entity_id'];
                $args[]=$this->storeId;
                $sql=vsprintf($this->sql,$args);
                $attrValue=$this->db->fetchOne($sql);
                if(empty($attrValue)){
                    array_pop($args);
                    $args[]=$this->defaultStoreId;
                    $sql=vsprintf($this->sql,$args);
                    $attrValue=$this->db->fetchOne($sql);
                }
                $attrValue=str_replace('"','""',$attrValue);
                $data[$attrName]=$attrValue;
               // $this->exportNotStaticAttributes[$attrName]['value']=$attrValue;
            }
        }
        return $data;//$this->exportNotStaticAttributes;
  }
  
  
  
  private $attrsSql="select attribute_id,backend_type from eav_attribute where attribute_code='%s' and entity_type_id=4 limit 1";
  
  private $sql="select `value` from catalog_product_entity_%s where attribute_id='%s' and entity_id='%s' and store_id='%s' limit 1";
  
  private $imageSql="select `value` from catalog_product_entity_media_gallery where attribute_id='%s' and entity_id='%s'"; //images

    /**
     * 从数据库读取数据
     * @param int $slimit
     * @param int $num
     * @return array
     */
  protected function dataExport($slimit=0,$num=20){
     $data=array();
     $sql="select a.entity_id,a.sku,(select attribute_set_name from eav_attribute_set where attribute_set_id=a.attribute_set_id limit 1) as attribute_set from catalog_product_entity a RIGHT join catalog_category_product b on a.entity_id=b.product_id and b.category_id='".$this->catalog."' limit $slimit,$num";
     $rs= $this->db->fetchAll($sql);
     if(!empty($rs)){
        $this->setDefaultValues();
        $this->getAttributeIdAndType();
        foreach($rs as $row){
            $data =$this->getDefaultValues($row);
            $data2=$this->getAttributeValues($row);
            $data3=$this->getCustomOptions($row);
            $data=array_merge($data,$data2,$data3);
            $this->setHead($data);
            $this->setData($data);
        }
        if(count($rs)==$num){
            $slimit+=$num;
            list($slimit,$num)=$this->dataExport($slimit,$num);
        }
     }
     return array($slimit,$num);
  }

    /**
     * 暂存csv头
     * @param $row
     */
  private function setHead($row){
        foreach($row as $key=>$val){
            if(!in_array($key,$this->head))
                $this->head[]=$key;
        }
  }

    /**
     * 暂存数据
     * @param $row
     */
  private function setData($row){
      $this->data[] = $row;
      // $this->data[]= '"'.implode('","',$row)."\"\n";
  }

    /**
     * 获取文件名称
     * @return string
     */
  private function getFileName(){
      if(empty($this->fileName))
      $this->fileName = $fileName ='export'. $this->catalog.$this->storeId.'.csv';
      return $this->fileName;
  }

    /**
     * 处理行
     */
  private function setLines(){
      $newData = array_flip($this->head);
      foreach ($newData as &$v)$v='';
      foreach ($this->data as &$row){
          $row = array_merge($newData,$row);
      }
  }

    /**
     * 生成csv文件
     */
  private function exportData(){
        $this->setLines();
        $str = implode(',',$this->head)."\n";
        foreach ($this->data as $row){
            $str.='"'.implode('","',$row)."\"\n";
        }
        @file_put_contents(dirname(__FILE__).'/'.$this->getFileName(),$str);
  }

    /**
     * 输出csv文件
     * @return bool
     */
  private function exportHasFile(){
      if(file_exists(dirname(__FILE__).'/'.$this->getFileName())){
          echo '<a href="'.$this->getFileName().'">'.$this->getFileName().'</a>';
          return true;
      }
      return false;
  }

    /**
     * 获取自定义属性
     * @param $row
     * @return mixed
     */
  protected function getCustomOptions($row){
      $data=array();
      $args[]=$row['entity_id'];
      $sql = "select a.title,b.type,b.sort_order,b.is_require,b.option_id from catalog_product_option b LEFT JOIN catalog_product_option_title a on a.option_id=b.option_id and a.store_id=0 where b.product_id='%s'";
      $sql=vsprintf($sql,$args);
      $options = $this->db->fetchAll($sql);
      if(!empty($options)){
      foreach ($options as $option){
          $_title = $option['title'].':'.$option['type'].':'.$option['sort_order'].':'.$option['is_require'];
          $_values = '';
          $args=array();
          $args[]=$option['option_id'];
          $sql = "select a.title,c.price,c.price_type,b.sort_order from catalog_product_option_type_value b LEFT JOIN catalog_product_option_type_title a on a.option_type_id=b.option_type_id and a.store_id=0 LEFT join catalog_product_option_type_price c ON c.option_type_id=b.option_type_id and c.store_id=0 where b.option_id='%s'";
          $sql=vsprintf($sql,$args);
          $option_values = $this->db->fetchAll($sql);
          if(!empty($option_values)){
          $_value=array();
          foreach ($option_values as $option_value){
              $_value[] = $option_value['title'].':'.$option_value['price_type'].':'.round($option_value['price'],2).':'.$option_value['sort_order'];
          }
          $_values = implode('||',$_value);
          }
          $data[$_title]=$_values;
      }}
       return  $data;
  }
}


 /**
  * @导入评论
  */
 
 class importReview{
    private $db;
    private $catalogs=array();
    private $replyColumnName='';
    private $ratingColumnName='';
    private $defaultStoreId=0;
    private $randnum=5;
    private $productSelectType='';
    private $del;
    private $valueOptions;
    private $skus;
    private function setDb(){
        if(is_object($this->db))return $this->db;
        $magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
    	require_once$magento_bootstrap;
    	Mage::app(); //加载……
    	$this->db=Mage::getSingleton('core/resource')->getConnection('core_read');
        return $this->db;
   }
   
   private $sqls=array(
        'getReviews'=>"select * from site_review_pp where titler!='' and catalog='%s' and store_id='%s' order by rand() limit %s",
        'getRandReviews'=>"select * from site_review_pp where titler!='' order by rand() limit %s",
        'getReviewsCatalog'=>"select store_id,catalog from site_review_pp where catalog!='' group by store_id,catalog",
        'getInformationSchemaAsReply'=>"select COLUMN_NAME from information_schema.COLUMNS where TABLE_NAME='review_detail'",
        'getInformationSchemaAsPp'=>"select COLUMN_NAME from information_schema.COLUMNS where TABLE_NAME='site_review_pp'",
        'getCatalogIdByName'=>"select entity_id from catalog_category_entity_varchar where `value` like '%%s%' and entity_type_id=3 limit 1",
        'getProductIdByCatalogId'=>"select product_id,(select count(*) from review where entity_pk_value=catalog_category_product.product_id) as num from catalog_category_product where category_id='%s' order by num limit 250",
       'getRatingIds'=>"select rating_id from rating where entity_id=1",
       'getValueOptions'=>"select option_id,`value` from rating_option where rating_id='%s'",

        'setTableReview'=>"insert into review(`created_at`,`entity_id`,`entity_pk_value`,`status_id`) values('%s',1,'%s',1)",
        'setTableReviewDetail'=>"insert into review_detail(review_id,`store_id`,`title`,`detail`,`nickname`,`%s`) values('%s','%s','%s','%s','%s','%s')",
        'setTableReviewDetailNoReply'=>"insert into review_detail(review_id,`store_id`,`title`,`detail`,`nickname`) values('%s','%s','%s','%s','%s')",
        'setTableReviewStore'=>"insert into review_store(`review_id`,`store_id`) values('%s',%s)",
        'setTableVote'=>"insert into rating_option_vote(`option_id`,`remote_ip`,`remote_ip_long`,`entity_pk_value`,`rating_id`,`review_id`,`percent`,`value`) values ('%s','%s','%s','%s','%s','%s','%s','%s')",
        'setTableVoteAggregated'=>"insert into rating_option_vote_aggregated(`rating_id`,`entity_pk_value`,`vote_count`,`vote_value_sum`,`percent`,`percent_approved`,`store_id`) values ('%s','%s','%s','%s','%s','%s','%s')",
        
        'getSummaryByProdutId'=>"select primary_id from review_entity_summary  where store_id='%s' and entity_pk_value='%s' limit 1",
        'setTableSummary'=>"UPDATE review_entity_summary set reviews_count='%s',rating_summary='%s' where entity_pk_value='%s' and store_id='%s' limit 1",
        'setTableSummaryNew'=>"insert into review_entity_summary set reviews_count='%s',rating_summary='%s',entity_pk_value='%s',store_id='%s',entity_type='%s'",
        'setDetailSummaryNew'=>"update review_detail set review_rating='%s' where review_id='%s'",
        
        'getReviewTimes'=>"select count(*) as num from review a inner join review_detail b on a.review_id=b.review_id where a.entity_pk_value='%s' and a.status_id=1 limit 1",//and (b.store_id=0 or b.store_id='%s')
        'delSitePPSrc'=>"delete from site_review_pp where review_id='%s' limit 1",
        
        'alterStoreId'=>"alter table site_review_pp add column store_id int",
        'setDefaultStoreId0'=>"update site_review_pp set store_id=1",
       // 'setDefaultStoreId1'=>"update site_review_pp set catalog=replace(`catalog`,' dresses','')",
     //   'setDefaultStoreId2'=>"update site_review_pp set catalog=replace(`catalog`,' Dresses','')",
     //   'setDefaultStoreId3'=>"update site_review_pp set catalog=replace(`catalog`,' dress','')",
     //   'setDefaultStoreId4'=>"update site_review_pp set catalog=replace(`catalog`,' Dress','')",
        'setDefaultStoreId5'=>"UPDATE site_review_pp set date_added=date_add(NOW(), interval -FLOOR(RAND()*350) hour)",
        'setDefaultStoreId6'=>"delete from site_review_pp where titler like '%title%'",
        'setDefaultStoreId7'=>"delete from site_review_pp where titler ='' or text=''",
       'setDefaultStoreId8'=>"delete from site_review_pp where text LIKE '%http%'",
       'setDefaultStoreId9'=>"delete from site_review_pp where text LIKE '%.com%'",
       'setDefaultStoreId10'=>"delete from site_review_pp where titler LIKE '%.com%'",
       'setDefaultStoreId11'=>"update site_review_pp set catalog= 'prom dress' where catalog='Special Occasion Dresses'",
        'getCount'=>"select count(*) as num from site_review_pp",
       'getReviewCount'=>"select count(*) as num from review",
   );
   
   
   
   public function run($storeCode='',$del=true){
        $this->del = $del;
        $this->setDb();
        $this->getAlterTable();
        $this->initDefault($storeCode);
        echo $this->getCount();
        echo '<br> ---sitepp-- <br>';
        $this->setReviews();
        echo $this->getCount();
        echo '<br> ---review-- <br>';
        echo $this->getReviewCount();
        echo '<br>the end';
   }

   public function setSku($str=''){
       $str = str_replace(array('，','|','；',';','、'),',',$str);
       $skue = explode(',',$str);
       $skue = array_unique($skue);
       $this->skus = $skue;
   }
   
   private function getAlterTable(){
      $haveStoreId=false;
      $rs =  $this->getInformationSchemaAsPp();
      
      foreach($rs as $r){
         if($r['COLUMN_NAME']=='store_id')$haveStoreId=true;
      }
      
      if(!$haveStoreId){
          try{
            $this->db->query($this->sqls['alterStoreId']);
          }catch(exception $e){
            
          }
      }
      for($i=0;$i<12;$i++){
          $sqltemp = "setDefaultStoreId$i";
          if(isset($this->sqls[$sqltemp])){
              $this->db->query($this->sqls[$sqltemp]);
          }
      }
   }
   
   private function getCount(){
      return  $this->db->fetchOne($this->sqls['getCount']);
   }

     private function getReviewCount(){
         return  $this->db->fetchOne($this->sqls['getReviewCount']);
     }

   protected function initDefault($storeCode){
        $catalog=$this->getReviewsCatalog();
        if(!empty($catalog)){
            foreach($catalog as $cata){
                if(!isset($cata['store_id']))$cata['store_id']=0;
                $this->catalogs[$cata['store_id']][$cata['catalog']]=$this->getCatalogIdByName($cata['catalog']);
            }
        }
        $rs=$this->getInformationSchemaAsReply();
        foreach($rs as $r){
            if($r['COLUMN_NAME']=='reply'){
                $this->replyColumnName='reply'; break;   
            }
            if($r['COLUMN_NAME']=='response'){
                $this->replyColumnName='response'; break;  
            }
            if($r['COLUMN_NAME']=='review_rating'){
                $this->ratingColumnName='review_rating'; break;
            }
        }
        
        $rs=$this->getInformationSchemaAsPp();
        
        foreach($rs as $r){
            if($r['COLUMN_NAME']=='sku'){
                $this->productSelectType='sku'; break;   
            }
        }
        $this->_setDefaultStoreId($storeCode);
   }
   
   protected function getInformationSchemaAsPp(){
      $sql=$this->sqls['getInformationSchemaAsPp'];
      return $this->db->fetchAll($sql);
   }
   
   protected function getHaveSkuReviews(){
      $sql="select * from site_review_pp where titler!='' and sku!='' limit 100";
      return $this->db->fetchAll($sql);
   }

     /**
      * 根据已经设置好的review-sku设定
      * @param int $i
      * @return int
      */
   protected function setReviewsBySku($i=0){
        $reviews = $this->getHaveSkuReviews();
        if(!empty($reviews)){
            foreach($reviews as $review){
            $sql="select entity_id from catalog_product_entity where sku ='${review['sku']}' limit 1";
                $productId=$this->db->fetchOne($sql);
                if($productId){
                    list($review_count,$vote_precet)= $this->setReview($productId,$review);
                    $this->setTableSummary($productId,$review_count,$vote_precet);
                    $this->delSitePPSrc($review['review_id']);
                    $i++;
                }
            }
        }
        return $i;
   }

     /**
      * 根据get方法传递过来的sku来设定评论
      * @return int
      */
   protected function setReviewsForSkus(){
       $i = 0;
       if(!empty($this->skus)){
           foreach ($this->skus as $sku){
               if(empty($sku))continue;
               $sql="select entity_id from catalog_product_entity where sku ='$sku' limit 1";
               $productId=$this->db->fetchOne($sql);
               if($productId){
                   $num=rand(1,$this->randnum);
                   $reviews=$this->getRandReviews($num);
                   if(!empty($reviews)){
                       foreach($reviews as $review){
                           $ck = $this->check($productId,$review);
                           if($ck)continue;
                           list($review_count,$vote_precet)= $this->setReview($productId,$review);
                           if($this->del)$this->delSitePPSrc($review['review_id']);
                           $i++;
                       }
                       $this->setTableSummary($productId,$review_count,$vote_precet);
                   }
               }else{
                   echo "<br>没有找到商品 $sku<br>";
               }
           }
           echo "<br>add $i items<br>";
           return 1;
       }
       return 0;
   }
   
   protected function setReviews(){
        $i=0;
        $forSku = $this->setReviewsForSkus();
        if($forSku)return;
        if(!empty($this->catalogs)){
            foreach($this->catalogs as $storeId=>$catalogs){
            foreach($catalogs as $catalog=>$catalogId){
                 $products=$this->getProductIdByCatalogId($catalogId);
                 foreach($products as $product){
                    $num=rand(1,$this->randnum);
                    $reviews=$this->getReviews($catalog,$storeId,$num);
                    if(!empty($reviews)){
                        foreach($reviews as $review){
                           $ck = $this->check($product['product_id'],$review);
                           if($ck)continue;
                           list($review_count,$vote_precet)= $this->setReview($product['product_id'],$review);
                           if($this->del)$this->delSitePPSrc($review['review_id']);
                           $i++;
                        }
                        $this->setTableSummary($product['product_id'],$review_count,$vote_precet);
                    }else{
                        echo "No data in :$catalog <br>";
                        break;
                    }
                 }
            }}
        }
        if($this->productSelectType=='sku'){
            $i = $this->setReviewsBySku($i);
        }
       echo "<br>add $i items<br>";
   }
   
   protected function getStoreIds(){
        if($this->_storeIds===null){
         $storeIds=array();
         $sql="select store_id from core_store where store_id>0";
         $_storeIds=$this->db->fetchAll($sql);
         if(!empty($_storeIds)){
            foreach($_storeIds as $store){
                $storeIds[]=$store['store_id'];
            }
         }
         $this->_storeIds=$storeIds;
         }
         return $this->_storeIds;
   }

   private function getRatingIds(){
       $sql=$this->sqls['getRatingIds'];
       return  $this->db->fetchCol($sql);
   }

   private function getValueOptions($ratingId,$value){
       if(!isset($this->valueOptions[$ratingId])){
           $sql=vsprintf($this->sqls['getValueOptions'],array($ratingId));
           $rows = $this->db->fetchAll($sql);
           if(!empty($rows)){
               foreach ($rows as $row){
                   $this->valueOptions[$ratingId][$row['value']]=$row['option_id'];
               }
           }
       }
       if(isset($this->valueOptions[$ratingId])&&isset($this->valueOptions[$ratingId][$value]))
           return $this->valueOptions[$ratingId][$value];
       return 0;
   }

   private function check($productId,$review){
       $review['titler']=   mysql_escape_string($review['titler']);
       $review['text']=   mysql_escape_string($review['text']);
       $sql = "select review.review_id from review_detail INNER  JOIN review on review_detail.review_id=review.review_id where review_detail.title='${review['titler']}' and review_detail.detail='${review['text']}' and review.entity_pk_value='$productId' limit 1";
       return $this->db->fetchOne($sql);
   }


   protected function setReview($productId,$review){
       $sql="SET FOREIGN_KEY_CHECKS=0";
       $this->db->query($sql);
        $sql=vsprintf($this->sqls['setTableReview'],array($review['date_added'],$productId));
        $this->db->query($sql);
        $reviewId=$this->db->lastInsertId();
        $this->defaultStoreId=isset($review['store_id'])?(int)$review['store_id']:$this->defaultStoreId;
		

		//这里只需要在store_id=0的店插入一条就可以。	
       if(!empty($this->replyColumnName)){
           $sql=vsprintf($this->sqls['setTableReviewDetail'],array($this->replyColumnName,$reviewId,0,mysql_escape_string($review['titler']),mysql_escape_string($review['text']),mysql_escape_string($review['author']),mysql_escape_string($review['reply'])));
       }else{
           $sql=vsprintf($this->sqls['setTableReviewDetailNoReply'],array($reviewId,0,mysql_escape_string($review['titler']),mysql_escape_string($review['text']),mysql_escape_string($review['author'])));
       }
	   $this->db->query($sql);
         
       $this->setTableReviewStore($reviewId,$this->defaultStoreId);
       
       
       $vote_count=$this->getReviewTimes($productId);
       $_vote_precet=90;
       $_count_rate=0;
       $_rateing = 5;
       $ratingIds = $this->getRatingIds();
       foreach ($ratingIds as $index=>$id){
           $_rateing=rand($review['rateing'] ,5);
           $_option_id= $this->getValueOptions($id,$_rateing);// $index*5+$_rateing;  //与表中的对应关系必须是12345对应value12345
           if(!$_option_id)continue;
           $_rateing_id=$id;
           $_count_rate+=$_rateing;
           $_vote_precet=ceil($_rateing/5*100);
           $_ip='218.22.27.226';
           $_iplong=2147483647;
        //   'setTableVote'=>"insert into rating_option_vote(`option_id`,`remote_ip`,`remote_ip_long`,`entity_pk_value`,`rating_id`,`review_id`,`percent`,`value`) values ('%s','%s','%s','%s','%s','%s','%s','%s')",
           $sql=vsprintf($this->sqls['setTableVote'],array($_option_id,$_ip,$_iplong,$productId,$_rateing_id,$reviewId,$_vote_precet,$_rateing));
           $this->db->query($sql);
           $sql=vsprintf($this->sqls['setTableVoteAggregated'],array($_rateing_id,$productId,$vote_count,$_option_id,$_count_rate,$_count_rate,0));
           $this->db->query($sql);
           $storeIds=$this->getStoreIds();
           if(!empty($storeIds)){
               foreach($storeIds as $storeId){
                   $sql=vsprintf($this->sqls['setTableVoteAggregated'],array($_rateing_id,$productId,$vote_count,$_option_id,$_count_rate,$_count_rate,$storeId));
                   $this->db->query($sql);
               }
           }
       }
       if($this->ratingColumnName){
       $sql=vsprintf($this->sqls['setDetailSummaryNew'],array($_rateing,$reviewId));
       $this->db->query($sql);
       }

       $sql="SET FOREIGN_KEY_CHECKS=1";
       $this->db->query($sql);
       return array($vote_count,$_vote_precet);
   }
   
   private function getSummaryByProdutId($productId,$storeId=0){
       $sql=vsprintf($this->sqls['getSummaryByProdutId'],array($storeId,$productId));
        return $this->db->fetchOne($sql);
   }
   
   protected function setTableSummary($productId,$review_count,$vote_count){
        $pkeyid=$this->getSummaryByProdutId($productId);  //store_id = 0 
        if(!empty($pkeyid)){
           $sql=vsprintf($this->sqls['setTableSummary'],array($review_count,$vote_count,$productId,0));    //store_id = 0
         $this->db->query($sql);
        }else{
         $sql=vsprintf($this->sqls['setTableSummaryNew'],array($review_count,$vote_count,$productId,0,1));   //store_id = 0 
         $this->db->query($sql);
        }
        $this->setTableSummaryOther($productId,$review_count,$vote_count);  //store_id > 0
   }
   
   public function resetReviewSummarys(){
        $sql="select * from review_entity_summary where entity_type=1 and store_id=0";
        $rs=$this->db->fetchAll($sql);
        if(!empty($rs)){
            foreach($rs as $i=>$row){
				$vote_count=$this->getReviewTimes($row['entity_pk_value']);
				$sql = "update review_entity_summary set reviews_count='$vote_count' where primary_id='${row['primary_id']}'";
				$row['reviews_count']=$vote_count;  //都使用storeId=0的评论数

                $storeIds=$this->getStoreIds();
                $this->db->query("delete from review_entity_summary where entity_type=1 and entity_pk_value='".$row['entity_pk_value']."' and store_id>0");
                foreach($storeIds as $storeId){
                    $r=$this->db->fetchOne("select primary_id from review_entity_summary where entity_type=1 and entity_pk_value='".$row['entity_pk_value']."' and store_id='$storeId' limit 1");
                    if(!$r){
                        $set=array();
                        $row['store_id']=$storeId;
                        unset($row['primary_id']);
                        foreach($row as $key=>$val){    
                            $set[]="`$key`='$val'";
                        }
                        $_set = implode(',',$set);
                        $sql="insert into review_entity_summary set $_set";
                        $this->db->query($sql);
                    }
                }
            }
        }
   }
   
   protected function setTableSummaryOther($productId,$review_count,$vote_count){
        $storeIds=$this->getStoreIds();
        foreach($storeIds as $storeId){
            $this->setTableSummaryOtherRow($productId,$review_count,$vote_count,$storeId);
        }
   }
   
   private function setTableSummaryOtherRow($productId,$review_count,$vote_count,$storeId){
       if($storeId==0)return;
        $r=$this->getSummaryByProdutId($productId,$storeId);
        if($r){
           $sql=vsprintf($this->sqls['setTableSummary'],array($review_count,$vote_count,$productId,$storeId));
           $this->db->query($sql);
        }else{
           $sql=vsprintf($this->sqls['setTableSummaryNew'],array($review_count,$vote_count,$productId,$storeId,1));
           $this->db->query($sql);
        }
   }
   
   
   private function getReviewTimes($productId){
       $sql=vsprintf($this->sqls['getReviewTimes'],array($productId));
        return $this->db->fetchOne($sql);
   }
   
   private function setTableReviewStore($reviewId,$storeId){
        $rs =$this->getTableReviewStore($reviewId,0);
        if(empty($rs)){
            $this->setToTableReviewStore($reviewId,0);
            if($storeId>0){
                $this->setToTableReviewStore($reviewId,$storeId);
            }else{
				$storeIds = $this->getStoreIds();
				foreach($storeIds as $_storeId){
					if($_storeId>0){
						try{
							$this->setToTableReviewStore($reviewId,$_storeId);
						}catch(exception $e){ 	}
					}
				}
			}
           
        } 
   }
   
   private function getTableReviewStore($reviewId,$storeId){
        $sql="select `review_id` from review_store where `review_id`='$reviewId' and `store_id`='$storeId' limit 1";
         return $this->db->fetchOne($sql);
   }
   
   private function setToTableReviewStore($reviewId,$storeId){
       $sql="insert into review_store(`review_id`,`store_id`) values('$reviewId','$storeId')";
       $this->db->query($sql);
   }

   private function getProductIdByCatalogId($catalogId){
        return $this->db->fetchAll(vsprintf($this->sqls['getProductIdByCatalogId'],array($catalogId)));
   }
   
   private function getReviewsCatalog(){
        return $this->db->fetchAll($this->sqls['getReviewsCatalog']);
   }
   
   private function getInformationSchemaAsReply(){
      return $this->db->fetchAll($this->sqls['getInformationSchemaAsReply']);
   }
   
   private function getInformationSchemaAsStoreId(){
      return $this->db->fetchAll($this->sqls['getInformationSchemaAsStoreId']);
   }
   
   
   
   private function getCatalogIdByName($catalogName){
       $catalogName=mysql_escape_string($catalogName);   
       $sql = str_replace('%s',$catalogName,$this->sqls['getCatalogIdByName']);
       return $this->db->fetchOne($sql);
   }
   
   private function getReviews($catalogName,$storeId,$num){
      // $storeId='0';
     //  echo vsprintf($this->sqls['getReviews'],array($catalogName,$storeId,$num))."<br>";
       return $this->db->fetchAll(vsprintf($this->sqls['getReviews'],array($catalogName,$storeId,$num)));
   }


     private function getRandReviews($num){
         //  echo vsprintf($this->sqls['getRandReviews'],array($num))."<br>";
         return $this->db->fetchAll(vsprintf($this->sqls['getRandReviews'],array($num)));
     }
   
   
   
   private function delSitePPSrc($ppReviewId){
        $sql=vsprintf($this->sqls['delSitePPSrc'],array($ppReviewId));
        return $this->db->query($sql);
   }
   
   private function _setDefaultStoreId($storeCode=''){
        $storeCode=trim($storeCode);
        if($storeCode='en'){
            $storeCode='';
        }
        if(!empty($storeCode)){
           $sql="select store_id from core_store where code='$storeCode' limit 1";
           $storeId=$this->db->fetchOne($sql);
           if($storeId>0)$this->defaultStoreId=$storeId;
        }else{
            $this->defaultStoreId=0;
        }
        return $this->defaultStoreId;
   }
   
   public function setEnReviewToOtherStore($storeCode=''){
        $this->setDb();
        $storeCode=trim($storeCode);
        $storeId=-1;
        $storeIds=array();
        if(!empty($storeCode)){
            $sql="select store_id from core_store where code='$storeCode' limit 1";
            $_storeId=$this->db->fetchOne($sql);
            if($_storeId>0)$storeId=$_storeId;
        }else{
            $sql="select store_id from core_store where store_id>1";
            $storeIds=$this->db->fetchAll($sql);
        }
        $sql="select r.review_id from review_store r inner join review d on r.review_id=d.review_id where r.`store_id`='0' and d.status_id=1";
        $rs=$this->db->fetchAll($sql);
        if(!empty($rs)){
            if($storeId>0){
                foreach($rs as $review){
                   $r = $this->getTableReviewStore($review['review_id'],$storeId);
                   if(empty($r)){
                     $this->setToTableReviewStore($review['review_id'],$storeId);
                   }
                }
            }else if(!empty($storeIds)){
                foreach($rs as $review){
                    foreach($storeIds as $storeId){
                         $r = $this->getTableReviewStore($review['review_id'],$storeId['store_id']);
                         if(empty($r)){
                             $this->setToTableReviewStore($review['review_id'],$storeId['store_id']);
                         }
                    }
                }
            }
        }
        $sql="select * from rating_option_vote_aggregated where store_id=0 group by entity_pk_value,rating_id ORDER BY entity_pk_value";
        $rs=$this->db->fetchAll($sql);
        if(!empty($rs)){
            foreach($rs as $vote){
                foreach($storeIds as $storeId){
                    $r = $this->getRatingOptionVoteAggregated($vote['entity_pk_value'],$storeId['store_id']);
                    if(!$r){
                        $this->setRatingOptionVoteAggregated($vote,$storeId['store_id']);
                    }
                }
            }
        }
   }
   
   public function setEnReviewVoteToOtherStore($storeCode=''){
        $this->setDb();
        
        $this->resetReviewSummarys();
        
        $storeCode=trim($storeCode);
        $storeId=-1;
        $storeIds=array();
        if(!empty($storeCode)){
            $sql="select store_id from core_store where code='$storeCode' limit 1";
            $_storeId=$this->db->fetchOne($sql);
            if($_storeId>0){
                $storeIds['store_id']=$_storeId;
            }
        }else{
            $sql="select store_id from core_store where store_id>0";
            $storeIds=$this->db->fetchAll($sql);
        }
        
        
        $sql="select * from rating_option_vote_aggregated where store_id=0 group by entity_pk_value,rating_id ORDER BY entity_pk_value";
        $rs=$this->db->fetchAll($sql);
        if(!empty($rs)){
            $entity_pk_value=0;
            foreach($rs as $i=>$vote){
               // $sql="delete from rating_option_vote_aggregated where entity_pk_value='".$vote['entity_pk_value']."' and store_id = 1 and rating_id='".$vote['rating_id']."'";
               // $this->db->fetchRow($sql);
                $sql="delete from rating_option_vote_aggregated where entity_pk_value='".$vote['entity_pk_value']."' and store_id='".$vote['store_id']."' and rating_id='".$vote['rating_id']."' and primary_id!='".$vote['primary_id']."'";
                $this->db->query($sql);
                //$storeIds[]['store_id']=1;
                
                if(!empty($storeIds)){
                foreach($storeIds as $storeId){
                    $r = $this->getRatingOptionVoteAggregated($vote['entity_pk_value'],$vote['rating_id'],$storeId['store_id']);
                    if(!$r){
                        $data =$this->getRatingOptionVoteAggregatedSumAndCount($vote['entity_pk_value'],$vote['rating_id']);
                        $vote=array_merge($vote,$data);
                        $this->setRatingOptionVoteAggregated($vote,$storeId['store_id']);
                    }
                }
                }
            }
        }
        echo 'the end';
   }
   
   protected function getRatingOptionVoteAggregatedSumAndCount($entity_pk_value,$rating_id){
        $sql="select sum(`value`) as vote_value_sum,count(*) as vote_count from rating_option_vote where rating_id='$rating_id' and entity_pk_value='$entity_pk_value' limit 1";
        $r = $this->db->fetchRow($sql);
        if(!empty($r)){
             $r['percent_approved']=$r['percent']=ceil(($r['vote_value_sum']/$r['vote_count'])*20);
             return $r;
        }
        return array();
   }
   
   protected function getRatingOptionVoteAggregated($entityPkValue,$ratingId,$storeId){
        $sql="select primary_id from rating_option_vote_aggregated where store_id='$storeId' and rating_id='$ratingId' and entity_pk_value='$entityPkValue' limit 1";
        return $this->db->fetchOne($sql);
   }
   
    protected function setRatingOptionVoteAggregated($data,$storeId){
        unset($data['primary_id']);
        $data['store_id']=$storeId;
        $_set=array();
        foreach($data as $key=>$val){
            $_set[]="`$key`='$val'";
        }
        $set=implode(',',$_set);
        $sql="insert into  rating_option_vote_aggregated set $set";
        $this->db->query($sql);
   }
   
   public function runcls(){
          echo 'start ...';
          $this->setDb();
          $sql="SET FOREIGN_KEY_CHECKS=0";
          $this->db->query($sql);
          $sql="truncate table review;";
    	  $this->db->query($sql);
    	  $sql="truncate table review_detail;";
    	  $this->db->query($sql);
    	  $sql="truncate table review_entity_summary";
    	  $this->db->query($sql);
    	  $sql="truncate table review_store";
    	  $this->db->query($sql);
    	  $sql="truncate table rating_option_vote";
    	  $this->db->query($sql);
    	  $sql="truncate table rating_option_vote_aggregated;";
    	  $this->db->query($sql);
          $sql="SET FOREIGN_KEY_CHECKS=1";
          $this->db->query($sql);
          echo 'end';
    }
}

/**
 * @复制分类商品的关联
 * @把A分类的商品拷贝给B分类
 */
class copyCatalogAndProduct{
    private $db;
    private $srcCatalogId=0;
    private $dstCatalogId=0;
    private function setDb(){
        if(is_object($this->db))return $this->db;
        $magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
    	require_once$magento_bootstrap;
    	Mage::app(); //加载……
    	$this->db=Mage::getSingleton('core/resource')->getConnection('core_read');
        return $this->db;
   }
   
   private $sqls=array(
        'getSrcProducts'=>"select * from catalog_category_product where category_id='%s'",
        'setDstProduct'=>"insert into  catalog_category_product set category_id='%s',product_id='%s',position='%s'",
        'isExsit'=>"select * from catalog_category_product where category_id='%s' and product_id='%s' limit 1",
        
   );
   
   public function run($ctc=''){
        $this->setDb();
        $ctc=trim($ctc);
        if(!empty($ctc)){
            $_ctc=explode('-',$ctc);
            if(count($_ctc)==2){
                $this->srcCatalogId=(int)$_ctc[0];
                $this->dstCatalogId=(int)$_ctc[1];
            }
        }
        if($this->srcCatalogId>0 && $this->dstCatalogId>0){
            $this->copyCatalogTo();
        }
        echo 'the end.';
   }
   
   protected function copyCatalogTo(){
        $rs=$this->getSrcProducts();
        if(!empty($rs)){
            foreach($rs as $row){
                if($this->isExsit($row)){
                    $this->setDstProduct($row);
                }
            }
        }
   }
   
   
   private function isExsit($row){
        $sql=vsprintf($this->sqls['isExsit'],array($this->dstCatalogId,$row['product_id']));
        $rs=$this->db->fetchRow($sql);
        if(!empty($rs))return false;
        return true;
   }
   
   private function setDstProduct($row){
        $sql=vsprintf($this->sqls['setDstProduct'],array($this->dstCatalogId,$row['product_id'],$row['position']));
        $this->db->query($sql);
   }
   
   private function getSrcProducts(){
        $sql=vsprintf($this->sqls['getSrcProducts'],array($this->srcCatalogId));
        return $this->db->fetchAll($sql);
   }
}


/**
 * @相关商品混合随机写入
 * @设置相关产品
 */
class appendUpSell
{
    private $db;
    private $catalogIds = array();
    private $count=0;
    private $countName='';
    private $log=false;
    
    private $attributeCode='position';
    private $attributeTypeId=0;
    private $attributePosition=0;

    private $accessoriesNames = array('accessories');

    /**
     * @step1
     */
    private function setDb()
    {
        if (is_object($this->db))
            return $this->db;
        $magento_bootstrap = dirname(__file__) . '/../app/Mage.php';
        require_once $magento_bootstrap;
        Mage::app(); //加载……
        $this->db = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $this->db;
    }
   /**
    * @main run
    */
    public function run()
    {
        $data=array();
        if (!empty($_POST)) {
            $this->setDb();
            $this->log($_POST, 'post');
            $data = $this->processRequestDate($_POST);
            $this->log($data, 'data 1');
            $data = $this->processData($data);
            $this->log($data, 'data 2');

            if ($data['rnd']) { //随机取相关产品
                if ($data['new']) {
                    $this->clearLinkedProduct(array(), array(), $data['linkTypeId']);
                }
                 //从配件取相关产品规则： 这里的product_id已经根据规则处理好了
                  //1、如果填了产品，则加到商品上
                  //2、如果没有填，则加在所有分类的商品上
                  //3、如果填了分类，则加在分类的商品上
                $this->randToLinkProduct($data['linkTypeId'],$data['linked_product_id'],$data['product_id'],$data['num'],$data['isCatalog']);
            } else { //提交过来的值取相关产品
                if(!empty($data['product_id']) && $data['new']){
                     $this->clearLinkedProduct(array(), $data['product_id'], $data['linkTypeId']);
                }
                if (!empty($data['linked_product_id']) && !empty($data['product_id'])) {
                    foreach ($data['product_id'] as $productId) {
                        if (empty($productId))
                            continue;
                        foreach ($data['linked_product_id'] as $linkProductId){
                            if (empty($linkProductId))
                                continue;
                            $ck = $this->isNotLinkedProduct($linkProductId, $productId, $data['linkTypeId'],$data['num']);
                            if ($ck==1){
                                $this->linkProduct($linkProductId, $productId, $data['linkTypeId']);
                            }else if($ck==2){
                                break;
                            }
                        }
                    }
                }
            }
            echo '<div style="background-color: #009000;
    border: 1px solid #008000;
    color: #FFFFFF;
    padding-bottom: 10px;
    padding-top: 10px;
    width: 99.6%;">&nbsp;&nbsp;&nbsp;&nbsp;执行完成,请去网站检查数据是否写好.<br></div>';
        }
        $this->tohtml($data);
    }

    private function processData($data)
    {
        $data['product_id']=array();
        $data['linked_product_id']=array();
        if (isset($data['dstSkus'])) {
            foreach ($data['dstSkus'] as $str => $type) {
                if ($type == 2) {
                    $data['linked_product_id'][] = $this->getProductIdByUrl($str);
                } else {
                    $data['linked_product_id'][] = $this->getProductIdBySku($str);
                }
            }
        }

        if (isset($data['srcSkus'])) {
            foreach ($data['srcSkus'] as $str => $type) {
                if ($data['isCatalog']) { //如果是分类，处理分类
                    $this->getCatalogIdsByCatalog($str, $data['include']);
                } else { //如果是商品，处理商品
                    if ($type == 2) {
                        $data['product_id'][] = $this->getProductIdByUrl($str);
                    } else {
                        $data['product_id'][] = $this->getProductIdBySku($str);
                    }
                }
            }
        }
        
        if ($data['isCatalog'] && !empty($this->catalogIds)) {
            echo '分类获取正确';
            $data['product_id'] = $this->getProductIdsByCatalogIds($this->catalogIds);
        }elseif($data['isCatalog']){
            echo '分类获取失败';
        }
        
        $data['product_id']=array_unique($data['product_id']);
        $data['linked_product_id']=array_unique($data['linked_product_id']);
       return $data;
    }

    //处理提交表单的数据格式
    private function processRequestDate($data)
    {
        if (isset($data['rnd']) && $data['rnd'] == 1) {
            $data['rnd'] = true;
        }
        if (isset($data['include']) && $data['include'] == 1) {
            $data['include'] = true;
        }
        if (isset($data['isCatalog']) && $data['isCatalog'] == 1) {
            $data['isCatalog'] = true;
        }
        
        if (isset($data['new']) && $data['new'] == 1) {
            $data['new'] = true;
        }
        if (isset($data['linkTypeId']) && in_array((int)$data['linkTypeId'], array(
            1,
            4,
            5))) {
            $data['linkTypeIdOK'] = 1;
        }
        if (isset($data['dstSku']) && !empty($data['dstSku'])) {
            $data['dstSkus'] = $this->_checkSku($data['dstSku']);
        }
        if (isset($data['srcSku']) && !empty($data['srcSku'])) {
            $data['srcSkus'] = $this->_checkSku($data['srcSku']);
        }
        return $data;
    }

    //随机
    protected function randToLinkProduct($linkTypeId,$linkedProductIds,$productIds,$maxNum,$isCatalog)
    {
        if(empty($productIds)&&$isCatalog){
        $this->catalogIds = array();
        foreach ($this->accessoriesNames as $catalog) {
            $this->getCatalogIdsByCatalog($catalog, false);
        }
        $catalogAllIds = $this->catalogIds;
        $productIds = $this->getProductIdsByCatalogIds($catalogAllIds);
        }
        
        if(count($linkedProductIds) < $maxNum){
                $this->catalogIds = array();
                foreach ($this->accessoriesNames as $catalog){
                    $this->getCatalogIdsByCatalog($catalog, true);
                }
                $accessoriesCatalogIds = $this->catalogIds;
                $_linkProductIds = $this->getProductIdsByCatalogIds($accessoriesCatalogIds);
                $_maxNum=$maxNum-count($linkedProductIds);
                $ksys = array_rand($_linkProductIds, $_maxNum);
                if(is_array($ksys)){
                foreach($ksys as $key){
                    $linkedProductIds[]=$_linkProductIds[$key];
                }}else{
                    $linkedProductIds[]=$_linkProductIds[$ksys];
                }
        }
        $this->log($productIds,'productIds');
        $this->log($linkedProductIds,'linkedProductIds');
        foreach ($productIds as $productId) {
            foreach ($linkedProductIds as $linkedProductId) {
                $ck = $this->isNotLinkedProduct($linkedProductId, $productId, $linkTypeId,$maxNum);
                if ($ck==1) {
                    $this->linkProduct($linkedProductId, $productId, $linkTypeId);
                }else if($ck==2){
                    break;
                }
            }
        }
    }

    //清理
    private function clearLinkedProduct($linkedProductIds = array(), $productIds =
        array(), $linkTypeId = '')
    {
        $sql = "delete from catalog_product_link where 1=1";
        if (!empty($linkedProductIds)) {
            $sql .= " AND linked_product_id in (" . implode(',', $linkedProductIds) . ")";
        }
        if (!empty($productIds)) {
            $sql .= " AND product_id in (" . implode(',', $productIds) . ")";
        }

        if (!empty($linkTypeId)) {
            $sql .= " AND link_type_id = '$linkTypeId'";
        }
        $this->log($sql, 'clearLinkedProduct');
        $this->db->query($sql);
    }

    //写入数据库
    private function linkProduct($linkedProductId, $productId, $linkTypeId)
    {
        $this->setLinkedCount($productId);
        $sql = "insert into catalog_product_link(product_id,linked_product_id,link_type_id) values ($productId,$linkedProductId,'$linkTypeId')";
        $this->log($sql, 'linkProduct');
        $this->db->query($sql);
        $linkId=$this->db->lastInsertId();
        $this->setPosition($linkId,$linkTypeId,$this->count);
        $this->log($this->countName.' '.$this->count,'count');
        return 1;
    }

    //检查是不是已经写过，是不是写的数量太多了
    private function isNotLinkedProduct($linkedProductId, $productId, $linkTypeId, $maxNum = 0)
    {
        if ($maxNum > 0) {
            $num = $this->getLinkedCount($productId,$linkTypeId);
            if ($maxNum <= $num)
                return 2;
        }
        $sql = "select link_id from catalog_product_link where product_id='$productId' and linked_product_id='$linkedProductId' and link_type_id=$linkTypeId limit 1";
        $this->log($sql, 'isNotLinkedProduct');
        $rs = $this->db->fetchOne($sql);
        if (!empty($rs))
            return 0;
        
        return 1;
    }
    
    private function getLinkedCount($productId,$linkTypeId){
         if($this->countName==$productId){
            return $this->count;
         }else{
            $this->countName=$productId;
            $sql = "select count(*) as num from catalog_product_link where product_id='$productId' and link_type_id=$linkTypeId";
           // $this->log($sql, 'isNotLinkedProduct2');
            $num = $this->db->fetchOne($sql);
            $this->count = $num;
         }
         return $this->count;  
    }
    
    private function setLinkedCount($productId){
        if($this->countName==$productId){
            $this->count++;
        }
    }

    private function setAllCatalogIds($catalogIds)
    {
        if (is_array($catalogIds)) {
            $this->catalogIds = $catalogIds;
        } else {
            $this->catalogIds[] = $catalogIds;
        }
       // $this->log($catalogIds, 'setAllCatalogIds');
       // $this->log($this->catalogIds, 'setAllCatalogIds');
        return $this->catalogIds;
    }

    //获得所有分类
    private function getAllCatalogIds()
    {
        if (!$this->catalogIds) {
            $sql = "select entity_id from catalog_category_entity where `entity_id`>=2 and entity_type_id=3";
           // $this->log($sql, 'getAllCatalogIds');
            $rs = $this->db->fetchAll($sql);
            $result = array();
            foreach ($rs as $r) {
                $result[] = $r['entity_id'];
            }
            $this->catalogIds = $result;
        }
        return $this->catalogIds;
    }

    //根据分类名获取分类IDs
    protected function getCatalogIdsByCatalog($str, $include = true)
    {
        $sql = "select entity_id from catalog_category_entity_varchar where `value`='$str' and entity_type_id=3 limit 1";
      //  $this->log($sql, 'getCatalogIdsByCatalog');
        $catalogId = $this->db->fetchOne($sql);
        if (!$include) {
            $catalogIds = $this->getAllCatalogIds();
            foreach ($catalogIds as $_key => $_catalogId) {
                if ($_catalogId == $catalogId) {
                    unset($catalogIds[$_key]);
                }
            }
            $this->setAllCatalogIds($catalogIds);
        } else {
            $this->setAllCatalogIds($catalogId);
        }
        return $this->catalogIds;
    }

    //根据分类IDs获得产品IDs
    private function getProductIdsByCatalogIds($catalogIds)
    {
        $str = '';
        if (is_array($catalogIds)) {
            if (count($catalogIds) > 1) {
                $str = " in (" . implode(',', $catalogIds) . ")";
            } else {
                $catalogId = $catalogIds[0];
                $str = " = '$catalogId'";
            }
        } else {
            $str = " = '$catalogIds'";
        }
        $sql = "select product_id from catalog_category_product where `category_id` $str group by product_id";
      //  $this->log($sql, 'getProductIdsByCatalogIds');
        $rs = $this->db->fetchAll($sql);
        $result = array();
        if (!empty($rs)) {
            foreach ($rs as $r) {
                $result[] = $r['product_id'];
            }
        }
        return $result;
    }


    //获取sku对应的
    private function getProductIdBySku($str)
    {
        $sql = "select entity_id from catalog_product_entity where `sku`='$str' and entity_type_id=4 limit 1";
      //  $this->log($sql, 'getProductIdBySku');
        $rs = $this->db->fetchOne($sql);
        return $rs;
    }


    //获取url对应的商品
    private function getProductIdByUrl($str)
    {
        $sql = "select entity_id from catalog_product_entity_varchar where `value`='$str' and entity_type_id=4 limit 1";
      //  $this->log($sql, 'getProductIdByUrl');
        $rs = $this->db->fetchOne($sql);
        return $rs;
    }


    //获取url对应的分类
    private function getCatalogIdByUrlOrName($str)
    {
        $sql = "select entity_id from catalog_category_entity_varchar where `value`='$str' and entity_type_id=3 limit 1";
     //   $this->log($sql, 'getCatalogIdByUrlOrName');
        $rs = $this->db->fetchOne($sql);
        return $rs;
    }
    
    //获得位置属性id
    private function getAttributeId($linkTypeId){
        if($this->attributeTypeId==0){
            $sql="select product_link_attribute_id from catalog_product_link_attribute where product_link_attribute_code='{$this->attributeCode}' and link_type_id='$linkTypeId' limit 1";
            $this->attributeTypeId=$this->db->fetchOne($sql);
        }
        return $this->attributeTypeId;
    }
    
    private function getPositionCount($linkId){
        if($this->attributePosition==0){
            $sql="select count(*) as num from catalog_product_link_attribute_int where link_id='$linkId' and product_link_attribute_id='{$this->attributeTypeId}' limit 1";
            $this->attributePosition=$this->db->fetchOne($sql);
        }
        return $this->attributePosition;
    }
    
    //写入位置属性
    protected function setPosition($linkId,$linkTypeId,$position=0){
        if($position==0){
        $position=(int)$this->getPositionCount($linkId);
        $position++;
        }
        $attributeTypeId=$this->getAttributeId($linkTypeId);
        $sql="insert into catalog_product_link_attribute_int set link_id='$linkId',product_link_attribute_id='{$attributeTypeId}',`value`='$position'";
        $this->db->query($sql);
    }
    
    private function _checkSku($str)
    {
        $result = array();
        $strArr = explode("\n", $str);
        foreach ($strArr as $_s) {
            $_s = trim($_s);
            if (empty($_s)) {
                continue;
            }
            if ($this->isUrl($_s)) {
                $_s = $this->clsUrlParams($_s);
                $result[$_s] = 2; //is url
            } else
                if ($this->isENStr($_s)) {
                    $result[$_s] = 2; //is name
                } else {
                    $result[$_s] = 1; //is sku
                }
        }
        return $result;
    }

    private function clsUrlParams($strs)
    {
        $_strs = explode("?", $strs);
        $_strs[0]=trim($_strs[0],'/');
        $_strs = explode('/', $_strs[0]);
        $strs = array_pop($_strs);
        $strs = str_replace('.html','',$strs);
        return $strs;
    }

    private function isUrl($strs)
    {
        if (preg_match("/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/",
            $strs) == 1) {
            return true;
        } else {
            return false;
        }
    }
    private function isENStr($strs)
    {
        if (stripos($strs, ' ') !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function tohtml($data)
    {
        $data['url'] = $_SERVER['REQUEST_URI'];
        $data['isCatalog'] = isset($data['isCatalog']) ? (($data['isCatalog']) ?
            ' checked="checked"' : '') : '';
        $data['include'] = isset($data['include']) ? (($data['include']) ?
            ' checked="checked"' : '') : ' checked="checked"';
        $data['linkTypeId1'] = isset($data['linkTypeId']) ? (($data['linkTypeId'] == 1) ?
            ' checked="checked"' : '') : '';
        $data['linkTypeId4'] = isset($data['linkTypeId']) ? (($data['linkTypeId'] == 4) ?
            ' checked="checked"' : '') : '';
        $data['linkTypeId5'] = isset($data['linkTypeId']) ? (($data['linkTypeId'] == 5) ?
            ' checked="checked"' : '') : '';
        $data['new'] = isset($data['new']) ? (($data['new']) ? ' checked="checked"' :
            '') : '';
        $data['rnd'] = isset($data['rnd']) ? (($data['rnd']) ? ' checked="checked"' :
            '') : '';
        $data['num'] = isset($data['num'])?$data['num']:'5';
        $data['dstSku'] =  isset($data['dstSku'])?$data['dstSku']:'';
        $data['srcSku'] =  isset($data['srcSku'])?$data['srcSku']:'';
        $html = '<html><style>
fieldset,div{ border-bottom:1px solid #F3F3F3; font-size:13px;  padding-bottom: 10px;
    padding-top: 10px;
line-height:16px;

}
div span{
	width: 30px;
	vertical-align: top;
}
span.hspace{ width:20px;}
textarea{
    font-size: 12px;
    width: 500px;
}
</style>
        <fieldset>
  <legend>关联产品设置</legend>
  <form id="form1" name="form1" method="post" action="' . $data['url'] . '">
    <div>
    <span>交错展示数上限：</span>
    <span>  
    <input name="num" type="text" id="num" size="10" maxlength="5" value="' . $data['num'] .'" />
    </span>0或空没有上限</div>
    <div>
    <span>附加商品:
    <textarea name="dstSku" cols="50" rows="7">' . $data['dstSku'].'</textarea>
    </span>
    </div>
    <div>
    <span>>>主商品:
    <textarea name="srcSku" cols="50" rows="7">' . $data['srcSku'] .'</textarea>
    </span>
    <input name="isCatalog" type="checkbox" id="isCatalog" value="1" ' . $data['isCatalog'] .'/>
    <label for="isCatalog"></label>
    填的是分类
    <input name="include" type="checkbox" id="include" value="1" ' . $data['include'] .'/>
    <label for="include"></label>
    包含这些分类(否则排除)
    </div>
    <div>
    <span>说明：附加商品将在主商品展示页中列在下面或旁边展示，可以在文本框中填写url或者sku码。<br>如果填的是分类，选中则包含这些分类(否则排除)</span>
    </div>
    <div>
    <span>交错展示方式：</span>
    <span>
      <input name="linkTypeId" type="radio" value="1" ' . $data['linkTypeId1'] .'/>相关商品(relation)
      &nbsp;&nbsp;&nbsp;&nbsp;
      <input name="linkTypeId" type="radio" value="4" ' . $data['linkTypeId4'] .'/>追加销售(upsell)
      &nbsp;&nbsp;&nbsp;&nbsp;
      <input name="linkTypeId" type="radio" value="5" ' . $data['linkTypeId5'] .'/>交叉销售(cross_sell)
     </span>
    </div>
    <div>
      <span>
      <input type="checkbox" name="new" value="1" ' . $data['new'] .' />全新重置</span>
       <span>
      <input type="checkbox" name="rnd" value="1" ' . $data['rnd'] .'/>是否随机取配件分类下的商品</span>
    </div>
     <div>
     <span>
		<input type="submit" name="Submit" value=" 提  交 " />
      </span>
    </div>
  </form>
</fieldset>
        </html>';
        echo $html;
    }
    protected function log($str, $sper = '')
    {
        if($this->log)
        @file_put_contents(dirname(__file__) . '/log' . date('mdHi') . '.txt', "\n\n ==== $sper ====\n" .
            print_r($str, true), FILE_APPEND);
    }
}

/**
 * @清除数据库缓存
 * @清除url重写
 */
class cleanDbCacheCatalogAndProduct{
    private $db;
    private function setDb(){
        if(is_object($this->db))return $this->db;
        $magento_bootstrap=dirname(__FILE__).'/../app/Mage.php';
        require_once$magento_bootstrap;
        Mage::app(); //加载……
        $this->db=Mage::getSingleton('core/resource')->getConnection('core_read');
        return $this->db;
    }

    private $sqls=array(
        'cleanCatalog'=>"drop table if exists `catalog_category_flat_store_%s`",
        'cleanProduct'=>"drop table if exists `catalog_product_flat_%s`",
        'cleanReviews'=>"delete from review_entity_summary where reviews_count=0",
        'cleanUrl'=>array(
           'SET FOREIGN_KEY_CHECKS = 0;',
            'TRUNCATE TABLE `core_url_rewrite`;',
            'TRUNCATE TABLE `enterprise_catalog_category_rewrite`;',
            'TRUNCATE TABLE `enterprise_catalog_product_rewrite`;',
            'TRUNCATE TABLE `enterprise_url_rewrite`;',
            'TRUNCATE TABLE `enterprise_url_rewrite_category_cl`;',
            'TRUNCATE TABLE `enterprise_url_rewrite_product_cl`;',
            'TRUNCATE TABLE `enterprise_url_rewrite_redirect_cl`;',
            'TRUNCATE TABLE `enterprise_url_rewrite_redirect_rewrite`;',
            'SET FOREIGN_KEY_CHECKS = 1;',
            ),
    );

    public function run($ctc=''){
        $this->setDb();
        $ctb='';
        $cta='';
        if($ctc=='catalog'||$ctc=='cat'||$ctc=='c'){
            $ctc = 'cleanCatalog';
        }else if($ctc=='product'||$ctc=='pro'||$ctc=='p'){
            $ctc = 'cleanProduct';
        }else if($ctc=='url'||$ctc=='u'){
            $ctc = 'cleanUrl';
        }else if($ctc=='all'){
            $ctc = 'cleanCatalog';
            $ctb='cleanProduct';
            $cta='cleanUrl';
        }
        if($ctc){
            if($ctc=='cleanUrl'||$cta=='cleanUrl'){
                $sqls = $this->sqls[$ctc];
                foreach($sqls as $sql){
                    $this->db->query($sql);
                }
                echo 'cleanUrl end.<br>';
                if($ctc=='cleanUrl')return ;
            }
            for($i=1;$i<=15;$i++){
                if(!isset($this->sqls[$ctc]))break;
             echo   $sql=vsprintf($this->sqls[$ctc],array($i));
                echo '<br>';
                $this->db->query($sql);
                if(!empty($ctb)){
                 echo   $sql2 = vsprintf($this->sqls[$ctb],array($i));
                    echo '<br>';
                    $this->db->query($sql2);
                }
            }
            echo   $sql=$this->sqls['cleanReviews'];
            echo '<br>';
            $this->db->query($sql);
        }
        cleanCache();
        echo 'the end.';
    }
}

function cleanCache(){
    $magento_bootstrap='../app/Mage.php';
    require_once$magento_bootstrap;
    umask(0);
    Mage::app(); //加载……
    Mage::app()->cleanCache(); //清空缓存……
}

//整理所有栏目
function _processAll(){
	$magento_bootstrap='../app/Mage.php';
	require_once$magento_bootstrap;
    umask(0);
	Mage::app(); //加载……
	Mage::app()->cleanCache(); //清空缓存……
//		echo '-----------------------start-------------------';
		$_processCollection = Mage::getResourceModel('index/process_collection');
		foreach ($_processCollection as $process){
//					echo '<br/>正在处理 '.$process->getIndexerCode();
//					ob_flush();flush();
					   $process->reindexEverything();
//					echo '<br/>----'.$process->getIndexerCode().'处理完毕!<br/>';
//					ob_flush();flush();
		}
//		echo '-----------------------end-------------------';
}
?>