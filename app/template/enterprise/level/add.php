<?php
namespace ttwms;

use function Lite\func\ha;

/**
 * @var \ttwms\model\EnterpriseLevel $level
 * @var array $isActiveList
 * @var array $get
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
	<style>
		.repeat-password {display:block; margin-top:10px}
	</style>
<?= $this->buildBreadCrumbs(array('用户级别管理'=>'enterprise/level/index',"添加")); ?>
<section class="container">
	<form action="<?= ViewBase::getUrl('enterprise/level/add',["id"=>$get['id']]); ?>" class="frm <?=$get['readonly']?>" method="post" data-component="async" <?=$get['readonly']?>>
		<table class="frm-tbl">
			<tbody>
				<tr>
					<th>级别名称</th>
					<td><input type="text" class="txt" name="name" required value="<?=ha($level->name)?>"></td>
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