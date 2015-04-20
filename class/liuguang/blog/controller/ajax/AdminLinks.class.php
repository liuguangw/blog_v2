<?php

namespace liuguang\blog\controller\ajax;

use liuguang\blog\controller\BaseController;
use liuguang\blog\model\User;
use liuguang\mvc\DataMap;

/**
 * 处理友链
 *
 * @author liuguang
 *        
 */
class AdminLinks extends BaseController {
	private $msg;
	private function checkName($t_name) {
		$tLength = mb_strlen ( $t_name, 'UTF-8' );
		if ($tLength == 0) {
			$this->msg = '友链的名称不能为空';
			return false;
		} elseif ($tLength > 30) {
			$this->msg = '友链的名称不能超过30个字符';
			return false;
		} else
			return true;
	}
	private function checkUrl($t_url) {
		$tLength = strlen ( $t_url );
		if ($tLength == 0) {
			$this->msg = 'URL不能为空';
			return false;
		} elseif ($tLength > 90) {
			$this->msg = 'URL不能超过90个字符';
			return false;
		} else
			return true;
	}
	private function checkColor($t_color) {
		if (strlen ( $t_color) > 10) {
			$this->msg = '颜色不能超过10个字符';
			return false;
		} else
			return true;
	}
	private function linkUrlExists($t_url,$ex_tid=0){
		$db=$this->getDb();
		$tablePre=$this->getTablePre();
		$sql='SELECT COUNT(*) AS s_num FROM '.$tablePre.'links WHERE t_url=\''.addslashes($t_url).'\'';
		if($ex_tid!=0)
			$sql.=(' AND t_id!='.$ex_tid);
		$stm=$db->query($sql);
		$rst=$stm->fetch();
		return ($rst['s_num']!=0);
	}
	private function linkIdExists($t_id){
		$db=$this->getDb();
		$tablePre=$this->getTablePre();
		$sql='SELECT COUNT(*) AS s_num FROM '.$tablePre.'links WHERE t_id='.$t_id;
		$stm=$db->query($sql);
		$rst=$stm->fetch();
		return ($rst['s_num']!=0);
	}
	/**
	 * 处理友链的添加
	 *
	 * @return void json输出
	 */
	public function addAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user = new User ();
		if (! $user->checkAdmin ( $db, $tablePre )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$t_name = htmlspecialchars ( $postData->get ( 't_name', '' ) );
		$t_url = str_replace ( '&', '&amp;', $postData->get ( 't_url', '' ) );
		$t_color = $postData->get ( 't_color', '' );
		if (! $this->checkName ( $t_name )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $this->msg;
			echo json_encode ( $ajaxReturn );
			return;
		}
		if (! $this->checkUrl ( $t_url )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $this->msg;
			echo json_encode ( $ajaxReturn );
			return;
		}
		if (! $this->checkColor ( $t_color )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $this->msg;
			echo json_encode ( $ajaxReturn );
			return;
		}
		if($this->linkUrlExists($t_url)){
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '友链中已含有此链接';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$sql = 'INSERT INTO ' . $tablePre . 'links(t_name,t_url,t_color) VALUES (\'%s\',\'%s\',\'%s\')';
		$sql=sprintf($sql,addslashes($t_name),addslashes($t_url),addslashes($t_color));
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '添加友链时,执行SQL语句失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 处理友链的修改
	 *
	 * @return void json输出
	 */
	public function updateAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user = new User ();
		if (! $user->checkAdmin ( $db, $tablePre )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$t_id=(int)$postData->get ( 't_id', '0' );
		$t_name = htmlspecialchars ( $postData->get ( 't_name', '' ) );
		$t_url = str_replace ( '&', '&amp;', $postData->get ( 't_url', '' ) );
		$t_color = $postData->get ( 't_color', '' );
		if (! $this->checkName ( $t_name )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $this->msg;
			echo json_encode ( $ajaxReturn );
			return;
		}
		if (! $this->checkUrl ( $t_url )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $this->msg;
			echo json_encode ( $ajaxReturn );
			return;
		}
		if (! $this->checkColor ( $t_color )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $this->msg;
			echo json_encode ( $ajaxReturn );
			return;
		}
		if(!$this->linkIdExists($t_id)){
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '此友链不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		if($this->linkUrlExists($t_url,$t_id)){
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '友链中已含有此链接';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$sql = 'UPDATE ' . $tablePre . 'links SET t_name=\'%s\',t_url=\'%s\',t_color=\'%s\' WHERE t_id='.$t_id;
		$sql=sprintf($sql,addslashes($t_name),addslashes($t_url),addslashes($t_color));
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '修改友链时,执行SQL语句失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 处理友链的删除
	 *
	 * @return void json输出
	 */
	public function deleteAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user = new User ();
		if (! $user->checkAdmin ( $db, $tablePre )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$t_id=(int)$postData->get ( 't_id', '0' );
		if(!$this->linkIdExists($t_id)){
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '此友链不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$sql = 'DELETE FROM ' . $tablePre . 'links WHERE t_id='.$t_id;
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '删除友链时,执行SQL语句失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
}