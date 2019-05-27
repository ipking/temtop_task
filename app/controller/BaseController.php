<?php

namespace ttwms\controller;

use Lite\Component\Paginate;
use Lite\Core\Config;
use Lite\Core\Hooker;
use Lite\Core\RefParam;
use Lite\Core\Router;
use Lite\Core\View;
use Lite\Crud\AbstractController;
use Lite\Crud\ControllerInterface;
use Temtop\StaticVersion;
use ttwms\CurrentUser;
use function Lite\func\filter_date_range;

abstract class BaseController extends AbstractController{
	public function __construct($ctrl = null, $act = null){
		if($ctrl && $act){
			//静态资源版本号
			Hooker::add(Router::EVENT_GET_STATIC_URL, function(RefParam $ref){
				$url = $ref->get('url');
				if(strpos($url, '?') === false){
					$ref->set('url', StaticVersion::buildVersion($url));
				}
			});
			CurrentUser::checkAuth($ctrl, $act);
		}
		$paginate = Paginate::instance();
		$paginate->page_size_flag = true;
		$url_mode = $paginate->getUrl(1, '__PS__');
		$paginate->setConfig([
			'mode' => 'prev,num,next,info',
			'lang' => ['page_info' => '共 %s 条数据, 每页 <b rel="page-size-trigger" data-url-mode="'.$url_mode.'">%i</b> 条']
		]);
		$paginate->setPageSize(15);
		parent::__construct();
	}
	
	/**
	 * 过滤时间范围
	 * @param array $ranges [开始时间，结束时间】
	 * @param string $default_start 默认开始时间
	 * @param string $default_end 默认结束时间
	 * @param bool $datetime
	 * @return array
	 */
	protected static function filterDateRange($ranges, $default_start='', $default_end='', $datetime=false){
		return filter_date_range($ranges, $default_start, $default_end, $datetime);
	}
	
	public static function __getTemplate($ctrl, $act){
		$class = get_called_class();
		$interfaces = class_implements($class, true);
		if(!$interfaces[ControllerInterface::class]){
			return parent::__getTemplate($ctrl, $act);
		}
		
		/** @var View $viewer */
		$viewer = Config::get('app/render');
		
		//存在缺省模版
		if($file = $viewer::resolveTemplate()){
			return $file;
		}
		
		$act = strtolower($act);
		switch($act){
			case 'index':
				return $viewer::resolveTemplate('crud/index.php');
			
			case 'update':
				return $viewer::resolveTemplate('crud/update.php');
			
			case 'info':
				return $viewer::resolveTemplate('crud/info.php');
		}
		return parent::__getTemplate($ctrl, $act);
	}
}