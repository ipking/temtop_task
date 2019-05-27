<?php

use Lite\Core\Config;
use ttwms\ViewBase;

$auth_flag = '#NOACCESS';
$current_uri = ViewBase::getCurrentActiveUri();

$buildAttributesStr = function($attributes=array()){
	$html = '';
	foreach($attributes ?: [] as $k => $v){
		if($k == 'pattern'){
			$html .= " $k=\"".$v."\"";
		} else{
			$html .= " $k=\"".htmlspecialchars($v)."\"";
		}
	}
	return $html;
};
$print_url = function($uri, $title, $attr=null)use($current_uri,$buildAttributesStr){
	if($uri){
		$attr = $buildAttributesStr($attr);
		return '<a href="'.ViewBase::getUrl($uri).'" '.$attr.' class="'.((strcasecmp($current_uri, $uri) === 0)?"active":"").'">'.$title.'</a>';
	}
	return "<span>$title</span>";
};

$navList = Config::get("nav");
?>
<nav class="main-nav" id="step_1">
	<ul>
		<?php foreach($navList as list($title,$uri,$subs)){?>
			<li class="<?=$subs?"has-child":""?>">
				<?=$print_url($uri,$title)?>
				<?php if($subs){?>
					<div class="sub-panel">
						<?php foreach($subs as $sub_title=>$subNav){?>
							<dl>
								<dt><?=$sub_title?></dt>
								<?php foreach($subNav as list($child_title,$child_uri)){?>
									<dd data-action="<?=ViewBase::getUrl($child_uri)?>" class="<?=(strcasecmp($current_uri, $child_uri) === 0)?"active":""?>"><?=$print_url($child_uri,$child_title)?></dd>
								<?php }?>
							</dl>
						<?php }?>
					</div>
				<?php }?>
			</li>
		<?php }?>
	</ul>
</nav>
<script>
	seajs.use(["jquery", 'ywj/net','jquery/highlight'],function($, Net){
		//main nav mark actived
		var $main_nav = $(".main-nav");
		$main_nav.find(".active").closest("li").addClass("active");
	})
</script>