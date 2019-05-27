<?php
/**
 * @var \ttwms\model\TransitDeliveryOrder $transit
 * @var \ttwms\model\TransitDeliveryOrderItem[] $boxList
 * @var array $get
 * @var array $boxNoList
 */
use ttwms\business\ProviderAddress;
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="pragma" content="no-cache">
	<title><?="打印FBA唛头"?></title>
</head>
<style>
	body *{padding: 0;margin: 0; font-family:Helvetica,Arial,sans-serif;}
	.title{font-size: 24px;font-weight: bold;margin: 0 0 0 10px;}
	.content{box-sizing:border-box;border: 1px solid white;min-height:6cm;page-break-after:always;margin:5px auto;width:<?=$get['paper_width']*0.1*0.9?>cm;height:<?=$get['paper_height']*0.1*0.9?>cm;}
	.address{width: 100%;}
	.address div{float: left;width: 50%;font-size: 14px;}
	p{padding: 0;margin: 0 2px;word-break:break-all;}
</style>
<body>

	<?php foreach($boxList as $box){
		if($boxNoList && !in_array($box->box_no,$boxNoList)){
			continue;
		}
		
		$shipmentCode = $transit->shipment_code."U".str_pad($box->box_no,6,"0",STR_PAD_LEFT);
		?>
	<div class="content">
		<div class="div_line">
			<span class="title">FBA(<?=$transit->target_wh_code?>)</span>
			<hr style="border: 2px solid #000000"/>
			<div class="address">
				<div class="address-to">
					<p>
						<strong style="font-size: 15px">TO:</strong><br/>
						<span style="font-size: 13px;">Declarant: <?=$transit->user_company?></span><br/>
						<?=$transit->user_name?><br/>
						<?=$transit->user_street?> <?=$transit->user_house_no?><br/>
						<?=$transit->user_postcode?> <?=$transit->user_city?><br/>
						<?=$transit->user_country?>
						<br/>
					</p>
				</div>
				<div class="address-from">
					<p>
						<strong style="font-size: 15px">FROM:</strong><br/>
						<span style="font-size: 13px;">
                        <?= ProviderAddress::company_name ?><br/>
							<?= ProviderAddress::street_name ?> <?= ProviderAddress::house_number ?><br/>
							<?= ProviderAddress::zip ?> <?= ProviderAddress::city ?> <?= ProviderAddress::state ?> <br/>
							<?= ProviderAddress::country ?>
							</span>
					</p>
				</div>
			</div>
			<div style="clear: both"></div>
			<hr style="border: 6px solid #000000"/>
			<div style="width: 100%;text-align: center;margin-top: 10px;margin-bottom: 5px;">
				<div class="span_block span_code">
					<img class="i1" src="<?php echo \Temtop\component\TBase64::barcode($shipmentCode, 95,'C128',2); ?>"/>
					<p style="margin: -2px 0 0 10px;font-size: 18px"><?=$shipmentCode?></p>
				</div>
			</div>
			<hr style="border: 2px solid #000000"/>
			<p style="width:95%;text-align:right;"><?="Single SKU"?></p>
			<p style="width:95%;text-align:right;font-size:14px;"><?=$box->product->sku?> &times; <?=$box->quantity?></p>
		</div>
	</div>
	<?php }?>

</body>
</html>