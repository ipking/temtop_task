<?php

namespace ttwms;

use Temtop\Server;

return [
	'site_name'       => '任务系统',
	'sys_code'        => 'TTTH',
	'url'             => '/',
	'debug'           => !Server::inIDC(),
	'render'          => ViewBase::class,
	'page404'   => function ($err = null, $ex = null){
		(new ViewBase(['error'=>$err]))->render('index/404.php', false, ViewBase::REQ_PAGE);
	},
	'pageError' => function ($err){
		(new ViewBase(['error'=>$err]))->render('index/5xx.php', false, ViewBase::REQ_PAGE);
	},
	'static'          => '/static/',
	'cdn_url'         => 'http://s.mytemtop.com/',
	'default_image'   => 'noimg.jpg',
	'richeditor_home' => 'http://s.mytemtop.com/ueditor/',
];