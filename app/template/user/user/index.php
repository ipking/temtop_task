<?php
namespace ttwms;

use ttwms\model\SysUser;

/**
 * @var $list
 * @var array $isActiveList
 * @var array $roleList
 * @var array $search
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
$defines = SysUser::meta()->getPropertiesDefine();
?>
<?= $this->buildBreadCrumbs(array('员工管理')); ?>
	<section class="container">
	<form action="<?= ViewBase::getUrl('user/user/index'); ?>" class="quick-search-frm" method="get" >
		<input type="search" class="txt" placeholder="关键字" name="kw" value="<?=$search['kw']?>">
		
		<?=$this->renderSearchFormElement($search['status'], 'status', $defines['status']);?>
		<button class="btn-search mr-10" type="submit">搜索</button>
	</form>
	<div class="operate-bar">
		<a href="<?=ViewBase::getUrl("user/user/add")?>" class="btn" data-component="popup">添加员工</a>
	</div>
	<table class="data-tbl" data-empty-fill="1" data-component="fixedhead">
		<thead>
		<tr>
			<th>登录账号</th>
			<th>用户姓名</th>
			<th>角色</th>
			<th>邮箱</th>
			<th>状态</th>
			<th>添加时间</th>
			<th class="col-op">操作</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach($list as $user): ?>
			<tr>
				<td><?=ViewBase::displayField('account',$user)?></td>
				<td><?=ViewBase::displayField('name',$user)?></td>
				<td><?=ViewBase::displayField('role_id',$user)?></td>
				<td><?=ViewBase::displayField('email',$user)?></td>
				<td><?=ViewBase::displayField('status',$user)?></td>
				<td><?=ViewBase::displayField('create_time',$user)?></td>
				<td>
					<dl class="drop-list drop-list-left">
						<dt>
							<a href="<?=ViewBase::getUrlWithReturnUrl('user/user/add',['id'=>$user->id,"readonly"=>"readonly"] );?>" data-component="popup">查看</a>
						</dt>
						<dd>
							<a href="<?=ViewBase::getUrlWithReturnUrl('user/user/add', array('id'=>$user->id));?>" data-component="popup">编辑</a>
							<a href="<?=ViewBase::getUrlWithReturnUrl('user/user/resetPwd', array('id'=>$user->id));?>"  data-component="popup">重置密码</a>
					
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