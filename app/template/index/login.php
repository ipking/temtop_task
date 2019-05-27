<?php

use Lite\Core\Config;
use ttwms\ViewBase;

ViewBase::setPagePath(['系统登录']);
include ViewBase::resolveTemplate('inc/header.inc.php');
echo $PAGE_HEAD_HTML .= $this->getCss($FRONTEND_BASE."mytemtop/css/login.css");
?>
<form action="<?=ViewBase::getUrl('index/login');?>" class="login-frm" method="post" data-component="async">
	<img class="login-logo" src="<?=ViewBase::getImgUrl($FRONTEND_BASE.'mytemtop/img/temtop_logo.png')?>">
	<img class="line-space" src="<?=ViewBase::getImgUrl($FRONTEND_BASE.'mytemtop/img/line-space.png')?>">
	<div class="system-name"><?=Config::get('app/site_name')?></div>

	<table class="frm-tbl login-box">
		<tbody>
		<tr>
			<td><input type="text" name="username" placeholder="输入用户名" value="" required/></td>
		</tr>
		<tr>
			<td>
				<input type="password" name="password" placeholder="输入密码" value="" required/>
			</td>
		</tr>
		</tbody>
	</table>
	<div class="login-btn">
		<input type="submit" value="登录">
	</div>
</form>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php');?>
