<?php
/** @var array $list */
/** @var PurchaseReceipt $info */

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
</style>
<body>
    <table id="tb-bar">
        <tr>
            <td align="left"><img src="<?php echo \Temtop\component\TBase64::barcode($info->receipt_no); ?>"/><p><?php echo $info->receipt_no; ?></p></td>
            <td align="center"><h1>Receiving List</h1></td>
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
    <?php
    $page_count = count($print_data);
    foreach($print_data as $page_idx=> $page_items):?>
    <?php if($page_idx == 0 && $page_count>1 || $page_idx):?>
    <div class="page-count">Page: <?=$page_idx+1;?>/<?=$page_count;?></div>
    <?php endif;?>
    <table class="data-tbl <?php if(($page_idx == 0 && $page_count > 1) || $page_idx != ($page_count-1)){echo 'page-breaker';}?>">
        <thead>
            <tr>
	            <th>Barcode</th>
                <th>Total<br/>Qty</th>
	            <th>Box<br/>No</th>
	            <th>Order<br/>Qty</th>
                <th>Rettwmsend<br/>Location</th>
                <th width="220">Real Location</th>
                <th width="50">Real<br/>Qty</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($page_items as $items):
            foreach($items as $sku_data):
	        foreach($sku_data['item_list'] as $sku_k=>$sku):
	           ?>
            <tr>
	            <?php if($sku_k == 0):?>
                <td rowspan="<?=count($sku_data['item_list'])?>">
                    <b style="font-weight:bolder; font-family:Helvetica Neue, Helvetica, Arial, sans-serif"><?=$sku_data['barcode'];?></b>
	                <br/><br/>
	                (<?=$sku['clearance_name']?>)
                </td>
                <td rowspan="<?=count($sku_data['item_list'])?>">
	                <span style="white-space:nowrap">
	                <?=$sku_data['total_qty'];?>
                    <?php if($barcode_total_tmp[$sku_data['barcode']] != $sku_data['total_qty']){
	                    echo '/ '.$barcode_total_tmp[$sku_data['barcode']];
                    };?>
	                </span>
                </td>
                <?php endif;?>

                <td><?=$sku['box_no']?></td>
                <td class="expectNum"><?=$sku['qty']?></td>
                <td><?=$sku['location']?></td>
                <td></td>
                <td></td>
            </tr>
            <?php endforeach;endforeach;endforeach;?>
        </tbody>
    </table>
    <?php endforeach;?>
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