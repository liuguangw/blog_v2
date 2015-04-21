<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;
/**
 * 博客设置的视图
 *
 * @author liuguang
 *        
 */
class AdminSets {
	private $db;
	private $tablePre;
	public function __construct(\PDO $db, $tablePre) {
		$this->db = $db;
		$this->tablePre = $tablePre;
	}
	public function getHtml(){
		$inputs = array (
                array (
                        'nickname',
                        'text',
                        '博主名称',
                        '' 
                ),
                array (
                        'blogname',
                        'text',
                        '博客名称',
                        '' 
                ),
                array (
                        'blogname_color',
                        'text',
                        '博客名称颜色',
                        '' 
                ),
                array (
                        'description',
                        'text',
                        '博客描述',
                        '' 
                ),
                array (
                        'descr_color',
                        'text',
                        '博客描述颜色',
                        '' 
                ),
                array (
                        'nav_color',
                        'text',
                        '导航条普通颜色',
                        '' 
                ),
                array (
                        'nav_active_color',
                        'text',
                        '导航条激活颜色',
                        '' 
                ) ,
                array (
                        'blog_keywords',
                        'text',
                        '搜索引擎关键字',
                        '' 
                ) 
        );
            $result = array ();
            $stm = $this->db->query ( 'SELECT * FROM ' . $this->tablePre . 'config WHERE t_key IN(\'username\',\'nickname\',\'blogname\',\'blogname_color\',\'description\',\'blog_bottom\',\'abouts\',\'search_abouts\',\'descr_color\',\'nav_color\',\'nav_active_color\',\'blog_keywords\',\'open_compress\',\'allow_liuyan\',\'allow_reply\',\'bg_img\',\'top_img\',\'touxiang_img\')' );
            while ( $tmp = $stm->fetch () ) {
                $result [$tmp ['t_key']] = $tmp ['t_value'];
            }
            $inputs [0] [3] = $result ['nickname'];
            $inputs [1] [3] = $result ['blogname'];
            $inputs [2] [3] = $result ['blogname_color'];
            $inputs [3] [3] = $result ['description'];
            $inputs [4] [3] = $result ['descr_color'];
            $inputs [5] [3] = $result ['nav_color'];
            $inputs [6] [3] = $result ['nav_active_color'];
            $inputs [7] [3] = $result ['blog_keywords'];
        $html = '<div class="panel panel-default">
  <div class="panel-heading">博客基础设置</div>
  <div class="panel-body">
    <form class="form-horizontal" id="sets_common_form">';
        $tpl = '<div class="form-group">
<label for="%s_sets" class="col-sm-3 control-label">%s</label>
<div class="col-sm-8">
<input type="%s" class="form-control" id="%s_sets" value="%s">
</div>
</div>';
        foreach ( $inputs as $node ) {
            $html .= sprintf ( $tpl, $node [0], $node [2], $node [1], $node [0], $node [3] );
        }
        $html.=('<div class="form-group">
<label for="abouts_sets" class="col-sm-3 control-label">本站说明[用于搜索引擎]</label>
<div class="col-sm-8">
<textarea id="search_abouts_sets" class="form-control" rows="4">'.htmlspecialchars($result['search_abouts']).'</textarea>
</div>
</div>');
        $html.=('<div class="form-group">
<label for="abouts_sets" class="col-sm-3 control-label">关于本站说明</label>
<div class="col-sm-8">
<textarea id="abouts_sets" class="form-control" rows="4">'.htmlspecialchars($result['abouts']).'</textarea>
</div>
</div>');
        $html.=('<div class="form-group">
<label for="blog_bottom" class="col-sm-3 control-label">博客底部代码</label>
<div class="col-sm-8">
<textarea id="blog_bottom_sets" class="form-control" rows="4">'.htmlspecialchars($result['blog_bottom']).'</textarea>
</div>
</div>');
        $html .= ('<div class="form-group">
<div class="col-sm-2 col-sm-offset-8">
    <button type="button" class="btn btn-primary">更新设置</button>
</div>
</div>');
        
        $html .= ('</form></div></div>');
        $html .= '<div class="panel panel-default">
  <div class="panel-heading">URL设置</div>
  <div class="panel-body">
    <form class="form-horizontal" id="sets_url_form">';
        $inputs1 = array (
                array (
                        'bg_img',
                        'text',
                        '背景色图片URL',
                        $result ['bg_img']
                ),
                array (
                        'top_img',
                        'text',
                        '背景图片URL',
                        $result ['top_img']
                ),
                array (
                        'touxiang_img',
                        'text',
                        '头像URL',
                        $result['touxiang_img']
                )
        );
        foreach ( $inputs1 as $node ) {
            $html .= sprintf ( $tpl, $node [0], $node [2], $node [1], $node [0], $node [3] );
        }
        
        $html .= ('<div class="form-group">
<div class="col-sm-2 col-sm-offset-8">
    <button type="button" class="btn btn-primary">修改设置</button>
</div>
</div>');
        $html .= ('</form></div></div>');
        $inputs2 = array (
                array (
                        'open_compress',
                        '页面压缩',
                        array('关闭压缩','开启压缩'),
                        $result['open_compress']
                ),
                array (
                        'allow_liuyan',
                        '游客留言',
                        array('禁止留言','允许留言'),
                        $result['allow_liuyan']
                ),
                array (
                        'allow_reply',
                        '文章评论',
                        array('禁止评论','允许评论'),
                        $result['allow_reply']
                )
        );
        $html .= '<div class="panel panel-default">
  <div class="panel-heading">功能权限开关</div>
  <div class="panel-body">
    <form class="form-horizontal" id="sets_gn_form">';
        foreach ( $inputs2 as $node ) {
            $html .= sprintf ('<div class="form-group">
<label for="%s_sets" class="col-sm-3 control-label">%s</label>
<div class="col-sm-8">
<select id="%s_sets" class="form-control">',$node[0],$node[1],$node[0]);
            $select_nums=count($node[2]);
            for($i=0;$i<$select_nums;$i++){
                $html .=('<option value="'.$i.'"');
                if($i==$node[3])
                    $html .=' selected="seclected"';
                $html.=('>'.$node[2][$i].'</option>');
            }
            $html.='</select></div></div>';
        }
        $html .= ('<div class="form-group">
<div class="col-sm-2 col-sm-offset-8">
    <button type="button" class="btn btn-primary">修改设置</button>
</div>
</div>');
        $html .= ('</form></div></div>');
        $html .= '<div class="panel panel-default">
  <div class="panel-heading">修改用户名密码</div>
  <div class="panel-body">
    <form class="form-horizontal" id="sets_pass_form">';
        $inputs3 = array (
                array (
                        'username',
                        'text',
                        '用户名',
                        ''
                ),
                array (
                        'pass',
                        'password',
                        '原始密码',
                        ''
                ),
                array (
                        'pass1',
                        'password',
                        '新密码',
                        ''
                ),
                array (
                        'pass2',
                        'password',
                        '重复新密码',
                        ''
                )
        );
        $inputs3 [0] [3] = $result ['username'];
        foreach ( $inputs3 as $node ) {
            $html .= sprintf ( $tpl, $node [0], $node [2], $node [1], $node [0], $node [3] );
        }
        $app=Application::getApp();
        $urlHandler=$app->getUrlHandler();
        $urlArr=array(
        	'common'=>$urlHandler->createUrl('ajax/DoAdminSets', 'common',array(),false),
        	'pass'=>$urlHandler->createUrl('ajax/DoAdminSets', 'pass',array(),false),
        	'gn'=>$urlHandler->createUrl('ajax/DoAdminSets', 'gn',array(),false),
        	'url'=>$urlHandler->createUrl('ajax/DoAdminSets', 'url',array(),false)
        	
        );
        $html .= ('<div class="form-group">
<div class="col-sm-2 col-sm-offset-8">
    <button type="button" class="btn btn-primary">修改密码</button>
</div>
</div>');
        $html .= ('</form></div></div>');
        $html.='<script type="text/javascript">
        $("#sets_common_form button").click(function(){
            var r=confirm("你确定要更新配置吗?");
                if(r==false)
                    return;
            $.ajax({
                "url" : "'.$urlArr['common'].'",
                "method" : "POST",
                "cache" : false,
                "dataType" : "json",
                "data" : {
                    "nickname": $("#nickname_sets").val(),
                    "blogname": $("#blogname_sets").val(),
                    "blogname_color": $("#blogname_color_sets").val(),
                    "description": $("#description_sets").val(),
                    "descr_color": $("#descr_color_sets").val(),
                    "nav_color": $("#nav_color_sets").val(),
                    "nav_active_color": $("#nav_active_color_sets").val(),
					"blog_keywords":$("#blog_keywords_sets").val(),
                	"search_abouts":$("#search_abouts_sets").val(),
                    "abouts":$("#abouts_sets").val(),
                	"blog_bottom":$("#blog_bottom_sets").val()
                },
                "success" : function(data) {
                    if(data.success)
                        alertModal("success","操作成功","修改基础设置成功,请刷新页面查看效果");
                    else
                        alertModal("danger","操作失败",data.msg);
                },
                "error" : function(jqXHR, textStatus, errorThrown) {
                    alertModal("danger","异步操作失败",errorThrown);
                }
            });
        });
        /*账户密码*/
        $("#sets_pass_form button").click(function(){
            var r=confirm("你确定要修改账户信息吗?");
                if(r==false)
                    return;
            $.ajax({
                "url" : "'.$urlArr['pass'].'",
                "method" : "POST",
                "cache" : false,
                "dataType" : "json",
                "data" : {
                    "username": $("#username_sets").val(),
                    "pass": $("#pass_sets").val(),
                    "pass1": $("#pass1_sets").val(),
                    "pass2": $("#pass2_sets").val()
                },
                "success" : function(data) {
                    if(data.success)
                        logoutConfirm(true);
                    else
                        alertModal("danger","操作失败",data.msg);
                },
                "error" : function(jqXHR, textStatus, errorThrown) {
                    alertModal("danger","异步操作失败",errorThrown);
                }
            });
        });
        /*功能权限开关*/
        $("#sets_gn_form button").click(function(){
            var r=confirm("你确定要修改功能权限信息吗?");
                if(r==false)
                    return;
            $.ajax({
                "url" : "'.$urlArr['gn'].'",
                "method" : "POST",
                "cache" : false,
                "dataType" : "json",
                "data" : {
                    "open_compress": $("#open_compress_sets").val(),
                    "allow_liuyan": $("#allow_liuyan_sets").val(),
                    "allow_reply": $("#allow_reply_sets").val()
                },
                "success" : function(data) {
                    if(data.success)
                        alertModal("success","操作成功","修改功能权限设置成功,请刷新页面查看效果");
                    else
                        alertModal("danger","操作失败",data.msg);
                },
                "error" : function(jqXHR, textStatus, errorThrown) {
                    alertModal("danger","异步操作失败",errorThrown);
                }
            });
        });
        /*URL设置*/
        $("#sets_url_form button").click(function(){
            var r=confirm("你确定要修改URL设置吗?");
                if(r==false)
                    return;
            $.ajax({
                "url" : "'.$urlArr['url'].'",
                "method" : "POST",
                "cache" : false,
                "dataType" : "json",
                "data" : {
                    "bg_img": $("#bg_img_sets").val(),
                    "top_img": $("#top_img_sets").val(),
                    "touxiang_img": $("#touxiang_img_sets").val()
                },
                "success" : function(data) {
                    if(data.success)
                        alertModal("success","操作成功","修改URL设置成功,请刷新页面查看效果");
                    else
                        alertModal("danger","操作失败",data.msg);
                },
                "error" : function(jqXHR, textStatus, errorThrown) {
                    alertModal("danger","异步操作失败",errorThrown);
                }
            });
        });
        </script>';
        return $html;
	}
	public function getTitle() {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst['t_value'].'-博客设置';
	}
}