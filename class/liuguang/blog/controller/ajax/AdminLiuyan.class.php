<?php

namespace liuguang\blog\controller\ajax;

use liuguang\blog\controller\BaseController;
use liuguang\blog\model\User;
use liuguang\mvc\DataMap;

/**
 * 处理留言的删除
 *
 * @author liuguang
 *        
 */
class AdminLiuyan extends BaseController {
	/**
	 * 处理博主删除留言
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
		$t_id = ( int ) $postData->get ( 'reply_id', 0 );
		// 判断留言id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'liuyan WHERE t_id=' . $t_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		$stm = null;
		if ($rst ['t_num'] == 0) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '此留言不存在';
			echo json_encode ( $ajaxReturn );
			return;
		}
		// 删除留言
		$sql = 'DELETE FROM ' . $tablePre . 'liuyan WHERE t_id=' . $t_id;
		if ($db->exec ( $sql ) === false) {
			$ajaxReturn ['success'] = false;
			$ajaxReturn ['msg'] = '删除留言记录失败';
			echo json_encode ( $ajaxReturn );
			return;
		}
		echo json_encode ( $ajaxReturn );
	}
}