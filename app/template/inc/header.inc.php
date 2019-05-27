<?php

namespace ttwms;

use Lite\Core\Config;
use Lite\Core\Router;
use Lite\Core\View;
use Temtop\Server;
use Temtop\StaticVersion;


/** @var View $this */
$FRONTEND_BASE = Config::get('app/cdn_url'); ?>
<!Doctype html>
<html lang="en" class="<?=$_GET['ref'] == 'iframe' ? 'page-iframe':'';?> server-<?=strtolower(Server::getServerEnvIdentify());?>">
<head>
	<meta charset="UTF-8">
	<title><?=ViewBase::getPageTitle()?></title>
	<?= $this->getCss(
		$FRONTEND_BASE . 'ywj/ui/common/reset.css',
		$FRONTEND_BASE . 'ywj/ui/backend/common.css',
		//$FRONTEND_BASE . "mytemtop/css/style.css",
		$FRONTEND_BASE . "mytemtop/css/access.css"
	)?>
	<link rel="stylesheet" href="<?=$this->getStaticUrl($FRONTEND_BASE.'awesome/css/font-awesome.min.css');?>">
	<link rel="shortcut icon" href="<?=$FRONTEND_BASE;?>mytemtop/img/favicon-ttwms.ico"/>
	<!-- Iconfont字体图标JS -->
	<script src="//at.alicdn.com/t/font_1059711_53z5i0nvxw4.js"></script>
	<!-- 几个项目公用样式（包含自定义主题功能） -->
	<?=ViewStyle::getStyle([$FRONTEND_BASE."mytemtop/css/style.less"],'blue')?>
	<!-- 当前项目全局样式 -->
  <?= $this->getCss('style.css')?>
	<script>
		var STATIC_VERSION_CONFIG = <?=json_encode(StaticVersion::getConfig());?>;
		var FRONTEND_HOST = '<?=$FRONTEND_BASE?>';
		var PRINTER_SET_CGI = '<?=Router::getUrl('sys/config/printerSetup')?>';
		var UEDITOR_HOME_URL = window.UEDITOR_HOME_URL || '<?=$FRONTEND_BASE?>ueditor/';
		var UEDITOR_INT_URL = '<?=Router::getUrl('richeditor');?>';
		var EXCHANGE_RATE_URL = '<?=Router::getUrl('sys/currency/exchangeData');?>';
		var UPLOAD_URL = window.UPLOAD_URL || '<?=Router::getUrl('upload/upload', array('ref'=>'json'));?>';
		var UPLOAD_PROGRESS_URL = window.UPLOAD_PROGRESS_URL || '<?=Router::getUrl('upload/progress', array('ref'=>'json'));?>';
	</script>
	<?=$this->getJs($FRONTEND_BASE.'seajs/sea.js',
		$FRONTEND_BASE.'seajs/config.js',
		$FRONTEND_BASE.'ywj/component/imagescale.js',
		$FRONTEND_BASE."mytemtop/js/global.js",
		$FRONTEND_BASE."mytemtop/js/scroll_bar.js");?>
	<script>
		seajs.use('ywj/auto');
		seajs.use('ywj/liteladder');
		<?php if(!Server::inIDC()):?>seajs.use('temtop/dev');<?php endif;?>
	</script>
	<?= isset($PAGE_HEAD_HTML) ? $PAGE_HEAD_HTML : '';?>
</head>
<body>
<section class="page">
	<header class="header">
		<h1 class="logo">
			<a href="/">
				<img src="#" alt="" class="log-img">
				<span class="site-name"><?=Config::get('app/site_name')?></span>
			</a>
		</h1>
		<?php if(true || CurrentUser::instance()->getLoginInfo()):
			?>
			<?php include ViewBase::resolveTemplate('inc/nav.inc.php');?>
			<!-- 个人信息 -->
			<ul class="account-nav" id="account-nav">
				<li class="has-child">
					<svg class="icon" aria-hidden="true">
						<use xlink:href="#iconicon_20" id="iconicon_20"></use>
					</svg>
					<span class="account-name"><?=CurrentUser::getUserName()?></span>
					<ul>
						<li><a href="<?=ViewBase::getUrl("user/user/myInfo",['id'=>CurrentUser::getUserId()]);?>" data-component="popup" data-popup-width="500">用户信息</a></li>
						<li><a href="<?=ViewBase::getUrl("user/user/updatePwd");?>" data-component="popup">修改密码</a></li>
						<li><a href="<?=ViewBase::getUrl("index/logout");?>" data-component="async">退出</a></li>
					</ul>
				</li>
			</ul>
			
		
		<?php endif;?>
	</header>
	<?=ViewBase::getPageBreadCrumbs();?>