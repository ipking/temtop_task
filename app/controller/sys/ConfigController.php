<?php
namespace ttwms\controller\sys;

use Lite\Core\Config;
use ttwms\controller\BaseController;

/**
 * @auth 打印机
 * Class PrinterController
 * @package oms\www\controller
 */
class ConfigController extends BaseController{
	/**
	 * @auth 打印机设置
	 * @param $get
	 * @return array
	 */
	public function printerSetup($get){
		$papers = Config::get('printer');
		return [
			'papers' => $papers,
			'get' => $get,
		];
	}
	/**
	 * @auth 纸张大小设置
	 * @param $get
	 * @return array
	 */
	public function pageSizeSetup($get){
		$papers = Config::get('page');
		return [
			'papers' => $papers,
			'get' => $get,
		];
	}
}