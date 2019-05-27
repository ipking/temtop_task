<?php
namespace ttwms;

use ttwms\model\SysUser;

/**
 * @var SysUser $user
 * @var array $isActiveList
 * @var array $get
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
<?= $this->buildBreadCrumbs(array('用户管理'=>'user/user/index',"查看用户信息")); ?>
<section class="container">
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
					<th>角色</th>
					<td><?=ViewBase::displayField('role_id',$user)?></td>
				</tr>
				<tr>
					<th>邮箱</th>
					<td>
						<?=$user->email?:"-"?>
					</td>
				</tr>
			</tbody>
		</table>
		
		<div class="operate-row">
			<?=ViewBase::getDialogCloseBtn()?>
		</div>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>