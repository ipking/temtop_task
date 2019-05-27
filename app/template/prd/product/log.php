<?php
namespace ttwms;

use ttwms\model\PrdProduct;
use ttwms\model\SysUser;
use function Temtop\t;

/**
 * @var \ttwms\model\PrdProduct $info
 * @var array $isActiveList
 * @var array $get
 * @var string $logList
 * @var string $model
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

$userList = SysUser::find()->map('id','name');
?>
<?= $this->buildBreadCrumbs(array('产品管理'=>'prd/product/index',"日志")); ?>
<section class="container">
	<table class="data-tbl"  data-empty-fill="1">
		<thead>
		<tr>
			<th class="col-min">SKU</th>
			<th class="col-min"><?=t("字段")?></th>
			<th><?=t("修改前")?></th>
			<th><?=t("修改后")?></th>
			<th class="col-min"><?=t("操作人")?></th>
			<th class="col-min"><?=t("操作时间")?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$fieldMapping = PrdProduct::$fieldMapping;
		/**
		 * @var \ttwms\model\PrdProductReviseLog $row
		 */
		foreach ($logList?:array() as $row):?>
			<tr>
				<td class="col-min"><?=$row->product->sku?></td>
				<td class="col-min"><?=t($fieldMapping[$row->field])?></td>
				<td style="word-break: break-all; white-space: pre-wrap"><?=$row->before?></td>
				<td style="word-break: break-all; white-space: pre-wrap"><?=$row->after?></td>
				<td class="col-min"><?=$userList[$row->user_id]?></td>
				<td class="col-min"><?=$row->create_time?></td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>
	<?php echo $paginate; ?>
	<div class="operate-row">
		<?=ViewBase::getDialogCloseBtn()?>
	</div>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>