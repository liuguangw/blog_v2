<?php

namespace liuguang\blog\controller\ajax;

use liuguang\blog\model\User;
use liuguang\mvc\DataMap;
use liuguang\mvc\Application;
use liuguang\blog\controller\BaseController;

/**
 *
 * @author liuguang
 *        
 */
class Install extends BaseController {
	/**
	 * 检测某行的输入
	 *
	 * @return void
	 */
	public function indexAction() {
		$userModel = new User ();
		$postData = new DataMap ( $_POST );
		$input_id = $postData->get ( 'input_id', '' );
		$input_val = $postData->get ( 'input_val', '' );
		$result = array (
				'success' => true 
		);
		switch ($input_id) {
			case 'username' :
				if (! $userModel->checkUsername ( $input_val ))
					$result = array (
							'success' => false,
							'msg' => $userModel->getErrMsg () 
					);
				break;
			case 'nickname' :
				if (! $userModel->checkNickname ( $input_val ))
					$result = array (
							'success' => false,
							'msg' => $userModel->getErrMsg () 
					);
				break;
			case 'blogname' :
				if (! $userModel->checkBlogname ( $input_val ))
					$result = array (
							'success' => false,
							'msg' => $userModel->getErrMsg () 
					);
				break;
			case 'pass1' :
				if (! $userModel->checkPass ( $input_val ))
					$result = array (
							'success' => false,
							'msg' => $userModel->getErrMsg () 
					);
				break;
			case 'pass2' :
				if (! $userModel->checkPass ( $input_val ))
					$result = array (
							'success' => false,
							'msg' => $userModel->getErrMsg () 
					);
				break;
			default :
				$result = array (
						'success' => false,
						'msg' => '未知的检测项' 
				);
		}
		header ( 'Content-Type: application/json' );
		echo json_encode ( $result );
	}
	/**
	 * 检测用户提交的表单
	 *
	 * @param DataMap $postData        	
	 * @param User $userModel        	
	 * @return array
	 */
	private function checkAll(DataMap $postData, User $userModel) {
		$result = array (
				'success' => true 
		);
		if (! $userModel->checkUsername ( $postData->get ( 'username', '' ) )) {
			$result = array (
					'success' => false,
					'msg' => $userModel->getErrMsg () 
			);
			return $result;
		}
		if (! $userModel->checkNickname ( $postData->get ( 'nickname', '' ) )) {
			$result = array (
					'success' => false,
					'msg' => $userModel->getErrMsg () 
			);
			return $result;
		}
		if (! $userModel->checkBlogname ( $postData->get ( 'blogname', '' ) )) {
			$result = array (
					'success' => false,
					'msg' => $userModel->getErrMsg () 
			);
			return $result;
		}
		if (! $userModel->checkPass ( $postData->get ( 'pass1', '' ) )) {
			$result = array (
					'success' => false,
					'msg' => $userModel->getErrMsg () 
			);
			return $result;
		}
		if ($postData->get ( 'pass1', '' ) != $postData->get ( 'pass2', '' )) {
			$result = array (
					'success' => false,
					'msg' => '两次输入的密码不一致' 
			);
			return $result;
		}
		return $result;
	}
	
