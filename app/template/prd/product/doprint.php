<?php
$pW = $param['paper_width'];
$pH = $param['paper_height'];
/**
 * @var \ttwms\model\PrdProduct $productInfo
 */
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>产品条码打印-<?=date('Y-m-d H:i:s')?></title>
    <style>
        html, body{padding:0; margin:0; font-size:12px;}
        body {width:<?= $pW ?>mm; height:<?= $pH ?>mm; margin:0 auto;}
        img {margin:0 auto; padding:0; display:block;}
        .barcode-item {box-sizing:border-box; height:<?= $pH / 10.7;?>cm;overflow:hidden; page-break-after:always; text-align:center; font-family:Helvetica, Arial, sans-serif;}
        .barcode {margin-top:<?= $pH / 10;?>px; font-weight:bold; font-size:18px;}
    </style>
</head>
<body>
<div class="barcode-item">
    <img src="<?php echo \Temtop\component\TBase64::barcode($productInfo->barcode, $pH*1.5); ?>"/>
    <div class="barcode"><?=$productInfo->enterprise->code.'-'.$productInfo->sku?> </div>
    <div class="memo">
        <span class="clearance-name"><?=$productInfo->clearance_name?></span>
        <?php if($productInfo->clearance_name):?>/<?php endif;?>
        <span class="date"><?=date("ymd")?></span>
    </div>
</div>
</body>
</html>