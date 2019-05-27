<?php
/**
 * Created by PhpStorm.
 * Date: 2019-01-22
 * Time: 15:02
 */

namespace ttwms;


/**
 * @var \ttwms\model\TransitDeliveryOrder $transit
 * @var array $get
 * @var array $printerConfig
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
<?= $this->buildBreadCrumbs(array('打印唛头')); ?>

<style>
	.paper-content {margin-left:20%;margin-top:5px;line-height:28px;font-size:14px;}
	#iframe{width:100%;height:300px;}
	.frm_tbl tr td{height:50px;}
</style>
<section class="container">
	<?php $url =ViewBase::getUrl("order/TransitDeliveryOrder/{$get['page']}",array('id'=>$transit->id,'paper_type'=>$printerConfig['paper_type'],'paper_width'=>$printerConfig['paper_width'],'paper_height'=>$printerConfig['paper_height'])); ?>
	<form action="<?=ViewBase::getUrl("order/TransitDeliveryOrder/printShipment") ?>" class="frm" method="POST" data-component="async">
		<iframe src="<?=ViewBase::getUrl($url)?>" frameborder="0" id="iframe"></iframe>
		<div class="paper-content">
			<table class="frm_tbl">
				<tr>
					<td>打印机：
						<select class="item-print-set">
							<option value="">--未设置--</option>
						</select>
					</td>
					<td>纸张大小：<?=$printerConfig['paper_type']=="A4"?"A4纸张":($printerConfig['paper_width']."&times;".$printerConfig['paper_height']." mm")?></td>
				</tr>
				<tr>
					<td>箱号：<input type="text" class="txt" id="box_no" placeholder="例如：1-5、7-9"></td>
				</tr>
			</table>
		</div>
		<div class="operate-row">
			<a href="javascript:;" class="btn" id="btn-print" target="_blank">打印</a>
			<a href="<?=$url?>" id="btn-download" class="btn" download="preview.html">下载HTML</a>
		</div>
	</form>
</section>

<script>
	seajs.use(['jquery',"ywj/msg", "ywj/net", "temtop/printer"], function($,Msg,Net, Printer){
		
		var CGI = "<?=$url?>";
		var paperType = "<?=$printerConfig['paper_type']?>";
		var paperWidth = "<?=$printerConfig['paper_width']?>";
		var paperHeight = "<?=$printerConfig['paper_height']?>";
	

		Msg.showLoading('正在加载打印机列表', 10);
		Printer.init(function(lodop){
			Msg.hide();
			//init printer list
			var printer_list = [];
			var printer_count = lodop.GET_PRINTER_COUNT();
			for(var i = 0; i < printer_count; i++){
				printer_list[i] = lodop.GET_PRINTER_NAME(i);
			}
			var printerIndex = "<?=$printerConfig['index']?>";
			for(var i = 0; i < printer_list.length; i++){
				$('<option value="' + i + '" '+(i== $.cookie(printerIndex)?'selected':"")+'>' + printer_list[i] + '</option>').appendTo($(".item-print-set"));
			}
		});
		$('#box_no').change(function(){
			var boxNo = $("#box_no").val();
			var href = Net.mergeCgiUri(CGI,{paper_type:paperType,paper_width:paperWidth,paper_height:paperHeight,box_no:boxNo});
			$('#iframe').attr('src',href);
			$('#btn-download').attr('href',href);

		});
		
		$("#btn-print").click(function(){
			var boxNo = $("#box_no").val();
			toPrinter(paperType,paperWidth,paperHeight,boxNo);
			return false;
		});

		var toPrinter = function(paperType,paperWidth,paperHeight,boxNo){
			var printer_index = $(".item-print-set").val();
			var url = Net.mergeCgiUri(CGI,{paper_type:paperType,paper_width:paperWidth,paper_height:paperHeight,box_no:boxNo});
			var direction = 2;//打印方向，纵向
			if(paperType == 'A4'){
				direction = 1;
			}
			Printer.printURL_new(url, printer_index, direction, paperHeight*10, paperWidth*10);
		}
	});
</script>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>
