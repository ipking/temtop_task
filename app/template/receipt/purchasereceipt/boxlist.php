<?php
namespace ttwms;

use ttwms\model\PurchaseReceipt;
use ttwms\model\PurchaseReceiptBox;
use function Lite\func\h;
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
	<?php if($mode != 'view'){
	echo $this->buildBreadCrumbs(array('入库单'=>'receipt/purchaseReceipt/index',"操作收货"));
} else {
	echo $this->buildBreadCrumbs(array('入库单'=>'receipt/purchaseReceipt/index',"查看"));
} ?>
<style>
	.operate-bar{margin: 5px 0}
	.receipt-no {text-align: center; padding:0.7em 0; margin-bottom:-0.3em;}
	#tag_form{text-align:right; float:right;}
	.data-tbl .col-op {text-align:left;}
	.locate-frm {float:right; margin-left:10px;}
	#tag_no {text-align:center; width:80px;}
</style>
	<section class="container">
		
		<h1 class="receipt-no"><?=t("入库单号")?>:<?=$info->receipt_no?></h1>
		<div class="operate-bar">
			<?php if($mode != 'view'){ ?>
			<a href="<?=ViewBase::getUrl("receipt/purchaseReceipt/batchReceiptDone")?>" data-component="temtop/muloperate,confirm,async" class="btn <?=$mode?>" id="btn-batch-Receipt" data-confirm-message="<?=t("是否确认执行")?>"  <?php echo $param['sku'] ? "disabled='disabled'" : '';?>><?=t("批量收货完毕")?></a>
			<?php } ?>

			<div class="locate-frm">
				<input type="number" min="1" class="txt" id="tag_no" placeholder="<?=t("箱号")?>">
				<span class="btn" id="tag_btn"><?=t("定位到")?></span>
			</div>

			<form id="tag_form" action="<?=ViewBase::getUrl("receipt/purchaseReceipt/boxlist",array("id"=>$info->id,"mode"=>"view"))?>" method="get">
				<input type="text" class="txt" placeholder="SKU" name="sku" value="<?=$param['sku']?>">
				<input type="submit" class="btn" value="<?=t("搜索")?>">
			</form>
		</div>

		<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
			<thead>
				<tr>
					<?php if($mode!='view'):?>
						<th class="col-chk"><input class="<?=$mode?>" type="checkbox" data-component="checker"/></th>
					<?php endif;?>
					<th><?=t("箱号")?></th>
					<th><?=t("数量")?></th>
					<th><?=t("已收货数量")?></th>
					<th class="col-min"><?=t("状态")?></th>
					<th><?=t("收货说明")?></th>
					<th class="col-op"><?=t("操作")?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($list as $k=>$box): ?>
				<tr>
                    <?php if($mode!='view'):?>
                        <td class="col-chk">
                            <?php if($box['status']!=PurchaseReceiptBox::STATUS_RECEIVED): ?>
                                <input type="checkbox" name="ids[]" data-status="<?=$box['status']?>" value="<?=$box['id']?>"/>
                            <?php endif; ?>
                        </td>
                    <?php endif;?>
					<td id="box_<?=$box['no']?>"><?=$box['no']?></td>
					<td><?=$box['allCount']?></td>
					<td><?=$box['allReceive']?></td>
					<td class="col-min"><?=$box['status']==PurchaseReceiptBox::STATUS_UNSETTLED?t("待处理"):t("已收完")?></td>
					<td>
						<input type="text" class="txt" value="<?=h($box['note'])?>" readonly>
						<a href="<?=ViewBase::getUrl("receipt/purchaseReceipt/note",array('id'=>$box['id']))?>" data-component="popup"><?=t("添加收货说明")?></a>
					</td>
					<td class="col-op">
						<a class="btn" href="<?=ViewBase::getUrl("receipt/purchaseReceipt/boxview",array('id'=>$box['id']))?>" data-popup-width="950" data-component="popup"><?=t("查看")?></a>
						<?php if($box['status']==PurchaseReceiptBox::STATUS_UNSETTLED): ?>
						<a class="btn" href="<?=ViewBase::getUrl("receipt/purchaseReceipt/receipt",array('id'=>$box['id']))?>" data-component="popup" data-popup-width="950"><?=t("收货")?></a>
							<?php if($box['allReceive']):?>
								<a class="btn" href="<?=ViewBase::getUrl("receipt/purchaseReceipt/batchReceiptDone",array('ids'=>$box['id']))?>"
								   data-component="confirm,async"
								   data-confirm-message="<?=$box['allCount'] == $box['allReceive'] ? t('确认收货完毕？') : t('实际收货数量与清单数量不一致，是否确认收货完毕？');?>"
								   data-async-timeout="120"><?=t("收货完毕")?></a>
							<?php endif;?>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
	<script>
		seajs.use(["jquery", "temtop/printer", 'ywj/msg'], function($, Printer, Msg){
			var locate = function(){
				var no = $("#tag_no").val();
				var url = window.location.toString();
				var t_url = url.split("#")[0];
				window.location = t_url + "#box_" + no;
			};

			$('#tag_no').keydown(function(e){
				if(e.keyCode == 13){
					locate();
					return false;
				}
			});

			//锚点定位
			$("#tag_btn").click(locate);


		
			//批量收货完毕
			$("#btn-batch-Receipt").click(function(){
				var checked = $("input[type=checkbox]:checked");
				var ids = [];
				var flag = false;
				$.each(checked, function(i, n){
					var v = $(n).val();
					var status = $(n).attr('data-status');
					if(status == '<?=PurchaseReceiptBox::STATUS_RECEIVED?>'){
						flag = true;
					}
					ids.push(v);
				});
				if(flag){
					alert('<?=t("不能重复操作已完毕的项目")?>');
					return false;
				}
				if(!ids.join(",")){
					alert('<?=t("请选择至少一个项目")?>');
					return false;
				}
				$(this).attr("href", '<?=ViewBase::getUrl("receipt/purchaseReceipt/batchReceiptDone")?>' + "?ids=" + ids.join(","));
			});
			

			var url = window.location.toString();
			var id = url.split("#")[1];
			if(id){
				setTimeout(function(){
					var t = $("#" + id).offset().top;
					$(window).scrollTop(t);
				}, 1000)
			}
		});

	</script>
	
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>