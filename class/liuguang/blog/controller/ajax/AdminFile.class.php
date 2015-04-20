<?php

namespace liuguang\blog\controller\ajax;

use liuguang\mvc\DataMap;
use liuguang\mvc\FsException;
use liuguang\mvc\Application;
use liuguang\blog\controller\BaseController;
use liuguang\blog\model\User;

/**
 * 处理uploadify上传的文件
 *
 * @author liuguang
 *        
 */
class AdminFile extends BaseController {
	/**
	 * 处理文件上传
	 *
	 * @return void
	 */
	public function ajaxUploadAction() {
		header ( 'Content-Type: text/plain; charset=utf-8' );
		$postData = new DataMap ( $_POST );
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user=new User();
		if (! $user->checkAdmin($db,$tablePre,$postData->get ( 'osid', '' ))) {
			$this->uploadResp ( 'danger', '只有博主才能进行此操作' );
			return;
		}
		if (! isset ( $_FILES ['Filedata'] )) {
			$this->uploadResp ( 'danger', "服务器未收到上传的文件" );
			return;
		}
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		$fs = $this->getFs ();
		$objectName = 'blog/' . date ( 'Ymd/His_' ) . rand ( 100000, 999999 );
		$obj_type = strrchr ( $_FILES ['Filedata'] ['name'], '.' );
		if ($obj_type !== false)
			$objectName .= $obj_type;
		try {
			$fs->upload ( $_FILES ['Filedata'], $objectName );
			$sql = 'INSERT INTO ' . $tablePre . 'blog_upload(obj_name,obj_beizhu,add_time) VALUES(\'%s\',\'%s\',%d)';
			$sql = sprintf ( $sql, $objectName, $objectName, time () );
			if ($db->exec ( $sql ) === false) {
				$this->uploadResp ( 'danger', '将文件信息存入数据库失败' );
				return;
			}
			$this->uploadResp ( 'success', '上传文件' . htmlspecialchars ( $_FILES ['Filedata'] ['name'] ) . '成功,新文件名为' . $objectName );
		} catch ( FsException $e ) {
			$this->uploadResp ( 'danger', $e->getMessage () );
			return;
		}
	}
	private function uploadResp($msgType, $msg) {
		echo '<div class="alert alert-', $msgType, '" role="alert">', $msg, '</div>';
	}
	/**
	 * 修改文件的备注
	 *
	 * @return void json输出
	 */
	public function updateAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$postData = new DataMap ( $_POST );
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user=new User();
		if (! $user->checkAdmin($db,$tablePre)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$f_id = ( int ) $postData->get ( 'f_id', 0 );
		// 文件记录验证
		$stm = $db->query ( 'SELECT COUNT(*) as f_num FROM ' . $tablePre . 'blog_upload WHERE index_id=' . $f_id );
		$rst = $stm->fetch ();
		$stm = null;
		if ($rst ['f_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '数据库中没有文件id：' . $f_id . '的记录';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$beizhu = $postData->get ( 'beizhu' );
		$b_length = mb_strlen ( $beizhu, 'UTF-8' );
		if (($b_length < 1) || ($b_length > 500)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '备注长度限制为0~500';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$sql = 'UPDATE ' . $tablePre . 'blog_upload SET obj_beizhu=\'' . addslashes ( $beizhu ) . '\' WHERE index_id=' . $f_id;
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '修改文件的备注到' . $beizhu . '失败';
		} else
			$ajaxReturn ['msg'] = '修改文件的备注到' . $beizhu . '成功';
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 删除文件
	 *
	 * @return void json输出
	 */
	public function deleteAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$postData = new DataMap ( $_POST );
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user=new User();
		if (! $user->checkAdmin($db,$tablePre)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$f_id = ( int ) $postData->get ( 'f_id', 0 );
		// 文件记录验证
		$stm = $db->query ( 'SELECT COUNT(*) as f_num FROM ' . $tablePre . 'blog_upload WHERE index_id=' . $f_id );
		$rst = $stm->fetch ();
		$stm = null;
		if ($rst ['f_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '数据库中没有文件id：' . $f_id . '的记录';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 获取文件名
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'blog_upload WHERE index_id=' . $_POST ['f_id'] );
		$rst = $stm->fetch ();
		$stm = null;
		$objectName = $rst ['obj_name'];
		$fs = $this->getFs ();
		try {
			$fs->delete ( $objectName );
			$sql = 'DELETE FROM ' . $tablePre . 'blog_upload WHERE index_id=' . $f_id;
			if ($db->exec ( $sql ) === false) {
				$ajaxReturn ['success'] = false;
				$ajaxReturn ['msg'] = '删除文件成功,但是数据库中删除文件记录失败';
				echo json_encode ( $ajaxReturn );
				return;
			}
		} catch ( FsException $e ) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $e->getMessage ();
			echo json_encode ( $ajaxReturn );
			return;
		}
		$ajaxReturn ['msg'] = '删除文件' . $objectName . '成功';
		echo json_encode ( $ajaxReturn );
	}
}