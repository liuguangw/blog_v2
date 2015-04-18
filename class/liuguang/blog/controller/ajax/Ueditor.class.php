<?php

namespace liuguang\blog\controller\ajax;

use liuguang\blog\controller\BaseController;
use liuguang\mvc\Application;
use liuguang\mvc\FsException;

/**
 *
 * @author liuguang
 *        
 */
class Ueditor extends BaseController {
	private $imageFieldName;
	private $imageMaxSize;
	private $imageAllowFiles;
	private $videoAllowFiles;
	private $fileAllowFiles;
	private $fileMaxSize;
	public function __construct() {
		// 配置限制
		$this->imageFieldName = 'upfile';
		$this->imageMaxSize = 2048000;
		$this->imageAllowFiles = array (
				'.png',
				'.jpg',
				'.jpeg',
				'.gif',
				'.bmp' 
		);
		$this->videoAllowFiles = array (
				'.flv',
				'.swf',
				'.mkv',
				'.avi',
				'.rm',
				'.rmvb',
				'.mpeg',
				'.mpg',
				'.ogg',
				'.ogv',
				'.mov',
				'.wmv',
				'.mp4',
				'.webm',
				'.mp3',
				'.wav',
				'.mid' 
		);
		$this->fileAllowFiles = array (
				'.png',
				'.jpg',
				'.jpeg',
				'.gif',
				'.bmp',
				'.flv',
				'.swf',
				'.mkv',
				'.avi',
				'.rm',
				'.rmvb',
				'.mpeg',
				'.mpg',
				'.ogg',
				'.ogv',
				'.mov',
				'.wmv',
				'.mp4',
				'.webm',
				'.mp3',
				'.wav',
				'.mid',
				'.rar',
				'.zip',
				'.tar',
				'.gz',
				'.7z',
				'.bz2',
				'.cab',
				'.iso',
				'.doc',
				'.docx',
				'.xls',
				'.xlsx',
				'.ppt',
				'.pptx',
				'.pdf',
				'.txt',
				'.md',
				'.xml' 
		);
		$this->fileMaxSize = 102400000;
	}
	public function configAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array ();
		// 上传图片配置项
		$ajaxReturn ['imageActionName'] = 'uploadimage';
		$ajaxReturn ['imageFieldName'] = $this->imageFieldName;
		$ajaxReturn ['imageMaxSize'] = $this->imageMaxSize;
		$ajaxReturn ['imageAllowFiles'] = $this->imageAllowFiles;
		$ajaxReturn ['imageCompressEnable'] = true;
		$ajaxReturn ['imageCompressBorder'] = 1600;
		$ajaxReturn ['imageInsertAlign'] = 'none';
		$ajaxReturn ['imageUrlPrefix'] = '';
		$ajaxReturn ['imagePathFormat'] = '----';
		// 涂鸦图片上传配置项
		$ajaxReturn ['scrawlActionName'] = 'uploadscrawl';
		$ajaxReturn ['scrawlFieldName'] = $this->imageFieldName;
		$ajaxReturn ['scrawlPathFormat'] = '----';
		$ajaxReturn ['scrawlMaxSize'] = $this->imageMaxSize;
		$ajaxReturn ['scrawlUrlPrefix'] = '';
		$ajaxReturn ['scrawlInsertAlign'] = 'none';
		// 截图上传配置项
		$ajaxReturn ['snapscreenActionName'] = 'uploadimage';
		$ajaxReturn ['snapscreenPathFormat'] = '----';
		$ajaxReturn ['snapscreenUrlPrefix'] = '';
		$ajaxReturn ['snapscreenInsertAlign'] = 'none';
		// 上传视频配置
		$ajaxReturn ['videoActionName'] = 'uploadfile';
		$ajaxReturn ['videoFieldName'] = $this->imageFieldName;
		$ajaxReturn ['videoPathFormat'] = '----';
		$ajaxReturn ['videoUrlPrefix'] = '';
		$ajaxReturn ['videoMaxSize'] = $this->fileMaxSize;
		$ajaxReturn ['videoAllowFiles'] = $this->fileAllowFiles;
		// 上传文件配置
		$ajaxReturn ['fileActionName'] = 'uploadfile';
		$ajaxReturn ['fileFieldName'] = $this->imageFieldName;
		$ajaxReturn ['filePathFormat'] = '----';
		$ajaxReturn ['fileUrlPrefix'] = '';
		$ajaxReturn ['fileMaxSize'] = $this->fileMaxSize;
		$ajaxReturn ['fileAllowFiles'] = $this->fileAllowFiles;
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 判断用户是否为博主
	 *
	 * @return boolean
	 */
	private function isAdmin() {
		if (! isset ( $_COOKIE ['osid'] ))
			return false;
		$stm = $this->getDb ()->query ( 'SELECT * FROM ' . $this->getTablePre () . 'config WHERE t_key=\'pass\'' );
		$rst = $stm->fetch ();
		if ($_COOKIE ['osid'] != $rst ['t_value'])
			return false;
		else
			return true;
	}
	public function uploadimageAction() {
		$ajaxReturn = array (
				'state' => '',
				'url' => '',
				'title' => '',
				'original' => '',
				'type' => '',
				'size' => '' 
		);
		if (! $this->isAdmin ()) {
			$ajaxReturn ['state'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		if (! isset ( $_FILES [$this->imageFieldName] )) {
			$ajaxReturn ['state'] = '表单中没有' . $this->imageFieldName . '的内容';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postFile = $_FILES [$this->imageFieldName];
		// 大小验证
		$f_size = $postFile ['size'];
		if ($f_size > $this->imageMaxSize) {
			$ajaxReturn ['state'] = '图片文件太大';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 后缀名
		$obj_type = strrchr ( strtolower ( $postFile ['name'] ), '.' );
		if ($obj_type === false)
			$obj_type = '.ext';
		if (! in_array ( $obj_type, $this->imageAllowFiles )) {
			$ajaxReturn ['state'] = '不允许此类型的图片格式';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$app = Application::getApp ();
		$urlHandler=$app->getUrlHandler();
		date_default_timezone_set ( $app->getAppConfig ()->get ( 'timeZone' ) );
		$f_name = date ( 'His_' ) . rand ( 100000, 999999 ) . $obj_type;
		$objectName = 'topic/' . date ( 'Ymd' ) . '/' . $f_name;
		$fs = $this->getFs ();
		try {
			$fs->upload ( $postFile, $objectName );
			$ajaxReturn ['state'] = 'SUCCESS';
			if ($fs->canGetUrl ())
				$ajaxReturn ['url'] = $fs->getUrl ( $objectName );
			else
				$ajaxReturn ['url'] = $urlHandler->createUrl ( 'ajax/BlogUtil', 'showFile', array (
						'f' => $objectName 
				) );
			$ajaxReturn ['title'] = $f_name;
			$ajaxReturn ['original'] = strtolower ( $postFile ['name'] );
			$ajaxReturn ['type'] = $obj_type;
			$ajaxReturn ['size'] = $f_size;
		} catch ( FsException $e ) {
			$ajaxReturn ['state'] = $e->getMessage ();
		}
		echo json_encode ( $ajaxReturn );
	}
	public function uploadscrawlAction() {
		$ajaxReturn = array (
				'state' => '',
				'url' => '',
				'title' => '',
				'original' => '',
				'type' => '',
				'size' => '' 
		);
		if (! $this->isAdmin ()) {
			$ajaxReturn ['state'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		if (! isset ( $_POST [$this->imageFieldName] )) {
			$ajaxReturn ['state'] = '表单中没有' . $this->imageFieldName . '的内容';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$f_data = base64_decode ( $_POST [$this->imageFieldName] );
		if ($f_data === false) {
			$ajaxReturn ['state'] = '涂鸦数据解码失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 大小验证
		$f_size = strlen ( $f_data );
		if ($f_size > $this->imageMaxSize) {
			$ajaxReturn ['state'] = '图片文件太大';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 后缀名
		$obj_type = '.png';
		$app = Application::getApp ();
		$urlHandler=$app->getUrlHandler();
		date_default_timezone_set ( $app->getAppConfig ()->get ( 'timeZone' ) );
		$f_name = date ( 'His_' ) . rand ( 100000, 999999 ) . $obj_type;
		$objectName = 'topic/' . date ( 'Ymd' ) . '/' . $f_name;
		$fs = $this->getFs ();
		try {
			$fs->write ( $objectName, $f_data );
			$ajaxReturn ['state'] = 'SUCCESS';
			if ($fs->canGetUrl ())
				$ajaxReturn ['url'] = $fs->getUrl ( $objectName );
			else
				$ajaxReturn ['url'] = $urlHandler->createUrl ( 'ajax/BlogUtil', 'showFile', array (
						'f' => $objectName 
				) );
			$ajaxReturn ['title'] = $f_name;
			$ajaxReturn ['original'] = $objectName;
			$ajaxReturn ['type'] = $obj_type;
			$ajaxReturn ['size'] = $f_size;
		} catch ( FsException $e ) {
			$ajaxReturn ['state'] = $e->getMessage ();
		}
		echo json_encode ( $ajaxReturn );
	}
	public function uploadfileAction() {
		$ajaxReturn = array (
				'state' => '',
				'url' => '',
				'title' => '',
				'original' => '',
				'type' => '',
				'size' => '' 
		);
		if (! $this->isAdmin ()) {
			$ajaxReturn ['state'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		if (! isset ( $_FILES [$this->imageFieldName] )) {
			$ajaxReturn ['state'] = '表单中没有' . $this->imageFieldName . '的内容';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postFile = $_FILES [$this->imageFieldName];
		// 大小验证
		$f_size = $postFile ['size'];
		if ($f_size > $this->fileMaxSize) {
			$ajaxReturn ['state'] = '文件太大';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 后缀名
		$obj_type = strrchr ( strtolower ( $postFile ['name'] ), '.' );
		if ($obj_type === false)
			$obj_type = '.ext';
		if (! in_array ( $obj_type, $this->fileAllowFiles )) {
			$ajaxReturn ['state'] = '不允许此类型的文件格式';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$app = Application::getApp ();
		$urlHandler=$app->getUrlHandler();
		date_default_timezone_set ( $app->getAppConfig ()->get ( 'timeZone' ) );
		$f_name = date ( 'His_' ) . rand ( 100000, 999999 ) . $obj_type;
		$objectName = 'topic/' . date ( 'Ymd' ) . '/' . $f_name;
		$fs = $this->getFs ();
		try {
			$fs->upload ( $postFile, $objectName );
			$ajaxReturn ['state'] = 'SUCCESS';
			if ($fs->canGetUrl ())
				$ajaxReturn ['url'] = $fs->getUrl ( $objectName );
			else
				$ajaxReturn ['url'] = $urlHandler->createUrl ( 'ajax/BlogUtil', 'showFile', array (
						'f' => $objectName 
				) );
			$ajaxReturn ['title'] = $f_name;
			$ajaxReturn ['original'] = strtolower ( $postFile ['name'] );
			$ajaxReturn ['type'] = $obj_type;
			$ajaxReturn ['size'] = $f_size;
		} catch ( FsException $e ) {
			$ajaxReturn ['state'] = $e->getMessage ();
		}
		echo json_encode ( $ajaxReturn );
	}
}