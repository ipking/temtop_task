<?php
namespace ttwms;

use ttwms\model\WhArea;
use ttwms\model\WhLocation;
use function Lite\func\h;
use function Temtop\t;

/**
 * @var \ttwms\model\WhLocation $info
 * @var array $isActiveList
 * @var array $get
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
$areaList = WhArea::find('status = ? ',WhArea::STATUS_ENABLED)->map('id','code');
$info = new WhLocation();
?>
	<style>
		.repeat-password {display:block; margin-top:10px}
	</style>
<?= $this->buildBreadCrumbs(array('货位管理'=>'wh/location/index',"添加")); ?>
<style>
	.block-info p{text-align: center;}
	.area-code{display: inline-block;background-color: #ccc;width: 40px;text-align:center;height: 27px;vertical-align:middle;}
	.block-info-bottom{display: none;}
	#col-main #info_main caption{display:table-caption;}

	.frm .col-label {white-space:nowrap;}
	.block-wrap {display:flex;}
	.block-wrap .block-info {flex:1; margin:0 10px;}
</style>
<section class="container">
	
	
	<div id="col-main">
		<form action="<?=ViewBase::getUrl("wh/location/batch",$get)?>" class="frm" method="POST" data-component="async" id="info_main">
			<div class="block-wrap">
				<div class="block-info">
					<table class="frm-tbl">
						<caption><?=t("基本属性")?></caption>
						<thead></thead>
						<tbody>
						<tr>
							<td  class="col-label"><em>*</em><?=t("所属库区")?></td>
							<td>
								<select id="area" name="info[area_id]" id="" required>
									<option value="">--请选择--</option>
									<?php foreach ($areaList as $id => $name):?>
										<option <?=$info->area_id == $id ?'selected':''?> value="<?=$id?>"><?=h($name)?></option>
									<?php endforeach;?>
								</select>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class="block-wrap">
				<div class="block-info">
					<table class="frm-tbl">
						<caption><?=t("货位属性")?></caption>
						<thead></thead>
						<tbody>
						<tr>
							<td  class="col-label"><em>*</em><?=t("长")?>(cm)</td>
							<td><input type="number" min="1" step="1" name="info[length]" value="<?=$info->length?>" class="txt"></td>
						</tr>
						<tr>
							<td  class="col-label"><em>*</em><?=t("宽")?>(cm)</td>
							<td><input type="number" min="1" step="1" name="info[width]" value="<?=$info->width?>" class="txt"></td>
						</tr>
						<tr>
							<td  class="col-label"><em>*</em><?=t("高")?>(cm)</td>
							<td><input type="number" min="1" step="1" name="info[height]" value="<?=$info->height?>" class="txt"></td>
						</tr>
						<tr>
							<td  class="col-label"><em>*</em><?=t("最大容量")?>(PCS)</td>
							<td><input type="number" min="1" step="1" name="info[max_pcs]" value="<?=$info->max_pcs?>" class="txt"></td>
						</tr>
						<tr>
							<td  class="col-label"><em>*</em><?=t("最大承重")?>(Kg)</td>
							<td><input type="number" min="1" step="0.001" name="info[max_weight]" value="<?=$info->max_weight?>" class="txt"></td>
						</tr>
						<tr>
							<td  class="col-label"><?=t("是否体积限制")?></td>
							<td><?=ViewBase::renderElementQuick($info,"is_volume_limit","info[is_volume_limit]")?></td>
						</tr>
						<tr>
							<td  class="col-label"><?=t("是否重量限制")?></td>
							<td><?=ViewBase::renderElementQuick($info,"is_weight_limit","info[is_weight_limit]")?></td>
						</tr>
						<tr>
							<td  class="col-label"><?=t("是否数量限制")?></td>
							<td><?=ViewBase::renderElementQuick($info,"is_pcs_limit","info[is_pcs_limit]")?></td>
						</tr>
						<tr>
							<td  class="col-label"><?=t("是否支持混放")?></td>
							<td><?=ViewBase::renderElementQuick($info,"is_mixed","info[is_mixed]")?></td>
						</tr>
						<tr>
							<td  class="col-label"><?=t("状态")?></td>
							<td><?=ViewBase::renderElementQuick($info,"status","info[status]")?></td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="operate-row">
				<input type="submit" class="btn" value="<?=t("确定")?>" />
				<?=ViewBase::getDialogCloseBtn()?>
			</div>
		</form>
	</div>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>