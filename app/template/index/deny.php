<?php

use Lite\Core\Router;

$user_info = array();
$role_id = $user_info['title_id'];
$sys_id = $_SERVER['HTTP_HOST'];
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<style>
		* {margin:0; padding:0;}
		body {font-family:"Tahoma", "Helvetica", "Microsoft YaHei New", "Microsoft YaHei", "宋体", "SimSun", "STXihei", "华文细黑", sans-serif}
		body {color:#b13a00; font-size:16px; background-color:#fafafa; padding:20px; line-height:1.8; text-shadow:1px 1px 1px white;}
		h1 {font-size:28px; margin-bottom:5px;}
		.main-info {position:relative; padding-left:90px;}
		.icon {display:block; width:70px; height:70px; line-height:1; text-align:center; border:1px dashed #E21515; font-size:54px; font-weight:bold; background-color:white; position:absolute; left:0; top:8px; transform: rotate(90deg)}
		.desc {padding-bottom:20px; border-bottom:1px solid #ddd;}
		.tech-info {font-size:12px; color:gray; border-top:1px solid white; padding-top:8px;}
		.tech-info p {font-size:14px;}
		ul {margin:5px 0 0 20px; display:inline-block}
		li {border-top:1px solid white; border-bottom:1px solid #eee;}
		ul span {display:inline-block; width:100px;}
	</style>
</head>
<body>
<div class="main-info">
	<span class="icon">
		:(
	</span>
	<h1>拒绝访问</h1>
	<p class="desc">
		您没有当前页面访问权限 <br/>
		申请更多权限，请咨询ERP系统管理人员。<br/>
		<a href="/">返回首页</a>
		<a href="<?php echo ViewBase::getUrl('index/logout');?>">退出登录</a>
	</p>
	<div class="tech-info">
		<p>技术支持信息：</p>
		<ul>
			<li><span>UID:</span><?php echo $user_info['id'];?></li>
			<li><span>Host:</span><?php echo $sys_id;?></li>
			<li><span>CONTROLLER:</span><?php echo Router::getControllerAbbr();?></li>
			<li><span>ACTION:</span><?php echo Router::getAction();?></li>
		</ul>
	</div>
</div>
</body>
</html>