	/**
	 * 检测安装的所有输入,并尝试安装
	 *
	 * @return void
	 */
	public function doAction() {
		header ( 'Content-Type: application/json' );
		// 判断是否已经安装
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		$blogInit = $appConfig->get ( 'blogInit' );
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		if ($blogInit) {
			$result = array (
					'success' => false,
					'msg' => '博客已安装,若需要重装,请把配置文件中的blogInit的值改为false' 
			);
			echo json_encode ( $result );
			return;
		}
		// 检测输入
		$userModel = new User ();
		$postData = new DataMap ( $_POST );
		$result = $this->checkAll ( $postData, $userModel );
		if (! $result ['success']) {
			echo json_encode ( $result );
			return;
		}
		// 执行安装
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$result ['success'] = $this->execSql ( $postData, $db, $userModel, $tablePre );
		if (! $result ['success']) {
			$errInfo = $db->errorInfo ();
			$result ['msg'] = '数据导入出错:' . $errInfo [2];
		}
		echo json_encode ( $result );
	}
	/**
	 * 将数据导入数据库
	 *
	 * @param DataMap $postData
	 *        	用户提交的数据对象
	 * @param \PDO $db
	 *        	数据库对象
	 * @param User $userModel
	 *        	处理用户数据的对象
	 * @param string $tablePre
	 *        	博客的数据表前缀
	 * @return boolean 若全部导入成功则为true,否则为false
	 */
	private function execSql(DataMap $postData, \PDO $db, User $userModel, $tablePre) {
		
		// 文章类别表
		if ($db->exec ( 'DROP TABLE IF EXISTS ' . $tablePre . 'leibie' ) === false)
			return false;
		if ($db->exec ( 'CREATE TABLE ' . $tablePre . 'leibie(
				t_id int UNSIGNED NOT NULL AUTO_INCREMENT,
				t_name varchar(255) NOT NULL,
				create_time int UNSIGNED NOT NULL,
				PRIMARY KEY (t_id),
				UNIQUE (t_name)
		)' ) === false)
			return false;
			// 标签类别表
		if ($db->exec ( 'DROP TABLE IF EXISTS ' . $tablePre . 'tag' ) === false)
			return false;
		if ($db->exec ( 'CREATE TABLE ' . $tablePre . 'tag(
				t_id int UNSIGNED NOT NULL AUTO_INCREMENT,
				t_name varchar(255) NOT NULL,
				create_time int UNSIGNED NOT NULL,
				PRIMARY KEY (t_id),
				UNIQUE (t_name)
		)' ) === false)
			return false;
			// 文章标签表
		if ($db->exec ( 'DROP TABLE IF EXISTS ' . $tablePre . 'topic_tag' ) === false)
			return false;
		if ($db->exec ( 'CREATE TABLE ' . $tablePre . 'topic_tag(
				index_id int UNSIGNED NOT NULL AUTO_INCREMENT,
				topic_id int UNSIGNED NOT NULL,
				tag_id int UNSIGNED NOT NULL,
				add_time int UNSIGNED NOT NULL,
				PRIMARY KEY (index_id),
				UNIQUE (topic_id,tag_id)
		)' ) === false)
			return false;
			// 博客文章表
		if ($db->exec ( 'DROP TABLE IF EXISTS ' . $tablePre . 'topic' ) === false)
			return false;
		if ($db->exec ( 'CREATE TABLE ' . $tablePre . 'topic(
				t_id int UNSIGNED NOT NULL AUTO_INCREMENT,
				t_title varchar(35) NOT NULL,
				t_content text NOT NULL,
				t_prev_text varchar(200) NOT NULL,
				leibie_id int UNSIGNED NOT NULL,
				last_update int UNSIGNED NOT NULL,
				post_time int UNSIGNED NOT NULL,
				post_ym int UNSIGNED NOT NULL,
				view_num int UNSIGNED NOT NULL,
				PRIMARY KEY (t_id)
		)' ) === false)
			return false;
			// 文章评论表
		if ($db->exec ( 'DROP TABLE IF EXISTS ' . $tablePre . 'reply' ) === false)
			return false;
		if ($db->exec ( 'CREATE TABLE ' . $tablePre . 'reply(
				t_id int UNSIGNED NOT NULL AUTO_INCREMENT,
				topic_id int UNSIGNED NOT NULL,
				t_user varchar(20) NOT NULL,
				is_admin_post tinyint(1) NOT NULL,
				t_content text NOT NULL,
				post_time int UNSIGNED NOT NULL,
				PRIMARY KEY (t_id)
		)' ) === false)
			return false;
			// 博客中文件管理处上传的文件表
		if ($db->exec ( 'DROP TABLE IF EXISTS ' . $tablePre . 'blog_upload' ) === false)
			return false;
		if ($db->exec ( 'CREATE TABLE ' . $tablePre . 'blog_upload(
				index_id int UNSIGNED NOT NULL AUTO_INCREMENT,
				obj_name varchar(500) NOT NULL,
				obj_beizhu varchar(500) NOT NULL,
				add_time int UNSIGNED NOT NULL,
				PRIMARY KEY (index_id)
		)' ) === false)
			return false;
			// 留言表
		if ($db->exec ( 'DROP TABLE IF EXISTS ' . $tablePre . 'liuyan' ) === false)
			return false;
		if ($db->exec ( 'CREATE TABLE ' . $tablePre . 'liuyan(
				t_id int UNSIGNED NOT NULL AUTO_INCREMENT,
				t_user varchar(20) NOT NULL,
				is_admin_post tinyint(1) NOT NULL,
				t_content text NOT NULL,
				post_time int UNSIGNED NOT NULL,
				PRIMARY KEY (t_id)
		)' ) === false)
			return false;
			// 博客配置表
		if ($db->exec ( 'DROP TABLE IF EXISTS ' . $tablePre . 'config' ) === false)
			return false;
		if ($db->exec ( 'CREATE TABLE ' . $tablePre . 'config(
				t_key varchar(255) NOT NULL,
				t_value varchar(2000) NOT NULL,
				PRIMARY KEY (t_key)
		)' ) === false)
			return false;
			//友情链接表
		if ($db->exec ( 'DROP TABLE IF EXISTS ' . $tablePre . 'links' ) === false)
			return false;
		if ($db->exec ( 'CREATE TABLE ' . $tablePre . 'links(
				t_id int UNSIGNED NOT NULL AUTO_INCREMENT,
				t_name varchar(30) NOT NULL,
				t_url varchar(90) NOT NULL,
				t_color varchar(10) NOT NULL,
				PRIMARY KEY (t_id),
				UNIQUE (t_url)
		)' ) === false)
			return false;
			// 初始数据导入。。。
		$sql = 'INSERT INTO %sconfig(t_key,t_value) VALUES (\'username\',\'%s\'),(\'pass\',\'%s\'),(\'blogname\',\'%s\'),(\'nickname\',\'%s\'),(\'install_time\',\'%d\')';
		if ($db->exec ( sprintf ( $sql, $tablePre, $_POST ['username'], $userModel->encodePass ( $_POST ['username'], $_POST ['pass1'] ), addslashes ( $_POST ['blogname'] ), addslashes ( $_POST ['nickname'] ), time () ) ) === false)
			return false;
		$sql = 'INSERT INTO %sconfig(t_key,t_value) VALUES (\'description\',\'本博客程序由流光开发制作\'),(\'blog_bottom\',\'Powered by liuguang\'),(\'descr_color\',\'#666666\'),(\'bg_img\',\'\'),(\'top_img\',\'\'),(\'touxiang_img\',\'\'),(\'allow_reply\',\'1\'),(\'allow_liuyan\',\'1\'),(\'open_compress\',\'1\'),(\'blogname_color\',\'#0B0BEE\'),(\'nav_color\',\'#90DDD7\'),(\'nav_active_color\',\'#333\'),(\'blog_keywords\',\'流光博客,php\'),(\'abouts\',\'流光博客。。。。。。。。。。。。。。。。\'),(\'search_abouts\',\'本站程序是由流光开发的php开源博客,开源免费,而且十分方便移植\')';
		if ($db->exec ( sprintf ( $sql, $tablePre ) ) === false)
			return false;
			// 导入默认分类
		$sql = 'INSERT INTO %sleibie(t_id,t_name,create_time) VALUES (1,\'默认分类\',%d)';
		if ($db->exec ( sprintf ( $sql, $tablePre, time () ) ) === false)
			return false;
			// 导入默认文章
		$postTime = time ();
		$postYm = date ( 'Ym' );
		$t_content = '<p>
    <span style="color: rgb(49, 133, 155);">如果你看到了这篇文章，那么说明博客已经安装成功了。</span>
</p>';
		$t_prev_text = '如果你看到了这篇文章，那么说明博客已经安装成功了。';
		$sql = 'INSERT INTO %stopic(t_id,t_title,t_content,t_prev_text,leibie_id,last_update,post_time,post_ym,view_num) VALUES (1,\'%s\',\'%s\',\'%s\',1,0,%d,%d,0)';
		if ($db->exec ( sprintf ( $sql, $tablePre, '你好，世界', $t_content, $t_prev_text, $postTime, $postYm ) ) === false)
			return false;
		return true;
		//导入友情链接
		$sql = 'INSERT INTO %slinks(t_name,t_url,t_color) VALUES (\'流光博客\',\'%s\',\'##A00\')';
		if ($db->exec ( sprintf ( $sql, $tablePre,'http://liusapp.vipsinaapp.com' ) ) === false)
			return false;
	}
}