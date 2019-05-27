<?php
use function Lite\func\ha;

/**
 * @var \ttwms\model\PrdProduct[] $productList
 * @var \ttwms\model\TransitDeliveryOrderItem[] $boxList
 * @var array $boxNoList
 * @var array $get
 * @var \ttwms\model\TransitDeliveryOrder $transit
 */
?>
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
		.wrap{box-sizing:border-box;border: 1px solid #000;min-height:6cm;page-break-after:always;margin:5px auto;width:<?=$get['paper_width']*0.1*0.9?>cm;}
		.list_tb,.print_tb{width:100%;}
	
		.print_tb img{margin:10px 0 5px 10px;}
		.print_tb .img-span{display:inline-block;width:230px;text-align:center;float:left;font-weight: bold;}
		.print_tb .detail{margin:0 0 0 230px;font-weight: bold;font-size:18px;}
		.print_tb .detail p{margin:0;}
		.print_tb .line{border-width:1px 0 1px;border-style: solid;border-color: #000;}
		.list_tb th{font-weight: bold;}
		.list_tb td{text-align: center;font-weight: bold;}
		.footer{height:0.8cm;}
		.china{text-align: right;height:0.8cm;margin-right:5px;font-size:14px;font-weight: bold;}
		.page{display:block;font-size:18px;font-weight: bold;text-align:right;margin-right:5px;}
	</style>
</head>
<body>

<div class="content">
	<?php foreach ($boxList as $box):
		if($boxNoList && !in_array($box->box_no,$boxNoList)){
			continue;
		}
		?>
		<div class="wrap">
			<table class="print_tb" >
				<tr>
					<td class="info">
						<?php $ro_no = $transit->shipment_code?>
						<div class="img-span">
	                        <img src="<?php echo \Temtop\component\TBase64::barcodeJpg($ro_no,55); ?>">
	                        <p><?=$ro_no?></p>
	                    </div>
						<div class="detail">
							<p>
								Whs Code:<?=$transit->target_wh_code?><br/>
								Customer code:<?=$transit->enterprise->code?><br/>
								RO NO.:<?=$transit->shipment_code?><br/>
								Delivery No.:<?=$transit->enterprise_order_no?><br/>
								All documents are in the first carton<br/>
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<td class="line">&nbsp;</td>
				</tr>
				<tr>
					<td>
						<table class="list_tb" cellpadding="0" cellspacing="0">
							<tr>
								<th class="col-min">No.</th>
								<th class="col-min">SKU</th>
								<th>Clearance Name</th>
								<th class="col-min">Quantity</th>
							</tr>
								<tr>
									<td>1</td>
									<td><?=$box->product->sku?></td>
									<td><?=ha($box->product->clearance_name,10)?></span></td>
									<td><?=$box->quantity?></td>
								</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<p class="china">MADE IN CHINA</p>
						<p class="footer">
							<span class="page">Box No:<?=$box->box_no?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$box->box_no?>/<?=count($boxList)?></span>
						</p>
					</td>
				</tr>
			</table>
		</div>
	<?php endforeach?>
</div>
</body>
</html>