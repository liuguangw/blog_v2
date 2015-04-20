<?php

namespace liuguang\blog\controller\ajax;

use liuguang\mvc\DataMap;
use liuguang\mvc\Application;
use liuguang\blog\controller\BaseController;
use liuguang\blog\model\User;

/**
 * 用于管理文章
 *
 * @author liuguang
 *        
 */
class AdminTopic extends BaseController {
	/**
	 * 处理博主发表的文章
	 *
	 * @return void json输出
	 */
	public function postAction() {
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
		$t_title = $postData->get ( 't_title', '' );
		$t_title_leng = mb_strlen ( $t_title, 'UTF-8' );
		if (($t_title_leng < 1) || ($t_title_leng > 35)) {
			$ajaxReturn ['success'] = 0;
			$ajaxReturn ['msg'] = '标题的长度限定为1~35';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$posts_type = ( int ) $postData->get ( 'posts_type', 0 );
		$stm = $db->query ( 'SELECT COUNT(*) AS s_num FROM ' . $tablePre . 'leibie WHERE t_id=' . $posts_type );
		$rst = $stm->fetch ();
		if ($rst ['s_num'] == 0) {
			$ajaxReturn ['success'] = 0;
			$ajaxReturn ['msg'] = '分类id' . $posts_type . '无效';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$tag_ids = $postData->get ( 'tag_ids', '' );
		if ($tag_ids != '') {
			if (! preg_match ( '/^\d+(,\d+)*$/', $tag_ids )) {
				$ajaxReturn ['success'] = 0;
				$ajaxReturn ['msg'] = 'tag_ids格式错误';
				echo json_encode ( $ajaxReturn );
				return;
			}
			$tag_snum = substr_count ( $tag_ids, ',' ) + 1;
			$stm = $db->query ( 'SELECT COUNT(*) AS tag_snum FROM ' . $tablePre . 'tag WHERE t_id IN (' . $tag_ids . ')' );
			$rst = $stm->fetch ();
			if ($tag_snum != $rst ['tag_snum']) {
				$ajaxReturn ['success'] = 0;
				$ajaxReturn ['msg'] = '部分标签不存在';
				echo json_encode ( $ajaxReturn );
				return;
			}
		}
		$t_contents = $postData->get ( 't_contents', '' );
		$t_contents_leng = strlen ( $t_contents );
		if ($t_contents_leng > 65535) {
			$ajaxReturn ['success'] = 0;
			$ajaxReturn ['msg'] = '帖子内容过长';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$t_prev_text = mb_substr ( strip_tags ( $t_contents ), 0, 150, 'UTF-8' );
		$t_prev_text = str_replace ( array (
				'<',
				'>' 
		), '', $t_prev_text );
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		// 插入数据
		$sql = 'INSERT INTO %s (t_title,t_content,t_prev_text,leibie_id,last_update,post_time,post_ym,view_num) VALUES (\'%s\',\'%s\',\'%s\',%d,0,%d,%d,0)';
		$sql = sprintf ( $sql, $tablePre . 'topic', addslashes ( $t_title ), addslashes ( $t_contents ), addslashes ( $t_prev_text ), $posts_type, time (), date ( 'Ym' ) );
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = 0;
			$ajaxReturn ['msg'] = '帖子内容存入数据库失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 插入标签
		if ($tag_ids != '') {
			$tagArrs = explode ( ',', $tag_ids );
			$sql1 = 'INSERT INTO ' . $tablePre . 'topic_tag (topic_id,tag_id,add_time) VALUES';
			$topic_id = $db->lastInsertId ();
			$add_time = time ();
			for($i = 0; $i < $tag_snum; $i ++) {
				if ($i != 0)
					$sql1 .= ', ';
				$sql1 .= sprintf ( '(%s,%s,%d)', $topic_id, $tagArrs [$i], $add_time );
			}
			if ($db->exec ( $sql1 ) === false) {
				$ajaxReturn ['success'] = 0;
				$ajaxReturn ['msg'] = '帖子已发表成功，但是标签插入失败';
				echo json_encode ( $ajaxReturn );
			}
		}
		echo json_encode ( $ajaxReturn );
	}
	
	/**
	 * 处理博主删除文章
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
		$user=new User();
		if (! $user->checkAdmin($db,$tablePre)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$t_id = ( int ) $postData->get ( 't_id', 0 );
		// 判断文章id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'topic WHERE t_id=' . $t_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		$stm = null;
		if ($rst ['t_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '文章不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 删除文章的标签记录
		$sql0 = 'DELETE FROM ' . $tablePre . 'topic_tag WHERE topic_id=' . $t_id;
		if ($db->exec ( $sql0 ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '删除文章的标签信息失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 删除文章记录
		$sql0 = 'DELETE FROM ' . $tablePre . 'topic WHERE t_id=' . $t_id;
		if ($db->exec ( $sql0 ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '删除文章记录失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 处理博主删除文章标签
	 *
	 * @return void json输出
	 */
	public function deleteTagAction() {
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
		$t_id = ( int ) $postData->get ( 't_id', 0 );
		// 判断文章id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'topic WHERE t_id=' . $t_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		$stm = null;
		if ($rst ['t_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '文章不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$tag_id = ( int ) $postData->get ( 'tag_id', 0 );
		// 判断标签id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'topic_tag WHERE topic_id=' . $t_id . ' AND tag_id=' . $tag_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		$stm = null;
		if ($rst ['t_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '此标签不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 删除标签
		$sql = 'DELETE FROM ' . $tablePre . 'topic_tag WHERE topic_id=' . $t_id . ' AND tag_id=' . $tag_id;
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '删除标签记录失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 处理博主添加文章标签
	 *
	 * @return void json输出
	 */
	public function addTagAction() {
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
		$t_id = ( int ) $postData->get ( 't_id', 0 );
		// 判断文章id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'topic WHERE t_id=' . $t_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		$stm = null;
		if ($rst ['t_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '文章不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$tag_id = ( int ) $postData->get ( 'tag_id', 0 );
		// 判断标签id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'topic_tag WHERE topic_id=' . $t_id . ' AND tag_id=' . $tag_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		$stm = null;
		if ($rst ['t_num'] != 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '此标签已存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 添加标签
		$sql = sprintf ( 'INSERT INTO %stopic_tag (topic_id,tag_id,add_time) VALUES (%d,%d,%d)', $tablePre, $t_id, $tag_id, time () );
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '添加标签记录失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 处理博主修改文章
	 *
	 * @return void json输出
	 */
	public function editAction() {
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
		$t_id = ( int ) $postData->get ( 't_id', 0 );
		// 判断文章id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'topic WHERE t_id=' . $t_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		$stm = null;
		if ($rst ['t_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '文章不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$t_title = $postData->get ( 't_title', '' );
		$t_title_leng = mb_strlen ( $t_title, 'UTF-8' );
		if (($t_title_leng < 1) || ($t_title_leng > 35)) {
			$ajaxReturn ['success'] = 0;
			$ajaxReturn ['msg'] = '标题的长度限定为1~35';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$posts_type = ( int ) $postData->get ( 'posts_type', 0 );
		$stm = $db->query ( 'SELECT COUNT(*) AS s_num FROM ' . $tablePre . 'leibie WHERE t_id=' . $posts_type );
		$rst = $stm->fetch ();
		if ($rst ['s_num'] == 0) {
			$ajaxReturn ['success'] = 0;
			$ajaxReturn ['msg'] = '分类id' . $posts_type . '无效';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$t_contents = $postData->get ( 't_contents', '' );
		$t_contents_leng = strlen ( $t_contents );
		if ($t_contents_leng > 65535) {
			$ajaxReturn ['success'] = 0;
			$ajaxReturn ['msg'] = '帖子内容过长';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$t_prev_text = mb_substr ( strip_tags ( $t_contents ), 0, 150, 'UTF-8' );
		$t_prev_text = str_replace ( array (
				'<',
				'>' 
		), '', $t_prev_text );
		// update
		$sql = 'UPDATE %s SET t_title=\'%s\',t_prev_text=\'%s\',t_content=\'%s\',leibie_id=%s,last_update=%d WHERE t_id=%s';
		$sql = sprintf ( $sql, $tablePre . 'topic', addslashes ( $t_title ), addslashes ( $t_prev_text ), addslashes ( $t_contents ), $posts_type, time (), $t_id );
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = 0;
			$ajaxReturn ['msg'] = '帖子内容存入数据库失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
}