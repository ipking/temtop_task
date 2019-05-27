<?php
namespace ttwms;

use ttwms\model\Enterprise;
use ttwms\model\WhInventory;
use function Lite\func\h;
use function Temtop\t;

/**
 * @var WhInventory[] $list
 * @var array $isActiveList
 * @var array $roleList
 * @var array $param
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $pagination
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('库存查询')); ?>
	
	<?php
$active["status_".$param['goods_type']]="class='active'";
?>
	<ul class="tab">
		<?php foreach (\ttwms\business\Form::$goodsTypeList as $id => $code):?>
			<li <?=$active['status_'.$id]?> ><a href="<?=ViewBase::getUrl('wh/inventory/index',array("goods_type"=>$id))?>"><?=$code?></a></li>
		<?php endforeach;?>
	</ul>

	<section class="container">
	<form action="<?=ViewBase::getUrl('wh/inventory/index')?>" method="GET" class="search-frm quick-search-frm">
        <?php $enterprise = Enterprise::find()->map("id","code")?>
		<select name="enterprise_id"  placeholder="valid">
			<option value="">--客户代码--</option>
			<?php foreach ($enterprise as $id => $code):?>
				<option <?=$param['enterprise_id'] == $id ?'selected':''?> value="<?=$id?>"><?=h($code)?></option>
			<?php endforeach;?>
		</select>
		
		<input type='text' name='sku' class="txt" placeholder="SKU<?=t("查询")?>" value="<?=$param['sku']?>" />
		<button class="btn-search mr-10" type="submit" value="<?=t("搜索")?>">搜索</button>
        <input type="submit" value="<?= t('导出') ?>" name="export" class="btn">
	</form>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th>SKU</th>
            <th><?=t("客户代码")?></th>
            <th><?=t("中文名称")?></th>
			<th><?=t("英文名称")?></th>
			<th width="50"><?=t("总库存")?></th>
            <th width="50"><?=t("可用数量")?></th>
            <th width="50"><?=t("待出库数量")?></th>
            <th width="50"><?=t("货物类型")?></th>
			<th class="col-min"><?=t("操作")?></th>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($list as $row):?>
			<tr>
				<td><?=$row->product->sku?></td>
                <td class="text-center"><?=$row->product->enterprise->code?></td>
                <td><?=$row->product->name?></td>
				<td><?=$row->product->ename?></td>
				<td class="text-center"><?=$row->qty + $row->frozen_qty?></td>
				<td class="text-center"><?=$row->qty?></td>
                <td class="text-center">
	                <?=$row->frozen_qty?>
                </td>
				<td class="text-center"><?=\ttwms\business\Form::$goodsTypeList[$row->goods_type]?></td>
				<td class="col-min">
                    <dl class="drop-list drop-list-left">
                        <dt><a href="<?=ViewBase::getUrl("wh/productLocation/index",array('sku'=>$row->product->sku,'enterprise_id'=>$row->product->enterprise_id,'goods_type'=>$row->goods_type))?>" target="_blank" data-component="popup" data-popup-width="600" title="<?=t('查看').'['.$row->product->sku.']'.t('存放库位')?>"><?=t("存放库位")?></a></dt>
                        <dd>
                            <a href="<?=ViewBase::getUrl("wh/inventoryRecord/index",array('sku'=>$row->product->sku,'enterprise_id'=>$row->product->enterprise_id,'goods_type'=>$row->goods_type))?>" data-component="popup" data-popup-width="600" title="<?=t('查看[').$row->product->sku.']'.t('库存流水')?>"><?=t("查看流水")?></a>
                            <a href="<?php echo ViewBase::getUrl('wh/inventoryRo/index', array('sku'=>$row->product->sku,'enterprise_id'=>$row->product->enterprise_id,'goods_type'=>$row->goods_type));?>" data-component="popup" data-popup-width="800"><?=t('查看批次库存')?></a>
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