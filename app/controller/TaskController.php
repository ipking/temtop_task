<?php
namespace ttwms\controller;

use Lite\Core\Config;
use Lite\Core\Result;
use Lite\Core\Router;
use Lite\Exception\BizException;
use function Temtop\array_trim;


/**
 * Class IndexController
 * @package ttwms\controller
 */
class TaskController extends BaseController{
	
	/**
	 * @param $get
	 * @return array
	 */
	public function prd_sku_three_and_one($get){
		exec("php E:/htdocs/temtop/temtopsys/lib/crontab/prd_sku_three_and_one.php --sku=".trim($get['sku']),$out);
		return [
			'out'=>$out
		];
	}
	
	
	
}