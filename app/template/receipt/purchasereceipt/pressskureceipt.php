<?php
namespace ttwms;

use ttwms\model\PurchaseReceipt;
use ttwms\model\PurchaseReceiptBox;
use function Temtop\t;

/**
 * @var $list
 * @var string $mode
 * @var array $param
 * @var PurchaseReceipt $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
	<?php
echo $this->buildBreadCrumbs(array('入库单'=>'receipt/purchaseReceipt/index',"按SKU收货"));
?>
	<style>
		.td_limit{max-width:200px !important;-ms-word-break: break-all;word-break: break-all;}
	</style>
	<section class="container">
	
		<h1 style="text-align: center"><?=t('入库单号')?>:<?=$info->receipt_no?></h1>
		<div class="operate-bar">
			<a href="<?=ViewBase::getUrl("receipt/purchaseReceipt/batchReceiptDone")?>" data-component="temtop/muloperate,confirm,async" data-muloperate-scope="#receive_list input[type=checkbox]" class="btn" id="btn-batch-receipt" data-confirm-message="<?=t("是否确认执行")?>"><?=t("批量收货完毕")?></a>
			
			<span style="display: block;float: right;">
				<a class="btn" href="javascript:;" id="autoFillBtn"><?=t("快速填充")?></a>
			</span>
		</div>
		<form action="<?=ViewBase::getUrl("receipt/purchaseReceipt/pressskureceipt",['id'=>$info->id])?>" class="frm" method="POST" data-component="async" id="receiveForm">
			<input type="hidden" name="id" value="<?=$info->id?>">
			<table class="data-tbl" id="receive_list">
				<thead>
					<tr>
						<th class="col-chk"><input  type="checkbox" data-component="checker"/></th>
						<th >SKU</th>
						<th ><?=t("产品名称")?></th>
						<th ><?=t("装箱数量")?> x <?=t('箱数')?></th>
						<th ><?=t("预报数量")?></th>
						<th ><?=t("收货")?><?=t("数量")?></th>
						<th class="col-min"><?=t("操作")?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($list as $item):$group=[];?>
						<tr class="data-tr" data-status="<?=$item['status']?>">
							<td class="col-chk">
								<?php if($item['status']==PurchaseReceiptBox::STATUS_UNSETTLED): ?>
									<input type="checkbox" name="ids[]" data-ids="<?=implode(',',$item['box_id'])?>" data-product-id="<?=$item['product_id']?>" value="<?=$item['product_id']?>"/>
								<?php endif; ?>
							</td>
							<td >
								<?=$item['sku']?>
								<input type="hidden" class="product_id" name="items[<?=$item['product_id']?>][product_id]" value="<?=$item['product_id']?>">
							</td>
							<td class="td_limit">
								<div class="ch-name"><?=$item['name']?></div>
								<div class="en-name"><?=$item['ename']?></div>
							</td>
							<td>
								<?php foreach($item['boxDetail'] as $v):?>
									<?php $group[$v][] = $v?>
								<?php endforeach;?>
								<?php foreach($group as $g=>$count):?>
									<?=$g?> x <?=count($count)?><br/>
								<?php endforeach;?>
							</td>
							<td>
								<input type="hidden" class="expectNum" name="items[<?=$item['product_id']?>][expectNum]" value="<?=$item['qty']?>"/>
								<?=$item['qty']?>
							</td>
							<td>
								<input type="number" class="txt receive_qty" name="items[<?=$item['product_id']?>][receive_qty]" min="0" step="1" value="<?=$item['receive_qty']?>" <?=$item['status'] == PurchaseReceiptBox::STATUS_RECEIVED?'readonly':''?>>
							</td>
							
							<td class="col-op">
								<?php if($item['status']==PurchaseReceiptBox::STATUS_UNSETTLED):?>
									<span class="btn receipt" data-product-id="<?=$item['product_id']?>"><?=t('收货')?></span>
									<?php if($item['receive_qty']):?>
										<a class="btn" data-component="async" href="<?=ViewBase::getUrl('receipt/purchaseReceipt/batchReceiptDone',array('ids'=>implode(',',$item['box_id'])))?>"><?=t('确认收货')?></a>
									<?php endif;?>
								<?php endif;?>
							</td>
						</tr>
					<?php endforeach;?>
				</tbody>
			</table>
		</form>
	</section>
	<script type="text/template" id="row-template">
		<tr class="locationDetail">
			<td align="center" width="230">
				<input type="text" class="choose-location-code txt" name="" value="">
				<a href="<?=ViewBase::getUrl("wh/location/simpleview")?>"  data-onsuccess="chooseLocation" data-component="popup" data-popup-width="800" ><?=t("选择货位")?></a>
			</td>
			<td width="80"><input type='number' class="txt real_qty" name="" value=""></td>
			<td width="60"><span class="small-btn small-delete-btn" rel="row-delete-btn" data-allow-empty="true"><?=t("删除")?></span></td>
		</tr>
	</script>
	<script>
		seajs.use(["jquery",'ywj/net','ywj/msg'],function($,Net,Msg){
			
			var $autoFillBtn = $("#autoFillBtn");
			//快速填充
			$autoFillBtn.click(function(){
				$("#receive_list .data-tr").each(function(){
					var status = $(this).data('status');
					if(status == "<?=PurchaseReceiptBox::STATUS_UNSETTLED?>"){
						var qty=$(this).find(".expectNum").val();
						$(this).find(".receive_qty").val(qty);
					}
				});
			});
			
			$(".receipt").click(function(){
				var url = "<?=ViewBase::getUrl('receipt/purchaseReceipt/pressskureceipt')?>";
				var productId = $(this).data('product-id');
				url = Net.mergeCgiUri(url, {product_id: productId});
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

			//批量收货完毕
			$("#btn-batch-receipt").click(function(){
				var checked = $("#receive_list tbody").find("input[type=checkbox]:checked");
				var idsString = '';
				$.each(checked, function(){
					var ids = $(this).data('ids');
					idsString = idsString+','+ids;
				});
				var url = "<?=ViewBase::getUrl("receipt/purchaseReceipt/batchReceiptDone")?>";
				url = Net.mergeCgiUri(url,{ids:idsString});
				$(this).attr('href',url);
			});
			
		});
	</script>
	
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>