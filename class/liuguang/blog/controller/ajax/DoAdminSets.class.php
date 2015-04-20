<?php

namespace liuguang\blog\controller\ajax;

use liuguang\mvc\DataMap;
use liuguang\blog\model\User;
use liuguang\blog\controller\BaseController;

/**
 * 用于处理博客设置的提交
 *
 * @author liuguang
 *        
 */
class DoAdminSets extends BaseController {
	/**
	 * 博客基础设置
	 *
	 * @return void 输出json
	 */
	public function commonAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user=new User();
		if (! $user->checkAdmin($db,$tablePre)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$inputs = array (
				'nickname',
				'blogname',
				'blogname_color',
				'description',
				'descr_color',
				'nav_color',
				'nav_active_color',
				'blog_keywords',
				'abouts',
				'blog_bottom'
		);
		if (! $user->checkNickname ( $postData->get ( 'nickname', '' ) )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $user->getErrMsg ();
			echo json_encode ( $ajaxReturn );
			return;
		}
		if (! $user->checkBlogname ( $postData->get ( 'blogname', '' ) )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $user->getErrMsg ();
			echo json_encode ( $ajaxReturn );
			return;
		}
		foreach ( $inputs as $t_key ) {
			$sql = 'UPDATE ' . $tablePre . 'config SET t_value=\'' . addslashes ( $postData->get ( $t_key ) ) . '\' WHERE t_key=\'' . $t_key . '\'';
			if ($db->exec ( $sql ) === false) {
				$ajaxReturn ['success'] = false;
				$ajaxReturn ['msg'] = '更新设置时,sql语句执行失败.';
				echo json_encode ( $ajaxReturn );
				return;
			}
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 博客URL设置
	 *
	 * @return void 输出json
	 */
	public function urlAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user=new User();
		if (! $user->checkAdmin($db,$tablePre)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$inputs = array (
				'bg_img',
				'top_img',
				'touxiang_img' 
		);
		foreach ( $inputs as $t_key ) {
			$sql = 'UPDATE ' . $tablePre . 'config SET t_value=\'' . addslashes ( $postData->get ( $t_key ) ) . '\' WHERE t_key=\'' . $t_key . '\'';
			if ($db->exec ( $sql ) === false) {
				$ajaxReturn ['success'] = false;
				$ajaxReturn ['msg'] = '更新URL设置时,sql语句执行失败.';
				echo json_encode ( $ajaxReturn );
				return;
			}
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 功能权限开关
	 *
	 * @return void 输出json
	 */
	public function gnAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user=new User();
		if (! $user->checkAdmin($db,$tablePre)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$inputs = array (
				'open_compress',
				'allow_liuyan',
				'allow_reply' 
		);
		foreach ( $inputs as $t_key ) {
			$t_value = ( int ) $postData->get ( $t_key );
			if (($t_value != 0) && ($t_value != 1)) {
				$ajaxReturn ['success'] = false;
				$ajaxReturn ['msg'] = '传入的设置非法';
				echo json_encode ( $ajaxReturn );
				return;
			}
			$sql = 'UPDATE ' . $tablePre . 'config SET t_value=\'' . $t_value . '\' WHERE t_key=\'' . $t_key . '\'';
			if ($db->exec ( $sql ) === false) {
				$ajaxReturn ['success'] = false;
				$ajaxReturn ['msg'] = '更新功能设置时,sql语句执行失败.';
				echo json_encode ( $ajaxReturn );
				return;
			}
		}
		echo json_encode ( $ajaxReturn );
	}
	
	/**
	 * 修改用户名密码
	 *
	 * @return void 输出json
	 */
	public function passAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user=new User();
		if (! $user->checkAdmin($db,$tablePre)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$username = $postData->get ( 'username', '' );
		$pass = $postData->get ( 'pass', '' );
		$pass1 = $postData->get ( 'pass1', '' );
		$pass2 = $postData->get ( 'pass2', '' );
		if (! $user->checkUsername ( $username )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $user->getErrMsg ();
			echo json_encode ( $ajaxReturn );
			return;
		}
		if (! $user->checkPass ( $pass1 )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $user->getErrMsg ();
			echo json_encode ( $ajaxReturn );
			return;
		}
		if ($pass1 != $pass2) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '两次输入的密码不一致';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 验证原始密码
		$data = array ();
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'config WHERE t_key IN(\'pass\',\'username\')' );
		while ( $tmp = $stm->fetch () ) {
			$data [$tmp ['t_key']] = $tmp ['t_value'];
		}
		$stm = null;
		if ($user->encodePass ( $data ['username'], $pass ) != $data ['pass']) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '原始密码不正确';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$newPass = $user->encodePass ( $username, $pass1 );
		$sqls = array ();
		$sqls [0] = 'UPDATE ' . $tablePre . 'config SET t_value=\'' . $username . '\' WHERE t_key=\'username\'';
		$sqls [1] = 'UPDATE ' . $tablePre . 'config SET t_value=\'' . $newPass . '\' WHERE t_key=\'pass\'';
		foreach ( $sqls as $sql ) {
			if ($db->exec ( $sql ) === false) {
				$ajaxReturn ['success'] = false;
				$ajaxReturn ['msg'] = '修改密码时,sql语句执行失败.';
				echo json_encode ( $ajaxReturn );
				return;
			}
		}
		echo json_encode ( $ajaxReturn );
	}
}