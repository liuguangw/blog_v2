<?php

namespace liuguang\blog\controller;

use liuguang\mvc\PdoDb;
use liuguang\mvc\Application;
use liuguang\mvc\FsInter;
use liuguang\mvc\FileBucket;
use liuguang\mvc\FsException;

/**
 *
 * @author liuguang
 *        
 */
class BaseController {
	protected $db = null;
	protected $fs = null;
	protected $tablePre = null;
	/**
	 * 获取博客的数据库连接对象
	 *
	 * @return \PDO
	 */
	protected function getDb() {
		if ($this->db == null) {
			$app = Application::getApp ();
			$appConfig = $app->getAppConfig ();
			try {
				$this->db = PdoDb::getConn ( $appConfig->get ( 'blogDbId' ) );
			} catch ( \PDOException $e ) {
				$app->getErrHandler ()->handle ( 500, $e->getMessage () );
			}
		}
		return $this->db;
	}
	/**
	 * 获取博客的文件存储对象
	 *
	 * @return FsInter
	 */
	protected function getFs() {
		if ($this->fs == null) {
			$app = Application::getApp ();
			$appConfig = $app->getAppConfig ();
			try {
				$this->fs = FileBucket::getFs ( $appConfig->get ( 'blogFsId' ) );
			} catch ( FsException $e ) {
				$app->getErrHandler ()->handle ( 500, $e->getMessage () );
			}
		}
		return $this->fs;
	}
	
	/**
	 * 获取博客设置的数据表前缀
	 *
	 * @return string
	 */
	protected function getTablePre() {
		if ($this->fs == null) {
			$app = Application::getApp ();
			$this->tablePre = $app->getAppConfig ()->get ( 'blogTablePre' );
		}
		return $this->tablePre;
	}
	/**
	 * 用于安装检查
	 * 
	 * @return void
	 */
	protected function checkInstall() {
		$this->getDb ();
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		if (! $appConfig->get ( 'blogInit' )) {
			$installUrl=$app->getUrlHandler()->createUrl('web/Install', 'index', array(),false);
			header('Location: '.$installUrl);
			exit();
		}
	}
}