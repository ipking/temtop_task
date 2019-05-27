<?php

use ttwms\ViewBase;
use function lite\func\h;


include ViewBase::resolveTemplate('inc/header.inc.php');
ViewBase::buildBreadCrumbs(array('操作日志'));
?>
<style>
	.log-frm-tbl th{background:#ccc}
	.log-frm-tbl tr td{width:25%}
	[data-changes] {color:#1f69b3; cursor:pointer;}
</style>
<?php if($logs):?>
<table class="data-tbl">
	<thead>
		<tr>
			<th>描述</th>
			<th>类型</th>
            <th><?= ViewBase::getOrderLink('操作人', 'user_id') ?></th>
            <th><?= ViewBase::getOrderLink('日期', 'create_time') ?></th>
			<th class="col-op">变更内容</th>
		</tr>
	</thead>
	<tbody>
	<?php
	
	foreach($logs as $log):?>
		<tr>
			<td>
				<?=h($log->content);?>
			</td>
			<td><?=ViewBase::displayField('type', $log);?></td>
			<td><?=ViewBase::displayField('user_id', $log);?></td>
			<td><?=ViewBase::displayField('create_time', $log);?></td>
			<td class="col-op"><span data-changes="<?=h($log->changes);?>">查看</span></td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
<?php echo $paginate;?>
<div class="operate-row">
	<?=ViewBase::getDialogCloseBtn();?>
</div>
<script>
	seajs.use('jquery', function($){
		$('[data-changes]').click(function(){
			alert($(this).data('changes'));
		})
	})
</script>
<?php else:?>
<div class="empty">没有数据</div>
<?php endif;?>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php');?>
