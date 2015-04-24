<?php

namespace liuguang\blog\model;

use liuguang\mvc\Application;
use liuguang\mvc\DataMap;

/**
 * 流光的博客模板输出类
 *
 * @author liuguang
 *        
 */
class Template {
	private $tplPath;
	private $mimeType;
	private $openC;
	private $tplData;
	/**
	 * 构造方法
	 *
	 * @param string $tplName
	 *        	模板名,不需要后面的.tpl.php
	 * @param string $mimeType
	 *        	mime类型,默认值为text/html; charset=utf-8
	 */
	public function __construct($tplName, $mimeType = 'text/html; charset=utf-8') {
		$app = Application::getApp ();
		$tplPath = APP_PATH . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . $tplName . '.tpl.php';
		if (! is_file ( $tplPath )) {
			$app->getErrHandler ()->handle ( 404, 'tpl模板未找到' );
		}
		$this->tplPath = $tplPath;
		$this->mimeType = $mimeType;
		$this->openC = true;
		$tplData = array (
				'public_context' => $app->getAppConfig ()->get ( 'app_pub_context' ) 
		);
		$this->tplData = new DataMap ( $tplData );
	}
	/**
	 * 设置是否开启页面压缩
	 *
	 * @param boolean $open
	 *        	是否开启压缩
	 * @return void
	 */
	public function setCompress($open) {
		$this->openC = $open;
	}
	/**
	 * 获取模板变量映射
	 *
	 * @return DataMap
	 */
	public function getTplData() {
		return $this->tplData;
	}
	/**
	 * 获取适配当前浏览器的压缩类型
	 *
	 * @return int 0表示不支持,1表示deflate,2表示gzip
	 */
	private function getCtype() {
		if (! isset ( $_SERVER ['HTTP_ACCEPT_ENCODING'] ))
			return 0;
		if (strpos ( $_SERVER ['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false)
			return 2;
		elseif (strpos ( $_SERVER ['HTTP_ACCEPT_ENCODING'], 'deflate' ) !== false)
			return 1;
		else
			return 0;
	}
	/**
	 * 模板输出
	 *
	 * @return void
	 */
	public function display() {
		if ((! $this->openC) || (! extension_loaded ( 'zlib' )))
			$cType = 0;
		else
			$cType = $this->getCtype ();
		if ($cType > 0) {
			if ($cType == 1)
				$encodeType = 'deflate';
			else
				$encodeType = 'gzip';
			header ( 'Content-Encoding: ' . $encodeType );
			ob_start ();
		}
		header ( 'Content-Type: ' . $this->mimeType );
		$tplData = $this->getTplData ();
		include $this->tplPath;
		if ($cType > 0) {
			if ($cType == 1)
				echo gzdeflate ( ob_get_clean (), 9 );
			else
				echo gzencode ( ob_get_clean (), 9 );
		}
	}
}