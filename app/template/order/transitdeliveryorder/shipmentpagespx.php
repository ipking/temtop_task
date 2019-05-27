<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/html">
<head>
	<title>唛头打印-<?= time() ?></title>
	<style>
		body {
			font:14px/150% Arial, Helvetica, sans-serif, '宋体';margin:2px 0 0 0;padding:0;
		}
		table {border-spacing:0;}
		.list_tb th,.list_tb td{padding-top:2px;}
		tbody,tr,td,p,span{margin:0; padding:0;}
		.content{margin: 0 auto;padding:0;width: <?=$get['paper_width']*0.1?>cm;}
		.wrap{
			box-sizing:border-box;
			border: 0 solid #000;
			page-break-after:always;
			margin:10px auto;
			width:<?=$get['paper_width']*0.1*0.9?>cm;
			height:<?=$get['paper_height']*0.1*0.9?>cm;
			position:relative;
			padding:5px;}
		.list_tb,.print_tb{width:100%;}

		.print_tb img{margin:3px 0 0 0;}
		.print_tb .img-span{display:inline-block;width:100%;text-align:center;float:left;font-weight: bold;}


		.print_tb .detail{border-bottom:2px solid #000;}
		.print_tb .detail span{display:inline-block;font-size:14px;font-weight:bolder;padding-right:10px;}
		.list_tb th{font-weight: bold;border-bottom:2px dashed #000; }
		.list_tb td{text-align: center;font-weight: bold;border-bottom:2px dashed #000; }
	</style>
</head>
<body>
<?php
use function Lite\func\ha;

/**
 * @var \ttwms\model\PrdProduct[] $productList
 * @var array $boxList
 * @var array $boxNoList
 * @var array $get
 * @var array $sku_type_map
 * @var \ttwms\model\TransitDeliveryOrder $transit
 */
?>
<div class="content">
	<?php foreach ($boxList as $box):
		if($boxNoList && !in_array($box['box_no'],$boxNoList)){
			continue;
		}
		?>
		<?php
		/**
		 * @var \ttwms\model\TransitDeliveryOrderItem $item
		 */
		$qty = 0;
		foreach($box['item'] as $key=>$item){
			$qty+=$item->quantity;
		}
		?>
	
		<?php if($qty!=1):?>
		<div class="wrap">
			<table class="print_tb" >
				<tr>
					<td class="info">
						<p style="font-size:13px;font-weight:bolder;"><span style="border: 1px solid #000;padding: 0 2px;font-weight: 100;border-radius: 2px;margin-right: 3px;">M</span>Multiple items inside Open box for picking</p>
						<div class="img-span">
							<img src="<?php echo \Temtop\component\TBase64::barcodeJpg($box['box_barcode'],55,'C128',2); ?>">
							<p><?=$box['box_barcode']?></p>
						</div>

					</td>
				</tr>
				<tr>
					<td class="detail">
						<span>IC NO：<?=$transit->shipment_code?></span>
						<span>WH：<?=$transit->target_wh_code?></span>
						<span>Customer Code：<?=$transit->enterprise->code;?></span>
					</td>
				</tr>
				<tr>
					<td>
						<table class="list_tb" cellpadding="0" cellspacing="0">
							<tr>
								<th class="col-min">No.</th>
								<th class="col-min">SKU</th>
								<th>SKU Name</th>
								<th>Batch No.</th>
								<th class="col-min">QTY</th>
							</tr>
							<?php
							/**
							 * @var \ttwms\model\TransitDeliveryOrderItem $item
							 */
							foreach($box['item'] as $key=>$item):
								$prd = $item->product;
								?>
							<tr>
								<td><?=$key+1;?></td>
								<td><?=$prd->sku?></td>
								<td><?=ha($prd->clearance_name,10)?></span></td>
								<td></td>
								<td><?=$item->quantity?></td>
							</tr>
							<?php endforeach?>
						</table>
					</td>
				</tr>
			</table>
			<div style="font-weight:bolder;position: absolute;width: calc(100% - 20px);bottom: 0;">
				<p style="display:block;width:100%"><span style="float:right;">Made in China</span></p>
				<span style="border-bottom:2px solid #000;display:block;width:100%;clear:both;"></span>
				<p><span>Transport:MYSELF</span><span style="float:right;"><?=$box['box_no']?></span></p>
			</div>
		</div>
		<?php else:?>
		<div class="wrap">
			<table class="print_tb" >
				<tr>
					<td class="info">
						<p style="font-size:13px;font-weight:bolder;"></p>
						<div class="img-span">
							<img src="<?php echo \Temtop\component\TBase64::barcodeJpg($box['box_barcode'],55,'C128',2); ?>">
							<p><?=$box['box_barcode']?></p>
						</div>
					</td>
				</tr>
				<tr>
					<td class="detail">
						<span>IC NO：<?=$transit->shipment_code?></span>
						<span>WH：<?=$transit->target_wh_code?></span>
						<span>Customer Code：<?=$transit->enterprise->code;?></span>
					</td>
				</tr>
				<tr>
					<td>
						<table class="list_tb" cellpadding="0" cellspacing="0">
							<tr>
								<th class="col-min">No.</th>
								<th class="col-min">SKU</th>
								<th>SKU Name</th>
								<th>Batch No.</th>
								<th class="col-min">QTY</th>
							</tr>
							<?php
							/**
							 * @var \ttwms\model\TransitDeliveryOrderItem $item
							 */
							$item = $box['item'][0];
							$prd = $item->product;
							?>
							<tr>
								<td><?=1;?></td>
								<td><?=$prd->sku?></td>
								<td><?=ha($prd->clearance_name,10)?></span></td>
								<td></td>
								<td><?=1?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<div class="img-span">
							<img src="<?php echo \Temtop\component\TBase64::barcodeJpg($transit->enterprise->code.'-'.$prd->sku,55,'C128',2); ?>">
							<p><?=$transit->enterprise->code.'-'.$prd->sku?></p>
							<p style="font-size:2cm">
								<?=$sku_type_map[$prd->sku]?>
							</p>
						</div>
					</td>
				</tr>
			</table>
			<div style="font-weight:bolder;position: absolute;width: calc(100% - 20px);bottom: 0;">
				<p style="display:block;width:100%"><span style="float:right;">Made in China</span></p>
				<span style="border-bottom:2px solid #000;display:block;width:100%;clear:both;"></span>
				<p><span>Transport:MYSELF</span><span style="float:right;"><?=$box['box_no']?></span></p>
			</div>
		</div>
		<?php endif;?>
	<?php endforeach?>
</div>
</body>
</html>