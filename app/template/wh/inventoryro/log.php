<?php
namespace ttwms;

use ttwms\business\Form;
use ttwms\model\WhInventoryRoLog;
use function Temtop\t;

/**
 * @var WhInventoryRoLog[] $list
 * @var array $isActiveList
 * @var array $roleList
 * @var array $param
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $pagination
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('日志')); ?>
	<style>
		.data-tbl .add {color:green;}
		.data-tbl .add:before{content:"+"}
		.data-tbl .reduce {color:red;}
	</style>
	<section class="container">
	
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th width="150"><?= t("时间") ?></th>
			<th><?= t('变化数量') ?></th>
			<th><?= t('剩余数量') ?></th>
			<th><?= t('单号') ?></th>
			<th><?= t("类型") ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($list as $k => $log): ?>
			<tr>
				<td><?= $log->create_time ?></td>
				<td class="<?= $log->qty > 0 ? 'add' : 'reduce' ?>"><?= $log->qty ?></td>
				<td><?= $log->remain_qty ?></td>
				<td><?= $log->ref_code ?: '-' ?></td>
				<td><?= Form::$typeList[$log->ref_type] ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
		<?php echo $pagination; ?>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>