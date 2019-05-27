<?php
namespace ttwms;

use ttwms\business\Form;
use ttwms\model\Enterprise;
use ttwms\model\WhArea;
use function Lite\func\h;
use function Temtop\t;

/**
 * @var array $list
 * @var array $isActiveList
 * @var array $roleList
 * @var array $param
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
$areaList = WhArea::find()->map('id','code');
?>
<?= $this->buildBreadCrumbs(array('货位查询')); ?>
	<style>
		span.status_0{background-color: #ff7900;}
		span.status_1{background-color: green;}
		#msg-tbl td{text-align: center;}
		#msg-tbl .td_txt_left{text-align: left;}
		.select_block{display: inline-block;width: 200px;}
		.search-frm select {max-width: 180px; }
		.page-iframe .search-frm{display:none;}
		.page-iframe .col-sku,
		.page-iframe .col-name,
		.page-iframe .col-img,
		.page-iframe .col-op,
		.page-iframe .col-skuname{ display:none;}
		.data-tbl tfoot {font-weight:bold;display:none;}
		.page-iframe .data-tbl tfoot{display:table-footer-group;}
		.data-tbl thead tr th,
		.data-tbl tbody tr td{text-align: center}
	</style>
	<section class="container">
	<form action="<?=ViewBase::getUrl('wh/productLocation/index')?>" method="GET" class="search-frm">
        <?php $enterprise = Enterprise::find()->map("id","code")?>
		<select name="enterprise_id"  placeholder="valid">
			<option value="">--客户代码--</option>
			<?php foreach ($enterprise as $id => $code):?>
				<option <?=$param['enterprise_id'] == $id ?'selected':''?> value="<?=$id?>"><?=h($code)?></option>
			<?php endforeach;?>
		</select>
		<select name="area_id" placeholder="valid">
			<option value="">--库区--</option>
			<?php foreach ($areaList as $id => $name):?>
				<option <?=$param['area_id'] == $id ?'selected':''?> value="<?=$id?>"><?=h($name)?></option>
			<?php endforeach;?>
		</select>
		<select name="goods_type"  placeholder="valid">
			<option value="">--货物类型--</option>
			<?php foreach (\ttwms\business\Form::$goodsTypeList as $id => $code):?>
				<option <?=$param['goods_type'] == $id ?'selected':''?> value="<?=$id?>"><?=h($code)?></option>
			<?php endforeach;?>
		</select>
		<input type='text' name='code_row' class="txt" placeholder="<?=t("货位号-行")?>" value="<?=$param['code_row']?>" size="3"/>
		<input type='text' name='code_col' class="txt" placeholder="<?=t("货位号-列")?>" value="<?=$param['code_col']?>" size="3"/>
		<input type='text' name='code_top' class="txt" placeholder="<?=t("货位号-层")?>" value="<?=$param['code_top']?>" size="3"/>
		<input type='text' name='code' class="txt" placeholder="<?=t("货位号-ALL")?>" value="<?=$param['code']?>" size="25"/>
		<input type='text' name='sku' class="txt" placeholder="SKU" value="<?=$param['sku']?>" />
		<input type='number' name='qty' class="txt" placeholder="<?=t('存放数量')?>" min="0" title="<?=t('只显示存放数量小于该值的货位')?>" value="<?=$param['qty']?>" />
		<input type="submit" value="<?=t("搜索")?>" class="btn"/>
	</form>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th class="col-min"><?=t("存放货架")?></th>
			<th width="50"><?=t("存放数量")?></th>
			<th width="50"><?=t("可用数量")?></th>
			<th width="50"><?=t("冻结数量")?></th>
			<th width="50"><?=t("货物类型")?></th>
			<?php if(empty($param['ref']) || ($param['ref'] != 'iframe')): ?>
				<th class="col-min"><?=t('客户代码')?></th>
				<th class="col-sku">SKU</th>
				<th class="col-skuname"><?=t("中文名称")?></th>
				<th class="col-skuname"><?=t("英文名称")?></th>
				<th><?=t("操作")?></th>
			<?php endif; ?>
		</tr>
		</thead>
		<tbody>
		<?php
		$sum_total = $sum_frozen = 0;
		?>
		<?php foreach ($list?:array() as $row):?>
			<tr>
				<td class="col-min"><?=$row['location']->code?></td>
				<td><?=$row['info']['qty']?></td>
				<td>
					<?php if($row['product']->id){?>
						<?=$row['info']['qty']-$row['info']['frozen_qty']?>
					<?php }?>
				</td>
				<td>
					<a href="<?=ViewBase::getUrl("wh/frozen/location",array('pid'=>$row['product']->id,'loc_id'=>$row['location']->id))?>" target="_blank" data-component="popup" data-popup-width="600" title="<?=$row['product']->sku ?> <?=t("冻结库存")?>">
						<?=$row['info']['frozen_qty']?>
					</a>
				</td>
				<td>
					<?= Form::$goodsTypeList[$row['info']['goods_type']] ?>
				</td>
				<?php if(empty($param['ref']) || ($param['ref'] != 'iframe')) { ?>
					<td class="col-min"><?=$enterprise[$row['product']->enterprise_id]?></td>
					<td class="col-sku"><?=$row['product']->sku?></td>
					<td class="col-skuname"><?=$row['product']->name?></td>
					<td class="col-skuname"><?=$row['product']->ename?></td>
					<td class="col-op">
						<?php if($row['product']->id){?>
							<?php if($row['info']['is_check']==0){?>
								<a href="<?=ViewBase::getUrl("wh/productLocation/editinventory",array('id'=>$row['info']['wpl_id']))?>" class="btn" data-component="popup" data-onsuccess="refesh" data-popup-width="800" style="display:none;"><?=t("调库存")?></a>
								<a href="<?=ViewBase::getUrl("wh/productLocation/editlocation",array('id'=>$row['info']['wpl_id']))?>" class="btn" data-component="popup" data-onsuccess="refesh" data-popup-width="1000"><?=t("移货位")?></a>
							<?php }?>
							<a href="<?=ViewBase::getUrl("wh/productLocation/log",array('id'=>$row['info']['id'],'product_id'=>$row['product']->id))?>" class="btn" data-component="popup"><?=t("日志")?></a>
						<?php }?>
					</td>
				<?php } ?>
			</tr>
			<?php
			$sum_total += $row['info']['qty'];
			$sum_frozen += $row['info']['frozen_qty'];
			?>
		<?php endforeach;?>
		</tbody>
		<tfoot>
		<tr>
			<td><?=t("总计")?></td>
			<td><?=$sum_total?></td>
			<td><?=$sum_total - $sum_frozen?></td>
			<td><?=$sum_frozen?></td>
			<?php if(empty($param['ref']) || ($param['ref'] != 'iframe')) { ?>
				<td></td>
				<td></td>
				<td></td>
			<?php }?>
		</tr>
		</tfoot>
	</table>
		<?php echo $paginate; ?>
	</section>
	<script>
		seajs.use(["jquery","ywj/msg"],function($,Msg){
			$("#editStock").click(function(){
				var href="<?=ViewBase::getUrl("wh/productLocation/editBatch")?>";
				var param=$("#msg-tbl tbody input[type=checkbox]").serialize();
				if(""==param){
					alert('<?=t("请选择要编辑的项")?>');return false;
				}
				$(this).attr("href",href+"?"+param);
			});

			$("#free").click(function(){
				$.post("<?=ViewBase::getUrl("wh/productLocation/free")?>?ref=json",{},function(data) {
					if(data.code == 0) {
						Msg.show(data.message, 'succ');
					}
				},"json")
			});
			window.refesh=function(){
				window.location.reload();
			}
		});
	</script>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>