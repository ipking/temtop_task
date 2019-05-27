<?php
namespace ttwms;

use ttwms\business\Form;
use ttwms\model\PurchaseReceipt;
use ttwms\model\PurchaseReceiptQt;
use ttwms\model\WhLocation;
use ttwms\model\WhProductLocationMapping;
use function Temtop\t;

/**
 * @var $list
 * @var string $type
 * @var string $mode
 * @var array $param
 * @var PurchaseReceipt $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

$sku_predict_qty_map = $info->sku_predict_qty_map;
$sku_receipt_qty_map = $info->sku_receipt_qty_map;
$sku_qt_good_qty_map = $info->sku_qt_good_qty_map;
$sku_qt_bad_qty_map = $info->sku_qt_bad_qty_map;
$sku_put_good_qty_map = $info->sku_put_good_qty_map;
$sku_put_bad_qty_map = $info->sku_put_bad_qty_map;
?>
<?php
echo $this->buildBreadCrumbs(array('入库单'=>'receipt/purchaseReceipt/index',"上架"));
?>
	<style>
		.td_limit{max-width:200px !important;-ms-word-break: break-all;word-break: break-all;}
	</style>
	<?php
$active[$type] = "class='active'";
?>
	<ul class="tab">
		<li <?= $active[Form::GOODS_TYPE_GOOD] ?> ><a
					href="<?= ViewBase::getUrl("receipt/purchaseReceipt/pressSkuPut", array(
						'type'=>Form::GOODS_TYPE_GOOD,
						"id"         => $info->id
					)) ?>"><?= t("良品上架") ?></a></li>
		<li <?= $active[Form::GOODS_TYPE_BAD] ?> ><a
					href="<?= ViewBase::getUrl("receipt/purchaseReceipt/pressSkuPut", array(
						'type'=>Form::GOODS_TYPE_BAD,
						"id"         => $info->id
					)) ?>"><?= t("不良品上架") ?></a></li>
	</ul>

	<section class="container">
	
		<h1 style="text-align: center"><?=t('入库单号')?>:<?=$info->receipt_no?></h1>
		
		<div class="operate-bar">
			<a href="<?=ViewBase::getUrl("receipt/purchaseReceipt/batchPutDone",['type'=>$type])?>" data-component="temtop/muloperate,confirm,async" data-muloperate-scope="#receive_list input[type=checkbox]" class="btn" id="btn-batch-put" data-confirm-message="<?=t("是否确认执行")?>"><?=t("批量上架完毕")?></a>
			<input type="button" data-href="<?=ViewBase::getUrl("receipt/purchaseReceipt/printBySku", array('id'=>$info->id,'type'=>$type))?>" class="btn btn-disabled <?=$mode?>" data-component="temtop/muloperate" id="btn-print-sku" <?php if($param['sku']!=''){echo "disabled='disabled'";}?> value="<?=t("打印上架单(SKU)")?>">
		</div>
		<form action="<?=ViewBase::getUrl("receipt/purchaseReceipt/pressSkuPut",['id'=>$info->id,'type'=>$type])?>" class="frm" method="POST" data-component="async" id="receiveForm">
			<input type="hidden" name="id" value="<?=$info->id?>">
			<table class="data-tbl" id="receive_list">
				<thead>
					<tr>
						<th class="col-chk"><input  type="checkbox" data-component="checker"/></th>
						<th >SKU</th>
						<th ><?=t("产品名称")?></th>
						<th ><?=t("预报")?><?=t("数量")?></th>
						<th ><?=t("收货")?><?=t("数量")?></th>
						<?php if($type == Form::GOODS_TYPE_GOOD):?>
					
						<th ><?=t("良品")?><?=t("数量")?></th>
						<th ><?=t("良品")?><?=t("上架")?><?=t("数量")?></th>
				
						<?php else:?>
						<th ><?=t("不良品")?><?=t("数量")?></th>
						<th ><?=t("不良品")?><?=t("上架")?><?=t("数量")?></th>
		
						<?php endif;?>
						<th ><?=t("实际存放货位")?></th>
						<th ><?=t("存放数量")?></th>
						<th class="col-min"><?=t("操作")?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					/**
					 * @var \ttwms\model\PurchaseReceiptPutGood $item
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
								<input type="hidden" class="receipt_id" name="items[<?=$item->id?>][receipt_id]" value="<?=$item->receipt_id?>">
							</td>
							<td class="td_limit">
								<div class="ch-name"><?=$item->product->name;?></div>
								<div class="en-name"><?=$item->product->ename;?></div>
							</td>
							<td>
								<?=$sku_predict_qty_map[$item->product_id]?>
							</td>
							<td>
								<?=$sku_receipt_qty_map[$item->product_id]?>
							</td>
							<?php if($type == Form::GOODS_TYPE_GOOD):?>
									<td>
										<?=$sku_qt_good_qty_map[$item->product_id]?>
									</td>
									<td>
										<?=$sku_put_good_qty_map[$item->product_id]?>
									</td>
							<?php else:?>
									<td>
										<?=$sku_qt_bad_qty_map[$item->product_id]?>
									</td>
									<td>
										<?=$sku_put_bad_qty_map[$item->product_id]?>
									</td>
							<?php endif;?>
							
							<td colspan="2">
								<table id="<?=$item->id?>" width="100%" class="location-tbl">
									<tr></tr>
									<?php
									$put = WhProductLocationMapping::find('ref_id=? and product_id=? and ref_type=? and goods_type = ?',$item->id,$item->product_id,Form::TYPE_RO_IN,$type)->all();
									foreach($put as $row):
										$location = WhLocation::find('id=?',$row['location_id'])->one();
										?>
										<tr>
											<td align="center" width="230">
												<input type="text" class="choose-location-code txt" value="<?=$location->code?>" readonly name="items[<?=$item->id?>][location_code][]"  title="">
												<a href="<?=ViewBase::getUrl("wh/location/simpleview")?>"  data-onsuccess="chooseLocation" data-component="popup" data-popup-width="800" ><?=t("选择货位")?></a>
											</td>
											<td width="40"><input type='number' class="txt put_qty" min="0" step="1" name="items[<?=$item->id?>][put_qty][]" value="<?=$row->qty?>" required='required' title=""/></td>
											<td width="80"><span class="small-btn small-delete-btn" rel="row-delete-btn" data-allow-empty="true"><?=t("移除")?></span></td>
										</tr>
									<?php endforeach; ?>
										<tfoot>
										<tr>
											<td colspan="3"><span class="small-btn small-add-btn " rel="row-append-btn" data-tpl="row-template"><?=t("新增一行")?></span></td>
										</tr>
										</tfoot>
								</table>
							</td>
							<td class="col-op">
								<?php if($item->status==PurchaseReceiptQt::STATUS_UNSETTLED):?>
									<span class="btn put" data-id="<?=$item->id?>"><?=t('上架')?></span>
									<?php if($item->put_qty):?>
										<a class="btn" data-component="async" href="<?=ViewBase::getUrl('receipt/purchaseReceipt/batchPutDone',array('ids'=>$item->id,'type'=>$type))?>"><?=t('确认上架')?></a>
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
		<tr>
			<td align="center" width="230">
				<input type="text" class="choose-location-code txt" name="" title="">
				<a href="<?=ViewBase::getUrl("wh/location/simpleview")?>"  data-onsuccess="chooseLocation" data-component="popup" data-popup-width="800" ><?=t("选择货位")?></a>
			</td>
			<td width="40"><input type='number' class="txt put_qty" name="" min="0" step="1" title=""></td>
			<td width="80"><span class="small-btn small-delete-btn" rel="row-delete-btn" data-allow-empty="true"><?=t("删除")?></span></td>
		</tr>
	</script>
	<script>
		seajs.use(["jquery",'ywj/net','ywj/msg',"temtop/printer"],function($,Net,Msg,Printer){
			$('.choose-location-code').live('focus', function(){
				window.setName($(this));
			});

			//选择货位
			window.chooseLocation=function(data){
				window.setName($(this).parent().find(".choose-location-code"));
				$(this).parent().find(".choose-location-code").val(data.code);
			};

			window.setName=function(obj){
				var item_id = obj.closest('table').attr('id');
				var code = "items["+item_id+"][location_code][]";
				obj.closest('tr').find('.put_qty').attr('name','items['+item_id+'][put_qty][]');
				obj.closest('tr').find(".choose-location-code").attr('name',code);
			};
			
			$(".put").click(function(){
				var url = "<?=ViewBase::getUrl('receipt/purchaseReceipt/pressSkuPut',['id'=>$info->id,'type'=>$type])?>";
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

			//打印选中sku
			$("#btn-print-sku").click(function(){
				var $this = $(this);
				var url = $this.data('href');
				var data = $this.data('muloperate-value');
				var checked = $("#receive_list tbody").find("input[type=checkbox]:checked");
				var productIdsString = '';
				$.each(checked, function(){
					var productIds = $(this).data('product-id');
					productIdsString = productIdsString+','+ productIds;
				});
				if(!productIdsString){
					return;
				}
				url = Net.mergeCgiUri(url,{product_id:productIdsString,paper_type:'print_put'});
				Msg.showLoading('<?=t("正在打印中")?>');
				Printer.getPrinter('print_put', function(printer_index){
					Printer.printURLPost(url, printer_index, null, null, null, function(){
						Msg.hide();
						Msg.showSuccess('<?=t("已发送至打印任务")?>');
					},data);
				});
				return false;
			});
			
		});
	</script>
	
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>