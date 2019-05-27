<?php
namespace ttwms;

use function Temtop\t;

/**
 * @var \ttwms\model\PrdProduct $info
 * @var array $isActiveList
 * @var array $get
 * @var string $logList
 * @var string $model
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');


?>
<?= $this->buildBreadCrumbs(array('产品管理'=>'prd/product/index',"打印自定义条码")); ?>
<section class="container">
	<style>
		.scroll-wrap {max-height:300px; overflow-y:hidden;}
		.paper-content {margin-top:5px;}
	</style>
	<div id="col-main">
		<form action="<?=ViewBase::getUrl("prd/product/printSelfBarcode")?>" method="GET" class="frm" id="skuForm">
			<div class="scroll-wrap">
				<table class="data-tbl" id="msg-tbl">
					<thead>
					<tr>
						<th ><?= t('条码内容') ?></th>
						<th ><?=t("数量")?></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td align="center"><input type="text" class="txt barcode-content" value="" name="barcode_content"></td>
						<td align="center">
							<input type="number" min="1" step="1" class="txt sku-id barcode-number" value="1" name="qty"/>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
			<div class="paper-content">
				<label for=""><?= t('打印纸大小(宽*高,单位mm)') ?>:
					<input type="number" class="other-size txt o-width"  step="1" min="1" placeholder="宽">
					<input type="number" class="other-size txt o-height" step="1" min="1" placeholder="高">
				</label>
			</div>
			<div class="operate-row">
				<a href="javascript:;" class="btn" id="btn-print" target="_blank"><?=t("打印")?></a>
				<?=ViewBase::getDialogCloseBtn()?>
			</div>
		</form>
	</div>
	<script>
		seajs.use(["jquery","temtop/printer","ywj/popup", "ywj/msg", "ywj/net"],function($,Printer,Popup, Msg, Net){
			var $width = $('.o-width');
			var $height = $('.o-height');
			var paper_key = 'PRODUCT_BARCODE_SIZE';
			var printer_index = $.cookie(paper_key);
			$("#btn-print").click(function(){
				var paperWidth,paperHeight;
				paperWidth = $width.val();
				paperHeight = $height.val();
				if(paperWidth<=0||paperHeight<=0){
					Msg.showError("<?= t("请正确填写纸张大小") ?>");
					return;
				}
				var barcodeContent = $.trim($(".barcode-content").val());
				var barcodeNumber = $(".barcode-number").val();

				if (barcodeContent.length <= 0) {
					Msg.showError("<?= t("请填写条形码内容") ?>");
					return;
				}
				if(printer_index =='' || printer_index == null|| printer_index == 'undefined'||printer_index=='*'){
					var val = paperWidth+'*'+ paperHeight;
					$.cookie(paper_key, val, {expires: 365, path: '/'});
				}
				Printer.getPrinter('print_barcode', function (printer_index) {
					var url = Net.mergeCgiUri("<?=ViewBase::getUrl('prd/product/printSelfBarcode')?>", {content:barcodeContent, paper_width:paperWidth, paper_height:paperHeight});
					Printer.printURL_new(url, printer_index, 1, paperWidth*10, paperHeight*10, function(){}, barcodeNumber);
				});
				return false;
			});
			Printer.init(function(){
				var paper_key = 'PRODUCT_BARCODE_SIZE';
				var printer_index = $.cookie(paper_key);
				if(printer_index !== '' && printer_index !== null){
					var arr = printer_index.split('*');
					$width.val(arr[0]);
					$height.val(arr[1]);
				}
			}, function(){
				Printer.showInstall();
			});
		});
	</script>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>