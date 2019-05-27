<?php
namespace ttwms;
/** @var array $search */
/** @var string $error */
/** @var string $FRONTEND_BASE */

include ViewBase::resolveTemplate('inc/header.inc.php');
?>
	<style>
		.error-ti {color:gray; font-size:18px;}
		.error-ti:before,
		.error-ti:after {font-family:FontAwesome, serif; content:"\f10d"; color:#ccc; margin:0 0.5em;}
		.error-ti:after {content:"\f10e"}
		.page-404 img {display:block; margin:2em auto 4em auto;}
		.page-404 .op {text-align:center; color:#ccc;}
		.page-404 .op a {color:gray;}
	</style>
	<section class="page-wrap page-404">
		<img src="<?= ViewBase::getImgUrl($FRONTEND_BASE.'mytemtop/img/404.png');?>" alt="">
		<div class="op">
			<?php if($error):?>
				<p><span class="error-ti"><?=$error;?></span></p>
			<?php endif;?>
			你可以：<a href="<?=ViewBase::getUrl();?>">返回首页</a> |
			<a href="#nogo" onclick="location.reload()">刷新页面</a>
		</div>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php');?>