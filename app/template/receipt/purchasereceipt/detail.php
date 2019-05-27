<?php
namespace ttwms;

use ttwms\model\PurchaseReceipt;
use ttwms\model\PurchaseReceiptBox;
use function Temtop\t;

/**
 * @var $list
 * @var string $model
 * @var array $param
 * @var PurchaseReceipt $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('收货详情')); ?>
	<style>
		.title { display:inline-block; margin:5px 0; font-size:15px; font-weight:bold; }
		.detail { display:inline-block; margin:0 5px; float:left; width:48%; }
		h1 { text-align:center; }
		.export { margin:10px; }
	</style>
	<section class="container">
		<?php
		$active[$param['mode']] = "class='active'";
		?>
		<h1><?= t("入库单号") ?>:<?= $param['receipt_no'] ?></h1>
		<ul class="tab">
			<li <?= $active['sku'] ?> ><a
					href="<?= ViewBase::getUrl("receipt/purchaseReceipt/detail", array(
						"mode"       => "sku",
						"id"         => $param['id'],
						"receipt_no" => $param['receipt_no']
					)) ?>"><?= t("SKU") ?></a></li>
			<li <?= $active['box'] ?> ><a
					href="<?= ViewBase::getUrl("receipt/purchaseReceipt/detail", array(
						"mode"       => "box",
						"id"         => $param['id'],
						"receipt_no" => $param['receipt_no']
					)) ?>"><?= t("箱号") ?></a></li>
		</ul>
		<?php if($param['mode'] == 'sku'): ?>
			<a class="btn export" target="_blank" href="<?= ViewBase::getUrl("receipt/purchaseReceipt/detail", array(
				"mode"   => "sku",
				"id"     => $param['id'],
				"export" => "export"
			)) ?>"><?= t("导出") ?></a>
			<table class="data-tbl" id="tbl" data-empty-fill="1" data-component="fixedhead">
				<thead>
				<tr>
					<th style="text-align:center;"><?= t("SKU") ?></th>
					<th style="text-align:center;"><?= t("总数量") ?></th>
					<th style="text-align:center;"><?= t("已收货数量") ?></th>
					<th style="text-align:center;"><?= t("收货差异数量") ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach($list ?: array() as $row): ?>
					<tr class="box_main">
						<td align="center"><?= $row['sku'] ?></td>
						<td align="center"><?= $row['total'] ?></td>
						<td align="center"><?= $row['receive'] ?></td>
						<td align="center"><?= $row['total']-$row['receive'] ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php elseif($param['mode'] == 'box'):?>
			<div>
				<div class="detail">
					<span class="title"><?=t("待上架")?></span>
					<table class="data-tbl" data-empty-fill="1">
						<thead>
						<tr>
							<th><?= t("箱号") ?></th>
							<th><?= t("数量") ?></th>
							<th><?= t("已收数量") ?></th>
						</tr>
						</thead>
						<tbody>
						<?php if(count($list['unsettled'])): ?>
							<?php
							/**
							 * @var PurchaseReceiptBox $row
							 */
							foreach($list['unsettled'] ?: array() as $row): ?>
								<tr class="box_main">
									<td align="center"><?= $row->no ?></td>
									<td align="center"><?= $row->allCount ?></td>
									<td align="center"><?= $row->allReceive ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
						</tbody>
					</table>
				</div>
				<div class="detail">
					<span class="title"><?=t("已上架")?></span>
					<table class="data-tbl" data-empty-fill="1">
						<thead>
						<tr>
							<th><?= t("箱号") ?></th>
							<th><?= t("数量") ?></th>
							<th><?= t("已收数量") ?></th>
							<th><?= t("已上架数量") ?></th>
						</tr>
						</thead>
						<tbody>
						<?php if(count($list['done'])): ?>
							<?php
							/**
							 * @var PurchaseReceiptBox $row
							 */
							foreach($list['done'] ?: array() as $row): ?>
								<tr class="box_main">
									<td align="center"><?= $row->no ?></td>
									<td align="center"><?= $row->allCount ?></td>
									<td align="center"><?= $row->allReceive ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif; ?>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>