<?php
namespace ttwms;

use ttwms\model\PurchaseReceiptBox;
use ttwms\model\PurchaseReceiptBoxItem;
use function Temtop\t;

/**
 * @var $list
 * @var string $model
 * @var string $mode
 * @var string $url
 * @var PurchaseReceiptBox $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('收货')); ?>
	
	<style>
		#codeInput{ font-size: 20px;}
		#codeInput input{ font-size: 20px;}
		.autoFill {float:right;}
		h2 { font-weight:normal; font-size:22px; padding-bottom:0.5em;}
		.td_limit{max-width:200px !important;-ms-word-break: break-all;word-break: break-all;}
	</style>
	<section class="container">
		<div class="frm">
			<span id="codeInput">
				SKU: <input placeholder="<?=t("扫描枪扫描条形码")?>" class="txt no" type="text" id="code"/>
			</span>
			<div class="autoFill">
				<a class="btn" href="javascript:;" id="autoFill_btn"><?=t("快速填充")?></a>
			</div>
		</div>
		<h2 style="text-align:center;"><?=t("箱号")?>:<?=$info->no?></h2>
		<form action="<?=ViewBase::getUrl("receipt/purchaseReceipt/receipt")?>" class="frm <?=$mode?'':'readonly'?>" method="POST" data-component="async" id="receiveForm">
			<input type="hidden" name="id" id="box_id" value="<?=$info->id?>">
			<input type="hidden" id="enterpriseCode" value="<?=$info->receipt->code?>">
			<table class="data-tbl" id="receive_list">
				<thead>
				<tr>
					<th>SKU</th>
					<th ><?=t("产品名称")?></th>
					<th ><?=t("数量")?></th>
					<th ><?=t("收货")?><?=t("数量")?></th>
				</tr>
				</thead>
				<tbody>
				<?php /** @var PurchaseReceiptBoxItem[] $list */
				foreach ($list as $item):?>
					<tr class="data-tr" id="<?=$item->product->sku?>">
						<td><?=$item->product->sku;?>
							<input type="hidden" class="item_id" name="items[<?=$item->id?>][item_id]" value="<?=$item->id?>"/>
							<input type="hidden" class="product_id" name="items[<?=$item->id?>][product_id]" value="<?=$item->product->id?>">
						</td>
						<td class="td_limit">
							<div class="ch-name"><?=$item->product->name?></div>
							<div class="en-name"><?=$item->product->ename?></div>
						</td>
						<td class="expectNum"><?=$item->qty?><input type="hidden" name="items[<?=$item->id?>][expectNum]"  value="<?=$item->qty?>"></td>
						<td><input type="number" class="txt receive_qty" name="items[<?=$item->id?>][receive_qty]" required='required' min="0" step="1" value="<?=$item->receive_qty?>"  title=""/></td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
			<div class="operate-row">
				<input type="submit" class="btn" value="<?=t("保存所有")?>" id="receiveSubmit"/>
				<?=ViewBase::getDialogCloseBtn()?>
			</div>
		</form>
	</section>
	
	<script type="text/template" id="row-template">
		<tr>
			<td align="center" width="230">
				<input type="text" class="choose-location-code txt" name="" title="">
				<a href="<?=ViewBase::getUrl("wh/location/simpleview")?>"  data-onsuccess="chooseLocation" data-component="popup" data-popup-width="800" ><?=t("选择货位")?></a>
			</td>
			<td width="40"><input type='number' class="txt real_qty" name="" min="0" step="1" title=""></td>
			<td width="80"><span class="small-btn small-delete-btn" rel="row-delete-btn" data-allow-empty="true"><?=t("删除")?></span></td>
		</tr>
	</script>
	<script>
		<?php if($mode==false){?>
		$("#codeInput").hide();
		$("#autoFill_btn").hide();
		$("a[data-component='popup']").hide();
		<?php } ?>

		seajs.use(["jquery", 'ywj/net', 'ywj/popup','ywj/msg'], function($, Net, Pop,Msg){
			var code = $("#code");
			code.focus();
			$(window).keydown(function(event){
				if(event.keyCode==13) {
					if(code.val().length<3){
						return false;
					}
					$.ajax({
						url:'<?=ViewBase::getUrl("receipt/purchasereceipt/getsku")?>',
						type:"GET",
						data:{code:code.val(),enterpriseCode:$('#enterpriseCode').val()},
						success:function(data){
							if(data.length==0){
								Msg.showError("<?=t("条码未找到")?>");
								return false;
							}
							code.val(data);
							//显示当前条码的，隐藏其他的
							$("#receive_list tbody tr").hide();
							//所有表单只读
							$("#receive_list tbody tr input").attr("disabled","disabled");
							$("#"+data).show();
							$("#"+data).find('.location-tbl tr').show();
							$("#"+data+" input").attr("disabled",false);
						}
					});
				}
			});

			//快速填充
			$("#autoFill_btn").click(function(){
				$("#receive_list .data-tr").each(function(){
					var qty=$(this).find(".expectNum").text();
					$(this).find(".put").val(qty);
					$(this).find('span[rel="row-delete-btn"]').click();
					$(this).find('span[rel="row-append-btn"]').click();
					$(this).find('.receive_qty').val(qty);
					var inter = window.setInterval(function(){
						if($('.choose-location-code').length){
							$('.choose-location-code').each(function(i,k){
								var qty = $(this).closest('.data-tr').find('.expectNum').text();
								var code = $(this).closest('.data-tr').find('.rettwmsend_code').text();
								$(this).val(code);
								$(this).closest('tr').find('.real_qty').val(qty);
								window.setName($(this));
							});
							window.clearInterval(inter);
						}
					},100);

					//隐藏单条的和sku条码
					$("#codeInput").hide();
					$(".saveReceive").hide();
				});
			});


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
				obj.closest('tr').find('.real_qty').attr('name','items['+item_id+'][real_qty][]');
				obj.closest('tr').find(".choose-location-code").attr('name',code);
			};

			Pop.autoResizeCurrentPopup();
		});
	</script>
	
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>