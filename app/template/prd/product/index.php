<?php
namespace ttwms;

use ttwms\model\Enterprise;
use ttwms\model\PrdProduct;
use function Lite\func\h;
use function Lite\func\ha;
use function Temtop\t;

/**
 * @var PrdProduct $list
 * @var array $search
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
$defines = PrdProduct::meta()->getPropertiesDefine();
$EnterpriseList = Enterprise::find()->map('id','code');
?>
<?= $this->buildBreadCrumbs(array('产品管理')); ?>
	<style>
		span.status_<?=PrdProduct::IS_STOP_NO?>{width: 60px;text-align: center;height: 24px;line-height: 24px; color:#46C35F;background-color: #F1FFF4;border: 1px solid #46C35F;font-size: 12px;font-weight:400;border-radius:3px;display: inline-block;}
		span.status_<?=PrdProduct::IS_STOP_YES?>{width: 60px;text-align: center;height: 24px;line-height: 24px; color:#E06225;background-color: #FFF8E6;border: 1px solid #FFA751;font-size: 12px;font-weight:400;border-radius:3px;display: inline-block;}
		span.lock_<?=PrdProduct::IS_LOCK_YES?>{width: 60px;text-align: center;height: 24px;line-height: 24px; color:#E06225;background-color: #FFF8E6;border: 1px solid #FFA751;font-size: 12px;font-weight:400;border-radius:3px;display: inline-block;}
		span.lock_<?=PrdProduct::IS_LOCK_NO?>{width: 60px;text-align: center;height: 24px;line-height: 24px; color:#A0A6AD;background-color: #F3F5F8;border: 1px solid #D5DAE0;font-size: 12px;font-weight:400;border-radius:3px;display: inline-block;}
	</style>
	<section class="container">
	<form action="<?= ViewBase::getUrl('prd/product/index'); ?>" class="quick-search-frm" method="get">
		<select name="enterprise_id" id="" placeholder="valid">
			<option value="">--客户代码--</option>
			<?php foreach ($EnterpriseList as $id => $code):?>
				<option <?=$search['enterprise_id'] == $id ?'selected':''?> value="<?=$id?>"><?=h($code)?></option>
			<?php endforeach;?>
		</select>
		<?=$this->renderSearchFormElement($search['is_stop'], 'is_stop', $defines['is_stop']);?>
		<?=$this->renderSearchFormElement($search['package_type'], 'package_type', $defines['package_type']);?>
		<?=$this->renderSearchFormElement($search['barcode_type'], 'barcode_type', $defines['barcode_type']);?>
		<input type="search" name="sku" placeholder="SKU" value="<?= ha($search['sku']); ?>">
		<button class="btn-search mr-10" type="submit">搜索</button>
	</form>
		<div class="operate-bar">
			<a href="<?=ViewBase::getUrl("prd/product/add")?>" class="btn" data-component="popup" data-popup-widht="800">手工创建产品</a>
			<a href="<?=ViewBase::getUrl("prd/product/printSelfBarcode")?>" class="btn" data-component="popup"><?=t("自定义条码打印")?></a>
			<a href="<?=ViewBase::getUrl("prd/product/toprint")?>" class="btn"  data-component="temtop/muloperate,popup" ><?=t("打印所选SKU条码")?></a>
		</div>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th class="col-chk"><input type="checkbox" data-component="checker"/></th>
			<th>SKU</th>
			<th>条码类型</th>
			<th>条码</th>
			<th>客户代码</th>
			<th>报关名称</th>
			<th>中文名称</th>
			<th>英文名称</th>
			<th>报关价格($)</th>
			<th>长×宽×高(cm)</th>
			<th>重量(g)</th>
			<th>是否自带包装</th>
			<th>状态</th>
			<th>锁定状态</th>
			<th class="col-op">操作</th>
		</tr>
		</thead>
		<tbody>
		<?php
		/**
		 * @var PrdProduct $product
		 */
		foreach($list as $product): ?>
			<tr>
				<td class="col-chk">
					<input type="checkbox" name="ids[]"  value="<?=$product->id?>"/>
				</td>
				<td><?= ViewBase::displayField('sku', $product) ?></td>
				<td><?= ViewBase::displayField('barcode_type', $product) ?></td>
				<td><?= ViewBase::displayField('barcode', $product) ?></td>
				<td><?= ViewBase::displayField('enterprise_code', $product) ?></td>
				<td><?= ViewBase::displayField('clearance_name', $product) ?></td>
				<td><?= ViewBase::displayField('name', $product) ?></td>
				<td><?= ViewBase::displayField('ename', $product) ?></td>
				<td><?= ViewBase::displayField('clearance_price', $product) ?></td>
				<td><?=$product->length."&times;".$product->width."&times;".$product->height?></td>
				<td><?= ViewBase::displayField('weight_rough', $product) ?></td>
				<td><?= ViewBase::displayField('package_type', $product) ?></td>
				<td class="col-min"><span class="status status_<?=$product->is_stop?>"><?=$product->is_stop == PrdProduct::IS_STOP_NO?t("启用"):t("停用")?></span></td>
				<td class="col-min"><span class="status lock_<?=$product->is_lock?>"><?=$product->is_lock == PrdProduct::IS_LOCK_YES?t("已锁定"):t("未锁定")?></span></td>
				<td class="col-op">
					<dl class="drop-list drop-list-left">
							<dt>
								<a href="<?= ViewBase::getUrl('prd/product/view', array('id' => $product->id)); ?>" data-component="popup" data-popup-widht="800">查看</a>
							</dt>
							<dd>
								<a href="<?= ViewBase::getUrl('prd/product/edit', array('id' => $product->id)); ?>" data-component="popup" data-popup-widht="800">编辑</a>
								<?php if($product->is_stop == PrdProduct::IS_STOP_NO):?>
								<a href="<?= ViewBase::getUrl('prd/product/stop', array('id' => $product->id)); ?>" data-component="confirm,async" data-confirm-message="<?=t("确定停用？")?>"><?=t("停用")?></a>
							<?php else:?>
								<a href="<?= ViewBase::getUrl('prd/product/stop', array('id' => $product->id)); ?>" data-component="async"><?=t("启用")?></a>
							<?php endif;?>
							
							<?php if($product->is_lock == PrdProduct::IS_LOCK_YES):?>
								<a href="<?= ViewBase::getUrl('prd/product/lock', array('id' => $product->id)); ?>" data-component="async"><?=t("解除锁定")?></a>
							<?php else:?>
								<a href="<?= ViewBase::getUrl('prd/product/lock', array('id' => $product->id)); ?>" data-component="confirm,async" data-confirm-message="<?=t("确定锁定？")?>"><?=t("锁定")?></a>
							<?php endif;?>
							<a href="<?= ViewBase::getUrl('prd/product/log', array('id' => $product->id)); ?>" data-component="popup" data-popup-widht="800">查看日志</a>
							</dd>
					</dl>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo $paginate; ?>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>