<?php
$pW = $param['paper_width'];
$pH = $param['paper_height'];
/**
 * @var string $fnsku
 * @var string $enterpriseCode
 */
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>FNSKU条码打印-<?=date('Y-m-d H:i:s')?></title>
	<style>
		html, body{padding:0; margin:0; font-size:12px;}
		body {width:<?= $pW ?>mm; height:<?= $pH ?>mm; margin:0 auto;}
		img {margin:0 auto; padding:0; display:block;}
		.barcode-item {box-sizing:border-box; height:<?= $pH / 10.7;?>cm;overflow:hidden; page-break-after:always; text-align:center; font-family:Helvetica, Arial, sans-serif; position:relative;}
		.barcode {margin:<?= $pH / 10;?>px auto 0px; font-weight:bold; font-size:18px;}
		.new{width:<?= $pW ?>mm; height:15mm; text-align: center; font-size:12px;line-height:12px;}
	</style>
</head>
<body>
<div class="barcode-item">
	<img src="<?php echo \Temtop\component\TBase64::barcode($fnsku, $pH*1.5); ?>"/>
	<div class="barcode"><?=$fnsku?></div>
	<div class="new">
		<div style="float:left;margin-left: 3mm;">New</div>
		<div style="float:right; margin-right:3mm;"><?= $enterpriseCode?></div>
	</div>
</div>
</body>
</html>	