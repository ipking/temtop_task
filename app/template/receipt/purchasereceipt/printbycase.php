<?php
/** @var array $list */
/** @var PurchaseReceipt $info */

use ttwms\model\PurchaseReceipt;
use function Temtop\get_array_print_data;

$data = array();
foreach($list as $k=>$item){
	$data[] = array(
		'box_no'         => $item->box->no,
		'barcode'        => $item->product->sku,
		'clearance_name' => $item->product->clearance_name,
		'qty'            => $item->qty,
        'weight'         => $item->box->weight,
		'location'       => $item->location->id ? $item->location->code : ''
	);
}
$table_groups = get_array_print_data($data, 19, 23);
$other = [];
foreach ($table_groups as $key=>$value){
    foreach ($value as $k=>$v){
        $other[$key]['number'][$v['box_no']] += 1;
        $other[$key]['weight'][$v['box_no']] = $v['weight'];
    }
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
    #tb-bar .re-no{padding: 0 ; margin: 0;}
    .data-tbl  td .c-name {font-size: 10px; line-height: 11px;}
    h1{ display:inline-block;text-align:center; font-weight:bolder;}
    .page-breaker {page-break-after:always;}
    .data-tbl{border-collapse:collapse; width:100%;}
    .data-tbl td,.data-tbl th{border:1px solid black;line-height: 16px;}
	.data-tbl tbody td {text-align:center}
	.page-count {padding-bottom:0.25em;}
    .location{font-size: 12px;}
</style>
<body>
    <table id="tb-bar">
        <tr>
            <td>
				<img src="<?php echo \Temtop\component\TBase64::barcode($info->receipt_no, 40); ?>"/>
				<p class="re-no"><?php echo $info->receipt_no; ?></p>
			</td>
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
    $page_count = count($table_groups);
    foreach($table_groups as $page_idx=> $data):
	    if($page_idx == 0 && $page_count>1 || $page_idx):?>
	    <div class="page-count">Page: <?=$page_idx+1;?>/<?=$page_count;?></div>
	    <?php endif;?>
    <table class="data-tbl <?php if(($page_idx == 0 && $page_count > 1) || $page_idx != ($page_count-1)){echo 'page-breaker';}?>">
        <thead>
            <tr>
                <th width="40px">No</th>
	            <th width="40px">Box No</th>
                <th width="60px">Weight<br>(kg)</th>
	            <th width="150px">SKU</th>
	            <th width="40px">Order Qty</th>
	            <th width="40px">Receive Qty</th>
	            <th width="40px">Put Qty</th>
	            <th width="130px">Rettwmsend Location</th>
	            <th>Real Location</th>
            </tr>
        </thead>
        <tbody>
            <?php $box_no_before='';foreach ($data as $k=>$item):?>
            <tr>
                <td><?=$k+1;?></td>
                <?php $box_no = $item['box_no']; if($box_no_before!=$box_no):$box_no_before = $item['box_no'];?>
                    <td rowspan="<?= $other[$page_idx]['number'][$box_no] ?>"><?=$item['box_no'];?></td>
                    <td rowspan="<?= $other[$page_idx]['number'][$box_no] ?>"><?=$other[$page_idx]['weight'][$box_no]?:'-'?></td>
                <?php endif;?>
                <td style="max-width: 150px;">
                    <?=$item['barcode'];?> <br/>
	                <span class="c-name"><?=strtolower($item['clearance_name'])?></span>
                </td>
                <td class="expectNum"><?=$item['qty']?></td>
                <td></td>
                <td></td>
                <td class="location"><?=$item['location'];?></td>
                <td></td>
            </tr>
            <?php endforeach;?>
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