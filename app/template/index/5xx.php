<?php
namespace ttwms;
/** @var array $search */
/** @var array $result */
/** @var string $error */
/** @var Paginate $paginate */

use Lite\Component\Paginate;
use function Lite\func\h;

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<style>
	.error-ti {color:gray; font-size:32px; margin-top:3em;}
	.error-desc {font-size:18px; margin:1em 0;}
	.page-5xx img {display:block; margin:5em auto 4em auto;}
	.page-5xx .op {text-align:center; color:#555}
	.page-5xx .op a {color:#666; text-decoration:underline}
</style>
<section class="page-wrap page-5xx">
	<div class="op">
		<div class="error-ti">服务器给自己提了个<span data-component="tip" data-tip-content="问题是：<?=h($error);?>，你懂么 :P">问题</span>...</div>
		<div class="error-desc">出现这个页面是我们的问题。</div>

		如果你愿意，可以 <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=361591257&site=qq&menu=yes">告诉作者</a>，
		<div>或者 <a href="<?=ViewBase::getUrl();?>">返回首页</a> |
			<a href="#nogo" onclick="location.reload()">刷新页面</a></div>
	</div>
	<img src="<?=ViewBase::getImgUrl('error.png');?>" alt="">
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php');?>