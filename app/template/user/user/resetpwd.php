<?php
namespace ttwms;

/**
 * @var \ttwms\model\SysUser $user
 * @var array $isActiveList
 * @var array $get
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
	<style>
		.repeat-password {display:block; margin-top:10px}
	</style>
<?= $this->buildBreadCrumbs(array('用户管理'=>'user/user/index',"重置密码")); ?>
<section class="container">
	<form action="<?= ViewBase::getUrl('user/user/resetPwd',["id"=>$get['id']]); ?>" class="frm " method="post" data-component="async">
		<table class="frm-tbl">
			<tbody>
				<tr>
					<th>登录账号</th>
					<td><?=$user->account?></td>
				</tr>
				
				<tr>
					<th>用户姓名</th>
					<td><?=$user->name?></td>
				</tr>
				<tr>
					<th>新密码</th>
					<td>
						<input type="password" name="password" class="txt" value=""  data-component="password" data-password-generator="1" required/>
					</td>
				</tr>
			</tbody>
		</table>
		
		<div class="operate-row">
			<input type="submit" class="btn" value="保存">
			<?=ViewBase::getDialogCloseBtn()?>
		</div>
	</form>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>