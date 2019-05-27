<?php

use ttwms\ViewBase;
use function Lite\func\h;
use function Lite\func\ha;

/** @var $roleList array */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
<?=$this->buildBreadCrumbs(array('角色管理'));?>
<section class="container">
	<div class="content">
		<div class="operate-bar">
			<a href="<?=ViewBase::getUrl("user/role/add")?>" class="btn" data-component="popup">添加角色</a>
		</div>
		<table class="data-tbl" data-empty-fill="1">
			<thead>
			<tr>
				<th >角色名称</th>
				<th >类型</th>
				<th >描述</th>
				<th class="col-op">操作</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($list?:[] as $row):;?>
				<tr>
					<td  data-component="highlight" data-highlight="<?=h($search['kw'])?>">
						<?=h($row['name']);?>
					</td>
					<td>
						<?=ViewBase::displayField('type',$row)?>
					</td>
					<td>
						<?=ViewBase::displayField('description',$row)?>
					</td>
					<td class="col-op">
						<a href = "<?=ViewBase::getUrl('user/role/add', array('id'=>$row['id']))?>" class="btn btn-reset" data-component="popup">编辑角色</a>
						<?php if($row['type'] != \ttwms\model\SysRole::TYPE_ADMIN):?>
						<a href = "<?=ViewBase::getUrl('user/role/updateAccess', array('role_name'=>$row['name'],'role_id'=>$row['id']))?>" class="btn btn-reset">编辑权限</a>
					<?php endif;?>
					</td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
		<?=$paginate?>
	</div>
</section>
<script>
	seajs.use(['jquery'], function($){
		$('.PopupDialog-close').live('click',function(){
			location.reload();
		});
	});
</script>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php');?>
