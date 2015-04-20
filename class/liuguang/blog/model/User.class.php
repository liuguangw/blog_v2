<?php

namespace liuguang\blog\model;

/**
 *
 * @author liuguang
 *        
 */
class User {
	private $errMsg;
	/**
	 * 获取错误信息
	 * 
	 * @return string
	 */
	public function getErrMsg(){
		return $this->errMsg;
	}
	public function checkUsername($username){
		if($username==''){
			$this->errMsg='用户名不能为空';
			return false;
		}
		if(!preg_match('/^[a-z_][a-z0-9_]{0,15}$/', $username)){
			$this->errMsg='用户名只能包含小写字母、数字、下划线,且不能以数字开始,长度为1~16位';
			return false;
		}
		return true;
	}
	public function checkNickname($nickname){
		if($nickname==''){
			$this->errMsg='昵称不能为空';
			return false;
		}
		$nickLength=mb_strlen($nickname,'UTF-8');
		if($nickLength>13){
			$this->errMsg='昵称最长只能13位';
			return false;
		}
		return true;
	}
	public function checkBlogname($blogname){
		if($blogname==''){
			$this->errMsg='博客名不能为空';
			return false;
		}
		$nickLength=mb_strlen($blogname,'UTF-8');
		if($nickLength>18){
			$this->errMsg='博客名最长只能18位';
			return false;
		}
		return true;
	}
	public function checkPass($pass){
		$passLength=strlen($pass);
		if($passLength<6){
			$this->errMsg='密码长度不能短于6位';
			return false;
		}
		elseif($passLength>26){
			$this->errMsg='密码长度不能超过26位';
			return false;
		}
		else 
			return true;
	}
	public function encodePass($username,$pass){
		$salt=md5(md5('liuguang').$username);
		return md5(md5($salt.$pass).$pass);
	}
	/**
	 * 检测用户是否为博主
	 * 
	 * @param \PDO $db
	 * @param string $tablePre
	 * @param string $osid
	 * @return boolean
	 */
	public function checkAdmin(\PDO $db,$tablePre,$osid=''){
		if($osid==''){
			if(!isset($_COOKIE['osid']))
				return false;
			else 
				$osid=$_COOKIE['osid'];
		}
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'config WHERE t_key=\'pass\'' );
		$rst = $stm->fetch ();
		if ($osid != $rst ['t_value'])
			return false;
		else
			return true;
	}
}