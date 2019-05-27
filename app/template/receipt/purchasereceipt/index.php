<?php
namespace ttwms;

use function Lite\func\ha;
use ttwms\model\Enterprise;
use ttwms\model\PurchaseReceipt;
use function Lite\func\h;
use function Temtop\t;

/**
 * @var PurchaseReceipt[] $list
 * @var array $search
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $pagination
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

$EnterpriseList = Enterprise::find()->map('code','code');

$tab_map = [
	PurchaseReceipt::STATUS_PUBLISHED => '预到入库',
	PurchaseReceipt::STATUS_ARRIVAL => '待收货',
	PurchaseReceipt::STATUS_RECEIVED => '待质检',
	PurchaseReceipt::STATUS_CHECKED => '待上架',
	PurchaseReceipt::STATUS_FINISHED => '收货完毕',
];
?>
<?= $this->buildBreadCrumbs(array('入库单')); ?>
<style>
	.data-tbl thead tr th:nth-child(5),th:nth-child(6){width:40px;}
    .data-tbl thead tr th:nth-child(8){width:160px;}
    .date{width:120px;}
    .description{width:200px;}
	.tip-link{border-bottom: 2px dotted #08ab27;padding-bottom: 1px;cursor: help;}
	
	<?php if($search['status'] != PurchaseReceipt::STATUS_FINISHED):?>
	[data-tab="<?=PurchaseReceipt::STATUS_FINISHED?>"]{display:none}
	<?php endif;?>

	.sku-tip-style.tt-sku-list{ width: 400px; }
	.sku-tip-style.tt-sku-list li{ width: 50%; box-sizing: border-box; display: inline-block; margin: 3px 0; }
	.tt-sku-list li{ display: block; }
</style>

	<?php
$active["status_".$search['status']]="class='active'";
?>
	<ul class="tab">
		<?php foreach($tab_map as $s=>$t) :?>
			<li <?=$active['status_'.$s]?> ><a href="<?=ViewBase::getUrl('receipt/PurchaseReceipt/index',array("status"=>$s))?>"><?=$t?></a></li>
		<?php endforeach;?>
	</ul>
<section class="container">
 
	<form action="<?=ViewBase::getUrl('receipt/PurchaseReceipt/index') ?>" method="GET" class="search-frm quick-search-frm">
		<select name="enterprise_code" id="" placeholder="valid">
			<option value="">--客户代码--</option>
			<?php foreach ($EnterpriseList as $id => $code):?>
				<option <?=$search['enterprise_code'] == $id ?'selected':''?> value="<?=$id?>"><?=h($code)?></option>
			<?php endforeach;?>
		</select>
		<input type="text" class="txt" name="receipt_no" placeholder="<?=t("入库单号")?>" value='<?= $search['receipt_no'] ?>'/>
		<input type="text" class="txt" name="external_no" placeholder="<?=t("客户单号")?>" value='<?= $search['external_no'] ?>'/>
		<input type="text" class="txt" name="sku" placeholder="<?=t("SKU")?>" value='<?= $search['sku'] ?>'/>
		<span><?php if($search['status'] == PurchaseReceipt::STATUS_PUBLISHED){ ?></span>
		<span><?=t("发出日期")?>:</span><input type="text" class="date-txt txt" name="create_time_start" placeholder="<?=t("开始时间")?>"
		            value='<?= $search['create_time_start'] ?>'>-
		<input type="text" class="date-txt txt" name="create_time_end" placeholder="<?=t("结束时间")?>"
			       value='<?= $search['create_time_end'] ?>'>
		<span><?=t("预计到达日期")?>:</span><input type="text" class="date-txt txt" name="arrival_date_start" placeholder="<?=t("开始时间")?>"
		                        value='<?= $search['arrival_date_start'] ?>'>-
		<input type="text" class="date-txt txt" name="arrival_date_end" placeholder="<?=t("结束时间")?>"
			       value='<?= $search['arrival_date_end'] ?>'>
		<?php } else{ ?>
		<span><?=t("到货日期")?>:</span><input type="text" class="date-txt txt" name="confirm_date_start" placeholder="<?=t("开始时间")?>"
		            value='<?= $search['confirm_date_start'] ?>'>-
		<input type="text" class="date-txt txt" name="confirm_date_end" placeholder="<?=t("结束时间")?>"
			       value='<?= $search['confirm_date_end'] ?>'>
		<?php } ?>
		<input type="hidden" name="status" value="<?= $search['status'] ?>">
		<button class="btn-search mr-10" type="submit" value="<?=t("搜索")?>">搜索</button>
	</form>
	<table class="data-tbl" id="tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th width="120"><?=t("入库单号")?></th>
            <th width="120"><?=t("客户单号")?></th>
			<th width="80"><?=t("客户代码")?></th>
            <th width="30"><?=t("总箱数")?></th>
			<th width="30"><?=t("总款式")?></th>
			<th width="30"><?=t("总数量")?></th>
			<th width="250"><?=t("SKU")?></th>
			<th width="80" data-tab="<?=PurchaseReceipt::STATUS_FINISHED?>"><?=t("收货数")?></th>
			<th width="80" data-tab="<?=PurchaseReceipt::STATUS_FINISHED?>"><?=t("上架数")?></th>
			<th class="col-min"><?=$search['status']==PurchaseReceipt::STATUS_PUBLISHED?t("发出日期"):t("到货日期");?></th>
			<?php if($search['status']==PurchaseReceipt::STATUS_PUBLISHED){ ?>
			<th class="date col-min"><?=t("预计到达日期")?></th>
            <?php } ?>
			<th class="col-op"><?=t("操作")?><i class="fa fa-question-circle-o" data-component="tip" data-tip-content="<?=t('按SKU收货：装箱的明细中，同款SKU必须独立装箱，否则需按箱收货')?>"></i></th>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($list as $row):
				$i = 0;
				$tip_content = '<ul class="tt-sku-list sku-tip-style">';
				$show_content = '';
				foreach($row->sku_qty_list as $pid => $i_row){
					$tmp = '<li>'.$i_row['sku'].'<span class="sku-number-span">&times;'.$i_row['qty'].'</span></li>';
					$i++;
					$tip_content .= $tmp;
					if($i<=2){
						$show_content .= $tmp;
					}
				}
				$tip_content .= '</ul>';
				?>
			<tr class="box_main">
				<td align="center"><?=$row->receipt_no?></td>
                <td align="center"><?=$row->external_no?></td>
				<td align="center"><?=$row->code?></td>
                <td><?=$row->box_num?></td>
				<td><?=$row->sku_num?></td>
				<td><?=$row->qty_num?></td>
				<td <?=$i>2?'data-component="tip" data-tip-content="'.ha($tip_content).'"':''?>>
					<ul class="tt-sku-list">
						<?=$show_content;?>
						<?=$i>2?'<li><span>....</span></li>':''?>
					</ul>
				</td>
				<td data-tab="<?=PurchaseReceipt::STATUS_FINISHED?>">
					<?=array_sum($row->sku_receipt_qty_map?:[])?>
				</td>
				<td data-tab="<?=PurchaseReceipt::STATUS_FINISHED?>">
					良品:<?=array_sum($row->sku_put_good_qty_map?:[])?><br/>
					不良品:<?=array_sum($row->sku_put_bad_qty_map?:[])?>
				</td>
                <td class="col-min"><?=$search['status']==PurchaseReceipt::STATUS_PUBLISHED ? $row->create_time : $row->confirm_date?></td>
				<?php if($search['status']==PurchaseReceipt::STATUS_PUBLISHED){ ?>
				<td align="center" ><?=$row->arrival_date?></td>
                <?php } ?>
                <td class="col-op">
                    <dl class="drop-list drop-list-left">
                        <dt>
	                        <a  href="<?=ViewBase::getUrl("receipt/PurchaseReceipt/view",array('id'=>$row->id))?>" data-component="popup" data-popup-width="1000"><?=t("查看").'SKU'.t('汇总')?></a>
                        </dt>
                        <dd>
                            <?php if($search['status'] == PurchaseReceipt::STATUS_PUBLISHED):?>
                                <a href="<?=ViewBase::getUrl("receipt/PurchaseReceipt/arrivaldateeditanddescription",array('id'=>$row->id))?>" data-component="popup" data-popup-width="500" data-popup-height="250"><?=t("修改预计日期和备注")?></a>
                                <a  href="<?=ViewBase::getUrl("receipt/PurchaseReceipt/confirm",array('id'=>$row->id))?>" data-confirm-message="<?=t("是否确认到货")?>" data-component="confirm,async"><?=t("确认已到货")?></a>
                                <a href="<?=ViewBase::getUrl("receipt/PurchaseReceipt/back",array('id'=>$row->id))?>" data-component="popup"><?=t("退回")?></a>
                            <?php elseif($search['status']==PurchaseReceipt::STATUS_ARRIVAL):?>
                                <a  target="_blank"  href="<?=ViewBase::getUrl("receipt/PurchaseReceipt/BoxList",array('id'=>$row->id,'mode'=>'edit'))?>"><?=t("按箱收货")?></a>
								<?php if(!PurchaseReceipt::checkBoxSinglePack($row->id)):?>
									<a  target="_blank"  href="<?=ViewBase::getUrl("receipt/PurchaseReceipt/pressSKUReceipt",array('id'=>$row->id))?>"><?=t("按SKU收货")?></a>
								<?php endif;?>
                            
                            <?php elseif($search['status'] == PurchaseReceipt::STATUS_RECEIVED): ?>
	                            <a  target="_blank"  href="<?=ViewBase::getUrl("receipt/PurchaseReceipt/pressSkuQt",array('id'=>$row->id))?>"><?=t("质检")?></a>
                            <?php elseif($search['status'] == PurchaseReceipt::STATUS_CHECKED): ?>
	                            <a  target="_blank"  href="<?=ViewBase::getUrl("receipt/PurchaseReceipt/pressSkuPut",array('id'=>$row->id))?>"><?=t("上架")?></a>
                            <?php endif; ?>
                        </dd>
                    </dl>

                </td>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<?php echo $pagination; ?>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>