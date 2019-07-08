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
	public function index($get){
		return $this->$get['action']($get);
	}
	
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
	
	/**
	 * @param $get
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function tbs_sale_order_confirm($get){
		exec("php ".Config::get('app/code_path')."temtopsys/lib/test/wh_confirm_order.php --order_no=".trim($get['order_no']),$out);
		return [
			'out'=>$out
		];
	}
	
	/**
	 * @param $get
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function tbs_sale_order_sync($get){
		exec("php ".Config::get('app/code_path')."temtopsys/lib/test/wh_sync_order.php --order_no=".trim($get['order_no']),$out);
		return [
			'out'=>$out
		];
	}
	
	
	/**
	 * @param $get
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function oms_sale_order_confirm($get){
		exec("php ".Config::get('app/code_path')."oms/www/test/OrderTask.php asyncOrderToWms reference_no=".trim($get['order_no']),$out);
		return [
			'out'=>$out
		];
	}
	
	/**
	 * @param $get
	 * @return array
	 * @throws \Lite\Exception\Exception
	 */
	public function oms_sale_order_sync($get){
		exec("php ".Config::get('app/code_path')."oms/www/test/OrderTask.php getWmsOrder reference_no=".trim($get['order_no']),$out);
		return [
			'out'=>$out
		];
	}
	
}