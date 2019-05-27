<?php
namespace ttwms;

use function Lite\func\ha;

/**
 * @var \ttwms\model\SysRole $role
 * @var array $isActiveList
 * @var array $get
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
	<style>
		.repeat-password {display:block; margin-top:10px}
	</style>
<?= $this->buildBreadCrumbs(array('角色管理'=>'user/role/index',"添加角色")); ?>
<section class="container">
	<form action="<?= ViewBase::getUrl('user/role/add',["id"=>$get['id']]); ?>" class="frm <?=$get['readonly']?>" method="post" data-component="async" <?=$get['readonly']?>>
		<table class="frm-tbl">
			<tbody>
				<tr>
					<th>角色名</th>
					<td><?=ViewBase::renderFormElementQuick($role,"name")?></td>
				</tr>
				<tr>
					<th>描述</th>
					<td>
						<input type="text" name="description" class="txt" required value="<?=ha($role->description)?>">
					</td>
				</tr>
				<tr>
					<th>类型</th>
					<td><?=ViewBase::renderFormElementQuick($role,"type")?></td>
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