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
<?= $this->buildBreadCrumbs(array('用户管理'=>'user/user/index',"添加用户")); ?>
<section class="container">
	<form action="<?= ViewBase::getUrl('user/user/add',["id"=>$get['id']]); ?>" class="frm <?=$get['readonly']?>" method="post" data-component="async" <?=$get['readonly']?>>
		<table class="frm-tbl">
			<tbody>
				<tr>
					<th>登录账号</th>
					<td><input type="text" class="txt" name="account" required value="<?=$user->account?>"></td>
				</tr>
				<?php if(!$user->id){?>
				<tr>
					<th>密码</th>
					<td>
						<input type="password" name="password" class="txt" value=""  data-component="password" data-password-generator="1" required/>
					</td>
				</tr>
				<?php }?>
				<tr>
					<th>用户姓名</th>
					<td><input type="text" name="name" class="txt" value="<?=$user->name?>"></td>
				</tr>
				<tr>
					<th>角色</th>
					<td><?=ViewBase::renderFormElementQuick($user,"role_id")?></td>
				</tr>
				<tr>
					<th>邮箱</th>
					<td>
						<input type="email" name="email" class="txt" required value="<?=$user->email?>">
					</td>
				</tr>
				<tr>
					<th>状态</th>
					<td><?=ViewBase::renderFormElementQuick($user,"status")?></td>
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