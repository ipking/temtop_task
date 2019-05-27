<?php

namespace ttwms;

/** @var ViewBase $this */

use ttwms\controller\IndexModController;
use function Lite\func\array_group;

ViewBase::setPagePath(array('时钟设置'));
include ViewBase::resolveTemplate('inc/header.inc.php');
$time_config = IndexModController::getClockSetting();
$config_list = $time_config['list'];
$mode = $time_config['mode'];
?>
<style>
	#time-list {width:400px; max-height:200px; overflow-y:auto; padding:5px; border:1px solid #ccc; margin-top:10px;}
	#time-list li {padding:5px 5px;}
	#time-list li:hover {background-color:#98c5ea !important;}
	#time-list .tz {color:gray; display:inline-block; margin-left:0.2em;}
	#time-list .del {float:right; display:inline-block; padding:1px 5px; cursor:pointer;}
	#time-list .del:hover {color:red;}
	#time-list li:nth-child(even) {background-color:#eee;}
</style>
<form action="<?=ViewBase::getUrl("indexMod/clockSetting");?>" method="post" data-component="async">
	<table class="frm-tbl">
		<tbody>
			<tr>
				<th>显示样式</th>
				<td>
					<label><input type="radio" required <?=$mode == 'clock' ? 'checked':'';?> value="clock" name="mode">钟表</label>
					<label><input type="radio" required <?=$mode == 'digit' ? 'checked':'';?> value="digit" name="mode">数字</label>
				</td>
			</tr>
			<tr>
				<th>城市</th>
				<td>
					<select id="tz_sel">
						<option value="">选择地区</option>
						<?php
						$tmp = include __DIR__.'/tz.php';
						$tmp = array_group($tmp, 'ihg');
						foreach($tmp as $ihg=>$cities):
							?>
							<optgroup label="<?=$ihg;?>">
								<?php foreach($cities as $info):?>
								<option value="<?=$info['offset'];?>"><?=$info['city'];?></option>
								<?php endforeach;?>
							</optgroup>
						<?php endforeach;?>
					</select>
					<input type="button" id="add-btn" value="添加" class="btn-weak">

					<ul id="time-list">
						<?php foreach($config_list as list($ct, $zone)):?>
						<li>
							<span class="ct"><?=$ct;?></span>
							<span class="tz"><?=$zone;?></span>
							<span class="del">&times;</span>
							<input type="hidden" name="timezone[]" value="<?=$zone;?>">
							<input type="hidden" name="city[]" value="<?=$ct;?>">
						</li>
						<?php endforeach;?>
					</ul>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="submit" value="保存">
				</td>
			</tr>
		</tbody>
	</table>
</form>
<script>
	seajs.use('jquery', function($){
		var $list = $('#time-list');
		var $sel = $('#tz_sel');

		var pl = function(str){
			return (str+'').length < 2 ? ('0'+str) : str;
		};

		var min2tz = function(min){
			var sig = min >= 0;
			min = Math.abs(min);
			var h = parseInt(min/60, 10);
			var m = parseInt((min-h*60)/60, 10);
			return (sig ? '+' : '-') + pl(h) + ':'+pl(m);
		};
		
		$list.delegate('.del', 'click', function(){
			$(this).closest('li').remove();
		});
		
		$('#add-btn').click(function(){
			if($sel[0].selectedIndex === 0){
				return;
			}

			var $opt = $($sel[0].options[$sel[0].selectedIndex]);
			var ct = $opt.text();
			var tz = min2tz($opt.val());
			var html = ['<li>'];
			html.push('<span class="ct">'+ct+'</span>');
			html.push('<span class="tz">'+tz+'</span>');
			html.push('<span class="del">&times;</span>');
			html.push('<input type="hidden" name="timezone[]" value="'+tz+'">');
			html.push('<input type="hidden" name="city[]" value="'+ct+'">');
			html.push('</li>');
			$list.append(html.join(''));
			$sel[0].selectedIndex = 0;
		});
	});
</script>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php');?>
