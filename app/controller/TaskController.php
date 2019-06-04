<?php
namespace ttwms\controller;

use Lite\Core\Config;


/**
 * Class IndexController
 * @package ttwms\controller
 */
class TaskController extends BaseController{
	
	/**
	 * @param $get
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function prd_sku_three_and_one($get){
		exec("php ".Config::get('app/code_path')."temtopsys/lib/crontab/prd_sku_three_and_one.php --sku=".trim($get['sku']),$out);
		return [
			'out'=>$out
		];
	}
	
	/**
	 * @param $get
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function sale_sku_three_and_one($get){
		exec("php ".Config::get('app/code_path')."temtopsys/lib/crontab/sale_sku_three_and_one.php --sku=".trim($get['sku']),$out);
		return [
			'out'=>$out
		];
	}
	
}