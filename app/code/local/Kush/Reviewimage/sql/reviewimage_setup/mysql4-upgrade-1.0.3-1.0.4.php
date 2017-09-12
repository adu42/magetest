<?php 
// 新增视频播放功能
$installer = $this;
$installer->startSetup();
$tablename = $this->getTable('review/review_detail');
try{
$installer->run("ALTER TABLE `$tablename` ADD `review_video`  VARCHAR( 255 )  NULL AFTER `detail` ;");
$installer->run("ALTER TABLE `$tablename` ADD `review_video_thumb`  VARCHAR( 255 )  NULL AFTER `detail` ;");
}catch (exception $e){

}
$installer->endSetup();

 ?>