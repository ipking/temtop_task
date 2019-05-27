<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/html">
<head>
	<title>装箱单打印-<?=time()?></title>
</head>
<style>
	.content{margin: 0 auto;width: 18cm;}
	.china{text-align: right;margin-right:10px;}
	.page{text-align: right;margin-right:10px;font-size:18px;font-weight: bold;}

	h2{text-align:center;}
	.frm-tbl{font-size:13px;font-weight:bold;}
	.data-tbl{width:100%;font-size:12px;}
	.data-tbl .wrap td{border-top:1px dashed black;overflow:hidden;word-break:break-all}
	.td-sku{max-width:150px;}
	.pageBreak{page-break-after:always;}
	.end{margin-top:20px;}
	.col-min{text-align:center};
	.end span{font-size:13px;display:inline-block;margin:5px;}
	.end span.person_right{float:right;margin-right:100px;}
</style>
<body>
<?php
use function Lite\func\ha;

/**
 * @var \ttwms\model\TransitDeliveryOrder $transit
 * @var string $wh_code
 * @var \ttwms\model\TransitDeliveryOrderItem[] $boxList
 */
?>
<div class="content">
	<h2>入库清单</h2>
	<div>
		<img  src="<?php echo \Temtop\component\TBase64::barcode($transit->enterprise_order_no, 40); ?>">
		<table class="frm-tbl" width="100%">
			<tr>
				<td>参考编号：<?=$transit->enterprise_order_no?></td>
				<td>入库订单号：<?=$transit->shipment_code?></td>
			</tr>
			<tr>
				<td>目的仓库：<?=$transit->target_wh_code?></td>
				<td>订单时间：<?=$transit->create_time?></td>
			</tr>
			<tr>
				<td>公司代码：<?=$transit->enterprise->code ?></td>
				<td>派送方式：MYSELF</td>
			</tr>
			<tr>
				<td>收货地点：</td>
				<td>国内中转运输方式：</td>
			</tr>
			<tr>
				<td>海外公司名/个人VAT注册名：</td>
				<td>公司/个人注册码：</td>
			</tr>
			<tr>
				<td>VAT号码：</td>
				<td>EORI号码：</td>
			</tr>
		</table>
	</div>
	<?php $wrapCount=$pg=0;$cnt=1;?>
	<hr />
	<hr />
	<table class="data-tbl" cellpadding="5" cellspacing="0">
		<?php
		foreach($boxList as $row):
			
			?>
			<?php if($wrapCount==0): ?>
			<tr class="tr-title">
				<td class="col-min">箱号</td>
				<td>SKU</td>
				<td >外部条码</td>
				<td class="td-sku">货品名称</td>
				<td>预收数</td>
				<td>实收数</td>
				<td>重量</td>
				<td>包装类型</td>
				<td>货物类型</td>
				<td>申报价值USD</td>
			</tr>
		<?php endif; ?>
			<?php
			$style="";
			$wrapCount++;
			$cnt++;
			if($pg==0 && $cnt==29){
				$style="pageBreak";
				$wrapCount=0;
				$cnt=0;
				$pg++;
			}elseif($cnt%36==0 && $pg!=0){
				$style="pageBreak";
				$wrapCount=0;
				$pg++;
			}else{
				$style="";
			}
			?>
			<tr class="wrap <?=$style?>">
				<td><?=$row->box_no?></td>
				<td><?=$row->product->sku?></td>
				<td></td>
				<td class="td-sku"><?=ha($row->product->name,13)?></td>
				<td class="col-min"><?=$row->quantity?></td>
				<td></td>
				<td></td>
				<td class="col-min"><?="OW01"?></td>
				<td class="col-min"><?='Package'?></td>
				<td class="col-min"><?=$row->product->clearance_price?></td>
			</tr>
		<?php endforeach?>
	</table>
	<div class="end">
		<span class="person_left">发货人：</span><span class="person_right">检查人：</span>
		<hr>
		<span>运输备注：</span><br>
		<span>打印时间：<?=date('Y/m/d H:i:s')?></span>
	</div>
</div>
</body>
</html>
