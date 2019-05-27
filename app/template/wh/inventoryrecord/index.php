<?php
namespace ttwms;

use ttwms\business\Form;
use ttwms\model\Enterprise;
use ttwms\model\WhInventoryRecord;
use function Lite\func\h;
use function Temtop\t;

/**
 * @var WhInventoryRecord[] $list
 * @var array $isActiveList
 * @var array $roleList
 * @var array $param
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $pagination
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('库存流水查询')); ?>
	<style>
		.page-iframe .search-frm{display:none;}
		.page-iframe .data-tbl .col-skuname,
		.page-iframe .data-tbl .col-sku {display:none;}
		.data-tbl thead tr th,
		.data-tbl tbody tr td {text-align: center}
	</style>
	<section class="container">
	<form action="<?=ViewBase::getUrl('wh/inventoryRecord/index')?>" method="GET" class="search-frm quick-search-frm">
        <?php $enterprise = Enterprise::find()->map("id","code")?>
		<select name="enterprise_id"  placeholder="valid">
			<option value="">--客户代码--</option>
			<?php foreach ($enterprise as $id => $code):?>
				<option <?=$param['enterprise_id'] == $id ?'selected':''?> value="<?=$id?>"><?=h($code)?></option>
			<?php endforeach;?>
		</select>
		<select name="ref_type" placeholder="valid">
			<option value=""><?=t("--操作类型--")?></option>
			<?php foreach (Form::$typeList as $id => $code):?>
				<option <?=$param['ref_type'] == $id ?'selected':''?> value="<?=$id?>"><?=h($code)?></option>
			<?php endforeach;?>
		</select>
		<select name="goods_type"  placeholder="valid">
			<option value="">--货物类型--</option>
			<?php foreach (\ttwms\business\Form::$goodsTypeList as $id => $code):?>
				<option <?=$param['goods_type'] == $id ?'selected':''?> value="<?=$id?>"><?=h($code)?></option>
			<?php endforeach;?>
		</select>
		<input type='text' name='sku' class="txt" placeholder="SKU<?=t("查询")?>" value="<?=$param['sku']?>" />
		<?=t("操作时间")?>
		<input type="text" class="date-txt txt" name="add_time_start" placeholder="<?=t("开始时间")?>" value='<?=$param['add_time_start']?>'>-
		<input type="text" class="date-txt txt" name="add_time_end" placeholder="<?=t("结束时间")?>" value='<?=$param['add_time_end']?>'>
		<button class="btn-search mr-10" type="submit" value="<?=t("搜索")?>">搜索</button>
		<input type="submit" value="<?= t('导出') ?>" name="export" class="btn">
	</form>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th class="col-sku"><?= t('客户代码') ?></th>
			<th class="col-sku">SKU</th>
			<th  class="col-skuname"><?=t("中文名称")?></th>
			<th  class="col-skuname"><?=t("英文名称")?></th>
			<th ><?=t("单据号")?></th>
			<th ><?=t("类型")?></th>
			<th ><?=t("货物类型")?></th>
			<th class="col-min"><?=t("操作数量")?></th>
			<th class="col-min"><?=t("库内库存")?></th>
			<th width="120"><?=t("操作时间")?></th>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($list as $item=>$row):?>
				<tr>
					<td class="col-sku"><?=$row->product->enterprise_code?></td>
					<td class="col-sku"><?=$row->product->sku?></td>
					<td class="col-skuname"><?=$row->product->name?></td>
					<td class="col-skuname"><?=$row->product->ename?></td>
					<td><a href="<?=$row->TargetUrl()?>" target="_blank"><?=$row->no?></a></td>
					<td><?=Form::$typeList[$row->ref_type]?></td>
					<td><?=Form::$goodsTypeList[$row->goods_type]?></td>
					<td>
						<?php if($row->qty<0){?>
							<span style="color:red"> <?=$row->qty?></a></span>
						<?php }else{?>
							<span style="color:#0fc350"><?=$row->qty?></span>
						<?php }?>
					</td>
					<td><?=$row->qty_balance?></td>
					<td><?=$row->create_time?></td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
		<?php echo $pagination; ?>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>