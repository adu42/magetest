<?php

$installer = $this;
$installer->startSetup();

// 设置标题/描述/关键词
$installer->getConnection()->addColumn(
        $this->getTable('catalogsearch/search_query'), //table name
        'meta_title',      //column name
        'varchar(70) NULL'  //datatype definition
        );

$installer->getConnection()->addColumn(
        $this->getTable('catalogsearch/search_query'), //table name
        'keywords',      //column name
        'varchar(70) NULL'  //datatype definition
        );
$installer->getConnection()->addColumn(
        $this->getTable('catalogsearch/search_query'), //table name
        'descriptions',      //column name
        'varchar(255) NULL'  //datatype definition
        );



$installer->endSetup();
