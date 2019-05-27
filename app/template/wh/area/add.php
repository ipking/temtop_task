<?php
namespace ttwms;

use function Lite\func\ha;

/**
 * @var \ttwms\model\WhArea $area
 * @var array $isActiveList
 * @var array $get
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
	<style>
		.repeat-password {display:block; margin-top:10px}
	</style>
<?= $this->buildBreadCrumbs(array('库区管理'=>'wh/area/index',"添加库区")); ?>
<section class="container">
	<form action="<?= ViewBase::getUrl('wh/area/add',["id"=>$get['id']]); ?>" class="frm <?=$get['readonly']?>" method="post" data-component="async" <?=$get['readonly']?>>
		<table class="frm-tbl">
			<tbody>
				<tr>
					<th>库区名称</th>
					<td><input type="text" class="txt" name="name" required value="<?=ha($area->name)?>"></td>
				</tr>
				<tr>
					<th>库区代码</th>
					<td>
						<input type="text" name="code" class="txt" required value="<?=ha($area->code)?>"/>
					</td>
				</tr>
				<tr>
					<th>性质</th>
					<td><?=ViewBase::renderFormElementQuick($area,"type")?></td>
				</tr>
				<tr>
					<th>顺序</th>
					<td><?=ViewBase::renderFormElementQuick($area,"seq")?></td>
				</tr>
				<tr>
					<th>状态</th>
					<td><?=ViewBase::renderFormElementQuick($area,"status")?></td>
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