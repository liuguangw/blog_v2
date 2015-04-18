<?php
$config = array ();
$config ['errHandler'] = 'liuguang\\mvc\\MvcErrHandler';
$config ['urlHandler'] = 'liuguang\\mvc\\MvcUrlHandler';//liuguang\blog\model\StaticUrlHandler
$config ['dblist'] = array (
		0 => array (
				'dsn' => 'mysql:host=localhost;port=3306;dbname=test',
				'username' => 'root',
				'password' => 'root',
				'options' => array (
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' 
				) 
		) 
);
$config ['fslist'] = array (
		0 => array (
				'type' => 'Local',
				'config' => array (
						'bucketName' => 'blog' 
				) 
		) 
);
$config ['extClass'] = array ();
$config ['controllerNs'] = 'liuguang\\blog\\controller';
$config ['cKey'] = 'c';
$config ['aKey'] = 'a';
$config ['defaultC'] = 'Index';
$config ['defaultA'] = 'index';
$config ['404C'] = 'Err404';
// ------
$config ['blogInit'] = false;
$config ['blogDbId'] = 0;
$config ['blogFsId'] = 0;
$config ['blogTablePre'] = 'pre_';
$config ['timeZone'] = 'Asia/Chongqing';
$config ['urlMap'] = array (
		'web/BlogList/index' => array (
				'page' 
		),
		'web/TocType/index' => array (
				't_id',
				'page' 
		),
		'web/Tag/index' => array (
				't_id',
				'page' 
		),
		'web/TocArch/index' => array (
				't_id',
				'page' 
		),
		'web/BlogTypes/index' => array (
				'page' 
		),
		'web/BlogArchs/index' => array (
				'page' 
		),
		'web/BlogAdmin/editTopic' => array (
				't_id',
				'page' 
		),
		'web/BlogAdmin/files' => array (
				'page' 
		),
		'web/Topic/index' => array (
				't_id' 
		),
		'web/BlogLiuyan/index' => array (
				'page' 
		) 
);