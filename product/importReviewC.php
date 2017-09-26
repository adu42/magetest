<?php

$do=isset($_GET["do"])?$_GET["do"]:'';
$store=isset($_GET["store"])?$_GET["store"]:'';
 
 if($do=='impview'){
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
        'setDefaultStoreId1'=>"update site_review_pp set catalog=replace(`catalog`,' dresses','')",
        'setDefaultStoreId2'=>"update site_review_pp set catalog=replace(`catalog`,' Dresses','')",
        'setDefaultStoreId3'=>"update site_review_pp set catalog=replace(`catalog`,' dress','')",
        'setDefaultStoreId4'=>"update site_review_pp set catalog=replace(`catalog`,' Dress','')",
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
        echo '---review-- <br>';
        echo $this->getReviewCount();
        echo '<br>the end';
	 echo '<br>小语种请用翻译后的分类名，尽量从网站上取URL-key';
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
            
          //  print_r($this->catalogs);
            
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
      
//'setTableReviewDetail'=>"insert into review_detail(review_id,`store_id`,`title`,`detail`,`nickname`,`%s`) values('%s',1,'%s','%s','%s','%s')",
       if(!empty($this->replyColumnName)){
           $sql=vsprintf($this->sqls['setTableReviewDetail'],array($this->replyColumnName,$reviewId,$this->defaultStoreId,mysql_escape_string($review['titler']),mysql_escape_string($review['text']),mysql_escape_string($review['author']),mysql_escape_string($review['reply'])));
       }else{
           $sql=vsprintf($this->sqls['setTableReviewDetailNoReply'],array($reviewId,$this->defaultStoreId,mysql_escape_string($review['titler']),mysql_escape_string($review['text']),mysql_escape_string($review['author'])));  
       }
       $this->db->query($sql);
       $this->setTableReviewStore($reviewId,$this->defaultStoreId);
       
       $vote_count=$this->getReviewTimes($productId);
       $_vote_precet=90;
       $_count_rate=0;
       for($i=0;$i<3;$i++){
            $_rateing=rand($review['rateing'] ,5);
    		$_option_id=$i*5+$_rateing;
            $_rateing_id=$i+1;
            $_count_rate+=$_rateing;
    		$_vote_precet=ceil($_rateing/5*100);
            $_ip='218.22.27.226';
            $_iplong=2147483647;
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
        $pkeyid=$this->getSummaryByProdutId($productId);
        if(!empty($pkeyid)){
           $sql=vsprintf($this->sqls['setTableSummary'],array($review_count,$vote_count,$productId,0));    //store_id = 0
         $this->db->query($sql);
        }else{
         $sql=vsprintf($this->sqls['setTableSummaryNew'],array($review_count,$vote_count,$productId,0,1));   //store_id = 0 
         $this->db->query($sql);   
        }
        $this->setTableSummaryOther($productId,$review_count,$vote_count);
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
