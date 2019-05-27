<?php
namespace ttwms;

use function Lite\func\h;

/**
 * @var \ttwms\model\Enterprise $user
 * @var array $isActiveList
 * @var array $get
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
	<style>
		.repeat-password {display:block; margin-top:10px}
	</style>
<?= $this->buildBreadCrumbs(array('用户管理'=>'enterprise/enterprise/index',"重置密码")); ?>
<section class="container">
	<form action="<?= ViewBase::getUrl('enterprise/enterprise/password',["id"=>$get['id']]); ?>" class="frm <?=$get['readonly']?>" method="post" data-component="async" <?=$get['readonly']?>>
		<table class="frm-tbl">
			<tbody>
				<tr>
					<th>客户名称</th>
					<td>
						<span><?=h($user->name)?></span>
					</td>
				</tr>
				<tr>
					<th>客户代码</th>
					<td>
						<span><?=h($user->name)?></span>
					</td>
				</tr>
				<tr>
					<th>客户级别</th>
					<td>
						<?=ViewBase::displayField('level_id',$user)?>
					</td>
				</tr>
				<tr>
					<th>密码</th>
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