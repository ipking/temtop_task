<?php

use ttwms\CurrentUser;
use ttwms\ViewBase;

ViewBase::setPagePath(['修改密码']);
include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<style>
	.repeat-password {display:block; margin-top:10px}
</style>
<section class="container">
		<form action="<?= ViewBase::getUrl('user/user/updatePwd'); ?>" class="frm" method="post" data-component="async">
			<table class="frm-tbl" >
				<tbody>
				<tr>
					<th>用户名称</th>
					<td><?= CurrentUser::getUserName() ?></td>
				</tr>
				<tr>
					<th>原密码</th>
					<td>
						<input type="password" placeholder="请输入原密码" name="old_password" required>
					</td>
				</tr>
				<tr>
					<th>新密码</th>
					<td>
						<input type="password" name="password" class="txt" value=""  data-component="password" data-password-generator="1" required/>
					</td>
				</tr>
				
				<tr>
					<td></td>
					<td>
						<button type="submit" class="btn">保存</button>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
</section>
<script>
	
</script>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>
