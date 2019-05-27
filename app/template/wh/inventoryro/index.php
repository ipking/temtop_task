<?php
namespace ttwms;

use ttwms\business\Form;
use ttwms\model\Enterprise;
use ttwms\model\WhInventoryRo;
use function Lite\func\h;
use function Temtop\t;

/**
 * @var WhInventoryRo[] $list
 * @var array $isActiveList
 * @var array $roleList
 * @var array $param
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $pagination
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('批次库存')); ?>
	<style>
		span.status_0{background-color: #ff7900;}
		span.status_1{background-color: green;}
		.select_block{display: inline-block;width: 200px;}
		.page-iframe .search-frm{display:none;}
		.page-iframe .data-tbl .col-skuname,
		.page-iframe .data-tbl .col-sku {display:none;}
		.data-tbl thead tr th,
		.data-tbl tbody tr td {text-align: center}
	</style>
	<section class="container">
	<form action="<?=ViewBase::getUrl('wh/inventoryRo/index')?>" method="GET" class="search-frm quick-search-frm">
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
		<input type='text' name='sku' class="txt" placeholder="SKU<?= t("查询") ?>" value="<?= $param['sku'] ?>"/>
		<span style="vertical-align:middle;">&nbsp;<?= t('库龄大于') ?></span><input type="number" min="0" value="<?= $param['put_on_day'] ?>" placeholder="<?=  t('天数')?>" class="txt" name="put_on_day">
		<input type="hidden" name="ref" value="<?=$param['ref']?>">
		<button class="btn-search mr-10" type="submit" value="<?=t("搜索")?>">搜索</button>
		<input type="submit" value="<?=t('导出')?>" name="export" class="btn">
	</form>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th>SKU</th>
			<th><?= t("客户代码") ?></th>
			<th><?= t("剩余数量") ?></th>
			<th><?= t('上架日期') ?></th>
			<th><?=ViewBase::getOrderLink(t('库龄(天)'),'put_on_date')?></th>
			<th><?= t('单据号') ?></th>
			<th><?= t('操作类型') ?></th>
			<th><?= t('货物类型') ?></th>
			<th class="col-op"><?= t("操作") ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($list as $row): ?>
			<tr>
				<td><?= $row->product->sku ?></td>
				<td><?= $row->product->enterprise->code ?></td>
				<td><?= $row->remain_qty ?></td>
				<td><?= $row->put_on_date ?></td>
				<td><?= $row->age ?></td>
				<td><?= $row->ref_code ?: '-' ?></td>
				<td><?= Form::$typeList[$row->ref_type] ?></td>
				<td><?= Form::$goodsTypeList[$row->goods_type] ?></td>
				<td class="col-op">
					<dl class="drop-list drop-list-left drop-list-only">
							<dt>
								<a href="<?=ViewBase::getUrl('wh/inventoryRo/log', array('id' => $row->id));?>" data-component="popup"><?= t('查看日志') ?></a>
							</dt>
					</dl>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
		<?php echo $pagination; ?>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>