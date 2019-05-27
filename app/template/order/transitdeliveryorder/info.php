<?php



/** @var ViewBase $this */

use ttwms\model\TransitDeliveryOrder;
use ttwms\ViewBase;

/** @var TransitDeliveryOrder $order */
include $this->resolveTemplate('inc/header.inc.php');
?>
<style>
	.data-tbl th.align-right { text-align: right; }
	.data-tbl .valign-top th { vertical-align: top;padding-top: 12px; line-height: 1; }
	.data-tbl .valign-top td { vertical-align: top; }
</style>
<div class="container">
	<?= $this->buildBreadCrumbs(array('订单管理', '出库单')); ?>
	
	<div class="content">
		<table class="data-tbl ">
			<caption>中转出库基本信息</caption>
			<tbody>
			<tr>
				<th class="align-right">客户单号：</th>
				<td><?= $order->enterprise_order_no ?></td>
				<th class="align-right">仓库单号：</th>
				<td><?= $order->wms_no ?></td>
			</tr>
			
			<tr class="valign-top">
				<th class="align-right">备注：</th>
				<td <?= (!in_array($order->status, [TransitDeliveryOrder::STATUS_CANCELED]))?'colspan="3"':''?>><?= $order->note ?></td>
				<?php if($order->status == TransitDeliveryOrder::STATUS_CANCELED): ?>
					<th class="align-right">取消备注：</th>
					<td><?= $order->enterprise_cancel_note ?: $order->wms_cancel_note ?></td>
				<?php endif ?>
			</tr>
			</tbody>
		</table>

		<table class="data-tbl scroll-tbl" style="margin-top:30px;">
			<caption>装箱明细</caption>
			<thead>
			<tr>
				<th>箱号</th>
				<th>SKU</th>
				<th>预报数量</th>
				<th>发出数量</th>
			</tr>
			</thead>
			<tbody id="item-list">
			<?php
			/**
			 * @var TransitDeliveryOrder $order
			 */
			foreach($order->all_item_list as $item): ?>
				<tr>
					<td><?= $item->box_no ?></td>
					<td><?= $item->product->sku ?></td>
					<td><?= $item->quantity ?></td>
					<td><?= $item->send_quantity ?: '-' ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="operate-row">
		<?=ViewBase::getDialogCloseBtn()?>
	</div>
</div>
<?php include $this->resolveTemplate('inc/footer.inc.php'); ?>
