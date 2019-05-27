<?php

use ttwms\ViewBase;
use function Lite\func\is_assoc_array;

$PAGE_HEAD_HTML .= <<<EOT
<style>
	.action-list label{display:inline-block;width:100%;}
</style>
EOT;
ViewBase::setPagePath(array('角色管理'=>'user/role/index','编辑权限'));
include ViewBase::resolveTemplate('inc/header.inc.php');

function show_check_box($check_state=0){
	$s = $check_state == 0 ? 'checkbox-unchecked' : ($check_state == 1 ? 'checkbox-checked' : 'checkbox-partial');
	return '<span class="touch-icon checkbox-icon '.$s.'">checkbox</span>';
}

function show_collapse($expand=false){
	return '<span class="touch-icon collapse-icon '.($expand ? 'collapse-expand':'').'">collapse</span>';
}

function show_access($k, $v, $is_mod=false,$level =0){
	$html = '';
	if(is_assoc_array($v)){
		$html .= '<li class="'.($is_mod ? 'access-mod':'').'"><label>'.show_collapse(true).show_check_box(2).'<span class="access-fold">'.$k.'</span></label>';
		$html .= '<ul class="access-expand level_'.$level.'">';
		$level++;
		foreach($v as $k2=>$v2){
			$html .= show_access($k2, $v2, null,$level);
		}
		$html .= '</ul>';
		$html .= '</li>';
	} else {
		$cls = $v[1] == '*' ? 'root-item': 'action-list';
		$html .= '<li class="'.$cls.'">
					<label>
					<input type="checkbox" name="ids[]" value="'.$v[0].'" '.($v[1]?'checked="checked"':'').'/>
					<span class="access-name">'.$k.'</span>
					</label>
				</li>';
	}
	return $html;
}
?>
<section class="container">
	<form action="<?= ViewBase::getUrl('user/role/updateAccess', array('id' => $get['id'])); ?>" class="frm" data-component="async"  method="post">
		<table class="frm-tbl access-frm">
			<tbody>
			<tr>
				<th>角色</th>
				<td>
					<span class="role-name">
					<?=$get['role_name']?>
					</span>
				</td>
			</tr>
			<tr>
				<th style="padding-top:1em">权限</th>
				<td data-component="partialcheck">
					<div class="access-list-wrap access-white-list">
						<div id="access-ctrl-list">
							<ul class="access-list">
								<?php foreach($auth_list as $k=>$v):?>
									<?php echo show_access($k, $v, true,0);?>
								<?php endforeach;?>
							</ul>
						</div>
					</div>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td style="text-align:center" colspan="2" class="col-action">
					<input type="submit" value="保存" class="btn btn-primary">
					<input type="hidden" name="role_id" value="<?=$get['role_id']?>">
					<a href="<?=ViewBase::getReturnUrl('user/role/index');?>" target="_top" class="btn btn-weak">返回</a>
				</td>
			</tr>
			</tfoot>
		</table>
	</form>
	<style>
		input[type=submit][disabled] {display: none}
		.access-list-wrap { background-color:#fff; border:1px solid #CDCDCD; width:100%;}
		.role-name {font-size:16px;}
	</style>
</section>
<script>
	seajs.use(['jquery', 'ywj/util'], function($, util){
		$('.collapse-icon').click(function(){
			var li = util.findParent(this, 'li');
			var ul = $('ul:first', li);
			var toExp = !(ul.hasClass('access-expand'));

			$(this)[toExp ? 'addClass' : 'removeClass']('collapse-expand');
			ul[toExp ? 'addClass' : 'removeClass']('access-expand');
			ul[toExp ? 'removeClass' : 'addClass']('access-collapse');

			//collapse all children
			if(!toExp){
				ul.find('.collapse-icon').removeClass('collapse-expand');
				ul.find('ul').removeClass('access-expand').addClass('access-collapse');
			}
			return false;
		});
	});
</script>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>
