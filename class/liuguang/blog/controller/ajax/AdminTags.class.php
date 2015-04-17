<?php

namespace liuguang\blog\controller\ajax;

use liuguang\mvc\DataMap;

/**
 * 管理标签和分类
 *
 * @author liuguang
 *        
 */
class AdminTags extends BaseAdmin {
	/**
	 * 处理标签或者分类的修改
	 *
	 * @return void json输出
	 */
	public function updateAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		if (! $this->isAdmin ()) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$tagType = $postData->get ( 't_type', 0 );
		if (($tagType != 1) && ($tagType != 2)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = 't_type参数错误';
			echo json_encode ( $ajaxReturn );
			return;
		}
		if ($tagType == 1) {
			$t_str = '标签';
			$tableName = $this->getTablePre () . 'tag';
		} else {
			$t_str = '分类';
			$tableName = $this->getTablePre () . 'leibie';
		}
		$tagId = ( int ) $postData->get ( 't_id', 0 );
		if (! $this->tagIdExists ( $tagId, $tagType )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $t_str . 'id不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$tagName = $postData->get ( 't_name', '' );
		$tagLength = mb_strlen ( $tagName, 'UTF-8' );
		if ($tagLength < 1) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $t_str . '名称不能为空';
			echo json_encode ( $ajaxReturn );
			return;
		} elseif ($tagLength > 18) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $t_str . '名称不能超过18个字符';
			echo json_encode ( $ajaxReturn );
			return;
		} elseif ($this->tagNameExists ( $tagName, $tagType )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '此' . $t_str . '名称已存在,不能重复';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$sql = 'UPDATE ' . $tableName . ' SET t_name=\'' . addslashes ( $tagName ) . '\' WHERE t_id=' . $tagId;
		if ($this->getDb ()->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '更新' . $t_str . '名称时,执行SQL语句失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 处理标签或者分类的添加
	 *
	 * @return void json输出
	 */
	public function addAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		if (! $this->isAdmin ()) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$tagType = $postData->get ( 't_type', 0 );
		if (($tagType != 1) && ($tagType != 2)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = 't_type参数错误';
			echo json_encode ( $ajaxReturn );
			return;
		}
		if ($tagType == 1) {
			$t_str = '标签';
			$tableName = $this->getTablePre () . 'tag';
		} else {
			$t_str = '分类';
			$tableName = $this->getTablePre () . 'leibie';
		}
		$tagName = $postData->get ( 't_name', '' );
		$tagLength = mb_strlen ( $tagName, 'UTF-8' );
		if ($tagLength < 1) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $t_str . '名称不能为空';
			echo json_encode ( $ajaxReturn );
			return;
		} elseif ($tagLength > 18) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $t_str . '名称不能超过18个字符';
			echo json_encode ( $ajaxReturn );
			return;
		} elseif ($this->tagNameExists ( $tagName, $tagType )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '此' . $t_str . '名称已存在,不能重复';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$sql = 'INSERT INTO ' . $tableName . '(t_name,create_time) VALUES (\'' . addslashes ( $tagName ) . '\',' . time () . ')';
		if ($this->getDb ()->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '添加' . $t_str . '名称时,执行SQL语句失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 处理标签或者分类的删除
	 *
	 * @return void json输出
	 */
	public function deleteAction() {
		header ( 'Content-Type: application/json' );
		$ajaxReturn = array (
				'success' => true 
		);
		if (! $this->isAdmin ()) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '只有博主才能进行此操作';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$postData = new DataMap ( $_POST );
		$tagType = $postData->get ( 't_type', 0 );
		if (($tagType != 1) && ($tagType != 2)) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = 't_type参数错误';
			echo json_encode ( $ajaxReturn );
			return;
		}
		if ($tagType == 1) {
			$t_str = '标签';
			$tableName = $this->getTablePre () . 'tag';
		} else {
			$t_str = '分类';
			$tableName = $this->getTablePre () . 'leibie';
		}
		$tagId = ( int ) $postData->get ( 't_id', 0 );
		if (! $this->tagIdExists ( $tagId, $tagType )) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = $t_str . 'id不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		$sql = 'DELETE FROM ' . $tableName . ' WHERE t_id=' . $tagId;
		if ($this->getDb ()->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '删除' . $t_str . '时,执行SQL语句失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
	/**
	 * 判断标签或者分类id是否存在
	 *
	 * @param int $tagId
	 *        	标签id或者分类id
	 * @param int $tagType
	 *        	1表示标签,2表示分类
	 * @return boolean
	 */
	private function tagIdExists($tagId, $tagType) {
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$sql = 'SELECT COUNT(*) AS s_num FROM ' . $tablePre;
		if ($tagType == 1)
			$sql .= 'tag';
		else
			$sql .= 'leibie';
		$sql .= (' WHERE t_id=' . $tagId);
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		return ($rst ['s_num'] != 0);
	}
	/**
	 * 判断标签或者分类名称是否存在
	 *
	 * @param int $tagName
	 *        	标签或者分类的名称
	 * @param int $tagType
	 *        	1表示标签,2表示分类
	 * @return boolean
	 */
	private function tagNameExists($tagName, $tagType) {
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$sql = 'SELECT COUNT(*) AS s_num FROM ' . $tablePre;
		if ($tagType == 1)
			$sql .= 'tag';
		else
			$sql .= 'leibie';
		$sql .= (' WHERE t_name=\'' . addslashes ( $tagName ) . '\'');
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		return ($rst ['s_num'] != 0);
	}
}