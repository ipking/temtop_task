<?php
	namespace ttwms;
	
	use ttwms\model\WhArea;
	use ttwms\model\WhLocation;
	use function Lite\func\h;
	use function Temtop\t;
	
	/**
	 * @var $list
	 * @var array $isActiveList
	 * @var array $roleList
	 * @var array $param
	 * @var array $supplyStatusList
	 * @var \Lite\Component\Paginate $paginate
	 */
	
	include ViewBase::resolveTemplate('inc/header.inc.php');
	
	$areaList = WhArea::find()->map('id','code');
	?>
	<?= $this->buildBreadCrumbs(array('货位管理')); ?>

		<style>
			span.status_0{background-color: #ff7900;}
			span.status_1{background-color: green;}
			#msg-tbl td,#msg-tbl th{text-align: center;}
			#msg-tbl .td_txt_left{text-align: left;}
			.select_block{display: inline-block;width: 200px;}
		</style>
	<section class="container">
	<form action="<?= ViewBase::getUrl('wh/location/SimpleView'); ?>" method="GET" class="search-frm" >
		<!-- <?=t("中转仓库")?>-<?=t("库区")?> <?=t("下拉")?> -->
		<select name="area_id" id="" placeholder="valid">
			<option value="">--库区--</option>
			<?php foreach ($areaList as $id => $name):?>
				<option <?=$param['area_id'] == $id ?'selected':''?> value="<?=$id?>"><?=h($name)?></option>
			<?php endforeach;?>
		</select>
		<input type='text' name='code_row' class="txt" placeholder="<?=t("货位号-行")?>" value="<?=$param['code_row']?>" size="3"/>
		<input type='text' name='code_col' class="txt" placeholder="<?=t("货位号-列")?>" value="<?=$param['code_col']?>" size="3"/>
		<input type='text' name='code_top' class="txt" placeholder="<?=t("货位号-层")?>" value="<?=$param['code_top']?>" size="3"/>

		<input type='text' name='code' class="txt" placeholder="<?=t("货位号-ALL")?>" value="<?=$param['code']?>" size="25"/>
		

		<input type="hidden" name="ref" value="iframe" />
		<input type="submit" value="<?=t("搜索")?>" class="btn"/>
	</form>
	
	<table class="data-tbl" id="msg-tbl" data-empty-fill="1">
		<caption><?=t("仓库管理")?></caption>
		<thead>
		<tr>
			<th width="200"><?=t("货位号")?></th>
			<th ><?=t("所属库区")?></th>
			<th ><?=t("是否支持混放")?></th>
			<th ><?=t("操作")?></th>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($list?:array() as $row):?>
			<tr>
                <td><?=$row->code?></td>
				<td><?=$areaList[$row->area_id]?></td>
				<td><?=ViewBase::displayField('is_mixed',$row)?></td>
				<td>
					<a href="javascript:void(0)" class="selectLocation" data-code="<?=$row->code?>" data-id="<?=$row->id?>"><?=t("选择")?></a>
				</td>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
		<?php echo $paginate; ?>
	</section>
<script>
	seajs.use(["jquery","ywj/popup"],function($,Pop){
		$(".selectLocation").click(function(){
			Pop.fire('onSuccess',$(this).data());
			Pop.closeCurrentPopup();
		});
	});
</script>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>