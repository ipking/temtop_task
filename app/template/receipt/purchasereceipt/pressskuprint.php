<?php
/** @var array $list */
/** @var PurchaseReceipt $info */

use ttwms\business\Warehouse;
use ttwms\model\PurchaseReceipt;
use function Temtop\get_array_print_data;

$tmp = array();
$barcode_total_tmp = array();
foreach($list as $items){
	$sku_code = $items->product->sku;
	$barcode = $sku_code;
	$barcode_total_tmp[$barcode] += $items->qty;
	$tmp[] = array(
		'barcode'        => $barcode,
		'box_no'         => $items->box->no,
		'clearance_name' => $items->product->clearance_name,
		'qty'            => $items->qty,
		'location'       => $items->location->code,
	);
}
$tmp_prt = get_array_print_data($tmp, 22, 25);

$print_data = array();
foreach($tmp_prt as $page_idx => $item_list){
	$tmp = array();
	foreach($item_list as $items){
		$barcode = $items['barcode'];
		if(!$tmp[$barcode]){
			$tmp[$barcode] = array(
				'barcode'   => $barcode,
				'total_qty' => 0,
				'item_list' => array()
			);
		}
		$tmp[$barcode]['total_qty'] += $items['qty'];
		$tmp[$barcode]['item_list'][] = $items;
	}
	$print_data[$page_idx][] = array_values($tmp);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>WHALEPIE WMS - 打印收货单</title>
</head>
<style>
	#tb-bar{width:100%; table-layout:fixed; font-family:"Helvetica Neue", Helvetica, Arial, sans-serif}
	h1{ display:inline-block;text-align:center; font-weight:bolder;}
	.page-breaker {page-break-after:always}
	.data-tbl{border-collapse:collapse; width:100%; }
	.data-tbl td,.data-tbl th{border:1px solid black;}
	.data-tbl tbody td {text-align:center}
	.page-count {padding-bottom:0.25em;}
	.data-tbl td {padding:0.25cm 0;}
    body{padding: 0 15px;}
</style>
<body>
    <table id="tb-bar">
        <tr>
            <td align="left">
                <img src="<?php echo \Temtop\component\TBase64::barcode($info->receipt_no); ?>"/>
                <p style="margin: 0 0 0 15px;"><?php echo $info->receipt_no; ?></p>
            </td>
            <td><h1>Shelf List</h1></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td><b><?=\ttwms\business\CurrentWMS::getWmsCode()?>WORLDWIDE EXPRESS</b></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td><b>Company Code:<?=$info->code?></b></td>
            <td><b>Ref No:<?=$info->external_no?></b></td>
        </tr>
        <tr>
            <td><b>Channel:MYSELF</b></td>
            <td></td>
            <td><b>Print Date:<?=date('Y-m-d H:i:s');?></b></td>
        </tr>
    </table>

    <table class="data-tbl">
        <thead>
            <tr>
	            <th>Barcode</th>
                <th>Total<br/>Qty</th>
                <th>Rettwmsend<br/>Location</th>
                <th width="300">Real Location</th>
                <th width="50">Real<br/>Qty</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($list as $item):?>
            <tr>
                <td>
                    <b style="font-weight:bolder; font-family:Helvetica Neue, Helvetica, Arial, sans-serif"><?=$item['sku'];?></b>
                    <br/>
                    (<?=$item['clearance_name']?>)
                </td>
                <td class="expectNum"><?=$item['qty']?></td>
                <td>
                    <?php $location = Warehouse::rettwmsendLocation($item['product_id'], $item['qty']);?>
                    <?=$location->code?>
                </td>
                <td></td>
                <td></td>
            </tr>
        <?php endforeach;?>

        </tbody>
    </table>
    <div style="margin:20px 0 10px 0;">
        <table width="100%">
            <tr>
                <td align="left"><b>Consigner Signature:_______________________</b></td>
                <td align="left"><b>IQC Signature:______________________</b></td>
            </tr>
            <tr>
                <td align="left"><b>Deliverer Signature:______________________</b></td>
                <td></td>
            </tr>
        </table>
    </div>
</body>
</html>