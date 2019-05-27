<?php
namespace ttwms;

use ttwms\model\WhArea;
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
	<?php if(!$info->id){?>
	<ul class="tab">
		<li class="active"><a href="javascript:;" id="add_type_single"><?=t("单个创建")?></a></li>
		<li><a href="javascript:;" id="add_type_batch"><?=t("批量创建")?></a></li>
	</ul>
	<?php }?>
<section class="container">
	
	
	<div id="col-main">
		<form action="<?=ViewBase::getUrl("wh/location/add",['id'=>$get['id']])?>" class="frm" method="POST" data-component="async" id="info_main">
			<input type="hidden" name="id" value="<?=$info->id?>"/>
			<div class="block-wrap">
				<div class="block-info">
					<table class="frm-tbl">
						<caption><?=t("基本属性")?></caption>
						<thead></thead>
						<tbody>
						<tr class="area_code_show">
							<td  class="col-label"><em>*</em><?=t("货位号")?></td>
							<td>
								<span class="area-code" id="area_code_area"><?=$areaList[$info->area_id]?:"&nbsp;"?></span>-
								<input name="info[row_no]" class="txt" size="3" type="number" min="1" max="99" step="1" value="<?=$info->row_no?>">-
								<input name="info[col_no]" class="txt" size="3" type="number" min="1" max="99" step="1" value="<?=$info->col_no?>">-
								<input name="info[top_no]" class="txt" size="3" type="number" min="1" max="999" step="1" value="<?=$info->top_no?>">
							</td>
						</tr>
						<tr class="area_code_show">
							<td  class="col-label"></td>
							<td>(<?=t("库区")?>code-<?=t("行号")?>-<?=t("列号")?>-<?=t("层号")?>)</td>
						</tr>
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

				<div class="block-info block-info-bottom">
					<table class="frm-tbl">
						<caption><?=t("批量创建规则")?></caption>
						<tbody>
						<tr style="clear:both">
							<td class="col-label"><em>*</em><?=t("起始排号")?></td>
							<td><input type="number" class="txt change_grid" name="batch[row_index]" min=1 step="1"></td>
							<td class="col-label"><em>*</em><?=t("总排数")?></td>
							<td><input type="number" class="txt change_grid" name="batch[row_numbers]" min=1 step="1"></td>
						</tr>
						<tr>
							<td class="col-label"><em>*</em><?=t("起始列号")?></td>
							<td><input type="number" class="txt change_grid" name="batch[col_index]" min=1 step="1"></td>
							<td class="col-label"><em>*</em><?=t("列数")?></td>
							<td><input type="number" class="txt change_grid" name="batch[col_numbers]" min=1 step="1"></td>
						</tr>
						<tr>
							<td class="col-label"><em>*</em><?=t("起始层号")?></td>
							<td><input type="number" class="txt change_grid" name="batch[top_index]" min=1 step="1"></td>
							<td class="col-label"><em>*</em><?=t("总层数")?></td>
							<td><input type="number" class="txt change_grid" name="batch[top_numbers]" min=1 step="1"></td>
						</tr>
						<tr>
							<td class="col-label"><?=t("总格数")?></td>
							<td><input size="3" id="grid_number" class="txt readonly" readonly="readonly" disabled="disabled"></td>
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
	<script>
		seajs.use(["jquery","ywj/popup"],function($,Pop){
			var areaCodeJson=<?=json_encode($areaList)?>;
			Pop.autoResizeCurrentPopup();

			$("#area").change(function(){
				var area=$(this).val();
				if(area>0){
					$("#area_code_area").text(areaCodeJson[area])
				}
			});

			$("#add_type_batch").click(function(){
				$(this).parent().parent().find("li").removeClass("active");
				$(this).parent().addClass("active");
				$("#info_main").attr("action","<?=ViewBase::getUrl("wh/location/addBatch")?>");
				$(".block-info-bottom").show();
				$(".area_code_show").hide();
				$(".area_code_show input").attr("disabled","disabled");
			});
			$("#add_type_single").click(function(){
				$(this).parent().parent().find("li").removeClass("active");
				$(this).parent().addClass("active");
				$("#info_main").attr("action","<?=ViewBase::getUrl("wh/location/add")?>");
				$(".block-info-bottom").hide();
				$(".area_code_show").show();
				$(".area_code_show input").attr("disabled",false);
			});
			$(".change_grid").change(function(){
				var row_numbers=$("input[name=batch\\[row_numbers\\]]").val();
				var col_numbers=$("input[name=batch\\[col_numbers\\]]").val();
				var top_numbers=$("input[name=batch\\[top_numbers\\]]").val();
				$("#grid_number").val(row_numbers*col_numbers*top_numbers);
			});
		});
	</script>

<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>