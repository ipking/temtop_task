<?php
namespace ttwms;

use ttwms\model\Enterprise;

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
<?= $this->buildBreadCrumbs(array('用户管理')); ?>
	<section class="container">
	<form action="<?= ViewBase::getUrl('Enterprise/Enterprise/index'); ?>" class="quick-search-frm" method="get" >
		<input type="search" class="txt" placeholder="关键字" name="kw" value="<?=$search['kw']?>">
		<button class="btn-search mr-10" type="submit">搜索</button>
	</form>
	<div class="operate-bar">
		<a href="<?=ViewBase::getUrl("Enterprise/Enterprise/add")?>" class="btn" data-component="popup">添加</a>
	</div>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th>客户代码</th>
			<th>客户名称</th>
			<th>客户级别</th>
			<th>Token</th>
			<th>余额(欧元)</th>
			<th>信用额度(欧元)</th>
			<th>创建时间</th>
			<th>修改时间</th>
			<th class="col-op">操作</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach($list as $user): ?>
			<tr>
				<td><?=ViewBase::displayField('code',$user)?></td>
				<td><?=ViewBase::displayField('name',$user)?></td>
				<td><?=ViewBase::displayField('level_id',$user)?></td>
				<td><?=ViewBase::displayField('token',$user)?></td>
				<td><?=ViewBase::displayField('balance',$user)?></td>
				<td><?=ViewBase::displayField('credit_line',$user)?></td>
				<td><?=ViewBase::displayField('create_time',$user)?></td>
				<td><?=ViewBase::displayField('update_time',$user)?></td>
				<td>
					<dl class="drop-list drop-list-left">
						<dt>
							<a href="<?=ViewBase::getUrl('Enterprise/Enterprise/add', array('id'=>$user->id));?>" data-component="popup">编辑</a>
						</dt>
						<dd>
							<a href="<?=ViewBase::getUrl('Enterprise/Enterprise/password', array('id'=>$user->id));?>" data-component="popup">重置密码</a>
							<a href="<?=ViewBase::getUrl('Enterprise/Enterprise/token', array('id'=>$user->id));?>" data-component="confirm,async" data-confirm-message="确认更新token ?">更新token</a>
						</dd>
					</dl>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo $paginate; ?>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>