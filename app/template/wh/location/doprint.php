<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?=Temtop\t("货位条码打印")?>-<?=time()?></title>
<style>
	*{padding:0;margin:0;font-size:30px;text-align:center;}
	body{background-color:white;}
	div{margin:1px auto;font-weight:bold;}
	.d-fill{display: inline-block;width: 1.8cm;}
	.d6{width:178px;margin:0 auto;overflow:hidden;display: inline-block; page-break-after :always;}
	.d-right{display: inline-block;overflow:inherit;width:1.3cm;font-size: 12px;font-weight: normal;}
</style>
</head>
<body>
<?php foreach($list as $barcode):?>
	<?php if (isset($counts[$barcode->id])):?>
		<?php for($i=0;$i<$counts[$barcode->id];$i++):?>
			<div style="text-align:center;padding-left:0.5cm;width:9cm;height:3.8cm;overflow:hidden;page-break-after :always;">
				<img style="display:inline-block;margin:0.25cm 0px 0px 0px;" class="i1" src="<?php echo \Temtop\component\TBase64::barcode($barcode->code, 80); ?>"/>
				<div style="text-align:center;margin:0px 0px 0.25cm 0px;"><?=$barcode->code?></div>
			</div>
		<?php endfor?>
	<?php endif?>
<?php endforeach?>
</body>
</html>
