<?php

use function Lite\func\ha;
use ttwms\model\Enterprise;
use ttwms\model\TransitDeliveryOrder;
use ttwms\ViewBase;
use function Lite\func\h;
use function Temtop\t;

/* @var ViewBase $this */
/* @var TransitDeliveryOrder[] $order_list */
/* @var TransitDeliveryOrder $p */
/* @var array $get */
include $this->resolveTemplate('inc/header.inc.php'); ?>
	<style>
		.tab-status-all .status {display:table-cell;}

		.sku-tip-style.tt-sku-list{ width: 400px; }
		.sku-tip-style.tt-sku-list li{ width: 50%; box-sizing: border-box; display: inline-block; margin: 3px 0; }
		.tt-sku-list li{ display: block; }
	</style>
<?php
/* @var Enterprise[] $enterprise_list */
$enterprise_list = Enterprise::find()->field(['id', 'name', 'code'])->all(false, 'id');
?>
<?= $this->buildBreadCrumbs(array('出库单')); ?>
	<ul class="tab clear">
		<li class="<?= !$get['status'] ? 'active' : '' ?>"><a href="<?= $this->getUrl('order/TransitDeliveryOrder/index'); ?>">全部</a></li>
		<?php foreach(TransitDeliveryOrder::$status_map as $k => $v): ?>
		<li class="<?= $get['status'] == $k ? 'active' : '' ?>"><a href="<?= $this->getUrl('order/TransitDeliveryOrder/index', ['status' => $k]); ?>"><?= $v ?></a></li>
		<?php endforeach; ?>
	</ul>
	<section class="container">
		
		<form action="<?= $this->getUrl('order/TransitDeliveryOrder/index'); ?>" class="quick-search-frm" method="get">
			<input type="hidden" name="status" value="<?=$get['status'];?>">
			<select name="enterprise_id" placeholder>
				<option value=""><?= t("客户") ?></option>
				<?php foreach($enterprise_list as $k => $v): ?>
				<option value="<?= $k ?>" <?= $k == $get['enterprise_id'] ? 'selected' : '' ?>><?= $v->name ?></option>
				<?php endforeach; ?>
			</select>
			<select name="first_shipping_type" placeholder>
				<option value=""><?= t("运输类型") ?></option>
				<?php foreach(TransitDeliveryOrder::$first_shipping_type_map as $k => $v): ?>
					<option value="<?= $k ?>" <?= $k == $get['first_shipping_type'] ? 'selected' : '' ?>><?= $v ?></option>
				<?php endforeach; ?>
			</select>
			<input type="text" name="wms_no" value="<?= h($get['wms_no']); ?>" placeholder="单号">
			<input type="text" class="txt" name="sku" placeholder="<?=t("SKU")?>" value='<?= $get['sku'] ?>'/>
			<button class="btn-search mr-10" type="submit">查询</button>
			<a class="btn btn-reset" href="<?= $this->getUrl('order/TransitDeliveryOrder/index',['status'=>$get['status']]); ?>">重置</a>
		</form>
		<table class="data-tbl tab-status-<?=strtolower($get['status']?:'all')?>" data-empty-fill="1" data-component="fixedhead">
			<thead>
			<tr>
				<th><?= t('客户') ?></th>
				<th><?= t('出库单号') ?></th>
				<th><?= t('客户单号') ?></th>
				<th><?= t('总箱数') ?></th>
				<th><?= t('总款式') ?></th>
				<th><?= t('总数量') ?></th>
				<th width="250"><?=t("SKU")?></th>
				<th><?= t('运输方式') ?></th>
				<th><?= $this->getOrderLink(t('创建时间'), 'create_time') ?></th>
				<th class="hide status"><?= t('状态') ?></th>
				<th><?= t('操作') ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($order_list ?: [] as $k => $p):
				$sku_group = $p->sku_group;
				
				$i = 0;
				$tip_content = '<ul class="tt-sku-list sku-tip-style">';
				$show_content = '';
				foreach($p->sku_qty_list as $pid => $i_row){
					$tmp = '<li>'.$i_row['sku'].'<span class="sku-number-span">&times;'.$i_row['qty'].'</span></li>';
					$i++;
					$tip_content .= $tmp;
					if($i<=2){
						$show_content .= $tmp;
					}
				}
				$tip_content .= '</ul>';
				?>
				<tr>
					<td><?= $enterprise_list[$p->enterprise_id]->name ?></td>
					<td><?= $p->wms_no ?: '-' ?></td>
					<td><?= $p->enterprise_order_no ?></td>
					<td><?= $sku_group['total_box_quantity'] ?></td>
					<td><?= $sku_group['total_style_quantity'] ?></td>
					<td><?= $sku_group['total_quantity'] ?></td>
					<td <?=$i>2?'data-component="tip" data-tip-content="'.ha($tip_content).'"':''?>>
						<ul class="tt-sku-list">
							<?=$show_content;?>
							<?=$i>2?'<li><span>....</span></li>':''?>
						</ul>
					</td>
					<td><?= TransitDeliveryOrder::$first_shipping_type_map[$p->first_shipping_type] ?></td>
					<td><?= ViewBase::displayField('create_time', $p) ?></td>
					<td class="hide status"><?=ViewBase::displayField('status', $p)?></td>
					<td>

						<dl class="drop-list drop-list-left">
							<dt>
								<a data-component="popup" data-popup-width="1000" href="<?= $this->getUrl('order/TransitDeliveryOrder/info', array('id' => $p->id)); ?>">详情</a>
							</dt>
							<dd>
								<?php if($p->status == TransitDeliveryOrder::STATUS_NEW):?>
									<a  href="<?=ViewBase::getUrl("order/TransitDeliveryOrder/confirm",array('id'=>$p->id))?>" data-confirm-message="<?=t("是否确认发出")?>" data-component="confirm,async"><?=t("确认发出")?></a>
									<a  href="<?=ViewBase::getUrl("order/TransitDeliveryOrder/boxItem",array('id'=>$p->id))?>" data-component="popup"><?=t("装箱明细")?></a>
									
									<a  href="<?=ViewBase::getUrl("order/TransitDeliveryOrder/printShipment",array('id'=>$p->id,'page'=>'rcvList'))?>" data-component="popup"><?=t("打印出库单")?></a>
									<a href="<?=ViewBase::getUrl("order/TransitDeliveryOrder/back",array('id'=>$p->id))?>" data-component="popup"><?=t("取消")?></a>
								<?php endif; ?>
								<?php if($p->target_wh_type == TransitDeliveryOrder::TARGET_WH_TYPE_TEMTOP):?>
									<a  href="<?=ViewBase::getUrl("order/TransitDeliveryOrder/printShipment",array('id'=>$p->id,'page'=>'shipmentPage'))?>" data-component="popup"><?=t("打印腾拓唛头")?></a>
								<?php endif; ?>
								<?php
								//todo 根据4px的唛头模板打印
								if($p->target_wh_type == TransitDeliveryOrder::TARGET_WH_TYPE_4PX):?>
									<a  href="<?=ViewBase::getUrl("order/TransitDeliveryOrder/printShipment",array('id'=>$p->id,'page'=>'shipmentPageSpx'))?>" data-component="popup"><?=t("打印4PX唛头")?></a>
								<?php endif; ?>
								<?php if($p->target_wh_type == TransitDeliveryOrder::TARGET_WH_TYPE_GC):?>
									<a  href="<?=ViewBase::getUrl("order/TransitDeliveryOrder/printShipment",array('id'=>$p->id,'page'=>'shipmentPage'))?>" data-component="popup"><?=t("打印谷仓唛头")?></a>
								<?php endif; ?>
								<?php if($p->target_wh_type == TransitDeliveryOrder::TARGET_WH_TYPE_FBA):?>
									<a  href="<?=ViewBase::getUrl("order/TransitDeliveryOrder/printShipment",array('id'=>$p->id,'page'=>'shipmentFba'))?>" data-component="popup"><?=t("打印FBA唛头")?></a>
								<?php endif; ?>
							</dd>
						</dl>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php echo $paginate; ?>
	</section>
<?php include $this->resolveTemplate('inc/footer.inc.php'); ?>