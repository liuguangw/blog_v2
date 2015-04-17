<?php

namespace liuguang\blog\controller\ajax;

use liuguang\mvc\DataMap;
use liuguang\blog\view\TopicPage;

/**
 * 处理用户发表的回复,以及回复的更新获取
 *
 * @author liuguang
 *        
 */
class Topic extends BaseAdmin {
	public function doReplyAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$stm = $db->query ( 'SELECT t_value FROM ' . $tablePre . 'config WHERE t_key=\'allow_reply\'' );
		$rst = $stm->fetch ();
		if ($rst ['t_value'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '博主已关闭回复功能';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$t_user = trim ( $postData->get ( 't_user', '' ) );
		$isAdmin = $this->isAdmin ();
		if (($t_user == '') && (! $isAdmin)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '请设置您的昵称';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$t_id = ( int ) $postData->get ( 'topic_id', '' );
		// 判断文章id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'topic WHERE t_id=' . $t_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		if ($rst ['t_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '文章不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$t_contents = $postData->get ( 't_contents', '' );
		if (! $isAdmin) {
			spl_autoload_register ( array (
					'HTMLPurifierLoader',
					'loadClass' 
			) );
			$conf = \HTMLPurifier_Config::createDefault ();
			$purifier = new \HTMLPurifier ( $conf );
			$t_contents = $purifier->purify ( $t_contents );
		}
		$max_length = 2000; // 最大长度限制
		$t_length = mb_strlen ( $t_contents, 'UTF-8' );
		if ($t_length == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '请输入回复内容';
			echo json_encode ( $ajaxReturn );
			return;
		} elseif ($t_length > $max_length) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '回复内容太长了';
			echo json_encode ( $ajaxReturn );
			return;
		}
		if ($isAdmin) {
			$t_user = '';
			$is_admin_post = 1;
		} else
			$is_admin_post = 0;
		$sql = sprintf ( 'INSERT INTO %sreply (topic_id,t_user,is_admin_post,t_content,post_time) VALUES (%s,\'%s\',%d,\'%s\',%d)', $tablePre, $t_id, addslashes ( $t_user ), $is_admin_post, addslashes ( $t_contents ), time () );
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '回复内容存入数据库失败';
		} else
			$ajaxReturn ['success'] = true;
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 获取最新的回复列表
	 */
	public function loadReplyAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$postData = new DataMap ( $_POST );
		$t_id = ( int ) $postData->get ( 'topic_id', '' );
		// 判断文章id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'topic WHERE t_id=' . $t_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		if ($rst ['t_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '文章不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$topicV = new TopicPage ( $db, $tablePre, $t_id );
		$ajaxReturn ['msg'] = $topicV->getReplyList ( $t_id );
		echo json_encode ( $ajaxReturn );
	}
}