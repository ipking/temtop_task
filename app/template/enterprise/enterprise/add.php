<?php
namespace ttwms;

use function Lite\func\ha;

/**
 * @var \ttwms\model\Enterprise $user
 * @var array $isActiveList
 * @var array $get
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
	<style>
		.repeat-password {display:block; margin-top:10px}
	</style>
<?= $this->buildBreadCrumbs(array('用户管理'=>'enterprise/enterprise/index',"添加用户")); ?>
<section class="container">
	<form action="<?= ViewBase::getUrl('enterprise/enterprise/add',["id"=>$get['id']]); ?>" class="frm <?=$get['readonly']?>" method="post" data-component="async" <?=$get['readonly']?>>
		<table class="frm-tbl">
			<tbody>
				<tr>
					<th>客户名称</th>
					<td><input type="text" class="txt" name="name" required value="<?=ha($user->name)?>"></td>
				</tr>
				<tr>
					<th>客户代码</th>
					<td>
						<input type="text" name="code" class="txt" value="<?=ha($user->code)?>" <?=(!$user->id)?'required':'readonly'?> />
					</td>
				</tr>
				<?php if(!$user->id){?>
				<tr>
					<th>密码</th>
					<td>
						<input type="password" name="password" class="txt" value=""  data-component="password" data-password-generator="1" required/>
					</td>
				</tr>
				<?php }?>
				
				<tr>
					<th>客户级别</th>
					<td><?=ViewBase::renderFormElementQuick($user,"level_id")?></td>
				</tr>
				<?php if($user->id){?>
				<tr>
					<th>余额(欧元)</th>
					<td>
						<span><?=ha($user->balance)?></span>
					</td>
				</tr>
				<?php }?>
				<tr>
					<th>信用额度(欧元)</th>
					<td><input type="text" name="credit_line" class="txt" value="<?=ha($user->credit_line)?>"></td>
				</tr>
			</tbody>
		</table>
		
		<div class="operate-row">
			<input type="submit" class="btn" value="保存">
			<?=ViewBase::getDialogCloseBtn()?>
		</div>
	</form>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>