<?php
namespace ttwms;

use ttwms\model\PurchaseReceipt;
use ttwms\model\PurchaseReceiptQt;
use function Temtop\t;

/**
 * @var $list
 * @var string $mode
 * @var array $param
 * @var PurchaseReceipt $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
$sku_receipt_qty_map = $info->sku_receipt_qty_map;
?>

	<?php
echo $this->buildBreadCrumbs(array('入库单'=>'receipt/purchaseReceipt/index',"质检"));
?>
	<style>
		.td_limit{max-width:200px !important;-ms-word-break: break-all;word-break: break-all;}
	</style>
	<section class="container">
	
		<h1 style="text-align: center"><?=t('入库单号')?>:<?=$info->receipt_no?></h1>
		<div class="operate-bar">
			<a href="<?=ViewBase::getUrl("receipt/purchaseReceipt/batchQtDone")?>" data-component="temtop/muloperate,confirm,async" data-muloperate-scope="#receive_list input[type=checkbox]" class="btn" id="btn-batch-qt" data-confirm-message="<?=t("是否确认执行")?>"><?=t("批量质检完毕")?></a>
			
		</div>
		<form action="<?=ViewBase::getUrl("receipt/purchaseReceipt/pressSkuQt",['id'=>$info->id])?>" class="frm" method="POST" data-component="async" id="receiveForm">
			<input type="hidden" name="id" value="<?=$info->id?>">
			<table class="data-tbl" id="receive_list">
				<thead>
					<tr>
						<th class="col-chk"><input  type="checkbox" data-component="checker"/></th>
						<th >SKU</th>
						<th ><?=t("产品名称")?></th>
						<th ><?=t("收货")?><?=t("数量")?></th>
						<th ><?=t("良品")?><?=t("数量")?></th>
						<th ><?=t("不良品")?><?=t("数量")?></th>
						<th ><?=t("备注")?></th>
						<th ><?=t("状态")?></th>
						<th class="col-min"><?=t("操作")?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					/**
					 * @var PurchaseReceiptQt $item
					 */
					foreach ($list as $item):?>
						<tr class="data-tr" data-status="<?=$item->status?>">
							<td class="col-chk">
								<?php if($item->status==PurchaseReceiptQt::STATUS_UNSETTLED): ?>
									<input type="checkbox" name="ids[]"  value="<?=$item->id?>"/>
								<?php endif; ?>
							</td>
							<td >
								<?=$item->product->sku;?>
								<input type="hidden" class="product_id" name="items[<?=$item->id?>][product_id]" value="<?=$item->product_id?>">
								<input type="hidden" class="item_id" name="items[<?=$item->id?>][item_id]" value="<?=$item->id?>">
							</td>
							<td class="td_limit">
								<div class="ch-name"><?=$item->product->name;?></div>
								<div class="en-name"><?=$item->product->ename;?></div>
							</td>
							<td>
								<?=$sku_receipt_qty_map[$item->product_id]?>
								<input type="hidden" class="expectNum" value="<?=$sku_receipt_qty_map[$item->product_id]?>"/>
							</td>
							<td>
								<input type="number" class="txt good_qty" name="items[<?=$item->id?>][good_qty]" min="0" step="1" value="<?=$item->good_qty?>" <?=$item->status == PurchaseReceiptQt::STATUS_QT?'readonly':''?>>
							</td>
							<td>
								<input type="number" class="txt bad_qty" name="items[<?=$item->id?>][bad_qty]" min="0" step="1" value="<?=$item->bad_qty?>" <?=$item->status == PurchaseReceiptQt::STATUS_QT?'readonly':''?>>
							</td>
							<td>
								<input type="text" class="txt note" name="items[<?=$item->id?>][note]" value="<?=$item->note?>" <?=$item->status == PurchaseReceiptQt::STATUS_QT?'readonly':''?>>
							</td>
							<td>
								<?=PurchaseReceiptQt::$status_map[$item->status]?>
							</td>
							
							<td class="col-op">
								<?php if($item->status==PurchaseReceiptQt::STATUS_UNSETTLED):?>
									<span class="btn qt" data-id="<?=$item->id?>"><?=t('保存')?></span>
									<?php if($item->good_qty or $item->bad_qty):?>
										<a class="btn qt-over" data-component="confirm,async" href="<?=ViewBase::getUrl('receipt/purchaseReceipt/batchQtDone',array('ids'=>$item->id))?>" data-confirm-message="<?=t("是否确认执行质检完毕")?>"><?=t('质检完毕')?></a>
									<?php endif;?>
								<?php endif;?>
							</td>
						</tr>
					<?php endforeach;?>
				</tbody>
			</table>
			<table class="frm-tbl">
				<thead></thead>
				<tbody>
				<tr class="">
					<td  class="col-label"><?=t("备注")?></td>
					<td  >
						<textarea cols="40" rows="7" name="qt_note"  class="txt" <?=$info->status == PurchaseReceipt::STATUS_CHECKED?'readonly':''?>><?=$info->qt_note?></textarea>
					</td>
				</tr>
				
				</tbody>
			</table>
			<?php if($info->status == PurchaseReceipt::STATUS_RECEIVED):?>
			<div class="operate-row">
				<input type="submit" class="btn" value="保存全部">
			</div>
			<?php endif;?>
		</form>
	</section>

	<script>
		seajs.use(["jquery",'ywj/net','ywj/msg'],function($,Net,Msg){
			$(".qt").click(function(){
				var url = "<?=ViewBase::getUrl('receipt/purchaseReceipt/pressSkuQt',['id'=>$info->id])?>";
				var id = $(this).data('id');
				url = Net.mergeCgiUri(url, {item_id: id});
				var fromData = $('form').serialize();
				Net.post(url,fromData,function(rsp){
					if(rsp.code!=0){
						Msg.showError(rsp.message);
						return;
					}
					Msg.showSuccess(rsp.message);
					location.reload();
				});
			});
		});
	</script>
	
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>