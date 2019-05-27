<?php
namespace ttwms;

/**
 * @var $list
 * @var array $isActiveList
 * @var array $roleList
 * @var array $search
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('用户级别管理')); ?>
	<section class="container">
	<form action="<?= ViewBase::getUrl('Enterprise/level/index'); ?>" class="quick-search-frm" method="get" >
		<input type="search" class="txt" placeholder="关键字" name="kw" value="<?=$search['kw']?>">
		<button class="btn-search mr-10" type="submit">搜索</button>
	</form>
	<div class="operate-bar">
		<a href="<?=ViewBase::getUrl("Enterprise/level/add")?>" class="btn" data-component="popup">添加</a>
	</div>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th>级别名称</th>
			<th>创建时间</th>
			<th class="col-op" style="width: 10%;min-width: 165px;">操作</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach($list as $level): ?>
			<tr>
				<td><?=ViewBase::displayField('name',$level)?></td>
				<td><?=ViewBase::displayField('create_time',$level)?></td>
				<td>
					<a href="<?=ViewBase::getUrl('Enterprise/level/add', array('id'=>$level->id));?>" data-component="popup" class="btn">编辑</a>
					<a href="<?=ViewBase::getUrl('Enterprise/level/delete', array('id'=>$level->id));?>" data-component="confirm,async" data-confirm-message="确认是否删除此级别？" class="btn btn-reset">删除</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo $paginate; ?>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>