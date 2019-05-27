<?php
namespace ttwms;

use ttwms\model\PrdProduct;
use function Temtop\t;

/**
 * @var \ttwms\model\PrdProduct $product
 * @var \ttwms\model\PrdProduct[] $products
 * @var array $isActiveList
 * @var array $param
 * @var string $logList
 * @var string $model
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');


?>
<?= $this->buildBreadCrumbs(array('产品管理'=>'prd/product/index',"打印")); ?>
<section class="container">
<style>
	.scroll-wrap {max-height:300px; overflow-y:scroll;}
	.paper-content {margin-top:5px;}
    .data-tbl{margin-top: 0px;}
	#skuForm table thead th,#skuForm table tbody td{text-align: center}
</style>
<div id="col-main">
	<form action="<?=ViewBase::getUrl("prd/product/print")?>" method="GET" class="frm" id="skuForm">

			<table class="data-tbl scroll-tbl">
				<thead>
				<tr>
					<th >SKU</th>
					<th ><?=t("条码")?></th>
					<th ><?=t("数量")?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($products as $product):?>
					<tr>
						<td><?=$product->sku ?></td>
						<td>
						<?php if (in_array($product['barcode_type'], array(PrdProduct::BARCODE_TYPE_OWH))):?>
							<?=$product->barcode?>
						<?php else:?>
							<?= $product['sku'] ?>
						<?php endif;?>

						<td>
							<input type="number" min="1" step="1" class="txt sku-id" value="<?= $param['qty']?:1 ?>" name="<?=$product->id?>"/>
						</td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		<div class="paper-content">
			<label for=""><?= t('打印纸大小(宽*高,单位mm)') ?>:
				<input type="number" class="other-size txt o-width"  step="1" min="1" placeholder="宽" value="">
				<input type="number" class="other-size txt o-height" step="1" min="1" placeholder="高" value="">
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
		if(printer_index =='' || printer_index == null|| printer_index == 'undefined'||printer_index=='*'){
			var val = paperWidth+'*'+ paperHeight;
			$.cookie(paper_key, val, {expires: 365, path: '/'});
		}
		Printer.getPrinter('print_barcode', function (printer_index) {
			$("#skuForm").find('.sku-id').each(function () {
				var $this = $(this);
				var number = $this.val();
				var product_id = $this.attr('name');
				var url = Net.mergeCgiUri("<?=ViewBase::getUrl('prd/product/doPrint')?>", {product_id:product_id, paper_width:paperWidth, paper_height:paperHeight});
				Printer.printURL_new(url, printer_index, 1, paperWidth*10, paperHeight*10, function(){}, number);
			});
		});
		return false;
	});


	Printer.init(function(){
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