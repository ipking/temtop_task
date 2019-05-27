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
 * @var array $search
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
$defines = WhLocation::meta()->getPropertiesDefine();
$areaList = WhArea::find()->map('id','code');
?>
<?= $this->buildBreadCrumbs(array('货位管理')); ?>
	<section class="container">
	<form action="<?= ViewBase::getUrl('wh/location/index'); ?>" class="quick-search-frm" method="get" >
		<?=$this->renderSearchFormElement($search['user_id'], 'user_id', $defines['user_id']);?>
		<?=$this->renderSearchFormElement($search['update_user_id'], 'update_user_id', $defines['update_user_id']);?>
		<?=$this->renderSearchFormElement($search['status'], 'status', $defines['status']);?>
		<?=$this->renderSearchFormElement($search['is_mixed'], 'is_mixed', $defines['is_mixed']);?>
		<select name="area_id" id="" placeholder="valid">
			<option value="">--库区--</option>
			<?php foreach ($areaList as $id => $name):?>
				<option <?=$search['area_id'] == $id ?'selected':''?> value="<?=$id?>"><?=h($name)?></option>
			<?php endforeach;?>
		</select>
		<input type="search" class="txt" placeholder="行号" name="row_no" value="<?=$search['row_no']?>">
		<input type="search" class="txt" placeholder="列号" name="col_no" value="<?=$search['col_no']?>">
		<input type="search" class="txt" placeholder="层号" name="top_no" value="<?=$search['top_no']?>">
		<button class="btn-search mr-10" type="submit">搜索</button>
	</form>
	<div class="operate-bar">
		<a href="<?=ViewBase::getUrl("wh/location/add")?>" class="btn" data-component="popup" data-popup-width="1000">添加</a>
		<a href="<?=ViewBase::getUrl("wh/location/batch")?>" class="btn" data-component="temtop/muloperate,popup" data-popup-width="1000"><?=t("批量修改货位属性")?></a>
		<a href="<?=ViewBase::getUrl("wh/location/toPrint")?>"  class="btn" data-component="temtop/muloperate,popup" ><?=t("打印所选条码")?></a>
	</div>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th class="col-chk"><input type="checkbox" data-component="checker"></th>
			<th>货位号</th>
			<th>所属库区</th>
			<th>是否支持混放</th>
			<th>状态</th>
			<th>创建人</th>
			<th>创建时间</th>
			<th>修改人</th>
			<th>修改时间</th>
			<th class="col-op">操作</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach($list as $location): ?>
			<tr>
				<td class="col-chk"><input type="checkbox" name="ids[]" value="<?=$location->id?>"/> </td>
				<td><?=ViewBase::displayField('code',$location)?></td>
				<td><?=ViewBase::displayField('area_id',$location)?></td>
				<td><?=ViewBase::displayField('is_mixed',$location)?></td>
				<td><?=ViewBase::displayField('status',$location)?></td>
				<td><?=ViewBase::displayField('user_id',$location)?></td>
				<td><?=ViewBase::displayField('create_time',$location)?></td>
				<td><?=ViewBase::displayField('update_user_id',$location)?></td>
				<td><?=ViewBase::displayField('update_time',$location)?></td>
				<td>
					<dl class="drop-list drop-list-left drop-list-only">
						<dt>
							<a href="<?=ViewBase::getUrl('wh/location/add', array('id'=>$location->id));?>" data-component="popup" data-popup-width="1000">编辑</a>
						</dt>
					</dl>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo $paginate; ?>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>