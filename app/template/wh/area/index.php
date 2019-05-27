<?php
namespace ttwms;

use ttwms\model\WhArea;

/**
 * @var $list
 * @var array $isActiveList
 * @var array $roleList
 * @var array $search
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
$defines = WhArea::meta()->getPropertiesDefine();
?>
<?= $this->buildBreadCrumbs(array('库区管理')); ?>
	<section class="container">
	<form action="<?= ViewBase::getUrl('wh/area/index'); ?>" class="quick-search-frm" method="get" >
		<?=ViewBase::renderSearchFormElement($search['user_id'], 'user_id', $defines['user_id']);?>
		<?=ViewBase::renderSearchFormElement($search['update_user_id'], 'update_user_id', $defines['update_user_id']);?>
		<input type="search" class="txt" placeholder="名称/代码" name="kw" value="<?=$search['kw']?>">
		<button class="btn-search mr-10" type="submit">搜索</button>
	</form>
	<div class="operate-bar">
		<a href="<?=ViewBase::getUrl("wh/area/add")?>" class="btn" data-component="popup">添加</a>
	</div>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th>库区名称</th>
			<th>库区代码</th>
			<th>性质</th>
			<th>顺序</th>
			<th>创建时间</th>
			<th>创建人</th>
			<th>修改时间</th>
			<th>修改人</th>
			<th>状态</th>
			<th class="col-op">操作</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach($list as $area): ?>
			<tr>
				<td><?=ViewBase::displayField('name',$area)?></td>
				<td><?=ViewBase::displayField('code',$area)?></td>
				<td><?=ViewBase::displayField('type',$area)?></td>
				<td><?=ViewBase::displayField('seq',$area)?></td>
				<td><?=ViewBase::displayField('create_time',$area)?></td>
				<td><?=ViewBase::displayField('user_id',$area)?></td>
				<td><?=ViewBase::displayField('update_time',$area)?></td>
				<td><?=ViewBase::displayField('update_user_id',$area)?></td>
				<td><?=ViewBase::displayField('status',$area)?></td>
				<td>
					<dl class="drop-list drop-list-left drop-list-only">
						<dt>
							<a href="<?=ViewBase::getUrl('wh/area/add', array('id'=>$area->id));?>" data-component="popup">编辑</a>
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